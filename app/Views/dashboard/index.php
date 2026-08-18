<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<?php if (in_array($userRole, ['admin', 'project_manager'])): ?>
    
    <!-- ==================== MOBILE VIEW (Exact Reference Match) ==================== -->
    <div class="md:hidden space-y-4 pb-6">
        
        <!-- Mobile Header Title -->
        <div class="flex items-center space-x-3 pt-2">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center shadow-lg shadow-purple-950/50 flex-shrink-0">
                <span class="material-symbols-outlined text-[22px]">dashboard</span>
            </div>
            <div>
                <h1 class="text-[20px] font-extrabold text-white leading-tight">Studio dashboard</h1>
                <p class="text-[11px] text-slate-400">Overview of your studio's activity</p>
            </div>
        </div>

        <!-- Mobile Horizontal Underline Navigation Tabs -->
        <div class="flex items-center space-x-6 border-b border-slate-800/80 text-[13px] font-semibold overflow-x-auto custom-scrollbar -mx-4 px-4 pt-1">
            <button class="text-white border-b-2 border-white pb-2 shrink-0">Overview</button>
            <a href="/admin/projects" class="text-slate-400 hover:text-white pb-2 shrink-0">Projects</a>
            <a href="/admin/reviews" class="text-slate-400 hover:text-white pb-2 shrink-0">Reviews</a>
            <a href="/admin/budgeting" class="text-slate-400 hover:text-white pb-2 shrink-0">Economics</a>
        </div>

        <!-- Hero Featured Card Carousel -->
        <div class="relative bg-gradient-to-b from-[#111625] to-[#0a0d18] border border-slate-800 rounded-2xl p-5 shadow-xl">
            <span class="text-xs font-semibold text-slate-400">Total Project Pipeline</span>
            <div class="flex items-center gap-2 mt-1 mb-1">
                <span class="text-[28px] font-extrabold text-white font-mono tracking-tight"><?= esc($studioCurrency) ?><?= number_format($totalPipelineBudget, 0) ?></span>
                <span class="text-xl">💼</span>
            </div>
            <p class="text-[11px] text-slate-400">Estimated value across <?= round($totalPipelineHours, 1) ?> production hrs</p>
            
            <div class="mt-3 flex items-center gap-1.5 text-amber-400 text-xs font-semibold">
                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                <span>Projects actively billing</span>
            </div>

            <!-- Dots Indicator -->
            <div class="flex justify-center items-center gap-1.5 mt-4">
                <span class="w-2 h-2 rounded-full bg-white"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
            </div>
        </div>

        <!-- Platform Overview 2x2 Metric Grid -->
        <div>
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="text-[15px] font-bold text-white">Platform Overview</h3>
                <span class="text-[11px] text-slate-500 font-medium">All time</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2.5">
                <!-- Projects -->
                <a href="/admin/projects" class="bg-[#0e1320] border border-slate-800/80 rounded-2xl p-3.5 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <span class="text-xs text-slate-400 font-medium">Projects</span>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-[22px] font-extrabold text-white"><?= isset($activeProjectsCount) ? $activeProjectsCount : 0 ?></span>
                        <span class="material-symbols-outlined text-[18px] text-emerald-400">check_circle</span>
                    </div>
                </a>

                <!-- Completed Tasks -->
                <div class="bg-[#0e1320] border border-slate-800/80 rounded-2xl p-3.5 flex flex-col justify-between">
                    <span class="text-xs text-slate-400 font-medium">Completed Tasks</span>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-[22px] font-extrabold text-white"><?= isset($completedTasksCount) ? $completedTasksCount : 0 ?></span>
                        <span class="material-symbols-outlined text-[18px] text-emerald-400">check_circle</span>
                    </div>
                </div>

                <!-- Pipeline Hours -->
                <div class="bg-[#0e1320] border border-slate-800/80 rounded-2xl p-3.5 flex flex-col justify-between">
                    <span class="text-xs text-slate-400 font-medium">Pipeline Hours</span>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-[22px] font-extrabold text-white"><?= round($totalPipelineHours, 0) ?></span>
                        <span class="material-symbols-outlined text-[18px] text-emerald-400">check_circle</span>
                    </div>
                </div>

                <!-- Pending Reviews -->
                <a href="/admin/reviews" class="bg-[#0e1320] border border-slate-800/80 rounded-2xl p-3.5 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <span class="text-xs text-slate-400 font-medium">Pending Reviews</span>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-[22px] font-extrabold text-white"><?= isset($pendingReviewsCount) ? $pendingReviewsCount : 0 ?></span>
                        <span class="material-symbols-outlined text-[18px] text-emerald-400">check_circle</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Latest Review Performance Card -->
        <div class="bg-[#0e1320] border border-slate-800/80 rounded-2xl p-4 shadow-xl">
            <h3 class="text-[14px] font-bold text-white mb-3 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-purple-400">rate_review</span> Latest Review Submission
            </h3>

            <?php if (isset($latestReview) && $latestReview): ?>
                <div class="w-full h-36 bg-[#050811] rounded-xl mb-3 flex items-center justify-center border border-slate-800 relative overflow-hidden group">
                    <?php if ($latestReview->file_type === 'video'): ?>
                        <video src="<?= media_cdn_url($latestReview->proxy_path) ?>" class="w-full h-full object-cover" muted loop playsinline></video>
                        <div class="absolute bottom-2 right-2 bg-black/80 px-2 py-0.5 rounded text-[10px] text-white flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">movie</span> Video
                        </div>
                    <?php else: ?>
                        <img src="<?= media_cdn_url($latestReview->proxy_path) ?>" class="w-full h-full object-cover">
                        <div class="absolute bottom-2 right-2 bg-black/80 px-2 py-0.5 rounded text-[10px] text-white flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">image</span> Image
                        </div>
                    <?php endif; ?>
                </div>

                <h4 class="text-[13px] font-bold text-white truncate mb-1"><?= esc($latestReview->project_name) ?> &mdash; <?= esc($latestReview->task_name) ?></h4>
                
                <div class="flex items-center justify-between text-[11px] text-slate-400 mb-3">
                    <span>By <?= esc($latestReview->artist_name) ?></span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $latestReview->status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' ?> uppercase">
                        <?= esc($latestReview->status) ?>
                    </span>
                </div>

                <a href="/admin/reviews" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold text-xs py-2.5 rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-md shadow-purple-900/30">
                    <span>Launch Review Player</span>
                    <span class="material-symbols-outlined text-[15px]">play_arrow</span>
                </a>
            <?php else: ?>
                <div class="py-8 text-center text-slate-500 text-xs">
                    No recent review submissions.
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ==================== DESKTOP VIEW (100% Preserved) ==================== -->
    <div class="hidden md:block">
        <div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-red-400 border border-red-900/50 shrink-0">
                        <span class="material-symbols-outlined text-[24px]">dashboard</span>
                    </div>
                    <div>
                        <h2 class="text-[24px] font-medium text-ytText leading-tight">Dashboard</h2>
                        <p class="text-[13px] text-ytMuted mt-1">Overview of your studio's activity</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Desktop 3-Col Widget Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Widget 1: Latest Review Submission -->
            <div class="bg-ytCard border border-ytBorder rounded-xl p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-[15px] font-medium text-ytText mb-4">Latest Review Submission</h3>
                    
                    <?php if (isset($latestReview) && $latestReview): ?>
                        <div class="w-full h-32 bg-[#121212] rounded-lg mb-4 flex items-center justify-center border border-ytBorder relative overflow-hidden group">
                            <?php if ($latestReview->file_type === 'video'): ?>
                                <video src="<?= media_cdn_url($latestReview->proxy_path) ?>" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                                <div class="absolute bottom-2 right-2 bg-black/80 px-1 rounded text-xs text-ytText"><span class="material-symbols-outlined text-[12px] align-middle">movie</span></div>
                            <?php else: ?>
                                <img src="<?= media_cdn_url($latestReview->proxy_path) ?>" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity">
                                <div class="absolute bottom-2 right-2 bg-black/80 px-1 rounded text-xs text-ytText"><span class="material-symbols-outlined text-[12px] align-middle">image</span></div>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-[13px] font-medium text-ytText mb-2 truncate"><?= esc($latestReview->project_name) ?> - <?= esc($latestReview->task_name) ?></p>
                        
                        <div class="flex items-center text-xs text-ytMuted space-x-3 mb-6">
                            <span class="flex items-center"><span class="material-symbols-outlined text-[14px] mr-1">person</span> <?= esc($latestReview->artist_name) ?></span>
                            <span class="flex items-center"><span class="material-symbols-outlined text-[14px] mr-1">schedule</span> <?= date('M d', strtotime($latestReview->created_at)) ?></span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between text-[13px]">
                                <span class="text-ytMuted">Status</span>
                                <span class="text-ytText capitalize"><?= esc($latestReview->status) ?></span>
                            </div>
                            <div class="flex justify-between text-[13px]">
                                <span class="text-ytMuted">Version</span>
                                <span class="text-ytText"><?= esc($latestReview->version_string ?? ('v' . ($latestReview->version_number ?? 1))) ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex-1 flex items-center justify-center text-ytMuted text-[13px] py-10">
                            No recent review submissions.
                        </div>
                    <?php endif; ?>
                </div>

                <a href="/admin/reviews" class="mt-4 text-[13px] font-medium text-ytBlue hover:text-blue-400 block text-center border border-ytBorder rounded py-1.5 hover:bg-ytHover transition-colors">
                    Go to Review Inbox &rarr;
                </a>
            </div>

            <!-- Widget 2: Studio Analytics -->
            <div class="bg-ytCard border border-ytBorder rounded-xl flex flex-col">
                <div class="p-6 border-b border-ytBorder/50">
                    <h3 class="text-[15px] font-medium text-ytText mb-4">Studio analytics</h3>
                    <p class="text-xs text-ytMuted mb-2">Active Projects</p>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold text-ytText"><?= isset($activeProjectsCount) ? $activeProjectsCount : 0 ?></span>
                    </div>
                </div>
                
                <div class="p-6 border-b border-ytBorder/50">
                    <h4 class="text-[15px] font-medium text-ytText mb-3">Summary</h4>
                    <p class="text-xs text-ytMuted mb-3">Last 28 days</p>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-[13px]">
                            <span class="text-ytText">Tasks completed</span>
                            <span class="text-ytMuted flex items-center"><?= isset($completedTasksCount) ? $completedTasksCount : 0 ?> <span class="material-symbols-outlined text-[14px] text-green-500 ml-1">check_circle</span></span>
                        </div>
                        <div class="flex justify-between text-[13px]">
                            <span class="text-ytText">Pending Reviews</span>
                            <span class="text-ytMuted flex items-center"><?= isset($pendingReviewsCount) ? $pendingReviewsCount : 0 ?> <span class="material-symbols-outlined text-[14px] text-orange-500 ml-1">pending_actions</span></span>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <h4 class="text-[15px] font-medium text-ytText mb-3">Top projects</h4>
                    <p class="text-xs text-ytMuted mb-3">By total active tasks</p>
                    
                    <div class="space-y-3">
                        <?php if (isset($topProjects) && !empty($topProjects)): ?>
                            <?php foreach($topProjects as $tp): ?>
                                <div class="flex justify-between text-[13px]">
                                    <span class="text-ytText truncate max-w-[180px]"><?= esc($tp->name) ?></span>
                                    <span class="text-ytMuted"><?= esc($tp->task_count) ?> tasks</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-[13px] text-ytMuted">No active projects found.</div>
                        <?php endif; ?>
                    </div>
                    
                    <a href="/admin/projects" class="mt-4 inline-block text-[13px] font-medium text-ytBlue hover:text-blue-400">Go to all projects</a>
                </div>
            </div>

            <!-- Widget 3: Studio Economics -->
            <div class="bg-ytCard border border-ytBorder rounded-xl p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[15px] font-medium text-ytText">Studio Economics</h3>
                        <span class="bg-green-950/70 border border-green-700/50 text-green-300 px-2 py-0.5 rounded text-[10px] font-mono font-bold">
                            LIVE
                        </span>
                    </div>

                    <div class="space-y-4">
                        <!-- Benchmark Quote -->
                        <div class="bg-[#121212] border border-ytBorder/60 rounded-lg p-3">
                            <div class="text-[11px] text-ytMuted uppercase font-bold tracking-wider">Total Pipeline Benchmark Quote</div>
                            <div class="text-[22px] font-bold text-ytBlue font-mono mt-0.5">
                                <?= esc($studioCurrency) ?><?= number_format($totalPipelineBudget, 0) ?>
                            </div>
                            <div class="text-[11px] text-ytMuted font-mono mt-0.5">
                                Across <?= round($totalPipelineHours, 1) ?> estimated production hours
                            </div>
                        </div>

                        <!-- Locked Budget (if set) -->
                        <div class="bg-[#121212] border border-green-700/40 rounded-lg p-3">
                            <div class="flex items-center justify-between text-[11px] uppercase font-bold tracking-wider text-green-400">
                                <span>Agreed / Locked Client Revenue</span>
                                <span class="material-symbols-outlined text-[15px]">lock</span>
                            </div>
                            <div class="text-[22px] font-bold text-green-300 font-mono mt-0.5">
                                <?= esc($studioCurrency) ?><?= number_format($totalLockedBudget > 0 ? $totalLockedBudget : $totalPipelineBudget, 0) ?>
                            </div>
                            <div class="text-[11px] text-ytMuted font-mono mt-0.5">
                                <?= $totalLockedBudget > 0 ? 'Scaled across active project caps' : '100% of benchmark quote' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-ytBorder/50 flex items-center justify-between">
                    <a href="/admin/budgeting" class="text-[13px] font-medium text-green-400 hover:text-green-300 flex items-center gap-1">
                        <span>Manage Monthly Bills</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                    <a href="/admin/projects" class="text-[13px] font-medium text-ytMuted hover:text-ytText">
                        View Projects &rarr;
                    </a>
                </div>
            </div>
            
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
