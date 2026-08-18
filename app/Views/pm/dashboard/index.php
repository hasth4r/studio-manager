<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 border-b border-ytBorder/50 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="p-2.5 bg-gradient-to-tr from-amber-900/40 to-yellow-900/30 rounded-2xl flex items-center justify-center text-amber-400 border border-amber-800/40 shadow-lg shadow-amber-950/40">
                <span class="material-symbols-outlined text-[26px]">shield_person</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-[22px] font-bold text-ytText leading-tight">Project Management Hub</h2>
                    <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">Supervisor Scope</span>
                </div>
                <p class="text-[13px] text-ytMuted mt-0.5">Manage your assigned production projects, shot breakdowns, and approvals</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="/admin/projects" class="bg-ytCard border border-ytBorder hover:border-ytBlue text-ytText hover:text-ytBlue px-4 py-2 rounded-full font-medium text-[13px] transition-colors flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">video_library</span>
                <span>All My Projects</span>
            </a>
            <a href="/admin/reviews" class="bg-gradient-to-r from-purple-900/40 to-indigo-900/40 border border-purple-700/50 text-purple-300 hover:text-white px-4 py-2 rounded-full font-medium text-[13px] transition-colors flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-purple-400">rate_review</span>
                <span>Review Inbox</span>
                <?php if(!empty($pendingReviews)): ?>
                    <span class="bg-purple-500 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full min-w-[16px] text-center"><?= count($pendingReviews) ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</div>

<!-- Supervised Projects Grid -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-[16px] font-bold text-ytText flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-400 text-[20px]">folder_special</span>
            <span>Supervised Projects (<?= count($supervisedProjects ?? []) ?>)</span>
        </h3>
    </div>

    <?php if (empty($supervisedProjects)): ?>
        <div class="bg-ytCard border border-ytBorder rounded-2xl p-8 text-center text-ytMuted">
            <span class="material-symbols-outlined text-[40px] text-ytMuted/40 mb-2">folder_off</span>
            <p class="text-[14px]">You are not currently designated as supervisor on any project.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($supervisedProjects as $p): ?>
                <?php 
                    $progressPct = ($p->total_tasks > 0) ? round(($p->completed_tasks / $p->total_tasks) * 100) : 0;
                ?>
                <div class="bg-ytCard border border-ytBorder hover:border-amber-500/50 rounded-2xl p-5 flex flex-col justify-between transition-all group shadow-lg shadow-black/20">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <span class="text-[10px] font-mono uppercase font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">Active Supervision</span>
                                <h4 class="text-[16px] font-bold text-ytText group-hover:text-amber-300 transition-colors mt-2"><?= esc($p->name) ?></h4>
                                <span class="text-[12px] text-ytMuted block mt-0.5"><?= esc($p->client_name ?: 'Internal Studio') ?></span>
                            </div>
                            <span class="material-symbols-outlined text-ytMuted group-hover:text-amber-400 transition-colors text-[24px]">movie</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-4 mb-3">
                            <div class="flex justify-between text-[11px] font-mono text-ytMuted mb-1">
                                <span>Task Progress</span>
                                <span class="text-ytText font-bold"><?= $p->completed_tasks ?> / <?= $p->total_tasks ?> (<?= $progressPct ?>%)</span>
                            </div>
                            <div class="w-full h-1.5 bg-black/60 rounded-full overflow-hidden border border-ytBorder/40">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-yellow-400 rounded-full" style="width: <?= $progressPct ?>%"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-[12px] text-ytMuted mt-3 font-mono">
                            <span><?= $p->shot_count ?> shots</span>
                            <span>&bull;</span>
                            <span class="capitalize text-ytText"><?= esc($p->status) ?></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-5 pt-3 border-t border-ytBorder/50">
                        <a href="/admin/projects/<?= $p->id ?>" class="text-center bg-ytHover hover:bg-ytBorder text-ytText text-[12px] font-medium py-2 rounded-xl transition-colors">
                            Project View
                        </a>
                        <a href="/admin/projects/<?= $p->id ?>/breakdown" class="text-center bg-gradient-to-r from-amber-500/20 to-yellow-500/20 hover:from-amber-500/30 hover:to-yellow-500/30 text-amber-300 border border-amber-500/40 text-[12px] font-bold py-2 rounded-xl transition-all flex items-center justify-center gap-1">
                            <span>Matrix</span>
                            <span class="material-symbols-outlined text-[15px]">table_chart</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Pending Reviews for Supervised Projects -->
<?php if (!empty($pendingReviews)): ?>
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-[16px] font-bold text-ytText flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-400 text-[20px]">rate_review</span>
            <span>Awaiting Your Review (<?= count($pendingReviews) ?>)</span>
        </h3>
        <a href="/admin/reviews" class="text-ytBlue hover:underline text-[12px]">View All Reviews &rarr;</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach (array_slice($pendingReviews, 0, 6) as $r): ?>
            <div class="bg-ytCard border border-ytBorder hover:border-purple-500/40 rounded-2xl p-4 flex flex-col justify-between transition-all shadow-md">
                <div class="flex items-start gap-3">
                    <div class="w-16 h-12 bg-black/60 rounded-lg overflow-hidden border border-ytBorder flex-shrink-0 relative">
                        <?php if ($r->file_type === 'video'): ?>
                            <video src="<?= media_cdn_url($r->proxy_path) ?>" class="w-full h-full object-cover"></video>
                        <?php else: ?>
                            <img src="<?= media_cdn_url($r->proxy_path) ?>" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="text-[13px] font-bold text-ytText truncate"><?= esc($r->project_name) ?> &mdash; <?= esc($r->shot_number) ?></h4>
                        <p class="text-[11px] text-purple-300 font-mono"><?= esc($r->task_name) ?> &bull; <?= esc($r->version_string ?? ('v' . $r->version_number)) ?></p>
                        <span class="text-[10px] text-ytMuted">By <?= esc($r->artist_name) ?></span>
                    </div>
                </div>
                <a href="/admin/reviews/player/<?= $r->id ?>" class="mt-3 w-full bg-purple-900/20 hover:bg-purple-900/40 border border-purple-700/50 text-purple-300 font-bold text-xs py-1.5 rounded-lg text-center transition-colors">
                    Review Version
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
