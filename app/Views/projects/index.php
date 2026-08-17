<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('message')) : ?>
    <div class="bg-[#122a15] border border-green-900 text-green-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-blue-400 border border-blue-900/50">
                <span class="material-symbols-outlined">movie</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">All Projects</h2>
                <p class="text-[13px] text-ytMuted mt-1">Manage production pipeline</p>
            </div>
        </div>
        <?php if(in_array(session()->get('userRole'), ['admin', 'project_manager'])): ?>
            <a href="/admin/projects/create" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors flex items-center">
                <span class="material-symbols-outlined mr-1 text-[18px]">add</span> New Project
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-ytBorder/50 text-[13px] text-ytMuted bg-[#1a1a1a]">
                    <th class="px-6 py-3 font-medium">Project Name</th>
                    <th class="px-6 py-3 font-medium">Client</th>
                    <th class="px-6 py-3 font-medium">Type</th>
                    <th class="px-6 py-3 font-medium">Budget &amp; Hours</th>
                    <th class="px-6 py-3 font-medium">FPS</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Priority</th>
                    <th class="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[14px] divide-y divide-ytBorder/50 text-ytText">
                <?php if(empty($projects)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-ytMuted text-[13px]">No projects found. Click "New Project" to get started.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($projects as $project): ?>
                        <tr class="hover:bg-ytHover transition-colors cursor-pointer" onclick="window.location.href='/admin/projects/<?= $project->id ?>'">
                            <td class="px-6 py-4 font-medium text-ytBlue" onclick="window.location.href='/admin/projects/<?= $project->id ?>'"><?= esc($project->name) ?></td>
                            <td class="px-6 py-4 text-ytMuted" onclick="window.location.href='/admin/projects/<?= $project->id ?>'"><?= esc($project->client_name ?: 'Unknown Client') ?></td>
                            <td class="px-6 py-4 text-ytMuted capitalize" onclick="window.location.href='/admin/projects/<?= $project->id ?>'"><?= esc($project->project_type_name ?: 'Unknown') ?></td>
                            <td class="px-6 py-4 font-mono text-[12px]">
                                <div class="flex flex-col gap-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-ytBlue font-bold"><?= $project->total_hours ?>h</span>
                                        <span class="text-ytMuted">&bull;</span>
                                        <span class="text-ytText font-semibold"><?= esc($studioCurrency) ?><?= number_format($project->ideal_budget, 0) ?></span>
                                    </div>
                                    <?php if(!empty($project->agreed_budget) && (float)$project->agreed_budget > 0): ?>
                                        <div class="flex items-center gap-1 text-[10px]">
                                            <span class="text-green-400 font-bold">Locked: <?= esc($studioCurrency) ?><?= number_format($project->agreed_budget, 0) ?></span>
                                            <span class="bg-green-950/80 border border-green-700/50 text-green-300 px-1 rounded font-bold">(<?= $project->scale_percent ?>%)</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-ytMuted">
                                <form action="/admin/projects/updateSettings/<?= $project->id ?>" method="POST" class="m-0 p-0 inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="return_url" value="/admin/projects">
                                    <select name="fps" onchange="this.form.submit()" class="bg-transparent border border-transparent hover:border-ytBorder hover:bg-[#1a1a1a] focus:bg-ytBg focus:border-ytBlue text-[13px] rounded px-1 py-0.5 outline-none text-ytText w-20 cursor-pointer transition-colors" onclick="event.stopPropagation()">
                                        <?php
                                            $commonFps = [23, 24, 25, 30, 48, 50, 60];
                                            $currentFps = $project->fps ?? 24;
                                            if (!in_array($currentFps, $commonFps)) {
                                                $commonFps[] = $currentFps;
                                                sort($commonFps);
                                            }
                                            foreach($commonFps as $f) {
                                                $selected = ($currentFps == $f) ? 'selected' : '';
                                                echo "<option value=\"{$f}\" {$selected}>{$f}</option>";
                                            }
                                        ?>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <form action="/admin/projects/updateSettings/<?= $project->id ?>" method="POST" class="m-0 p-0 inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="return_url" value="/admin/projects">
                                    <select name="status" onchange="this.form.submit()" class="bg-transparent border border-transparent hover:border-ytBorder hover:bg-[#1a1a1a] focus:bg-ytBg focus:border-ytBlue text-[11px] uppercase tracking-wider font-medium rounded px-1 py-0.5 outline-none text-ytText w-28 cursor-pointer transition-colors" onclick="event.stopPropagation()">
                                        <option value="active" <?= $project->status === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="completed" <?= $project->status === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="on_hold" <?= $project->status === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                                        <option value="archived" <?= $project->status === 'archived' ? 'selected' : '' ?>>Archived</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <form action="/admin/projects/updateSettings/<?= $project->id ?>" method="POST" class="m-0 p-0 inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="return_url" value="/admin/projects">
                                    <select name="priority" onchange="this.form.submit()" class="bg-transparent border border-transparent hover:border-ytBorder hover:bg-[#1a1a1a] focus:bg-ytBg focus:border-ytBlue text-[12px] font-medium rounded px-1 py-0.5 outline-none text-ytText w-24 cursor-pointer transition-colors" onclick="event.stopPropagation()">
                                        <option value="high" <?= $project->priority === 'high' ? 'selected' : '' ?> class="text-red-400">High</option>
                                        <option value="normal" <?= $project->priority === 'normal' ? 'selected' : '' ?> class="text-ytMuted">Normal</option>
                                        <option value="low" <?= $project->priority === 'low' ? 'selected' : '' ?> class="text-blue-400">Low</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5" onclick="event.stopPropagation()">
                                    <a href="/admin/projects/<?= $project->id ?>/breakdown" class="bg-ytCard border border-ytBorder hover:border-ytBlue text-ytText hover:text-ytBlue px-2.5 py-1 rounded text-[11px] font-medium transition-colors" title="Open Shot Breakdown Matrix">
                                        Matrix
                                    </a>
                                    <a href="/admin/projects/<?= $project->id ?>" class="text-ytMuted hover:text-ytText transition-colors p-1" title="View Project Cards">
                                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
