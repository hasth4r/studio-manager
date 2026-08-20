<?php

namespace App\Libraries;

/**
 * SchedulerEngine v2 — Industry-grade constraint solver
 *
 * Features:
 *  - Deadline-driven BACKWARD scheduling
 *  - Forward scheduling fallback
 *  - Per-artist capacity (weekly_hours from users table)
 *  - Holiday calendar awareness (skips registered holidays + weekends)
 *  - Auto-estimation from frame_count × complexity × benchmark_hours_per_second
 *  - Locked task (is_undocked) preservation
 *  - Critical path detection via longest-path algorithm
 *  - Topological sort for dependency ordering
 */
class SchedulerEngine
{
    private array $holidays = [];  // [Y-m-d => true]
    private array $artistCapacity = []; // [artist_id => daily_hours]

    // ─── PUBLIC INTERFACE ──────────────────────────────────────────────────────

    public function autoSchedule(int $projectId, bool $backwards = false): array
    {
        $db = \Config\Database::connect();

        // Load holidays into a lookup
        $this->loadHolidays($db);

        // Load per-artist daily capacity
        $this->loadArtistCapacity($db);

        // Fetch project deadline
        $project = $db->table('projects')->where('id', $projectId)->get()->getRow();
        $deadline = (!empty($project->deadline)) ? new \DateTime($project->deadline) : null;

        // Use backward mode only if we have a deadline
        if ($backwards && !$deadline) {
            $backwards = false;
        }

        // 1. Fetch all non-approved tasks
        $rawTasks = $db->table('tasks')
            ->select('tasks.*, task_types.benchmark_hours_per_second')
            ->join('task_types', 'task_types.id = tasks.task_type_id', 'left')
            ->where('tasks.project_id', $projectId)
            ->where('tasks.status !=', 'approved')
            ->get()->getResultArray();

        if (empty($rawTasks)) {
            return [];
        }

        // 2. Normalise fields & auto-estimate hours if 0/null
        $taskMap = [];
        foreach ($rawTasks as $task) {
            $task['dependencies']        = json_decode($task['dependencies'] ?? '[]', true) ?? [];
            $task['estimated_hours']     = $this->resolveHours($task);
            $task['priority_percentage'] = (int)($task['priority_percentage'] ?? 50);
            $task['is_undocked']         = (int)($task['is_undocked'] ?? 0);
            $taskMap[$task['id']]        = $task;
        }

        // 3. Sort by priority (high first)
        uasort($taskMap, fn($a, $b) => $b['priority_percentage'] <=> $a['priority_percentage']);

        // 4. Topological sort (dependencies first)
        $sortedIds = $this->topologicalSort($taskMap);

        // 5. Schedule
        $scheduled = $backwards && $deadline
            ? $this->scheduleBackward($sortedIds, $taskMap, $deadline)
            : $this->scheduleForward($sortedIds, $taskMap);

        // 6. Detect critical path
        $criticalPath = $this->detectCriticalPath($taskMap, $scheduled);

        // Annotate with critical flag
        foreach ($scheduled as &$t) {
            $t['is_critical'] = in_array($t['id'], $criticalPath);
        }

        return $scheduled;
    }

    // ─── ESTIMATION ────────────────────────────────────────────────────────────

    private function resolveHours(array $task): float
    {
        $manual = (float)($task['estimated_hours'] ?? 0);
        if ($manual > 0) return $manual;

        // Auto-estimate: (frame_count / fps) × benchmark_hours_per_second
        $frameCount = (int)($task['frame_count'] ?? 0);
        $fps        = max(1, (float)($task['fps'] ?? 24));
        $benchmark  = (float)($task['benchmark_hours_per_second'] ?? 0.5);

        if ($frameCount > 0) {
            return round(($frameCount / $fps) * $benchmark, 2);
        }

        return 8.0; // Default fallback
    }

    // ─── FORWARD SCHEDULING ────────────────────────────────────────────────────

    private function scheduleForward(array $sortedIds, array $taskMap): array
    {
        $artistCalendars = []; // [artistId][Y-m-d] => hours_booked
        $taskEndDates    = []; // [taskId] => DateTime
        $scheduled       = [];

        $globalStart = $this->nextWorkday(new \DateTime('today'));

        foreach ($sortedIds as $taskId) {
            $task = $taskMap[$taskId];

            // Locked task — keep as-is
            if ($task['is_undocked'] && !empty($task['start_date'])) {
                $scheduled[]             = $this->lockedEntry($task);
                $taskEndDates[$taskId]   = new \DateTime($task['end_date']);
                continue;
            }

            $artistId     = $task['assigned_to'] ?? 'unassigned';
            $dailyCap     = $this->artistDailyCap($artistId);
            $hoursNeeded  = $task['estimated_hours'];

            if (!isset($artistCalendars[$artistId])) $artistCalendars[$artistId] = [];

            // Earliest start = max(globalStart, latest dependency end)
            $earliest = clone $globalStart;
            foreach ($task['dependencies'] as $depId) {
                if (isset($taskEndDates[$depId]) && $taskEndDates[$depId] > $earliest) {
                    $earliest = clone $taskEndDates[$depId];
                    $earliest = $this->nextWorkday($earliest);
                }
            }

            [$taskStart, $taskEnd, $artistCalendars] = $this->bookForward(
                $earliest, $hoursNeeded, $artistId, $dailyCap, $artistCalendars
            );

            $scheduled[]           = $this->entry($taskId, $taskStart, $taskEnd, $hoursNeeded);
            $taskEndDates[$taskId] = $taskEnd;
        }

        return $scheduled;
    }

    // ─── BACKWARD SCHEDULING ───────────────────────────────────────────────────

    private function scheduleBackward(array $sortedIds, array $taskMap, \DateTime $deadline): array
    {
        // For backward: process in REVERSE topological order
        $reversedIds = array_reverse($sortedIds);

        $artistCalendars  = [];
        $taskStartDates   = []; // [taskId] => DateTime (must start no later than)
        $scheduled        = [];

        $globalEnd = $this->prevWorkday(clone $deadline);

        foreach ($reversedIds as $taskId) {
            $task = $taskMap[$taskId];

            if ($task['is_undocked'] && !empty($task['start_date'])) {
                $scheduled[]              = $this->lockedEntry($task);
                $taskStartDates[$taskId]  = new \DateTime($task['start_date']);
                continue;
            }

            $artistId    = $task['assigned_to'] ?? 'unassigned';
            $dailyCap    = $this->artistDailyCap($artistId);
            $hoursNeeded = $task['estimated_hours'];

            if (!isset($artistCalendars[$artistId])) $artistCalendars[$artistId] = [];

            // Latest end = min(globalEnd, earliest dependent start - 1 day)
            $latestEnd = clone $globalEnd;
            foreach ($taskMap as $otherTask) {
                if (in_array($taskId, $otherTask['dependencies'])) {
                    if (isset($taskStartDates[$otherTask['id']])) {
                        $depStart = clone $taskStartDates[$otherTask['id']];
                        $depStart->modify('-1 day');
                        $depStart = $this->prevWorkday($depStart);
                        if ($depStart < $latestEnd) $latestEnd = $depStart;
                    }
                }
            }

            [$taskStart, $taskEnd, $artistCalendars] = $this->bookBackward(
                $latestEnd, $hoursNeeded, $artistId, $dailyCap, $artistCalendars
            );

            $scheduled[]              = $this->entry($taskId, $taskStart, $taskEnd, $hoursNeeded);
            $taskStartDates[$taskId]  = $taskStart;
        }

        // Re-sort by start date ascending for Gantt rendering
        usort($scheduled, fn($a, $b) => strcmp($a['start_date'], $b['start_date']));
        return $scheduled;
    }

    // ─── BOOKING HELPERS ───────────────────────────────────────────────────────

    private function bookForward(\DateTime $from, float $hoursNeeded, $artistId, float $dailyCap, array $calendars): array
    {
        $cur   = $this->nextWorkday(clone $from);
        $start = clone $cur;
        $remaining = $hoursNeeded;

        while ($remaining > 0) {
            $key = $cur->format('Y-m-d');
            $booked    = $calendars[$artistId][$key] ?? 0;
            $available = $dailyCap - $booked;

            if ($available > 0) {
                $book = min($available, $remaining);
                $calendars[$artistId][$key] = $booked + $book;
                $remaining -= $book;
            }

            if ($remaining > 0) {
                $cur->modify('+1 day');
                $cur = $this->nextWorkday($cur);
            }
        }

        return [$start, clone $cur, $calendars];
    }

    private function bookBackward(\DateTime $from, float $hoursNeeded, $artistId, float $dailyCap, array $calendars): array
    {
        $cur   = $this->prevWorkday(clone $from);
        $end   = clone $cur;
        $remaining = $hoursNeeded;

        while ($remaining > 0) {
            $key = $cur->format('Y-m-d');
            $booked    = $calendars[$artistId][$key] ?? 0;
            $available = $dailyCap - $booked;

            if ($available > 0) {
                $book = min($available, $remaining);
                $calendars[$artistId][$key] = $booked + $book;
                $remaining -= $book;
            }

            if ($remaining > 0) {
                $cur->modify('-1 day');
                $cur = $this->prevWorkday($cur);
            }
        }

        return [clone $cur, $end, $calendars];
    }

    // ─── CRITICAL PATH ────────────────────────────────────────────────────────

    private function detectCriticalPath(array $taskMap, array $scheduled): array
    {
        // Build a map of scheduled hours per task
        $hoursMap = [];
        foreach ($scheduled as $s) {
            $hoursMap[$s['id']] = $s['estimated_hours'];
        }

        // Longest path (in hours) from each task forward
        $longest = [];
        $ids     = array_keys($taskMap);

        foreach ($ids as $id) {
            $longest[$id] = $this->longestPath($id, $taskMap, $hoursMap, []);
        }

        // The critical path is the chain ending with the maximum total
        $maxTotal   = max($longest ?: [0]);
        $criticalIds = [];

        foreach ($ids as $id) {
            if ($longest[$id] === $maxTotal) {
                $criticalIds[] = $id;
                // Walk dependencies
                $this->walkCritical($id, $taskMap, $hoursMap, $longest, $criticalIds);
            }
        }

        return array_unique($criticalIds);
    }

    private function longestPath(int $id, array $taskMap, array $hoursMap, array $visited): float
    {
        if (in_array($id, $visited)) return 0;
        $visited[] = $id;

        $myHours = $hoursMap[$id] ?? 0;
        $maxDep  = 0;

        foreach ($taskMap as $other) {
            if (in_array($id, $other['dependencies'])) {
                $sub = $this->longestPath($other['id'], $taskMap, $hoursMap, $visited);
                if ($sub > $maxDep) $maxDep = $sub;
            }
        }

        return $myHours + $maxDep;
    }

    private function walkCritical(int $id, array $taskMap, array $hoursMap, array $longest, array &$out): void
    {
        foreach ($taskMap[$id]['dependencies'] ?? [] as $depId) {
            if (!in_array($depId, $out)) {
                $out[] = $depId;
                $this->walkCritical($depId, $taskMap, $hoursMap, $longest, $out);
            }
        }
    }

    // ─── TOPOLOGICAL SORT ─────────────────────────────────────────────────────

    private function topologicalSort(array $taskMap): array
    {
        $sorted   = [];
        $visited  = [];
        $visiting = [];

        $visit = function(int $id) use (&$visit, &$sorted, &$visited, &$visiting, $taskMap): void {
            if (isset($visited[$id]) || isset($visiting[$id])) return;
            $visiting[$id] = true;

            foreach ($taskMap[$id]['dependencies'] ?? [] as $depId) {
                if (isset($taskMap[$depId])) $visit($depId);
            }

            unset($visiting[$id]);
            $visited[$id] = true;
            $sorted[]     = $id;
        };

        foreach (array_keys($taskMap) as $id) {
            $visit($id);
        }

        // sorted is post-order; reverse = dependencies before their dependents
        return array_reverse($sorted);
    }

    // ─── UTILITY ──────────────────────────────────────────────────────────────

    private function nextWorkday(\DateTime $d): \DateTime
    {
        while ((int)$d->format('N') >= 6 || $this->isHoliday($d)) {
            $d->modify('+1 day');
        }
        return $d;
    }

    private function prevWorkday(\DateTime $d): \DateTime
    {
        while ((int)$d->format('N') >= 6 || $this->isHoliday($d)) {
            $d->modify('-1 day');
        }
        return $d;
    }

    private function isHoliday(\DateTime $d): bool
    {
        return isset($this->holidays[$d->format('Y-m-d')]);
    }

    private function artistDailyCap($artistId): float
    {
        $weeklyHours = $this->artistCapacity[$artistId] ?? 40;
        return $weeklyHours / 5; // 5-day week → daily cap
    }

    private function loadHolidays($db): void
    {
        $rows = $db->table('holidays')->get()->getResultArray();
        foreach ($rows as $row) {
            $this->holidays[$row['holiday_date']] = true;
        }
    }

    private function loadArtistCapacity($db): void
    {
        $users = $db->table('users')->select('id, weekly_hours')->get()->getResultArray();
        foreach ($users as $u) {
            $this->artistCapacity[$u['id']] = (int)($u['weekly_hours'] ?? 40);
        }
    }

    private function entry(int $id, \DateTime $start, \DateTime $end, float $hours): array
    {
        return [
            'id'              => $id,
            'start_date'      => $start->format('Y-m-d 09:00:00'),
            'end_date'        => $end->format('Y-m-d 17:00:00'),
            'estimated_hours' => $hours,
        ];
    }

    private function lockedEntry(array $task): array
    {
        return [
            'id'              => $task['id'],
            'start_date'      => $task['start_date'],
            'end_date'        => $task['end_date'],
            'estimated_hours' => $task['estimated_hours'],
        ];
    }
}
