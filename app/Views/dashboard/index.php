<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<?php if (in_array($userRole, ['admin', 'project_manager'])): ?>
    <div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-red-400 border border-red-900/50">
                    <span class="material-symbols-outlined">dashboard</span>
                </div>
                <div>
                    <h2 class="text-[24px] font-medium text-ytText">Dashboard</h2>
                    <p class="text-[13px] text-ytMuted mt-1">Overview of your studio's activity</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Count Cards Section (YT Studio Widget Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Widget 1 -->
        <div class="bg-ytCard border border-ytBorder rounded-xl p-6 flex flex-col justify-between">
            <h3 class="text-[15px] font-medium text-ytText mb-4">Latest Review Submission</h3>
            
            <?php if (isset($latestReview) && $latestReview): ?>
                <div class="w-full h-32 bg-[#121212] rounded-lg mb-4 flex items-center justify-center border border-ytBorder relative overflow-hidden group">
                    <?php if ($latestReview->file_type === 'video'): ?>
                        <video src="<?= base_url('media/serve/' . $latestReview->proxy_path) ?>" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                        <div class="absolute bottom-2 right-2 bg-black/80 px-1 rounded text-xs text-ytText"><span class="material-symbols-outlined text-[12px] align-middle">movie</span></div>
                    <?php else: ?>
                        <img src="<?= base_url('media/serve/' . $latestReview->proxy_path) ?>" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity">
                        <div class="absolute bottom-2 right-2 bg-black/80 px-1 rounded text-xs text-ytText"><span class="material-symbols-outlined text-[12px] align-middle">image</span></div>
                    <?php endif; ?>
                </div>
                
                <p class="text-[13px] font-medium text-ytText mb-2"><?= esc($latestReview->project_name) ?> - <?= esc($latestReview->task_name) ?></p>
                
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
                <a href="/admin/reviews" class="mt-4 text-[13px] font-medium text-ytBlue hover:text-blue-400 block text-center border border-ytBorder rounded py-1.5 hover:bg-ytHover transition-colors">Go to Review Inbox</a>
            <?php else: ?>
                <div class="flex-1 flex items-center justify-center text-ytMuted text-[13px]">
                    No recent review submissions.
                </div>
            <?php endif; ?>
        </div>

        <!-- Widget 2 (Analytics Style) -->
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

        <!-- Widget 3 (Studio Pipeline Economics) -->
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
<?php endif; ?>

<?= $this->endSection() ?>
