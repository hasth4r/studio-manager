<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('message')) : ?>
    <div class="bg-[#122a15] border border-green-900 text-green-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="bg-[#2a1215] border border-red-900 text-red-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">error</span>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-indigo-400 border border-indigo-900/50">
                <span class="material-symbols-outlined">category</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Project Types</h2>
                <p class="text-[13px] text-ytMuted mt-1">Available categories for projects</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-ytBorder/50 text-[13px] text-ytMuted bg-[#1a1a1a]">
                            <th class="px-6 py-3 font-medium">Name</th>
                            <th class="px-6 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-[14px] divide-y divide-ytBorder/50 text-ytText">
                        <?php if(empty($project_types)): ?>
                            <tr>
                                <td colspan="2" class="px-6 py-8 text-center text-ytMuted text-[13px]">No project types found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($project_types as $pt): ?>
                                <tr class="hover:bg-ytHover transition-colors">
                                    <td class="px-6 py-4 font-medium"><?= esc($pt->name) ?></td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="text-ytMuted hover:text-ytText transition-colors p-1">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <form action="/project-types/store" method="POST" class="bg-ytCard border border-ytBorder rounded-xl p-6">
            <?= csrf_field() ?>
            <h3 class="text-[15px] font-medium text-ytText mb-4">Add Project Type</h3>
            
            <div class="mb-4">
                <label for="name" class="block text-[13px] font-medium text-ytText mb-2">Name</label>
                <input type="text" name="name" id="name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue transition-colors font-light text-[14px]" placeholder="e.g. Virtual Production">
            </div>

            <button type="submit" class="w-full bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">Add Type</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
