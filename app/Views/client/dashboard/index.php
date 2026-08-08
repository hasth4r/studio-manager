<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 border-b border-ytBorder/50 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-blue-400 border border-blue-900/50">
                <span class="material-symbols-outlined">business_center</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Welcome back, <?= esc(session()->get('userName')) ?></h2>
                <p class="text-[13px] text-ytMuted mt-1">Overview of your active projects and recent deliveries</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Active Projects -->
    <div class="col-span-1 lg:col-span-2 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 px-1">
            <h3 class="text-[15px] font-semibold text-ytText flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-ytBlue">movie</span> Active Projects
            </h3>
        </div>
        
        <?php if (empty($projects)): ?>
            <div class="bg-ytCard border border-ytBorder rounded-xl p-16 text-center">
                <span class="material-symbols-outlined text-[48px] text-ytMuted mb-3 block">inbox</span>
                <p class="text-ytText font-semibold text-[15px]">No active projects found.</p>
                <p class="text-ytMuted text-[13px] mt-1">You don't have any projects assigned currently.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($projects as $project): ?>
                    <div class="bg-ytCard border border-ytBorder rounded-xl p-5 hover:border-ytBlue/50 transition-colors group cursor-default flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="font-semibold text-[16px] text-ytText group-hover:text-ytBlue transition-colors"><?= esc($project->name) ?></h3>
                                <span class="bg-[#121c2a] text-blue-400 border border-blue-900/50 px-2.5 py-1 rounded-md text-[10px] uppercase tracking-wider font-semibold shrink-0 ml-2">Active</span>
                            </div>
                            <p class="text-ytMuted text-[13px] line-clamp-2 mb-5 h-[40px]"><?= esc($project->description ?? 'No description provided.') ?></p>
                            
                            <!-- Statistics Area -->
                            <?php if(isset($project->stats) && $project->stats['total'] > 0): ?>
                                <div class="mb-4">
                                    <div class="flex justify-between text-[11px] mb-1.5">
                                        <span class="text-ytMuted font-medium uppercase tracking-wider">Overall Progress</span>
                                        <span class="text-white font-semibold"><?= $project->stats['progress'] ?>%</span>
                                    </div>
                                    <div class="w-full bg-[#111111] rounded-full h-1.5 border border-ytBorder/50 overflow-hidden">
                                        <div class="bg-ytBlue h-1.5 rounded-full" style="width: <?= $project->stats['progress'] ?>%"></div>
                                    </div>
                                    <div class="flex gap-2 mt-3 text-[11px] font-medium">
                                        <div class="flex items-center gap-1 text-yellow-400/80 bg-yellow-900/10 px-2 py-0.5 rounded border border-yellow-900/30">
                                            <span class="material-symbols-outlined text-[13px]">pending</span>
                                            <?= $project->stats['pending'] + $project->stats['in_progress'] ?> Pending
                                        </div>
                                        <div class="flex items-center gap-1 text-purple-400/80 bg-purple-900/10 px-2 py-0.5 rounded border border-purple-900/30">
                                            <span class="material-symbols-outlined text-[13px]">visibility</span>
                                            <?= $project->stats['review'] ?> In Review
                                        </div>
                                        <div class="flex items-center gap-1 text-green-400/80 bg-green-900/10 px-2 py-0.5 rounded border border-green-900/30">
                                            <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                            <?= $project->stats['completed'] ?> Done
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-4 flex items-center justify-center h-[76px] bg-[#111] border border-ytBorder/50 rounded-lg border-dashed">
                                    <span class="text-ytMuted text-[12px]">No tasks tracked yet</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between items-center text-[12px] pt-4 border-t border-ytBorder/50 mt-2">
                            <span class="text-ytMuted flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">calendar_today</span> 
                                Started <?= date('M d, Y', strtotime($project->created_at)) ?>
                            </span>
                            
                            <?php if(!empty($project->deadline)): ?>
                                <span class="text-ytMuted flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">event</span> 
                                    Due <?= date('M d, Y', strtotime($project->deadline)) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-ytMuted font-medium"><?= esc($project->resolution ?? '') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Reviews -->
    <div class="col-span-1 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 px-1">
            <h3 class="text-[15px] font-semibold text-ytText flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-ytBlue">rate_review</span> Recent Deliveries
            </h3>
        </div>
        
        <div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
            <?php if (empty($reviews)): ?>
                <div class="p-8 text-center text-ytMuted">
                    <span class="material-symbols-outlined text-[36px] opacity-20 mb-3">done_all</span>
                    <p class="text-[13px]">You're all caught up. No pending reviews.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-ytBorder/50 max-h-[500px] overflow-y-auto custom-scrollbar">
                    <?php foreach ($reviews as $rev): ?>
                        <a href="/client/reviews/player/<?= $rev->id ?>" class="block p-4 hover:bg-ytHover transition-colors border-l-2 border-transparent hover:border-ytBlue group">
                            <div class="flex items-center gap-4">
                                <!-- Thumbnail -->
                                <div class="w-14 h-10 flex-shrink-0 bg-[#1a1a1a] border border-ytBorder/50 rounded overflow-hidden flex items-center justify-center group-hover:border-ytBlue/50 transition-colors">
                                    <?php if(!empty($rev->shot_thumb)): ?>
                                        <img src="/<?= esc($rev->shot_thumb) ?>" alt="Thumb" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="material-symbols-outlined text-[20px] text-ytMuted">movie</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="font-semibold text-[13px] text-ytText truncate pr-2 group-hover:text-white transition-colors">
                                            <?= esc($rev->project_name) ?> &mdash; 
                                            <span class="text-ytBlue">
                                                <?php if($rev->shot_number): ?>
                                                    <?= !empty($rev->seq_name) ? esc($rev->seq_name) . ' / ' : '' ?><?= esc($rev->shot_number) ?>
                                                <?php else: ?>
                                                    <?= esc($rev->asset_name ?? 'Global') ?>
                                                <?php endif; ?>
                                            </span>
                                        </h4>
                                        <?php if($rev->status === 'pending'): ?>
                                            <span class="bg-[#121c2a] text-blue-400 border border-blue-900/50 px-2 py-0.5 rounded text-[9px] uppercase tracking-wider font-semibold shrink-0 ml-2">Pending</span>
                                        <?php elseif($rev->status === 'approved'): ?>
                                            <span class="bg-[#122a15] text-green-400 border border-green-900/50 px-2 py-0.5 rounded text-[9px] uppercase tracking-wider font-semibold shrink-0 ml-2">Approved</span>
                                        <?php elseif($rev->status === 'revision_needed'): ?>
                                            <span class="bg-[#2a1215] text-red-400 border border-red-900/50 px-2 py-0.5 rounded text-[9px] uppercase tracking-wider font-semibold shrink-0 ml-2">Revision</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-[11px] text-ytMuted flex items-center">
                                            <span class="material-symbols-outlined text-[13px] mr-1">history</span> <?= esc($rev->version_string) ?> by <?= esc($rev->artist_name) ?>
                                        </p>
                                        <p class="text-[10px] text-ytMuted/70 font-medium tracking-wider">
                                            <?= date('M d, g:i A', strtotime($rev->created_at)) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #1a1a1a; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f3f3f; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #555; }
</style>

<?= $this->endSection() ?>
