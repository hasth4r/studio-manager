<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50 flex justify-between items-end">
    <div class="flex items-center space-x-4">
        <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-blue-400 border border-blue-900/50">
            <span class="material-symbols-outlined">storage</span>
        </div>
        <div>
            <h2 class="text-[24px] font-medium text-ytText">Database Manager</h2>
            <p class="text-[13px] text-ytMuted mt-1">Manage MySQL database snapshots, backups, and restorations.</p>
        </div>
    </div>
    <div class="flex gap-3">
        <form action="/admin/database/backup" method="POST">
            <?= csrf_field() ?>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-[14px] flex items-center gap-2 transition-colors border border-blue-500/50 shadow-lg shadow-blue-500/20">
                <span class="material-symbols-outlined text-[18px]">cloud_download</span>
                Create New Backup
            </button>
        </form>
    </div>
</div>

<!-- Notifications -->
<?php if (session()->getFlashdata('message')): ?>
    <div class="bg-green-900/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6 flex items-center">
        <span class="material-symbols-outlined mr-2">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="bg-red-900/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6 flex items-center">
        <span class="material-symbols-outlined mr-2">error</span>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<!-- Backup List -->
<div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
    <div class="p-5 border-b border-ytBorder/50 flex justify-between items-center bg-[#111111]">
        <h3 class="font-medium text-[15px] flex items-center gap-2 text-ytText">
            <span class="material-symbols-outlined text-[18px] text-ytMuted">inventory_2</span>
            Available Backups
        </h3>
        <span class="text-[12px] text-ytMuted bg-[#1a1a1a] px-2.5 py-1 rounded-md border border-ytBorder">
            <?= count($backups) ?> Files
        </span>
    </div>

    <?php if (empty($backups)): ?>
        <div class="p-16 text-center">
            <span class="material-symbols-outlined text-[48px] text-ytMuted opacity-50 mb-3">folder_open</span>
            <p class="text-ytText font-medium text-[15px]">No backups found</p>
            <p class="text-ytMuted text-[13px] mt-1">Generate your first database snapshot by clicking the button above.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[11px] uppercase tracking-wider text-ytMuted border-b border-ytBorder/50 bg-[#0a0a0a]">
                        <th class="px-5 py-3 font-medium">Filename</th>
                        <th class="px-5 py-3 font-medium text-center">Size</th>
                        <th class="px-5 py-3 font-medium">Created On</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] text-ytText divide-y divide-ytBorder/50">
                    <?php foreach ($backups as $index => $backup): ?>
                        <tr class="hover:bg-ytHover/30 transition-colors group">
                            <td class="px-5 py-4 font-medium flex items-center gap-3">
                                <span class="material-symbols-outlined text-ytBlue text-[20px]">description</span>
                                <?= esc($backup['name']) ?>
                                <?php if($index === 0): ?>
                                    <span class="ml-2 bg-blue-900/30 text-blue-400 border border-blue-900/50 px-2 py-0.5 rounded text-[9px] uppercase tracking-wider font-semibold">Latest</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-center text-ytMuted">
                                <?= esc($backup['size']) ?>
                            </td>
                            <td class="px-5 py-4 text-ytMuted">
                                <?= date('M d, Y', strtotime($backup['date'])) ?>
                                <span class="text-[11px] opacity-60 block"><?= date('g:i A', strtotime($backup['date'])) ?></span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <a href="/admin/database/download?filename=<?= urlencode($backup['name']) ?>" 
                                       class="p-2 bg-[#1a1a1a] hover:bg-[#252525] border border-ytBorder rounded-lg text-ytMuted hover:text-white transition-colors"
                                       title="Download">
                                        <span class="material-symbols-outlined text-[16px]">download</span>
                                    </a>
                                    
                                    <form action="/admin/database/restore" method="POST" class="inline" onsubmit="return confirm('WARNING: Restoring this backup will permanently overwrite the current live database. All changes made since this backup will be lost. Are you absolutely sure you want to proceed?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="filename" value="<?= esc($backup['name']) ?>">
                                        <button type="submit" class="p-2 bg-yellow-900/20 hover:bg-yellow-900/40 border border-yellow-900/50 rounded-lg text-yellow-500 hover:text-yellow-400 transition-colors" title="Restore Snapshot">
                                            <span class="material-symbols-outlined text-[16px]">settings_backup_restore</span>
                                        </button>
                                    </form>

                                    <form action="/admin/database/delete" method="POST" class="inline" onsubmit="return confirm('Delete this backup file forever?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="filename" value="<?= esc($backup['name']) ?>">
                                        <button type="submit" class="p-2 bg-red-900/20 hover:bg-red-900/40 border border-red-900/50 rounded-lg text-red-500 hover:text-red-400 transition-colors" title="Delete Backup">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
