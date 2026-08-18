<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 border-b border-ytBorder/50 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="p-2.5 bg-gradient-to-tr from-blue-900/40 to-indigo-900/30 rounded-2xl flex items-center justify-center text-blue-400 border border-blue-800/40 shadow-lg shadow-blue-950/40">
                <span class="material-symbols-outlined text-[26px]">business_center</span>
            </div>
            <div>
                <h2 class="text-[22px] font-bold text-ytText leading-tight">Welcome back, <?= esc(session()->get('userName')) ?></h2>
                <p class="text-[13px] text-ytMuted mt-0.5">Executive overview of your project budgets, milestones, and deliverables</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-[#121c2a] border border-blue-900/40 px-3.5 py-1.5 rounded-full flex items-center gap-2 text-xs font-semibold text-blue-400">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                <span><?= $kpis['active_projects_count'] ?> Active <?= $kpis['active_projects_count'] === 1 ? 'Project' : 'Projects' ?></span>
            </div>
        </div>
    </div>
</div>

<!-- 1. Executive Summary KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    
    <!-- KPI 1: Agreed / Allocated Budget -->
    <div class="bg-ytCard border border-ytBorder/80 rounded-2xl p-4 flex flex-col justify-between hover:border-blue-500/40 transition-colors shadow-lg shadow-black/20">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-ytMuted uppercase tracking-wider">Allocated Budget</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">payments</span>
            </div>
        </div>
        <div>
            <div class="text-[24px] font-bold text-white tracking-tight">
                <?= esc($currency) ?><?= number_format($kpis['total_agreed_budget'], 0) ?>
            </div>
            <p class="text-[11px] text-ytMuted mt-1 flex items-center gap-1">
                <span class="text-emerald-400 font-semibold">Agreed Scope</span> across active projects
            </p>
        </div>
    </div>

    <!-- KPI 2: Production Hours & Turnaround -->
    <div class="bg-ytCard border border-ytBorder/80 rounded-2xl p-4 flex flex-col justify-between hover:border-blue-500/40 transition-colors shadow-lg shadow-black/20">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-ytMuted uppercase tracking-wider">Estimated Time</span>
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">schedule</span>
            </div>
        </div>
        <div>
            <div class="text-[24px] font-bold text-white tracking-tight flex items-baseline gap-2">
                <span><?= number_format($kpis['total_estimated_hours'], 0) ?> <span class="text-xs font-semibold text-ytMuted font-sans">hrs</span></span>
            </div>
            <p class="text-[11px] text-ytMuted mt-1 flex items-center justify-between">
                <span><?= number_format($kpis['total_completed_hours'], 0) ?> hrs done</span>
                <span class="text-indigo-400 font-semibold"><?= number_format($kpis['hours_remaining'], 0) ?> hrs left</span>
            </p>
        </div>
    </div>

    <!-- KPI 3: Task Progress & Velocity -->
    <div class="bg-ytCard border border-ytBorder/80 rounded-2xl p-4 flex flex-col justify-between hover:border-blue-500/40 transition-colors shadow-lg shadow-black/20">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-ytMuted uppercase tracking-wider">Task Completion</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">task_alt</span>
            </div>
        </div>
        <div>
            <div class="flex items-center justify-between text-[24px] font-bold text-white tracking-tight">
                <span><?= $kpis['overall_progress'] ?>%</span>
                <span class="text-xs font-semibold text-ytMuted font-sans"><?= $kpis['completed_tasks'] ?> / <?= $kpis['total_tasks'] ?> Done</span>
            </div>
            <div class="w-full bg-[#111111] rounded-full h-1.5 border border-ytBorder/50 mt-2 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-500 h-1.5 rounded-full" style="width: <?= $kpis['overall_progress'] ?>%"></div>
            </div>
        </div>
    </div>

    <!-- KPI 4: Shots & Deliveries -->
    <div class="bg-ytCard border border-ytBorder/80 rounded-2xl p-4 flex flex-col justify-between hover:border-blue-500/40 transition-colors shadow-lg shadow-black/20">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-ytMuted uppercase tracking-wider">Shots & Lineups</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">movie</span>
            </div>
        </div>
        <div>
            <div class="text-[24px] font-bold text-white tracking-tight">
                <?= $kpis['total_shots'] ?> <span class="text-xs font-semibold text-ytMuted font-sans">Shots</span>
            </div>
            <p class="text-[11px] text-ytMuted mt-1 flex items-center gap-1.5">
                <span class="text-amber-400 font-semibold"><?= $kpis['in_review_tasks'] ?> in review</span> • <?= $kpis['in_progress_tasks'] ?> in production
            </p>
        </div>
    </div>

</div>

<!-- 2. Main Content: Projects Grid & Deliveries Feed -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Active Projects Detailed Cards -->
    <div class="col-span-1 lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-[15px] font-bold text-ytText flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-ytBlue">folder_open</span> Active Project Portfolios
            </h3>
        </div>
        
        <?php if (empty($projects)): ?>
            <div class="bg-ytCard border border-ytBorder rounded-2xl p-16 text-center">
                <span class="material-symbols-outlined text-[48px] text-ytMuted mb-3 block">inbox</span>
                <p class="text-ytText font-semibold text-[15px]">No active projects found.</p>
                <p class="text-ytMuted text-[13px] mt-1">You don't have any projects assigned currently.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($projects as $project): ?>
                    <div class="bg-ytCard border border-ytBorder/90 rounded-2xl p-5 hover:border-ytBlue/50 transition-all flex flex-col justify-between shadow-lg shadow-black/10 group">
                        <div>
                            <!-- Project Header -->
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-bold text-[16px] text-ytText group-hover:text-blue-400 transition-colors leading-tight">
                                        <?= esc($project->name) ?>
                                    </h3>
                                    <?php if(!empty($project->project_code)): ?>
                                        <span class="text-[10px] font-mono text-slate-400 font-semibold tracking-wider uppercase"><?= esc($project->project_code) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="bg-[#121c2a] text-blue-400 border border-blue-900/50 px-2.5 py-0.5 rounded-full text-[10px] uppercase tracking-wider font-semibold shrink-0 ml-2">Active</span>
                            </div>

                            <p class="text-ytMuted text-[12px] line-clamp-2 mb-4 h-[36px]"><?= esc($project->description ?? 'No description provided.') ?></p>
                            
                            <!-- Budget & Production Metrics Row -->
                            <div class="grid grid-cols-2 gap-2 mb-4 p-3 bg-[#0d121c] border border-slate-800/80 rounded-xl text-xs">
                                <div>
                                    <span class="text-[10px] text-ytMuted block font-medium uppercase tracking-wider">Agreed Budget</span>
                                    <span class="text-white font-bold font-mono text-[13px] text-emerald-400">
                                        <?php if(!empty($project->agreed_budget) && (float)$project->agreed_budget > 0): ?>
                                            <?= esc($currency) ?><?= number_format($project->agreed_budget, 0) ?>
                                        <?php else: ?>
                                            <span class="text-slate-500 font-sans text-xs">In Scope</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-ytMuted block font-medium uppercase tracking-wider">Estimated Time</span>
                                    <span class="text-white font-semibold text-[13px]">
                                        <?= number_format($project->stats['estimated_hours'] ?? 0, 0) ?> hrs
                                    </span>
                                </div>
                            </div>

                            <!-- Progress & Tasks Breakdown -->
                            <?php if(isset($project->stats) && $project->stats['total'] > 0): ?>
                                <div class="mb-4">
                                    <div class="flex justify-between text-[11px] mb-1.5">
                                        <span class="text-ytMuted font-medium">Milestone Progress</span>
                                        <span class="text-white font-bold"><?= $project->stats['progress'] ?>% (<?= $project->stats['completed'] ?>/<?= $project->stats['total'] ?> Tasks)</span>
                                    </div>
                                    <div class="w-full bg-[#111111] rounded-full h-1.5 border border-ytBorder/50 overflow-hidden">
                                        <div class="bg-gradient-to-r from-blue-600 to-indigo-500 h-1.5 rounded-full" style="width: <?= $project->stats['progress'] ?>%"></div>
                                    </div>

                                    <!-- Status Pills -->
                                    <div class="grid grid-cols-3 gap-1.5 mt-3 text-[10px] font-medium text-center">
                                        <div class="bg-yellow-900/10 text-yellow-400/90 py-1 px-1.5 rounded border border-yellow-900/30 truncate">
                                            <?= $project->stats['pending'] + $project->stats['in_progress'] ?> In Progress
                                        </div>
                                        <div class="bg-purple-900/10 text-purple-400/90 py-1 px-1.5 rounded border border-purple-900/30 truncate">
                                            <?= $project->stats['review'] ?> In Review
                                        </div>
                                        <div class="bg-emerald-900/10 text-emerald-400/90 py-1 px-1.5 rounded border border-emerald-900/30 truncate">
                                            <?= $project->stats['completed'] ?> Done
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-4 flex items-center justify-center h-[60px] bg-[#0d121c] border border-slate-800/80 rounded-xl border-dashed">
                                    <span class="text-ytMuted text-[11px]">Tasks will appear as breakdown is prepared</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-2 pt-2 border-t border-ytBorder/40">
                            <?php if(!empty($project->stats['primary_sequence'])): ?>
                                <a href="/client/reviews/sequence/<?= $project->stats['primary_sequence']->id ?>" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-[12px] py-2 px-3 rounded-xl flex items-center justify-center gap-2 transition-all shadow-md shadow-blue-900/20">
                                    <span class="material-symbols-outlined text-[16px]">play_circle</span> 
                                    <span>Watch Sequence Lineup (<?= esc($project->stats['shots_count']) ?> Shots)</span>
                                </a>
                            <?php endif; ?>

                            <a href="/client/projects/<?= $project->id ?>/briefing" class="w-full bg-[#141a29] hover:bg-[#1f293d] text-slate-300 hover:text-white border border-slate-700/60 font-semibold text-[12px] py-2 px-3 rounded-xl flex items-center justify-center gap-2 transition-all">
                                <span class="material-symbols-outlined text-[16px] text-blue-400">edit_note</span> 
                                <span>Shot Briefing &amp; Reference Matrix</span>
                            </a>
                        </div>

                        <!-- Project Footer Dates -->
                        <div class="flex justify-between items-center text-[11px] pt-3 text-ytMuted">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">calendar_today</span> 
                                <?= date('M d, Y', strtotime($project->created_at)) ?>
                            </span>
                            
                            <?php if(!empty($project->deadline)): ?>
                                <span class="flex items-center gap-1 text-amber-400/90 font-medium">
                                    <span class="material-symbols-outlined text-[13px]">event</span> 
                                    Due <?= date('M d, Y', strtotime($project->deadline)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Deliveries & Submissions Feed -->
    <div class="col-span-1 space-y-4">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-[15px] font-bold text-ytText flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-ytBlue">rate_review</span> Recent Deliveries
            </h3>
        </div>
        
        <div class="bg-ytCard border border-ytBorder/90 rounded-2xl overflow-hidden shadow-lg shadow-black/10">
            <?php if (empty($reviews)): ?>
                <div class="p-8 text-center text-ytMuted">
                    <span class="material-symbols-outlined text-[36px] opacity-20 mb-3">done_all</span>
                    <p class="text-[13px] font-medium">You're all caught up!</p>
                    <p class="text-[11px] text-ytMuted/70 mt-1">No pending review deliveries at the moment.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-ytBorder/50 max-h-[560px] overflow-y-auto custom-scrollbar">
                    <?php foreach ($reviews as $rev): ?>
                        <a href="/client/reviews/player/<?= $rev->id ?>" class="block p-4 hover:bg-ytHover transition-all border-l-2 border-transparent hover:border-ytBlue group">
                            <div class="flex items-center gap-3.5">
                                <!-- Thumbnail -->
                                <div class="w-16 h-11 flex-shrink-0 bg-[#0d121c] border border-ytBorder/50 rounded-xl overflow-hidden flex items-center justify-center group-hover:border-ytBlue/50 transition-colors shadow">
                                    <?php if(!empty($rev->shot_thumb)): ?>
                                        <img src="<?= media_cdn_url(esc($rev->shot_thumb)) ?>" alt="Thumb" loading="lazy" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="material-symbols-outlined text-[20px] text-ytMuted">movie</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-0.5">
                                        <h4 class="font-bold text-[13px] text-ytText truncate pr-1 group-hover:text-blue-400 transition-colors">
                                            <?= esc($rev->project_name) ?>
                                        </h4>
                                        <?php if($rev->status === 'pending'): ?>
                                            <span class="bg-[#121c2a] text-blue-400 border border-blue-900/50 px-2 py-0.5 rounded-full text-[9px] uppercase tracking-wider font-bold shrink-0 ml-1">Pending</span>
                                        <?php elseif($rev->status === 'approved'): ?>
                                            <span class="bg-emerald-950/40 text-emerald-400 border border-emerald-800/40 px-2 py-0.5 rounded-full text-[9px] uppercase tracking-wider font-bold shrink-0 ml-1">Approved</span>
                                        <?php elseif($rev->status === 'revision_needed'): ?>
                                            <span class="bg-red-950/40 text-red-400 border border-red-800/40 px-2 py-0.5 rounded-full text-[9px] uppercase tracking-wider font-bold shrink-0 ml-1">Revision</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="text-[12px] text-blue-400 font-semibold truncate mb-1">
                                        <?php if($rev->shot_number): ?>
                                            <?= !empty($rev->seq_name) ? esc($rev->seq_name) . ' / ' : '' ?><?= esc($rev->shot_number) ?>
                                        <?php else: ?>
                                            <?= esc($rev->asset_name ?? 'General Delivery') ?>
                                        <?php endif; ?>
                                    </p>

                                    <div class="flex justify-between items-center text-[10px] text-ytMuted">
                                        <span class="flex items-center text-slate-400">
                                            <span class="material-symbols-outlined text-[12px] mr-1">history</span> <?= esc($rev->version_string) ?>
                                        </span>
                                        <span class="text-slate-500 font-medium">
                                            <?= date('M d, g:i A', strtotime($rev->created_at)) ?>
                                        </span>
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
    .custom-scrollbar::-webkit-scrollbar-track { background: #0c1017; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #334155; }
</style>

<?= $this->endSection() ?>
