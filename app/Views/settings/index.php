<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-gray-400 border border-gray-700/50">
                <span class="material-symbols-outlined">settings</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Server Settings</h2>
                <p class="text-[13px] text-ytMuted mt-1">Configure global server and pipeline settings.</p>
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

<div class="space-y-6 max-w-3xl">
    <!-- Server Settings Card -->
    <div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ytBorder/60 bg-[#121212] flex items-center gap-2">
            <span class="material-symbols-outlined text-ytBlue text-[20px]">hard_drive</span>
            <h3 class="text-[15px] font-medium text-ytText">Server & Pipeline Storage</h3>
        </div>
        <div class="p-6">
            <form action="/admin/settings/update" method="POST">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="block text-[13px] font-medium text-ytText mb-1.5">Production Drive Path</label>
                    <p class="text-[11px] text-ytMuted mb-2.5">
                        Root directory where pipeline folders for Projects, Sequences, Shots, and Assets are generated.
                    </p>
                    <div class="flex items-center space-x-3">
                        <span class="material-symbols-outlined text-ytMuted">folder</span>
                        <input type="text" name="production_drive_path" value="<?= esc($production_drive_path) ?>" required 
                               class="flex-1 bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue font-mono text-[13px]" 
                               placeholder="e.g. F:\STUDIO_PRODUCTION\PROJECTS">
                    </div>
                </div>
                
                <div class="pt-3 border-t border-ytBorder/50 flex justify-end">
                    <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">
                        Save Storage Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Link to Studio Budgeting & Monthly Bills -->
    <div class="bg-gradient-to-r from-[#121c2a] to-[#122a1c] border border-green-700/40 rounded-xl p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-lg">
        <div class="flex items-center gap-3.5">
            <div class="p-3 bg-green-950/60 rounded-xl flex items-center justify-center text-green-400 border border-green-700/50 shadow-[0_0_15px_rgba(34,197,94,0.2)]">
                <span class="material-symbols-outlined text-[24px]">payments</span>
            </div>
            <div>
                <h3 class="text-[16px] font-semibold text-ytText">Studio Budgeting &amp; Monthly Bills</h3>
                <p class="text-[12px] text-ytMuted mt-0.5">Configure your monthly AI tools, cloud storage, software bills, and profit margins on the dedicated page.</p>
            </div>
        </div>
        <a href="/admin/budgeting" class="bg-green-600 hover:bg-green-500 text-white px-5 py-2 rounded-full font-medium text-[13px] shadow-[0_0_15px_rgba(34,197,94,0.3)] transition-all flex items-center gap-1.5 shrink-0">
            <span>Open Budgeting &amp; Bills</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        </a>
    </div>
</div>

<?= $this->endSection() ?>
