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

<div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden max-w-2xl">
    <div class="p-6">
        <form action="/admin/settings/update" method="POST">
            <?= csrf_field() ?>
            
            <div class="mb-6">
                <label class="block text-[14px] font-medium text-ytText mb-2">Production Drive Path</label>
                <p class="text-[12px] text-ytMuted mb-3">
                    The root directory where EnsoFlow will automatically generate pipeline folders for Projects, Sequences, Shots, and Assets.<br>
                    <strong>Note:</strong> Ensure the web server (Apache/PHP) has write permissions to this directory.
                </p>
                <div class="flex items-center space-x-3">
                    <span class="material-symbols-outlined text-ytMuted">folder</span>
                    <input type="text" name="production_drive_path" value="<?= esc($production_drive_path) ?>" required 
                           class="flex-1 bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue font-mono text-[13px]" 
                           placeholder="e.g. F:\STUDIO_PRODUCTION\PROJECTS">
                </div>
            </div>
            
            <div class="pt-4 border-t border-ytBorder/50 flex justify-end">
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
