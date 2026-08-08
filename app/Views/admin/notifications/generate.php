<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-blue-400 border border-blue-900/50">
                <span class="material-symbols-outlined">notification_add</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-ytText"><?= esc($title) ?></h2>
                <p class="text-[13px] text-ytMuted">Generate retroactive notifications for existing tasks and reviews.</p>
            </div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('message')) : ?>
    <div class="mb-6 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-lg">
        <p class="text-[13px] text-green-400 font-medium"><?= esc(session()->getFlashdata('message')) ?></p>
    </div>
<?php endif; ?>

<div class="bg-ytCard border border-ytBorder rounded-xl p-8 max-w-2xl mt-6">
    <h3 class="text-lg font-medium text-ytText mb-4">Backdate Existing Activity</h3>
    <p class="text-[14px] text-ytMuted mb-8 leading-relaxed">
        Because the notification system was just implemented, existing users have not been notified about their currently active tasks or pending review submissions. 
        <br><br>
        Clicking the button below will scan the database and automatically generate notifications for:
        <br>
        <ul class="list-disc list-inside text-ytText mt-2 space-y-1 mb-6 text-[13px]">
            <li>All currently assigned, incomplete tasks (alerts sent to the assigned artists).</li>
            <li>All pending review submissions (alerts sent to admins and project managers).</li>
        </ul>
    </p>

    <form action="/admin/notifications/generate/process" method="POST">
        <?= csrf_field() ?>
        <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-6 py-3 rounded-full font-medium text-[14px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">auto_fix_high</span> Generate Notifications Now
        </button>
    </form>
</div>

<?= $this->endSection() ?>
