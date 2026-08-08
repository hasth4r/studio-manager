<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center space-x-4">
        <a href="/admin/projects/<?= esc($asset->project_id) ?>" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center text-ytMuted">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <div class="flex items-center space-x-3">
                <h2 class="text-[24px] font-medium text-ytText">Asset: <?= esc($asset->name) ?></h2>
                <span class="bg-[#1a1a1a] text-ytMuted border border-ytBorder/50 px-2 py-0.5 rounded text-[11px] font-mono uppercase"><?= esc($asset->type) ?></span>
            </div>
            <p class="text-[13px] text-ytMuted mt-1">Project: <a href="/admin/projects/<?= esc($asset->project_id) ?>" class="text-ytBlue hover:underline"><?= esc($asset->project_name) ?></a></p>
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
            <div class="aspect-square bg-[#1a1a1a] relative">
                <?php if($asset->thumbnail_path): ?>
                    <img src="/<?= esc($asset->thumbnail_path) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-ytMuted">
                        <span class="material-symbols-outlined text-[48px]">view_in_ar</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-5">
                <h3 class="text-[14px] font-medium text-ytText mb-2">Description</h3>
                <p class="text-[13px] text-ytMuted whitespace-pre-wrap"><?= esc($asset->description ?: 'No description provided.') ?></p>
            </div>
        </div>
    </div>

    <!-- Right Column: Tasks -->
    <div class="col-span-2">
        <div class="bg-ytCard border border-ytBorder rounded-xl p-5">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-[16px] font-medium text-ytText">Asset Tasks</h3>
                <div class="flex gap-2">
                    <form action="/admin/tasks/bulkRecalculate/<?= $asset->project_id ?>" method="POST" class="m-0 p-0">
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
                    <p class="text-ytMuted text-[12px] mt-1">Assign modeling, texturing, rigging, etc.</p>
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
                                        <?= empty($task->estimated_hours) ? '-' : esc($task->estimated_hours) ?>
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
                                        <?php if(empty($task->estimated_hours)): ?>
                                            <form action="/admin/tasks/recalculate/<?= $task->id ?>" method="POST" class="m-0 p-0 inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" title="Calculate Hours" class="text-ytMuted hover:text-ytBlue p-1 transition-colors"><span class="material-symbols-outlined text-[16px]">calculate</span></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if($task->status === 'ready_for_review' && in_array(session()->get('userRole'), ['admin', 'project_manager'])): ?>
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
            <input type="hidden" name="project_id" value="<?= $asset->project_id ?>">
            <input type="hidden" name="asset_id" value="<?= $asset->id ?>">
            
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

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>

<?= $this->endSection() ?>
