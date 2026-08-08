<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-purple-400 border border-purple-900/50">
                <span class="material-symbols-outlined">inbox</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Review Inbox</h2>
                <p class="text-[13px] text-ytMuted mt-1">Pending submissions awaiting supervisor approval</p>
            </div>
        </div>
    </div>
</div>

<!-- Inbox Grid -->
<div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
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
                            <!-- Version Badge -->
                            <td class="px-5 py-4 text-center">
                                <span class="bg-purple-900/20 border border-purple-700/50 text-purple-300 font-bold px-2.5 py-1 rounded-md text-[11px]">
                                    <?= esc($review->version_string) ?>
                                </span>
                            </td>
                            
                            <!-- Context -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Thumbnail Placeholder -->
                                    <div class="w-12 h-12 flex-shrink-0 bg-[#1a1a1a] border border-ytBorder/50 rounded flex items-center justify-center relative overflow-hidden">
                                        <?php if(!empty($review->shot_thumb)): ?>
                                            <img src="/<?= esc($review->shot_thumb) ?>" alt="Thumbnail" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="material-symbols-outlined text-[20px] text-ytMuted">movie</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Text Details -->
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
                            
                            <!-- Artist -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2.5">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($review->artist_name) ?>&background=8b5cf6&color=fff&size=64&rounded=true" 
                                         class="w-7 h-7 rounded-full border border-ytBorder/50">
                                    <span class="font-medium"><?= esc($review->artist_name) ?></span>
                                </div>
                            </td>
                            
                            <!-- Notes -->
                            <td class="px-4 py-4">
                                <?php if(!empty($review->artist_notes)): ?>
                                    <div class="text-[12px] text-ytMuted italic line-clamp-2 max-w-[250px]" title="<?= esc($review->artist_notes) ?>">
                                        "<?= esc($review->artist_notes) ?>"
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] text-ytMuted">No notes provided</span>
                                <?php endif; ?>
                            </td>

                            <!-- Time -->
                            <td class="px-4 py-4 text-[12px] text-ytMuted">
                                <?php
                                    $date = new \DateTime($review->created_at);
                                    echo $date->format('M j, Y') . '<br><span class="text-[11px] opacity-70">' . $date->format('g:i A') . '</span>';
                                ?>
                            </td>
                            
                            <!-- Action -->
                            <td class="px-5 py-4 text-right">
                                <!-- The button will open the Review Player (Phase 4) -->
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
        window.location.href = '/admin/reviews/player/' + reviewId;
    }
</script>

<?= $this->endSection() ?>
