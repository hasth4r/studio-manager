<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Sticky Header & Action Bar -->
<div class="sticky top-0 z-40 bg-ytBg/95 backdrop-blur-md pt-4 pb-3 border-b border-ytBorder/60 space-y-3">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center space-x-3">
            <a href="/admin/projects/<?= esc($project->id) ?>" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center text-ytMuted hover:text-ytText" title="Back to Project View">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-[20px] font-semibold text-ytText leading-tight"><?= esc($project->name) ?></h2>
                    <span class="bg-ytCard border border-ytBorder text-ytBlue px-2.5 py-0.5 rounded-full text-[11px] font-mono font-medium">Shot Breakdown Matrix</span>
                </div>
                <div class="flex items-center gap-3 text-[12px] text-ytMuted mt-0.5 font-mono">
                    <span>Shots: <b class="text-ytText"><?= count($shots) ?></b></span>
                    <span>&bull;</span>
                    <span>Sequences: <b class="text-ytText"><?= count($sequences) ?></b></span>
                    <span>&bull;</span>
                    <span>Total Est: <b class="text-ytBlue font-bold" id="headerProjTotalHours"><?= round($totalProjectHours, 1) ?></b> hrs</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="/admin/projects/<?= $project->id ?>/briefing" class="bg-[#181818] border border-indigo-500/40 hover:border-indigo-400 text-indigo-200 px-3 py-1.5 rounded-lg font-medium text-[12px] hover:bg-indigo-950/40 transition-all flex items-center gap-1.5 shadow-[0_0_12px_rgba(99,102,241,0.15)]" title="Open Client Shot Briefing & Reference Matrix">
                <span class="material-symbols-outlined text-[16px] text-indigo-400">edit_note</span>
                <span>Shot Briefing</span>
            </a>
            <button type="button" onclick="autoGenerateAllThumbnails()" class="bg-[#181818] border border-purple-500/40 hover:border-purple-400 text-purple-200 px-3 py-1.5 rounded-lg font-medium text-[12px] hover:bg-purple-950/40 transition-all flex items-center gap-1.5 shadow-[0_0_12px_rgba(168,85,247,0.15)]" title="Auto-extract crisp mid-frame WebP thumbnails from all shot videos">
                <span class="material-symbols-outlined text-[16px] text-purple-400">photo_camera</span>
                <span>Auto-Gen WebP</span>
            </button>
            <button type="button" id="toggleMetaBtn" onclick="toggleMetadataColumns()" class="bg-ytCard border border-ytBorder hover:border-ytText text-ytText px-3.5 py-1.5 rounded-lg font-medium text-[12px] hover:bg-ytHover transition-all flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-ytBlue" id="toggleMetaIcon">visibility_off</span>
                <span id="toggleMetaText">Hide Metadata</span>
            </button>
            <form action="/admin/tasks/bulkRecalculate/<?= $project->id ?>" method="POST" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" title="Recalculate benchmark hours for all shot tasks" class="bg-ytCard border border-ytBorder hover:border-ytBlue text-ytText px-3.5 py-1.5 rounded-lg font-medium text-[12px] hover:bg-ytHover transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-ytBlue">calculate</span> Recalc Hours
                </button>
            </form>
            <a href="/admin/projects/<?= $project->id ?>" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-1.5 rounded-lg font-medium text-[12px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-all flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">grid_view</span> Card View
            </a>
        </div>
    </div>

    <!-- Filters & Bulk Operations Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 pt-1">
        <!-- Search & Filters -->
        <div class="flex items-center gap-2 flex-1 max-w-xl">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-ytMuted text-[16px]">search</span>
                <input type="text" id="shotSearchInput" oninput="filterShotsTable()" placeholder="Search shots, comp name, description..." class="w-full bg-ytCard border border-ytBorder text-ytText rounded-lg pl-8 pr-3 py-1.5 text-[12px] focus:outline-none focus:border-ytBlue placeholder:text-ytMuted/50 font-mono">
            </div>
            <select id="sequenceFilterSelect" onchange="filterShotsTable()" class="bg-ytCard border border-ytBorder text-ytText rounded-lg px-3 py-1.5 text-[12px] focus:outline-none focus:border-ytBlue font-mono">
                <option value="">All Sequences (<?= count($sequences) ?>)</option>
                <?php foreach($sequences as $seq): ?>
                    <option value="<?= esc($seq->id) ?>"><?= esc($seq->name) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="taskStatusFilterSelect" onchange="filterShotsTable()" class="bg-ytCard border border-ytBorder text-ytText rounded-lg px-3 py-1.5 text-[12px] focus:outline-none focus:border-ytBlue font-mono">
                <option value="all">All Shots</option>
                <option value="no_tasks">Shots With 0 Tasks</option>
                <option value="unassigned">Shots With Unassigned Tasks</option>
            </select>
        </div>

        <!-- Bulk Action Bar (Visible when >= 1 shot selected) -->
        <div id="bulkActionBar" class="flex items-center gap-2 bg-[#181818] border border-ytBlue/40 px-3 py-1 rounded-lg shadow-sm">
            <span class="text-[12px] text-ytText font-mono font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-ytBlue text-[16px]">check_circle</span>
                <span id="selectedShotsCounter">0</span> selected
            </span>
            <span class="text-ytBorder">|</span>
            
            <!-- Bulk Add Task Form -->
            <form id="bulkAddTasksForm" onsubmit="submitBulkAssign(event)" class="flex items-center gap-2 m-0">
                <?= csrf_field() ?>
                <input type="hidden" name="project_id" value="<?= $project->id ?>">
                
                <select name="task_type_id" required class="bg-ytBg border border-ytBorder text-ytText rounded px-2 py-1 text-[11px] focus:outline-none focus:border-ytBlue">
                    <option value="">+ Select Task Type...</option>
                    <?php foreach($taskTypes as $tt): ?>
                        <option value="<?= $tt->id ?>"><?= esc($tt->name) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="complexity" class="bg-ytBg border border-ytBorder text-ytText rounded px-2 py-1 text-[11px] focus:outline-none focus:border-ytBlue">
                    <option value="Simple">Simple</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="Complex">Complex</option>
                </select>

                <select name="assigned_to" class="bg-ytBg border border-ytBorder text-ytText rounded px-2 py-1 text-[11px] focus:outline-none focus:border-ytBlue">
                    <option value="">(Unassigned)</option>
                    <?php foreach($users as $u): ?>
                        <option value="<?= $u->id ?>"><?= esc($u->name) ?> (<?= esc($u->experience_level ?? 'mid') ?>)</option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" id="btnBulkApply" class="bg-ytBlue hover:bg-blue-600 text-white font-medium px-3 py-1 rounded text-[11px] flex items-center gap-1 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[14px]">bolt</span> Apply to Selected
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Spreadsheet Container -->
<div class="mt-4 pb-20">
    <div class="bg-ytCard border border-ytBorder rounded-xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto max-h-[calc(100vh-210px)] overflow-y-auto">
            <table class="w-full text-left border-collapse" id="breakdownTable">
                <thead class="sticky top-0 z-30 bg-[#141414] border-b border-ytBorder/80 text-[11px] uppercase tracking-wider text-ytMuted font-semibold select-none">
                    <tr>
                        <th class="py-2.5 px-2 w-8 text-center">
                            <input type="checkbox" id="selectAllShotsCheckbox" onchange="toggleSelectAllShots(this.checked)" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0 cursor-pointer">
                        </th>
                        <th class="py-2.5 px-2 w-16 text-center">Thumb</th>
                        <th class="py-2.5 px-2.5 w-28">Shot &amp; Seq</th>
                        
                        <!-- Metadata Columns (Collapsible) -->
                        <th class="py-2.5 px-1.5 w-16 text-center meta-col">Frame In</th>
                        <th class="py-2.5 px-1.5 w-16 text-center meta-col">Frame Out</th>
                        <th class="py-2.5 px-1.5 w-14 text-center meta-col">Frames</th>
                        <th class="py-2.5 px-1.5 w-12 text-center meta-col">FPS</th>
                        <th class="py-2.5 px-2 w-28 meta-col">Timecode</th>
                        <th class="py-2.5 px-2 w-20 meta-col">Resolution</th>
                        <th class="py-2.5 px-2 w-32 meta-col">AE Comp Name</th>
                        
                        <!-- Compact Meta summary (Shows when columns hidden) -->
                        <th class="py-2.5 px-3 w-40 meta-summary-col hidden">Timing &amp; Specs</th>

                        <!-- Task Pipeline Matrix -->
                        <th class="py-2.5 px-3 min-w-[320px]">Assigned Tasks &amp; Benchmark Estimates</th>
                        <th class="py-2.5 px-3 w-20 text-right">Shot Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ytBorder/30 text-[12px]">
                    <?php if(empty($shots)): ?>
                        <tr>
                            <td colspan="12" class="py-12 text-center text-ytMuted">
                                <span class="material-symbols-outlined text-[36px] mb-2">movie</span>
                                <p class="text-ytText font-medium">No shots in this project yet.</p>
                                <p class="text-[12px] mt-1">Import shots via AE Essentials CSV or Add Single Shot.</p>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach($shots as $shot): 
                        $shotTasks = $tasksByShot[$shot->id] ?? [];
                        $shotTotal = 0.0;
                        foreach($shotTasks as $st) {
                            $shotTotal += (float)($st->estimated_hours ?? 0);
                        }
                    ?>
                    <tr class="shot-row hover:bg-[#181818] transition-colors group" 
                        id="shot-row-<?= $shot->id ?>"
                        data-shot-id="<?= $shot->id ?>"
                        data-shot-number="<?= strtolower(esc($shot->shot_number)) ?>"
                        data-seq-id="<?= $shot->sequence_id ?? '' ?>"
                        data-comp-name="<?= strtolower(esc($shot->comp_name ?? '')) ?>"
                        data-task-count="<?= count($shotTasks) ?>"
                        data-has-unassigned="<?= !empty(array_filter($shotTasks, fn($t) => empty($t->assigned_to))) ? '1' : '0' ?>">
                        
                        <!-- 1. Selection Checkbox -->
                        <td class="py-1.5 px-2 text-center align-middle">
                            <input type="checkbox" value="<?= $shot->id ?>" onchange="updateSelectionState()" class="shot-checkbox rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0 cursor-pointer">
                        </td>

                        <!-- 2. Thumbnail & Floating Video Preview Trigger -->
                        <td class="py-1.5 px-2 text-center align-middle">
                            <div class="w-14 h-9 bg-[#111] rounded border border-ytBorder/60 overflow-hidden relative group/thumb cursor-pointer shrink-0 inline-block align-middle"
                                 onmouseenter="showFloatingPreview(event, '<?= !empty($shot->preview_video_path) ? base_url(esc($shot->preview_video_path)) : '' ?>', '<?= !empty($shot->thumbnail_path) ? base_url(esc($shot->thumbnail_path)) : '' ?>', '<?= esc($shot->shot_number) ?>')"
                                 onmousemove="moveFloatingPreview(event)"
                                 onmouseleave="hideFloatingPreview()"
                                 onclick="openVideoModal('<?= !empty($shot->preview_video_path) ? base_url(esc($shot->preview_video_path)) : '' ?>', '<?= esc($shot->shot_number) ?>')"
                                 title="Click to open full player">
                                <?php if($shot->thumbnail_path): ?>
                                    <img src="<?= base_url(esc($shot->thumbnail_path)) ?>" loading="lazy" class="shot-thumb-img-<?= $shot->id ?> w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-ytMuted">
                                        <span class="material-symbols-outlined text-[16px]">image</span>
                                    </div>
                                <?php endif; ?>

                                <?php if(!empty($shot->preview_video_path)): ?>
                                    <span class="thumb-play-tag absolute bottom-0.5 right-0.5 bg-blue-600 hover:bg-blue-500 text-white text-[8px] font-bold px-1 rounded flex items-center shadow-md">
                                        ▶
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 3. Shot Number & Sequence (Compact Stack) -->
                        <td class="py-1.5 px-2.5 align-middle">
                            <div class="flex flex-col justify-center">
                                <input type="text" value="<?= esc($shot->shot_number) ?>" 
                                       onchange="inlineUpdateShot(<?= $shot->id ?>, 'shot_number', this.value)"
                                       class="bg-transparent border-b border-transparent hover:border-ytBorder focus:border-ytBlue text-ytText font-bold text-[12px] px-1 py-0 rounded focus:bg-ytBg w-22 focus:outline-none transition-all font-mono leading-tight">
                                <span class="text-[10px] text-ytMuted font-mono px-1 truncate max-w-[120px] inline-flex items-center gap-1 mt-0.5" title="<?= esc($shot->sequence_name ?? 'Independent') ?>">
                                    <span class="material-symbols-outlined text-[12px] text-ytMuted shrink-0">folder</span>
                                    <span><?= esc($shot->sequence_name ?? 'Independent') ?></span>
                                </span>
                            </div>
                        </td>

                        <!-- 4. Frame In (Meta) -->
                        <td class="py-1.5 px-1.5 align-middle meta-col text-center">
                            <input type="number" value="<?= esc($shot->frame_in ?? '') ?>" 
                                   placeholder="1001"
                                   onchange="inlineUpdateShot(<?= $shot->id ?>, 'frame_in', this.value)"
                                   class="bg-ytBg/60 border border-ytBorder/40 hover:border-ytBorder focus:border-ytBlue text-ytText text-[11px] font-mono px-1 py-0.5 rounded w-14 focus:outline-none text-center">
                        </td>

                        <!-- 5. Frame Out (Meta) -->
                        <td class="py-1.5 px-1.5 align-middle meta-col text-center">
                            <input type="number" value="<?= esc($shot->frame_out ?? '') ?>" 
                                   placeholder="1075"
                                   onchange="inlineUpdateShot(<?= $shot->id ?>, 'frame_out', this.value)"
                                   class="bg-ytBg/60 border border-ytBorder/40 hover:border-ytBorder focus:border-ytBlue text-ytText text-[11px] font-mono px-1 py-0.5 rounded w-14 focus:outline-none text-center">
                        </td>

                        <!-- 6. Frames Count (Meta) -->
                        <td class="py-1.5 px-1.5 align-middle meta-col text-center">
                            <input type="number" value="<?= esc($shot->frame_count ?? '') ?>" 
                                   placeholder="75"
                                   onchange="inlineUpdateShot(<?= $shot->id ?>, 'frame_count', this.value)"
                                   class="bg-ytBg border border-ytBorder/60 hover:border-ytBlue focus:border-ytBlue text-ytText font-semibold text-[11px] font-mono px-1 py-0.5 rounded w-12 focus:outline-none text-center">
                        </td>

                        <!-- 7. FPS (Meta) -->
                        <td class="py-1.5 px-1.5 align-middle meta-col text-center">
                            <input type="number" value="<?= esc($shot->fps ?? $project->fps ?? 24) ?>" 
                                   placeholder="25"
                                   onchange="inlineUpdateShot(<?= $shot->id ?>, 'fps', this.value)"
                                   class="bg-ytBg/60 border border-ytBorder/40 hover:border-ytBorder focus:border-ytBlue text-ytText text-[11px] font-mono px-1 py-0.5 rounded w-11 focus:outline-none text-center">
                        </td>

                        <!-- 8. Timecode In/Out (Meta) -->
                        <td class="py-1.5 px-1.5 align-middle meta-col text-[10px] font-mono text-ytMuted space-y-0.5">
                            <input type="text" value="<?= esc($shot->timecode_in ?? '') ?>" placeholder="00:00:00:00"
                                   onchange="inlineUpdateShot(<?= $shot->id ?>, 'timecode_in', this.value)"
                                   class="bg-transparent border-b border-transparent hover:border-ytBorder focus:border-ytBlue text-ytText text-[10px] font-mono px-1 py-0 w-22 focus:bg-ytBg focus:outline-none block">
                            <input type="text" value="<?= esc($shot->timecode_out ?? '') ?>" placeholder="00:00:03:00"
                                   onchange="inlineUpdateShot(<?= $shot->id ?>, 'timecode_out', this.value)"
                                   class="bg-transparent border-b border-transparent hover:border-ytBorder focus:border-ytBlue text-ytText text-[10px] font-mono px-1 py-0 w-22 focus:bg-ytBg focus:outline-none block">
                        </td>

                        <!-- 9. Resolution (Meta) -->
                        <td class="py-1.5 px-2 align-middle meta-col text-[11px] font-mono text-ytText text-center">
                            <?php if(!empty($shot->width) && !empty($shot->height)): ?>
                                <span><?= esc($shot->width) ?>&times;<?= esc($shot->height) ?></span>
                            <?php else: ?>
                                <span class="text-ytMuted">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- 10. AE Comp Name (Meta) -->
                        <td class="py-1.5 px-2 align-middle meta-col text-[11px] font-mono text-ytMuted truncate max-w-[140px]" title="<?= esc($shot->comp_name ?? '') ?>">
                            <?= esc($shot->comp_name ?: '-') ?>
                        </td>

                        <!-- Compact Meta Summary (Shown when columns collapsed) -->
                        <td class="py-1.5 px-3 align-middle meta-summary-col hidden text-[11px] font-mono space-y-1">
                            <div class="text-ytText font-medium">
                                <?= !empty($shot->frame_in) && !empty($shot->frame_out) ? esc($shot->frame_in) . '–' . esc($shot->frame_out) : '' ?>
                                <span class="text-ytBlue">(<?= esc($shot->frame_count ?? '-') ?> fr)</span>
                            </div>
                            <div class="text-ytMuted text-[10px]">
                                <?= esc($shot->fps ?? $project->fps ?? 24) ?> FPS &bull; <?= esc($shot->width && $shot->height ? $shot->width . 'x' . $shot->height : '') ?>
                            </div>
                        </td>

                        <!-- 11. Assigned Tasks Matrix Column -->
                        <td class="py-1.5 px-3 align-middle">
                            <div class="space-y-1" id="shot-tasks-container-<?= $shot->id ?>">
                                <?php if(empty($shotTasks)): ?>
                                    <span class="text-[10px] text-ytMuted italic no-tasks-label">No tasks assigned.</span>
                                <?php else: ?>
                                    <?php foreach($shotTasks as $task): ?>
                                        <div class="flex flex-wrap items-center gap-1.5 bg-[#121212] border border-ytBorder/50 rounded px-2 py-0.5 text-[11px] group/task hover:border-ytBorder transition-all" id="task-item-<?= $task->id ?>">
                                            
                                            <!-- Task Type Badge -->
                                            <span class="font-semibold text-[10px] text-ytBlue w-18 truncate" title="<?= esc($task->task_type_name) ?>">
                                                <?= esc($task->task_type_name) ?>
                                            </span>

                                            <!-- Assignee Inline Dropdown -->
                                            <select onchange="inlineUpdateTask(<?= $task->id ?>, 'assigned_to', this.value, <?= $shot->id ?>)" class="bg-ytBg border border-ytBorder/40 hover:border-ytBorder text-ytText rounded px-1.5 py-0.5 text-[10px] focus:outline-none focus:border-ytBlue">
                                                <option value="">(Unassigned)</option>
                                                <?php foreach($users as $u): ?>
                                                    <option value="<?= $u->id ?>" <?= $task->assigned_to == $u->id ? 'selected' : '' ?>>
                                                        <?= esc($u->name) ?> (<?= esc($u->experience_level ?? 'mid') ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <!-- Complexity Inline Dropdown -->
                                            <select onchange="inlineUpdateTask(<?= $task->id ?>, 'complexity', this.value, <?= $shot->id ?>)" class="bg-ytBg border border-ytBorder/40 hover:border-ytBorder text-ytText rounded px-1 py-0.5 text-[10px] font-medium focus:outline-none focus:border-ytBlue">
                                                <option value="Simple" <?= $task->complexity === 'Simple' ? 'selected' : '' ?>>🟢 Simple</option>
                                                <option value="Medium" <?= ($task->complexity === 'Medium' || empty($task->complexity)) ? 'selected' : '' ?>>🟡 Medium</option>
                                                <option value="Complex" <?= $task->complexity === 'Complex' ? 'selected' : '' ?>>🔴 Complex</option>
                                            </select>

                                            <!-- Status Inline Dropdown -->
                                            <select onchange="inlineUpdateTask(<?= $task->id ?>, 'status', this.value, <?= $shot->id ?>)" class="bg-ytBg border border-ytBorder/40 hover:border-ytBorder text-ytText rounded px-1 py-0.5 text-[10px] focus:outline-none focus:border-ytBlue">
                                                <option value="pending" <?= $task->status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="in_progress" <?= $task->status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="review" <?= $task->status === 'review' ? 'selected' : '' ?>>Review</option>
                                                <option value="approved" <?= $task->status === 'approved' ? 'selected' : '' ?>>Approved</option>
                                            </select>

                                            <!-- Auto-calculated Hours Badge -->
                                            <span class="bg-[#1f2937] border border-blue-500/30 text-blue-300 px-1.5 py-0.5 rounded font-mono font-bold text-[10px]" id="task-hours-<?= $task->id ?>" title="Estimated hours based on project benchmarks">
                                                <?= round($task->estimated_hours ?? 0, 1) ?>h
                                            </span>

                                            <!-- Delete Task Button -->
                                            <button type="button" onclick="deleteTaskAjax(<?= $task->id ?>, <?= $shot->id ?>)" class="text-ytMuted hover:text-red-400 p-0.5 opacity-0 group-hover/task:opacity-100 transition-opacity ml-auto" title="Delete task">
                                                <span class="material-symbols-outlined text-[13px]">delete</span>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Row-Level Add Task Quick Dropdown -->
                            <div class="mt-1 flex items-center gap-1">
                                <select onchange="handleRowQuickAddTask(this, <?= $shot->id ?>, <?= $project->id ?>)" class="bg-transparent border border-dashed border-ytBorder/60 hover:border-ytBlue text-ytMuted hover:text-ytText rounded px-1.5 py-0.5 text-[9px] focus:outline-none focus:bg-ytBg transition-colors cursor-pointer">
                                    <option value="" selected>+ Add task...</option>
                                    <?php foreach($taskTypes as $tt): ?>
                                        <option value="<?= $tt->id ?>"><?= esc($tt->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>

                        <!-- 12. Shot Total Hours Sum -->
                        <td class="py-1.5 px-3 text-right align-middle">
                            <span class="text-[12px] font-bold font-mono text-ytText bg-[#111] px-2 py-0.5 rounded border border-ytBorder/40 inline-block" id="shot-total-hours-<?= $shot->id ?>">
                                <?= round($shotTotal, 1) ?>h
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating Toast Notification for Inline Saves -->
<div id="inlineSaveToast" class="fixed bottom-5 right-5 z-50 hidden bg-[#16381d] border border-green-700/80 text-green-200 px-4 py-2.5 rounded-xl shadow-2xl text-[12px] font-medium flex items-center gap-2 transition-all backdrop-blur-md">
    <span class="material-symbols-outlined text-green-400 text-[18px]">check_circle</span>
    <span id="inlineToastText">Saved</span>
</div>

<script>
    // 1. Toggle Extended Metadata Columns (Expand / Collapse)
    let metadataCollapsed = localStorage.getItem('breakdownMetaCollapsed') === 'true';

    function initMetadataVisibility() {
        const metaCols = document.querySelectorAll('.meta-col');
        const summaryCols = document.querySelectorAll('.meta-summary-col');
        const icon = document.getElementById('toggleMetaIcon');
        const text = document.getElementById('toggleMetaText');

        if (metadataCollapsed) {
            metaCols.forEach(el => el.classList.add('hidden'));
            summaryCols.forEach(el => el.classList.remove('hidden'));
            icon.textContent = 'visibility';
            text.textContent = 'Expand Metadata';
        } else {
            metaCols.forEach(el => el.classList.remove('hidden'));
            summaryCols.forEach(el => el.classList.add('hidden'));
            icon.textContent = 'visibility_off';
            text.textContent = 'Hide Metadata';
        }
    }

    function toggleMetadataColumns() {
        metadataCollapsed = !metadataCollapsed;
        localStorage.setItem('breakdownMetaCollapsed', metadataCollapsed);
        initMetadataVisibility();
    }

    document.addEventListener('DOMContentLoaded', () => {
        initMetadataVisibility();
        updateSelectionState();
    });

    // 2. Real-time Search & Filters
    function filterShotsTable() {
        const search = document.getElementById('shotSearchInput').value.toLowerCase().trim();
        const seqId = document.getElementById('sequenceFilterSelect').value;
        const taskStatus = document.getElementById('taskStatusFilterSelect').value;

        const rows = document.querySelectorAll('.shot-row');
        rows.forEach(row => {
            const shotNum = row.getAttribute('data-shot-number') || '';
            const compName = row.getAttribute('data-comp-name') || '';
            const rowSeqId = row.getAttribute('data-seq-id') || '';
            const taskCount = parseInt(row.getAttribute('data-task-count') || '0', 10);
            const hasUnassigned = row.getAttribute('data-has-unassigned') === '1';

            let matchSearch = !search || shotNum.includes(search) || compName.includes(search);
            let matchSeq = !seqId || rowSeqId === seqId;
            let matchTask = true;

            if (taskStatus === 'no_tasks') {
                matchTask = (taskCount === 0);
            } else if (taskStatus === 'unassigned') {
                matchTask = hasUnassigned;
            }

            if (matchSearch && matchSeq && matchTask) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // 3. Selection Checkboxes & Bulk Action Bar
    function toggleSelectAllShots(checked) {
        const checkboxes = document.querySelectorAll('.shot-checkbox');
        checkboxes.forEach(cb => {
            const row = cb.closest('.shot-row');
            if (row && row.style.display !== 'none') {
                cb.checked = checked;
            }
        });
        updateSelectionState();
    }

    function updateSelectionState() {
        const checked = document.querySelectorAll('.shot-checkbox:checked');
        const counter = document.getElementById('selectedShotsCounter');
        const bar = document.getElementById('bulkActionBar');

        counter.textContent = checked.length;
        if (checked.length > 0) {
            bar.classList.remove('opacity-40', 'pointer-events-none');
            bar.classList.add('border-ytBlue');
        } else {
            bar.classList.add('opacity-40', 'pointer-events-none');
            bar.classList.remove('border-ytBlue');
        }
    }

    // 4. Bulk Task Assignment
    async function submitBulkAssign(e) {
        e.preventDefault();
        const checked = Array.from(document.querySelectorAll('.shot-checkbox:checked')).map(cb => cb.value);
        if (checked.length === 0) {
            alert('Please select at least one shot.');
            return;
        }

        const form = document.getElementById('bulkAddTasksForm');
        const formData = new FormData(form);
        formData.append('shot_ids', checked.join(','));

        const btn = document.getElementById('btnBulkApply');
        const origBtnText = btn.innerHTML;
        btn.innerHTML = '<span class="material-symbols-outlined text-[14px] animate-spin">sync</span> Applying...';
        btn.disabled = true;

        try {
            const res = await fetch('/admin/projects/bulkAssignTasks', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                // Reload breakdown after 600ms to refresh all rows
                setTimeout(() => window.location.reload(), 600);
            } else {
                alert(data.message || 'Bulk assignment failed.');
            }
        } catch (err) {
            console.error(err);
            alert('Network error while assigning tasks.');
        } finally {
            btn.innerHTML = origBtnText;
            btn.disabled = false;
        }
    }

    // 5. Inline Task Updates (AJAX Auto-Save)
    async function inlineUpdateTask(taskId, field, value, shotId) {
        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('field', field);
        formData.append('value', value);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('/admin/projects/inlineUpdateTask', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                // Update hours badge
                const hoursBadge = document.getElementById(`task-hours-${taskId}`);
                if (hoursBadge) hoursBadge.textContent = `${data.estimated_hours}h`;

                // Update shot total
                const shotTotalBadge = document.getElementById(`shot-total-hours-${shotId}`);
                if (shotTotalBadge) shotTotalBadge.textContent = `${data.shot_total_hours}h`;

                // Update header total
                const projTotalHeader = document.getElementById('headerProjTotalHours');
                if (projTotalHeader) projTotalHeader.textContent = data.proj_total_hours;

                showToast(`Task ${field} updated & recalculated!`);
            }
        } catch (err) {
            console.error('Inline task update error:', err);
        }
    }

    // 6. Inline Shot Updates (AJAX Auto-Save)
    async function inlineUpdateShot(shotId, field, value) {
        const formData = new FormData();
        formData.append('shot_id', shotId);
        formData.append('field', field);
        formData.append('value', value);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('/admin/projects/inlineUpdateShot', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                if (data.updated_tasks && data.updated_tasks.length > 0) {
                    data.updated_tasks.forEach(t => {
                        const hb = document.getElementById(`task-hours-${t.id}`);
                        if (hb) hb.textContent = `${t.hours}h`;
                    });
                }
                const shotTotalBadge = document.getElementById(`shot-total-hours-${shotId}`);
                if (shotTotalBadge && data.shot_total_hours !== undefined) {
                    shotTotalBadge.textContent = `${data.shot_total_hours}h`;
                }
                showToast(`Shot ${field} saved!`);
            }
        } catch (err) {
            console.error('Inline shot update error:', err);
        }
    }

    // 7. Row-Level Quick Add Task
    async function handleRowQuickAddTask(selectEl, shotId, projectId) {
        const taskTypeId = selectEl.value;
        if (!taskTypeId) return;

        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('shot_id', shotId);
        formData.append('task_type_id', taskTypeId);
        formData.append('complexity', 'Medium');
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        selectEl.disabled = true;

        try {
            const res = await fetch('/admin/projects/inlineAddTask', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                showToast('Task added successfully!');
                setTimeout(() => window.location.reload(), 400);
            }
        } catch (err) {
            console.error('Add task error:', err);
        } finally {
            selectEl.value = '';
            selectEl.disabled = false;
        }
    }

    // 8. Delete Task via AJAX
    async function deleteTaskAjax(taskId, shotId) {
        if (!confirm('Are you sure you want to delete this task?')) return;

        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('/admin/projects/deleteTaskAjax', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                const item = document.getElementById(`task-item-${taskId}`);
                if (item) item.remove();

                const shotTotalBadge = document.getElementById(`shot-total-hours-${shotId}`);
                if (shotTotalBadge && data.shot_total_hours !== undefined) {
                    shotTotalBadge.textContent = `${data.shot_total_hours}h`;
                }
                showToast('Task deleted.');
            }
        } catch (err) {
            console.error('Delete task error:', err);
        }
    }

    // 9. Toast Notification Helper
    let toastTimeout = null;
    function showToast(msg) {
        const toast = document.getElementById('inlineSaveToast');
        const text = document.getElementById('inlineToastText');
        if (!toast) return;

        text.textContent = msg;
        toast.classList.remove('hidden');
        toast.classList.add('opacity-100');

        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.add('hidden');
        }, 2200);
    }

    // 10. Quick Video Player Modal with Loading Buffer Indicator
    function openVideoModal(videoUrl, shotTitle) {
        const modal = document.getElementById('quickVideoModal');
        const video = document.getElementById('quickVideoPlayer');
        const title = document.getElementById('quickVideoTitle');
        const loader = document.getElementById('quickVideoLoader');
        if (!modal || !video) return;

        title.textContent = `Preview: Shot ${shotTitle}`;
        if (loader) {
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }

        video.onplaying = () => {
            if (loader) {
                loader.classList.remove('opacity-100');
                loader.classList.add('opacity-0', 'pointer-events-none');
            }
        };
        video.onwaiting = () => {
            if (loader) {
                loader.classList.remove('opacity-0', 'pointer-events-none');
                loader.classList.add('opacity-100');
            }
        };

        video.src = videoUrl;
        modal.classList.remove('hidden');
        video.play().catch(() => {});
    }

    function closeVideoModal() {
        const modal = document.getElementById('quickVideoModal');
        const video = document.getElementById('quickVideoPlayer');
        const loader = document.getElementById('quickVideoLoader');
        if (!modal || !video) return;

        video.pause();
        video.currentTime = 0;
        video.src = '';
        video.onplaying = null;
        video.onwaiting = null;
        if (loader) {
            loader.classList.remove('opacity-100');
            loader.classList.add('opacity-0', 'pointer-events-none');
        }
        modal.classList.add('hidden');
    }

    // 11. Auto-Generate WebP Thumbnails from Video Mid-Frames
    const shotsWithVideos = <?= json_encode(array_values(array_filter(array_map(function($s) {
        return !empty($s->preview_video_path) ? [
            'id' => $s->id,
            'shot_number' => $s->shot_number,
            'video_url' => base_url($s->preview_video_path)
        ] : null;
    }, $shots ?? [])))) ?>;

    async function autoGenerateAllThumbnails() {
        if (!shotsWithVideos || shotsWithVideos.length === 0) {
            alert('No shots with video previews found in this project. Please upload videos first.');
            return;
        }

        if (!confirm(`Extract crisp mid-frame WebP thumbnails for all ${shotsWithVideos.length} shot videos?`)) {
            return;
        }

        const btn = event.currentTarget;
        const origText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75');
        }

        let successCount = 0;
        const canvas = document.createElement('canvas');
        canvas.width = 640;
        canvas.height = 360;
        const ctx = canvas.getContext('2d');

        for (let i = 0; i < shotsWithVideos.length; i++) {
            const item = shotsWithVideos[i];
            if (btn) {
                btn.innerHTML = `<span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span> Extracting ${i + 1}/${shotsWithVideos.length}...`;
            }

            try {
                const dataUrl = await extractVideoMidFrame(item.video_url, canvas, ctx);
                if (dataUrl) {
                    const formData = new FormData();
                    formData.append('shot_id', item.id);
                    formData.append('image_data', dataUrl);

                    const res = await fetch('/admin/projects/saveAutoThumbnailAjax', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const result = await res.json();
                    if (result.success && result.thumbnail_url) {
                        successCount++;
                        document.querySelectorAll(`.shot-thumb-img-${item.id}`).forEach(img => {
                            img.src = result.thumbnail_url + '?t=' + Date.now();
                        });
                    }
                }
            } catch (err) {
                console.warn(`Failed extracting thumbnail for ${item.shot_number}:`, err);
            }
        }

        if (btn) {
            btn.innerHTML = `<span class="material-symbols-outlined text-[16px] text-green-400">check_circle</span> Done (${successCount} WebP)!`;
            setTimeout(() => {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
                btn.innerHTML = origText;
                window.location.reload();
            }, 1200);
        }
    }

    function extractVideoMidFrame(videoUrl, canvas, ctx) {
        return new Promise((resolve, reject) => {
            const video = document.createElement('video');
            video.crossOrigin = 'anonymous';
            video.muted = true;
            video.playsInline = true;
            video.preload = 'auto';

            const timeout = setTimeout(() => {
                video.src = '';
                reject(new Error('Seek timeout'));
            }, 12000);

            video.onloadedmetadata = () => {
                const midTime = video.duration > 0 ? video.duration / 2 : 0.5;
                video.currentTime = midTime;
            };

            video.onseeked = () => {
                clearTimeout(timeout);
                try {
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const dataUrl = canvas.toDataURL('image/webp', 0.85);
                    video.src = '';
                    resolve(dataUrl);
                } catch (err) {
                    reject(err);
                }
            };

            video.onerror = () => {
                clearTimeout(timeout);
                reject(new Error('Video load error'));
            };

            video.src = videoUrl;
        });
    }
    // Thumbnail-Anchored Floating Hover Video Preview Engine
    let hoverTimeout = null;

    function showFloatingPreview(e, videoSrc, thumbSrc, shotNumber) {
        clearTimeout(hoverTimeout);
        const box = document.getElementById('globalHoverPreview');
        const vid = document.getElementById('globalHoverVideo');
        const img = document.getElementById('globalHoverImg');
        const loader = document.getElementById('globalHoverLoader');
        const tag = document.getElementById('globalHoverTag');
        if (!box) return;

        // Anchor directly over the thumbnail and shot & seq area (X = rect.left)
        const target = e.currentTarget;
        const rect = target.getBoundingClientRect();
        const boxWidth = 270;
        const boxHeight = 152;

        let left = Math.max(10, rect.left);
        let top = rect.top - 10;

        if (top + boxHeight > window.innerHeight - 10) {
            top = Math.max(10, window.innerHeight - boxHeight - 10);
        }
        if (top < 10) top = 10;

        box.style.top = top + 'px';
        box.style.left = left + 'px';
        if (tag) tag.innerText = shotNumber ? shotNumber.toUpperCase() : 'PREVIEW';

        if (videoSrc) {
            if (loader) {
                loader.classList.remove('opacity-0', 'pointer-events-none');
                loader.classList.add('opacity-100');
            }
            img.classList.add('hidden');
            vid.classList.remove('hidden');
            box.classList.remove('hidden');

            vid.onplaying = () => {
                if (loader) {
                    loader.classList.remove('opacity-100');
                    loader.classList.add('opacity-0', 'pointer-events-none');
                }
            };
            vid.onwaiting = () => {
                if (loader) {
                    loader.classList.remove('opacity-0', 'pointer-events-none');
                    loader.classList.add('opacity-100');
                }
            };

            vid.src = videoSrc;
            vid.play().catch(() => {});
        } else if (thumbSrc) {
            if (loader) loader.classList.add('opacity-0', 'pointer-events-none');
            vid.classList.add('hidden');
            img.src = thumbSrc;
            img.classList.remove('hidden');
            box.classList.remove('hidden');
        }
    }

    function hideFloatingPreview() {
        hoverTimeout = setTimeout(() => {
            const box = document.getElementById('globalHoverPreview');
            const vid = document.getElementById('globalHoverVideo');
            const loader = document.getElementById('globalHoverLoader');
            if (box) box.classList.add('hidden');
            if (vid) {
                vid.pause();
                vid.currentTime = 0;
                vid.src = '';
                vid.onplaying = null;
                vid.onwaiting = null;
            }
            if (loader) {
                loader.classList.remove('opacity-100');
                loader.classList.add('opacity-0', 'pointer-events-none');
            }
        }, 100);
    }

    // Quick Video Player Modal Engine
    function openVideoModal(videoUrl, shotTitle) {
        if (!videoUrl) return;
        const modal = document.getElementById('quickVideoModal');
        const video = document.getElementById('quickVideoPlayer');
        const title = document.getElementById('quickVideoTitle');
        const loader = document.getElementById('quickVideoLoader');
        if (!modal || !video) return;

        title.textContent = `Preview: Shot ${shotTitle}`;
        if (loader) {
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }

        video.onplaying = () => {
            if (loader) {
                loader.classList.remove('opacity-100');
                loader.classList.add('opacity-0', 'pointer-events-none');
            }
        };
        video.onwaiting = () => {
            if (loader) {
                loader.classList.remove('opacity-0', 'pointer-events-none');
                loader.classList.add('opacity-100');
            }
        };

        video.src = videoUrl;
        modal.classList.remove('hidden');
        video.play().catch(() => {});
    }

    function closeVideoModal() {
        const modal = document.getElementById('quickVideoModal');
        const video = document.getElementById('quickVideoPlayer');
        const loader = document.getElementById('quickVideoLoader');
        if (!modal || !video) return;

        video.pause();
        video.currentTime = 0;
        video.src = '';
        video.onplaying = null;
        video.onwaiting = null;
        if (loader) {
            loader.classList.remove('opacity-100');
            loader.classList.add('opacity-0', 'pointer-events-none');
        }
        modal.classList.add('hidden');
    }
</script>

<!-- Single Global Floating Hover Preview with Premium Glassmorphism & Glowing Loader -->
<div id="globalHoverPreview" class="fixed hidden z-50 pointer-events-none shadow-[0_12px_40px_rgba(0,0,0,0.9)] rounded-xl overflow-hidden border border-blue-500/60 bg-[#080808] w-72 aspect-video backdrop-blur-md transition-opacity duration-150">
    <!-- Sleek Glowing Loading Spinner -->
    <div id="globalHoverLoader" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/75 backdrop-blur-xs transition-opacity duration-300">
        <div class="relative w-8 h-8 flex items-center justify-center">
            <div class="absolute inset-0 rounded-full border-2 border-blue-500/20"></div>
            <div class="absolute inset-0 rounded-full border-2 border-t-blue-400 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
            <span class="material-symbols-outlined text-[13px] text-blue-400 animate-pulse">play_arrow</span>
        </div>
        <span class="text-[9px] text-blue-300/80 font-mono mt-1.5 uppercase tracking-widest font-semibold">Buffering...</span>
    </div>
    <video id="globalHoverVideo" class="w-full h-full object-cover hidden" muted loop playsinline></video>
    <img id="globalHoverImg" class="w-full h-full object-cover hidden" src="">
    <div id="globalHoverTag" class="absolute bottom-1.5 left-2 bg-black/85 backdrop-blur-md text-blue-300 font-bold text-[10px] font-mono px-2 py-0.5 rounded border border-blue-500/30 z-30 shadow-lg"></div>
</div>

<!-- Quick Video Player Modal with Loading Animation -->
<div id="quickVideoModal" class="fixed inset-0 z-50 hidden bg-black/85 backdrop-blur-md flex items-center justify-center p-4" onclick="if(event.target===this) closeVideoModal()">
    <div class="bg-ytCard border border-ytBorder rounded-2xl overflow-hidden shadow-2xl max-w-3xl w-full">
        <div class="px-5 py-3.5 border-b border-ytBorder/60 flex items-center justify-between bg-[#111]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-ytBlue text-[20px]">play_circle</span>
                <h4 id="quickVideoTitle" class="text-[14px] font-bold text-ytText font-mono">Video Preview</h4>
            </div>
            <button type="button" onclick="closeVideoModal()" class="text-ytMuted hover:text-ytText p-1 rounded-full hover:bg-ytHover transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div class="aspect-video bg-black relative flex items-center justify-center">
            <!-- Modal Loading Buffer Indicator -->
            <div id="quickVideoLoader" class="absolute inset-0 flex flex-col items-center justify-center bg-black/60 z-10 transition-opacity duration-300 pointer-events-none">
                <div class="relative w-10 h-10 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border-2 border-blue-500/20"></div>
                    <div class="absolute inset-0 rounded-full border-2 border-t-blue-400 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
                    <span class="material-symbols-outlined text-[16px] text-blue-400">smart_display</span>
                </div>
                <span class="text-[11px] text-blue-300 font-mono mt-2 tracking-wider">Loading Video Stream...</span>
            </div>
            <video id="quickVideoPlayer" controls playsinline class="w-full h-full object-contain"></video>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
