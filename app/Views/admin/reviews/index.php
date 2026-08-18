<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-4 md:pt-6 pb-3 md:pb-4 mb-4 md:mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3 md:space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-xl md:rounded-full flex items-center justify-center text-purple-400 border border-purple-900/50 shrink-0">
                <span class="material-symbols-outlined text-[20px] md:text-[24px]">inbox</span>
            </div>
            <div>
                <h2 class="text-[18px] md:text-[24px] font-bold md:font-medium text-ytText leading-tight">Review Inbox</h2>
                <p class="text-[11px] md:text-[13px] text-ytMuted mt-0.5">Pending submissions awaiting supervisor approval</p>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MOBILE VIEW (Compact Feed) ==================== -->
<div class="md:hidden space-y-3 pb-8">
    <?php if(empty($pending_reviews)): ?>
        <div class="p-8 bg-[#0e1320] border border-slate-800 rounded-2xl text-center">
            <span class="material-symbols-outlined text-[40px] text-slate-500 mb-2 block">inventory_2</span>
            <p class="text-white font-bold text-[14px]">Inbox Zero!</p>
            <p class="text-slate-400 text-[12px] mt-0.5">No pending submissions to review right now.</p>
        </div>
    <?php else: ?>
        <?php foreach($pending_reviews as $review): ?>
            <div class="bg-[#0e1320] border border-slate-800/90 rounded-2xl p-4 shadow-lg space-y-3">
                <div class="flex items-start gap-3">
                    <!-- Thumbnail -->
                    <div class="w-16 h-12 bg-[#050811] border border-slate-800 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center">
                        <?php if(!empty($review->shot_thumb)): ?>
                            <img src="<?= media_cdn_url(esc($review->shot_thumb)) ?>" alt="Thumb" loading="lazy" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="material-symbols-outlined text-[20px] text-slate-500">movie</span>
                        <?php endif; ?>
                    </div>

                    <!-- Meta -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-xs font-bold text-white truncate"><?= esc($review->project_name) ?></span>
                            <span class="bg-purple-950/60 border border-purple-800/60 text-purple-300 font-bold px-2 py-0.5 rounded text-[10px] shrink-0 font-mono">
                                <?= esc($review->version_string) ?>
                            </span>
                        </div>
                        <p class="text-[11px] font-semibold text-blue-400 truncate">
                            <?php if($review->shot_number): ?>
                                <?= !empty($review->seq_name) ? esc($review->seq_name) . ' / ' : '' ?><?= esc($review->shot_number) ?>
                            <?php else: ?>
                                <?= esc($review->seq_name ?? 'Global') ?>
                            <?php endif; ?>
                            <span class="text-slate-500 font-normal">• <?= esc($review->task_name ?? 'Task') ?></span>
                        </p>
                        <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">person</span> <?= esc($review->artist_name) ?>
                            <span class="mx-0.5">•</span>
                            <?= date('M d, g:i A', strtotime($review->created_at)) ?>
                        </p>
                    </div>
                </div>

                <?php if(!empty($review->artist_notes)): ?>
                    <p class="text-[11px] text-slate-400 italic bg-[#050811] p-2 rounded-lg border border-slate-800/60 line-clamp-2">
                        "<?= esc($review->artist_notes) ?>"
                    </p>
                <?php endif; ?>

                <a href="/admin/reviews/player/<?= $review->id ?>" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 text-white font-bold text-xs py-2.5 rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-md shadow-purple-900/30 text-center">
                    <span class="material-symbols-outlined text-[16px]">play_circle</span> Review Version
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ==================== DESKTOP VIEW (100% Preserved Table) ==================== -->
<div class="hidden md:block bg-ytCard border border-ytBorder rounded-xl overflow-hidden shadow-xl mb-8">
    <?php if(empty($pending_reviews)): ?>
        <div class="p-16 text-center">
            <span class="material-symbols-outlined text-[48px] text-ytMuted mb-3 block">inventory_2</span>
            <p class="text-ytText font-semibold text-[15px]">Inbox Zero!</p>
            <p class="text-ytMuted text-[13px] mt-1">There are no pending submissions to review right now.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-ytBorder/50 text-ytMuted text-[11px] uppercase tracking-wider font-medium bg-[#111111]">
                        <th class="px-5 py-4 w-16 text-center">Version</th>
                        <th class="px-4 py-4 min-w-[200px]">Project & Task</th>
                        <th class="px-4 py-4 min-w-[150px]">Artist</th>
                        <th class="px-4 py-4 min-w-[200px]">Notes</th>
                        <th class="px-4 py-4 min-w-[150px]">Submitted</th>
                        <th class="px-5 py-4 w-32 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-[13px] text-ytText divide-y divide-ytBorder/40">
                    <?php foreach($pending_reviews as $review): ?>
                        <tr class="hover:bg-ytHover/40 transition-colors group">
                            <td class="px-5 py-4 text-center">
                                <span class="bg-purple-900/20 border border-purple-700/50 text-purple-300 font-bold px-2.5 py-1 rounded-md text-[11px]">
                                    <?= esc($review->version_string) ?>
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 flex-shrink-0 bg-[#1a1a1a] border border-ytBorder/50 rounded flex items-center justify-center relative overflow-hidden">
                                        <?php if(!empty($review->shot_thumb)): ?>
                                            <img src="<?= media_cdn_url(esc($review->shot_thumb)) ?>" alt="Thumbnail" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="material-symbols-outlined text-[20px] text-ytMuted">movie</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex flex-col justify-center">
                                        <div class="font-medium text-[14px] text-ytText mb-0.5">
                                            <?= esc($review->project_name) ?>
                                            <span class="text-ytMuted mx-1">/</span>
                                            <span class="text-ytBlue">
                                                <?php if($review->shot_number): ?>
                                                    <?= !empty($review->seq_name) ? esc($review->seq_name) . ' / ' : '' ?><?= esc($review->shot_number) ?>
                                                <?php else: ?>
                                                    <?= esc($review->seq_name ?? 'Global') ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-ytMuted uppercase tracking-wider">
                                            <?= esc($review->task_name ?? 'Task') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2.5">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($review->artist_name) ?>&background=8b5cf6&color=fff&size=64&rounded=true" 
                                         class="w-7 h-7 rounded-full border border-ytBorder/50">
                                    <span class="font-medium"><?= esc($review->artist_name) ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <?php if(!empty($review->artist_notes)): ?>
                                    <div class="text-[12px] text-ytMuted italic line-clamp-2 max-w-[250px]" title="<?= esc($review->artist_notes) ?>">
                                        "<?= esc($review->artist_notes) ?>"
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] text-ytMuted">No notes provided</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-[12px] text-ytMuted">
                                <?php
                                    $date = new \DateTime($review->created_at);
                                    echo $date->format('M j, Y') . '<br><span class="text-[11px] opacity-70">' . $date->format('g:i A') . '</span>';
                                ?>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button type="button" onclick="openPlayer(<?= $review->id ?>)" class="inline-flex items-center justify-center gap-1.5 bg-[#1a122a] border border-purple-700 text-purple-300 px-4 py-2 rounded-full font-semibold text-[12px] hover:bg-purple-900/40 hover:shadow-[0_0_15px_rgba(126,34,206,0.3)] transition-all whitespace-nowrap opacity-80 group-hover:opacity-100">
                                    <span class="material-symbols-outlined text-[16px]">play_circle</span>
                                    Review
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function openPlayer(reviewId) {
    window.location.href = `/admin/reviews/player/${reviewId}`;
}
</script>

<?= $this->endSection() ?>
