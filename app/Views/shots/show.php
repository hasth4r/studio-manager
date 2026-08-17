<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="/admin/projects/<?= esc($shot->project_id) ?>" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center text-ytMuted">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-[24px] font-medium text-ytText">Shot: <?= esc($shot->shot_number) ?></h2>
                    <?php if($shot->sequence_name): ?>
                        <span class="bg-[#1a1a1a] text-ytMuted border border-ytBorder/50 px-2 py-0.5 rounded text-[11px] font-mono">Seq: <?= esc($shot->sequence_name) ?></span>
                    <?php endif; ?>
                </div>
                <p class="text-[13px] text-ytMuted mt-1">Project: <a href="/admin/projects/<?= esc($shot->project_id) ?>" class="text-ytBlue hover:underline"><?= esc($shot->project_name) ?></a></p>
            </div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('message')) : ?>
    <div class="bg-[#122a15] border border-green-900 text-green-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Details & Thumbnail -->
    <div class="col-span-1 space-y-6">
        <div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
            <div class="aspect-video bg-[#1a1a1a] relative">
                <?php if($shot->thumbnail_path): ?>
                    <img src="/<?= esc($shot->thumbnail_path) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-ytMuted">
                        <span class="material-symbols-outlined text-[48px]">image</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-5">
                <h3 class="text-[14px] font-medium text-ytText mb-2">Description</h3>
                <p class="text-[13px] text-ytMuted whitespace-pre-wrap"><?= esc($shot->description ?: 'No description provided.') ?></p>
            </div>
            
            <!-- Pipeline Metadata Overview -->
            <?php if(!empty($shot->comp_name) || !empty($shot->frame_in) || !empty($shot->width) || !empty($shot->timecode_in)): ?>
            <div class="px-5 py-3.5 bg-[#141414] border-t border-ytBorder/40 space-y-2">
                <div class="flex items-center justify-between text-[12px]">
                    <span class="text-ytMuted">Frame Range:</span>
                    <span class="font-mono text-ytText font-medium">
                        <?= esc($shot->frame_in ?? '-') ?> &ndash; <?= esc($shot->frame_out ?? '-') ?>
                        <span class="text-ytBlue font-normal">(<?= esc($shot->frame_count ?? '-') ?> fr)</span>
                    </span>
                </div>
                <?php if(!empty($shot->timecode_in)): ?>
                <div class="flex items-center justify-between text-[12px]">
                    <span class="text-ytMuted">Timecode:</span>
                    <span class="font-mono text-ytText"><?= esc($shot->timecode_in) ?> &ndash; <?= esc($shot->timecode_out) ?></span>
                </div>
                <?php endif; ?>
                <?php if(!empty($shot->width) && !empty($shot->height)): ?>
                <div class="flex items-center justify-between text-[12px]">
                    <span class="text-ytMuted">Resolution:</span>
                    <span class="font-mono text-ytText"><?= esc($shot->width) ?> &times; <?= esc($shot->height) ?></span>
                </div>
                <?php endif; ?>
                <?php if(!empty($shot->comp_name)): ?>
                <div class="text-[11px] pt-1 text-ytMuted truncate" title="<?= esc($shot->comp_name) ?>">
                    <span class="text-ytMuted">AE Comp:</span> <code class="text-ytBlue font-mono"><?= esc($shot->comp_name) ?></code>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="p-5 border-t border-ytBorder/50">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[14px] font-medium text-ytText">Shot Pipeline Settings</h3>
                </div>
                <form action="/admin/shots/updateSettings/<?= $shot->id ?>" method="POST" class="space-y-3">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-ytMuted mb-1">Frame Count</label>
                            <input type="number" name="frame_count" value="<?= esc($shot->frame_count ?? '') ?>" min="1" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="e.g. 75">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-ytMuted mb-1">FPS Override</label>
                            <input type="number" name="fps" value="<?= esc($shot->fps ?? '') ?>" min="1" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="Project: <?= esc($shot->project_fps ?? 24) ?>">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-ytMuted mb-1">Frame In</label>
                            <input type="number" name="frame_in" value="<?= esc($shot->frame_in ?? '') ?>" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="1001">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-ytMuted mb-1">Frame Out</label>
                            <input type="number" name="frame_out" value="<?= esc($shot->frame_out ?? '') ?>" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="1075">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-ytMuted mb-1">Timecode In</label>
                            <input type="text" name="timecode_in" value="<?= esc($shot->timecode_in ?? '') ?>" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="00:00:00:00">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-ytMuted mb-1">Timecode Out</label>
                            <input type="text" name="timecode_out" value="<?= esc($shot->timecode_out ?? '') ?>" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="00:00:03:00">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-ytMuted mb-1">Width (px)</label>
                            <input type="number" name="width" value="<?= esc($shot->width ?? '') ?>" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="3840">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-ytMuted mb-1">Height (px)</label>
                            <input type="number" name="height" value="<?= esc($shot->height ?? '') ?>" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="2160">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-ytMuted mb-1">AE Comp Name</label>
                        <input type="text" name="comp_name" value="<?= esc($shot->comp_name ?? '') ?>" class="w-full bg-[#1a1a1a] border border-ytBorder/50 text-ytText rounded px-3 py-1.5 focus:outline-none focus:border-ytBlue text-[13px]" placeholder="e.g. mhlya-1_war_sh0010_edit_v00">
                    </div>
                    <div class="text-right pt-2">
                        <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-1.5 rounded-full font-medium text-[12px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Tasks -->
    <div class="col-span-2">
        <div class="bg-ytCard border border-ytBorder rounded-xl p-5">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-[16px] font-medium text-ytText">Shot Tasks</h3>
                <div class="flex gap-2">
                    <form action="/admin/tasks/bulkRecalculate/<?= $shot->project_id ?>" method="POST" class="m-0 p-0">
                        <?= csrf_field() ?>
                        <button type="submit" title="Calculate missing hours for the entire project" class="bg-[#1a1a1a] text-ytText border border-ytBorder px-4 py-2 rounded-full font-medium text-[13px] hover:bg-ytHover transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">calculate</span> Bulk Calc
                        </button>
                    </form>
                    <button onclick="openModal('taskModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">
                        + Assign Task
                    </button>
                </div>
            </div>

            <?php if(empty($tasks)): ?>
                <div class="border border-ytBorder border-dashed rounded-lg p-8 text-center bg-[#1a1a1a]">
                    <span class="material-symbols-outlined text-[32px] text-ytMuted mb-2">task</span>
                    <p class="text-ytText font-medium text-[14px]">No tasks assigned</p>
                    <p class="text-ytMuted text-[12px] mt-1">Assign layout, animation, lighting, etc.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-ytBorder/50 text-ytMuted text-[12px] uppercase tracking-wider font-medium">
                                <th class="px-4 py-3">Task</th>
                                <th class="px-4 py-3">Complexity</th>
                                <th class="px-4 py-3">Est. Hrs</th>
                                <th class="px-4 py-3">Assignee</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-[14px] text-ytText divide-y divide-ytBorder/50">
                            <?php foreach($tasks as $task): ?>
                                <tr class="hover:bg-ytHover transition-colors">
                                    <td class="px-4 py-3 font-medium text-ytBlue"><?= esc($task->task_name) ?></td>
                                    <td class="px-4 py-3 text-ytMuted text-[13px]">
                                        <form action="/admin/tasks/updateComplexity" method="POST" class="m-0 p-0">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="task_id" value="<?= $task->id ?>">
                                            <select name="complexity" onchange="this.form.submit()" class="bg-[#1a1a1a] border border-transparent hover:border-ytBorder focus:bg-ytBg focus:border-ytBlue text-[11px] rounded px-1 py-0.5 outline-none text-ytMuted w-24 cursor-pointer transition-colors">
                                                <?php
                                                    $complexities = ['Simple', 'Medium', 'Complex'];
                                                    $current = $task->complexity ?? 'Medium';
                                                    foreach($complexities as $comp) {
                                                        $selected = ($current === $comp) ? 'selected' : '';
                                                        echo "<option value=\"{$comp}\" {$selected}>{$comp}</option>";
                                                    }
                                                ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-ytMuted">
                                        <?php
                                            // Build tooltip data
                                            $bm = $benchmarks[$task->task_type_id] ?? null;
                                            $comp = $task->complexity ?? 'Medium';
                                            $baseHrs = 0;
                                            if ($bm) {
                                                if ($comp === 'Simple')  $baseHrs = (float)$bm->simple_hours;
                                                if ($comp === 'Medium')  $baseHrs = (float)$bm->medium_hours;
                                                if ($comp === 'Complex') $baseHrs = (float)$bm->complex_hours;
                                            }
                                            $finalFps = $task->fps ?: ($shot->fps ?: ($shot->project_fps ?: 24));
                                            $finalFc  = $task->frame_count ?: ($shot->frame_count ?: null);
                                            $durMult  = ($finalFc && $finalFps > 0) ? round($finalFc / $finalFps, 4) : 0;
                                            $expMultiplier = 1.0;
                                            $expLabel = 'Mid (1.0×)';
                                            if (!empty($task->experience_level)) {
                                                if ($task->experience_level === 'Junior') { $expMultiplier = 1.5; $expLabel = 'Junior (1.5×)'; }
                                                if ($task->experience_level === 'Senior') { $expMultiplier = 0.8; $expLabel = 'Senior (0.8×)'; }
                                            }
                                            $estCalc = round($baseHrs * $durMult * $expMultiplier, 2);

                                            $tipLines = [];
                                            $tipLines[] = "Benchmark ({$comp}): " . ($bm ? "{$baseHrs} hrs" : '❌ No benchmark set');
                                            $tipLines[] = "Frames: " . ($finalFc ?? '❌ Not set') . " ÷ {$finalFps} fps = {$durMult}s";
                                            $tipLines[] = "Artist: {$expLabel}";
                                            $tipLines[] = "= {$baseHrs} × {$durMult} × {$expMultiplier} = {$estCalc} hrs";
                                            $tipText = implode('\n', $tipLines);
                                        ?>
                                        <div class="flex items-center gap-1.5">
                                            <span class="<?= empty($task->estimated_hours) ? 'text-ytMuted' : 'text-ytText font-medium' ?>">
                                                <?= empty($task->estimated_hours) ? '-' : esc($task->estimated_hours) . ' hrs' ?>
                                            </span>
                                            <div class="relative group/tip">
                                                <span class="material-symbols-outlined text-[14px] text-ytMuted/50 hover:text-ytBlue cursor-help transition-colors select-none">info</span>
                                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-[#1a1a1a] border border-ytBorder rounded-lg p-3 text-[11px] leading-relaxed hidden group-hover/tip:block z-50 shadow-xl pointer-events-none">
                                                    <p class="text-ytBlue font-semibold mb-2">Calculation Breakdown</p>
                                                    <?php if (!$bm): ?>
                                                        <p class="text-red-400">⚠ No benchmark set for this task type. Go to Project → Benchmarks tab.</p>
                                                    <?php else: ?>
                                                        <div class="space-y-1 text-ytMuted">
                                                            <div class="flex justify-between"><span>Benchmark (<?= $comp ?>):</span><span class="text-ytText"><?= $baseHrs ?> hrs</span></div>
                                                            <?php if ($finalFc): ?>
                                                                <div class="flex justify-between"><span>Duration:</span><span class="text-ytText"><?= $finalFc ?> ÷ <?= $finalFps ?> fps = <?= $durMult ?>s</span></div>
                                                            <?php else: ?>
                                                                <div class="text-yellow-400">⚠ No frame count set on shot</div>
                                                            <?php endif; ?>
                                                            <div class="flex justify-between"><span>Artist level:</span><span class="text-ytText"><?= $expLabel ?></span></div>
                                                            <div class="border-t border-ytBorder/50 mt-1.5 pt-1.5 flex justify-between font-semibold"><span class="text-ytMuted">Result:</span><span class="text-ytBlue"><?= $estCalc ?> hrs</span></div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-ytBorder"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-ytMuted">
                                        <form action="/admin/tasks/updateAssignee" method="POST" class="m-0 p-0">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="task_id" value="<?= $task->id ?>">
                                            <select name="assigned_to" onchange="this.form.submit()" class="bg-transparent border border-transparent hover:border-ytBorder hover:bg-[#1a1a1a] focus:bg-ytBg focus:border-ytBlue text-[13px] rounded px-2 py-1 outline-none text-ytText w-32 cursor-pointer transition-colors">
                                                <option value="" class="italic text-ytMuted">(Unassigned)</option>
                                                <?php foreach($users as $user): ?>
                                                    <option value="<?= $user->id ?>" <?= $task->assigned_to == $user->id ? 'selected' : '' ?>>
                                                        <?= esc($user->name) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if($task->status === 'pending'): ?>
                                            <span class="bg-[#2a2a12] text-yellow-400 border border-yellow-900/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">Pending</span>
                                        <?php elseif($task->status === 'in_progress'): ?>
                                            <span class="bg-[#121c2a] text-blue-400 border border-blue-900/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">In Progress</span>
                                        <?php elseif($task->status === 'ready_for_review'): ?>
                                            <span class="bg-[#2a122a] text-purple-400 border border-purple-900/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">Ready For Review</span>
                                        <?php elseif($task->status === 'revision_needed'): ?>
                                            <span class="bg-[#2a1212] text-red-400 border border-red-900/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">Revision Needed</span>
                                        <?php elseif($task->status === 'completed'): ?>
                                            <span class="bg-[#122a15] text-green-400 border border-green-900/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <button onclick="openTaskSettings(<?= $task->id ?>, <?= $task->frame_count ?? 'null' ?>, <?= $task->fps ?? 'null' ?>)" class="text-ytMuted hover:text-ytBlue p-1 transition-colors" title="Task Settings">
                                            <span class="material-symbols-outlined text-[16px]">settings</span>
                                        </button>
                                        <?php if(empty($task->estimated_hours)): ?>
                                            <form action="/admin/tasks/recalculate/<?= $task->id ?>" method="POST" class="m-0 p-0 inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" title="Calculate Hours" class="text-ytMuted hover:text-ytBlue p-1 transition-colors"><span class="material-symbols-outlined text-[16px]">calculate</span></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if(isset($task->pending_review)): ?>
                                            <a href="/admin/reviews/player/<?= $task->pending_review->id ?>" class="inline-flex items-center bg-[#1a122a] border border-purple-700 text-purple-300 px-3 py-1 rounded-full font-semibold text-[11px] hover:bg-purple-900/50 transition-colors mx-1 gap-1 shadow-[0_0_10px_rgba(126,34,206,0.2)]">
                                                <span class="material-symbols-outlined text-[14px]">rate_review</span> Open Player
                                            </a>
                                        <?php elseif($task->status === 'ready_for_review' && in_array(session()->get('userRole'), ['admin', 'project_manager'])): ?>
                                            <form action="/admin/tasks/reviewStatus/<?= $task->id ?>" method="POST" class="m-0 p-0 inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="bg-[#122a15] border border-green-900 text-green-400 px-2 py-1 rounded-full font-medium text-[11px] hover:bg-green-900/50 transition-colors mx-1">Approve</button>
                                            </form>
                                            <form action="/admin/tasks/reviewStatus/<?= $task->id ?>" method="POST" class="m-0 p-0 inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="revision_needed">
                                                <button type="submit" class="bg-[#2a1212] border border-red-900 text-red-400 px-2 py-1 rounded-full font-medium text-[11px] hover:bg-red-900/50 transition-colors">Revise</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL: Assign Task -->
<div id="taskModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-md mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Assign Task</h3>
            <button type="button" onclick="closeModal('taskModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/tasks/store" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $shot->project_id ?>">
            <input type="hidden" name="shot_id" value="<?= $shot->id ?>">
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Task Type <span class="text-ytRed">*</span></label>
                <select name="task_type_id" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                    <option value="" disabled selected>Select a task...</option>
                    <?php foreach($taskTypes as $type): ?>
                        <option value="<?= $type->id ?>"><?= esc($type->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Complexity</label>
                <select name="complexity" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                    <option value="Simple">Simple</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="Complex">Complex</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Frame Count (Override)</label>
                    <input type="number" name="frame_count" min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="Default: <?= esc($shot->frame_count ?? 'N/A') ?>">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">FPS (Override)</label>
                    <input type="number" name="fps" min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="Default: <?= esc($shot->fps ?? 'Project Default') ?>">
                </div>
            </div>

            <div class="mb-4">
                <p class="text-[11px] text-ytMuted mt-1">Used with project benchmarks to auto-calculate estimated hours.</p>
            </div>

            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Assign To</label>
                <select name="assigned_to" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                    <option value="">(Unassigned)</option>
                    <?php foreach($users as $user): ?>
                        <option value="<?= $user->id ?>"><?= esc($user->name) ?> (<?= esc($user->global_role ?? 'User') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('taskModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Add Task</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Task Settings -->
<div id="taskSettingsModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-sm mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[16px] font-medium text-ytText">Task Settings</h3>
            <button type="button" onclick="closeModal('taskSettingsModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/tasks/updateSettings" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="task_id" id="ts_task_id">
            
            <div class="mb-4">
                <label class="block text-[12px] font-medium text-ytText mb-2">Frame Count (Override)</label>
                <input type="number" name="frame_count" id="ts_frame_count" min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 focus:outline-none focus:border-ytBlue" placeholder="Default: <?= esc($shot->frame_count ?? 'N/A') ?>">
            </div>
            <div class="mb-6">
                <label class="block text-[12px] font-medium text-ytText mb-2">FPS (Override)</label>
                <input type="number" name="fps" id="ts_fps" min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 focus:outline-none focus:border-ytBlue" placeholder="Default: <?= esc($shot->fps ?? 'Project Default') ?>">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('taskSettingsModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
    function openTaskSettings(taskId, frameCount, fps) {
        document.getElementById('ts_task_id').value = taskId;
        document.getElementById('ts_frame_count').value = frameCount !== null ? frameCount : '';
        document.getElementById('ts_fps').value = fps !== null ? fps : '';
        openModal('taskSettingsModal');
    }
</script>

<?= $this->endSection() ?>
