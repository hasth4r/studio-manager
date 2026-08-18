<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<?php if (in_array($userRole, ['admin', 'project_manager'])): ?>
    <div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-4 md:pt-6 pb-3 md:pb-4 mb-4 md:mb-6 border-b border-ytBorder/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 md:space-x-4">
                <div class="p-2 bg-[#1a122a] rounded-xl md:rounded-full flex items-center justify-center text-red-400 border border-red-900/50 shrink-0">
                    <span class="material-symbols-outlined text-[20px] md:text-[24px]">dashboard</span>
                </div>
                <div>
                    <h2 class="text-[18px] md:text-[24px] font-bold md:font-medium text-ytText leading-tight">Dashboard</h2>
                    <p class="text-[11px] md:text-[13px] text-ytMuted mt-0.5">Overview of your studio's activity</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Count Cards Section: Mobile Horizontal Swipe Carousel / Desktop 3-Col Grid -->
    <div class="flex md:grid overflow-x-auto md:overflow-visible gap-4 md:gap-6 mb-6 md:mb-8 pb-3 md:pb-0 snap-x snap-mandatory scroll-smooth custom-scrollbar -mx-4 px-4 md:mx-0 md:px-0 md:grid-cols-2 lg:grid-cols-3">
        
        <!-- Widget 1: Latest Review Submission -->
        <div class="bg-ytCard border border-ytBorder rounded-2xl p-4 md:p-6 flex flex-col justify-between shrink-0 w-[285px] sm:w-[320px] md:w-auto snap-start shadow-lg shadow-black/20">
            <div>
                <h3 class="text-[14px] md:text-[15px] font-bold md:font-medium text-ytText mb-3 md:mb-4 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px] text-purple-400">rate_review</span> Latest Review Submission
                </h3>
                
                <?php if (isset($latestReview) && $latestReview): ?>
                    <div class="w-full h-28 md:h-32 bg-[#121212] rounded-xl mb-3 flex items-center justify-center border border-ytBorder relative overflow-hidden group">
                        <?php if ($latestReview->file_type === 'video'): ?>
                            <video src="<?= media_cdn_url($latestReview->proxy_path) ?>" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                            <div class="absolute bottom-2 right-2 bg-black/80 px-1.5 py-0.5 rounded text-[10px] text-ytText flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">movie</span> Video
                            </div>
                        <?php else: ?>
                            <img src="<?= media_cdn_url($latestReview->proxy_path) ?>" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity">
                            <div class="absolute bottom-2 right-2 bg-black/80 px-1.5 py-0.5 rounded text-[10px] text-ytText flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">image</span> Image
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-[13px] font-bold text-ytText mb-1.5 truncate"><?= esc($latestReview->project_name) ?> - <?= esc($latestReview->task_name) ?></p>
                    
                    <div class="flex items-center text-[11px] text-ytMuted space-x-3 mb-4">
                        <span class="flex items-center"><span class="material-symbols-outlined text-[13px] mr-1">person</span> <?= esc($latestReview->artist_name) ?></span>
                        <span class="flex items-center"><span class="material-symbols-outlined text-[13px] mr-1">schedule</span> <?= date('M d', strtotime($latestReview->created_at)) ?></span>
                    </div>
                    
                    <div class="space-y-2 pt-2 border-t border-ytBorder/40">
                        <div class="flex justify-between text-[12px]">
                            <span class="text-ytMuted">Status</span>
                            <span class="text-ytText font-medium capitalize"><?= esc($latestReview->status) ?></span>
                        </div>
                        <div class="flex justify-between text-[12px]">
                            <span class="text-ytMuted">Version</span>
                            <span class="text-ytText font-medium"><?= esc($latestReview->version_string ?? ('v' . ($latestReview->version_number ?? 1))) ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex-1 flex items-center justify-center text-ytMuted text-[13px] py-10">
                        No recent review submissions.
                    </div>
                <?php endif; ?>
            </div>

            <a href="/admin/reviews" class="mt-4 text-[12px] md:text-[13px] font-bold text-ytBlue hover:text-blue-400 block text-center border border-ytBorder rounded-xl py-2 hover:bg-ytHover transition-colors">
                Go to Review Inbox &rarr;
            </a>
        </div>

        <!-- Widget 2: Studio Analytics -->
        <div class="bg-ytCard border border-ytBorder rounded-2xl flex flex-col shrink-0 w-[285px] sm:w-[320px] md:w-auto snap-start shadow-lg shadow-black/20 overflow-hidden">
            <div class="p-4 md:p-6 border-b border-ytBorder/50">
                <h3 class="text-[14px] md:text-[15px] font-bold md:font-medium text-ytText mb-2 md:mb-4 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px] text-blue-400">analytics</span> Studio Analytics
                </h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-ytMuted uppercase tracking-wider font-semibold">Active Projects</p>
                        <span class="text-2xl md:text-3xl font-bold text-ytText"><?= isset($activeProjectsCount) ? $activeProjectsCount : 0 ?></span>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-ytMuted uppercase tracking-wider font-semibold">28-Day Completed</p>
                        <span class="text-base font-bold text-green-400"><?= isset($completedTasksCount) ? $completedTasksCount : 0 ?> Tasks</span>
                    </div>
                </div>
            </div>
            
            <div class="p-4 md:p-6 flex-1 flex flex-col justify-between">
                <div>
                    <h4 class="text-[13px] font-semibold text-ytText mb-2">Top Projects</h4>
                    <div class="space-y-2">
                        <?php if (isset($topProjects) && !empty($topProjects)): ?>
                            <?php foreach(array_slice($topProjects, 0, 3) as $tp): ?>
                                <div class="flex justify-between text-[12px] p-1.5 rounded-lg bg-[#0d121c] border border-slate-800/60">
                                    <span class="text-ytText truncate max-w-[150px] font-medium"><?= esc($tp->name) ?></span>
                                    <span class="text-blue-400 font-mono font-bold"><?= esc($tp->task_count) ?> tasks</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-[12px] text-ytMuted">No active projects found.</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <a href="/admin/projects" class="mt-4 block text-[12px] font-bold text-ytBlue hover:text-blue-400 text-center border border-ytBorder rounded-xl py-2 hover:bg-ytHover transition-colors">
                    Go to All Projects &rarr;
                </a>
            </div>
        </div>

        <!-- Widget 3: Studio Economics -->
        <div class="bg-ytCard border border-ytBorder rounded-2xl p-4 md:p-6 flex flex-col justify-between shrink-0 w-[285px] sm:w-[320px] md:w-auto snap-start shadow-lg shadow-black/20">
            <div>
                <div class="flex items-center justify-between mb-3 md:mb-4">
                    <h3 class="text-[14px] md:text-[15px] font-bold md:font-medium text-ytText flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-emerald-400">payments</span> Studio Economics
                    </h3>
                    <span class="bg-green-950/70 border border-green-700/50 text-green-300 px-2 py-0.5 rounded-full text-[9px] font-mono font-bold">
                        LIVE
                    </span>
                </div>

                <div class="space-y-2.5">
                    <!-- Benchmark Quote -->
                    <div class="bg-[#0d121c] border border-ytBorder/60 rounded-xl p-2.5 md:p-3">
                        <div class="text-[10px] text-ytMuted uppercase font-bold tracking-wider">Benchmark Quote</div>
                        <div class="text-[18px] md:text-[22px] font-bold text-ytBlue font-mono mt-0.5">
                            <?= esc($studioCurrency) ?><?= number_format($totalPipelineBudget, 0) ?>
                        </div>
                        <div class="text-[10px] text-ytMuted font-mono">
                            Across <?= round($totalPipelineHours, 1) ?> est. hours
                        </div>
                    </div>

                    <!-- Locked Budget (if set) -->
                    <div class="bg-[#0d121c] border border-green-700/40 rounded-xl p-2.5 md:p-3">
                        <div class="flex items-center justify-between text-[10px] uppercase font-bold tracking-wider text-green-400">
                            <span>Locked Revenue</span>
                            <span class="material-symbols-outlined text-[13px]">lock</span>
                        </div>
                        <div class="text-[18px] md:text-[22px] font-bold text-green-300 font-mono mt-0.5">
                            <?= esc($studioCurrency) ?><?= number_format($totalLockedBudget > 0 ? $totalLockedBudget : $totalPipelineBudget, 0) ?>
                        </div>
                        <div class="text-[10px] text-ytMuted font-mono">
                            <?= $totalLockedBudget > 0 ? 'Scaled across project caps' : '100% of quote' ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-ytBorder/50 flex items-center justify-between">
                <a href="/admin/budgeting" class="text-[12px] font-bold text-green-400 hover:text-green-300 flex items-center gap-1">
                    <span>Manage Bills</span>
                    <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                </a>
                <a href="/admin/projects" class="text-[12px] font-medium text-ytMuted hover:text-ytText">
                    Projects &rarr;
                </a>
            </div>
        </div>
        
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
