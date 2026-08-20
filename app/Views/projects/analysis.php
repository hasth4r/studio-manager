<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Sticky Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 border-b border-ytBorder/50 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="/projects/<?= $project->id ?>" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center text-ytMuted" title="Back to Project Overview">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-[24px] font-medium text-ytText"><?= esc($project->name) ?></h2>
                    <span class="bg-[#1a1a1a] text-ytMuted border border-ytBorder/50 px-2 py-0.5 rounded text-[11px] font-mono"><?= esc($project->project_code) ?></span>
                    <span class="bg-blue-950/70 border border-blue-700/50 text-blue-300 px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider">
                        Production &amp; Risk Intelligence
                    </span>
                </div>
                <p class="text-[13px] text-ytMuted mt-1">
                    Client: <span class="text-ytBlue font-medium"><?= esc($project->client_name) ?></span> &bull; 
                    Type: <?= esc($project->project_type_name) ?> &bull; 
                    Start: <span class="text-ytText font-mono"><?= $startDt->format('M d, Y') ?></span> &bull; 
                    Deadline: <span class="text-ytText font-mono"><?= $deadlineDt->format('M d, Y') ?></span>
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="/projects/<?= $project->id ?>/breakdown" class="bg-[#181818] border border-ytBlue/50 hover:border-ytBlue text-ytText px-4 py-2 rounded-lg font-medium text-[13px] hover:bg-ytHover transition-all flex items-center gap-1.5 shadow-[0_0_12px_rgba(23,123,207,0.15)]">
                <span class="material-symbols-outlined text-[16px] text-ytBlue">table_chart</span>
                <span>Shot Breakdown Matrix</span>
            </a>
            <a href="/projects/<?= $project->id ?>" class="bg-ytCard border border-ytBorder text-ytText px-4 py-2 rounded-lg font-medium text-[13px] hover:bg-ytHover transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">grid_view</span>
                <span>Project Cards</span>
            </a>
        </div>
    </div>
</div>

<!-- 4 Top Executive KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    
    <!-- 1. Timeline & Feasibility -->
    <div class="bg-ytCard border <?= $isOverdue ? 'border-red-700/60 bg-red-950/20' : ($workingDaysRemaining <= 3 ? 'border-amber-700/60 bg-amber-950/20' : 'border-ytBorder') ?> rounded-xl p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] text-ytMuted font-bold uppercase tracking-wider">Schedule Feasibility</span>
            <?php if($isOverdue): ?>
                <span class="bg-red-950 border border-red-700 text-red-300 px-2 py-0.5 rounded text-[10px] font-bold font-mono">OVERDUE</span>
            <?php elseif($hoursDeficitOrSurplus < 0): ?>
                <span class="bg-amber-950 border border-amber-700 text-amber-300 px-2 py-0.5 rounded text-[10px] font-bold font-mono">AT RISK</span>
            <?php else: ?>
                <span class="bg-green-950 border border-green-700 text-green-300 px-2 py-0.5 rounded text-[10px] font-bold font-mono">FEASIBLE</span>
            <?php endif; ?>
        </div>
        <div class="my-3">
            <div class="text-[28px] font-bold font-mono <?= $isOverdue ? 'text-red-400' : 'text-ytText' ?>">
                <?= $isOverdue ? abs($daysRemaining) . 'd Overdue' : $workingDaysRemaining . ' Working Days' ?>
            </div>
            <div class="text-[12px] text-ytMuted font-mono mt-0.5">
                Need <b class="text-ytBlue"><?= $requiredDailyHours ?> hrs / day</b> velocity
            </div>
        </div>
        <div class="text-[11px] text-ytMuted border-t border-ytBorder/50 pt-2 flex justify-between">
            <span>Remaining Est:</span>
            <span class="font-mono text-ytText font-bold"><?= $remainingHours ?>h / <?= $totalEstHours ?>h</span>
        </div>
    </div>

    <!-- 2. Artist Capacity (Artists Only) -->
    <div class="bg-ytCard border <?= $additionalArtistsNeeded > 0 ? 'border-amber-700/60 bg-amber-950/20' : 'border-ytBorder' ?> rounded-xl p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] text-ytMuted font-bold uppercase tracking-wider">Artist Capacity</span>
            <span class="text-[10px] font-bold font-mono text-purple-300 bg-purple-950/80 border border-purple-700/50 px-1.5 py-0.5 rounded">
                ARTISTS ONLY
            </span>
        </div>
        <div class="my-3">
            <div class="text-[28px] font-bold font-mono text-purple-300">
                <?= $activeArtistsCount ?> Active Artists
            </div>
            <div class="text-[12px] text-ytMuted font-mono mt-0.5">
                Team Capacity: <b class="text-ytText"><?= $dailyTeamCapacity ?>h / day</b>
            </div>
        </div>
        <div class="text-[11px] text-ytMuted border-t border-ytBorder/50 pt-2 flex justify-between">
            <span>Staffing Gap:</span>
            <?php if($additionalArtistsNeeded > 0): ?>
                <span class="font-mono text-red-400 font-bold">Deficit: Need +<?= $additionalArtistsNeeded ?> Artists</span>
            <?php else: ?>
                <span class="font-mono text-green-400 font-bold">+<?= $hoursDeficitOrSurplus ?>h Surplus buffer</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Financial Profit / Loss -->
    <div class="bg-ytCard border <?= $isLoss ? 'border-red-700/60 bg-red-950/20' : ($projectedMarginPct < 15 ? 'border-amber-700/60 bg-amber-950/20' : 'border-green-700/40') ?> rounded-xl p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] text-ytMuted font-bold uppercase tracking-wider">Projected P&amp;L</span>
            <?php if($isLoss): ?>
                <span class="bg-red-950 border border-red-700 text-red-300 px-2 py-0.5 rounded text-[10px] font-bold font-mono">NET LOSS</span>
            <?php elseif($agreedBudget > 0): ?>
                <span class="bg-green-950 border border-green-700 text-green-300 px-2 py-0.5 rounded text-[10px] font-bold font-mono"><?= $projectedMarginPct ?>% MARGIN</span>
            <?php else: ?>
                <span class="bg-blue-950 border border-blue-700 text-blue-300 px-2 py-0.5 rounded text-[10px] font-bold font-mono"><?= $commissionPct ?>% STD MARGIN</span>
            <?php endif; ?>
        </div>
        <div class="my-3">
            <div class="text-[28px] font-bold font-mono <?= $isLoss ? 'text-red-400' : 'text-green-300' ?>">
                <?= $isLoss ? '-' . esc($studioCurrency) . number_format($lossAmount, 0) : '+' . esc($studioCurrency) . number_format($projectedNetProfit, 0) ?>
            </div>
            <div class="text-[12px] text-ytMuted font-mono mt-0.5">
                Revenue: <b class="text-ytText"><?= esc($studioCurrency) ?><?= number_format($effectiveRevenue, 0) ?></b>
                <?php if($agreedBudget > 0): ?>
                    <span class="text-green-400 font-bold">(Locked)</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-[11px] text-ytMuted border-t border-ytBorder/50 pt-2 flex justify-between">
            <span>Raw Costs (Artist + Ops):</span>
            <span class="font-mono text-ytText font-bold"><?= esc($studioCurrency) ?><?= number_format($rawTotalCost, 0) ?></span>
        </div>
    </div>

    <!-- 4. Risk Index -->
    <div class="bg-ytCard border border-ytBorder rounded-xl p-5 flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] text-ytMuted font-bold uppercase tracking-wider">Identified Risks</span>
            <span class="text-[10px] font-bold font-mono bg-ytHover border border-ytBorder px-2 py-0.5 rounded text-ytText">
                <?= count($risks) ?> Issues
            </span>
        </div>
        <div class="my-3">
            <div class="text-[28px] font-bold font-mono <?= count($risks) > 0 ? (count(array_filter($risks, fn($r) => $r['level'] === 'CRITICAL')) > 0 ? 'text-red-400' : 'text-amber-400') : 'text-green-400' ?>">
                <?= count($risks) > 0 ? (count(array_filter($risks, fn($r) => $r['level'] === 'CRITICAL')) > 0 ? 'CRITICAL' : 'ELEVATED') : 'CLEAR' ?>
            </div>
            <div class="text-[12px] text-ytMuted font-mono mt-0.5">
                Unassigned Tasks: <b class="<?= $unassignedTaskCount > 0 ? 'text-amber-400' : 'text-green-400' ?>"><?= $unassignedTaskCount ?></b> (<?= $unassignedHours ?>h)
            </div>
        </div>
        <div class="text-[11px] text-ytMuted border-t border-ytBorder/50 pt-2 flex justify-between">
            <span>Daily Burn Rate:</span>
            <span class="font-mono text-ytText font-bold"><?= esc($studioCurrency) ?><?= number_format($dailyBurnRate, 0) ?> / day</span>
        </div>
    </div>

</div>

<!-- 2-Column Intelligence Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <!-- Left 2 Cols: Production Feasibility & Risks -->
    <div class="lg:col-span-2 space-y-6">

        <!-- 1. Production Timeline & Velocity Health -->
        <div class="bg-ytCard border border-ytBorder rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-ytBlue">timer</span>
                    <h3 class="text-[16px] font-medium text-ytText">Production Velocity &amp; Deadline Feasibility</h3>
                </div>
                <span class="text-[12px] font-mono text-ytMuted">
                    <?= $completionPct ?>% Completed (<?= $completedHours ?>h of <?= $totalEstHours ?>h)
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-[#121212] border border-ytBorder/60 rounded-full h-3.5 mb-6 overflow-hidden flex">
                <div class="bg-green-500 h-full transition-all" style="width: <?= min(100, $completionPct) ?>%" title="Completed: <?= $completedHours ?>h"></div>
                <?php 
                    $inProgressPct = $totalEstHours > 0 ? round(($inProgressHours / $totalEstHours) * 100, 1) : 0;
                ?>
                <div class="bg-blue-500 h-full transition-all" style="width: <?= min(100 - $completionPct, $inProgressPct) ?>%" title="In Progress: <?= $inProgressHours ?>h"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-mono text-[12px]">
                <div class="bg-[#121212] border border-ytBorder/40 rounded-lg p-3">
                    <div class="text-[11px] text-ytMuted uppercase font-bold">Remaining Workload</div>
                    <div class="text-[18px] font-bold text-ytText mt-0.5"><?= $remainingHours ?> hrs</div>
                    <div class="text-[10px] text-ytMuted mt-1"><?= $inProgressHours ?>h in progress &bull; <?= $pendingHours ?>h pending</div>
                </div>
                <div class="bg-[#121212] border border-ytBorder/40 rounded-lg p-3">
                    <div class="text-[11px] text-ytMuted uppercase font-bold">Required Velocity</div>
                    <div class="text-[18px] font-bold text-ytBlue mt-0.5"><?= $requiredDailyHours ?> hrs / day</div>
                    <div class="text-[10px] text-ytMuted mt-1">Across <?= $workingDaysRemaining ?> remaining working days</div>
                </div>
                <div class="bg-[#121212] border <?= $hoursDeficitOrSurplus < 0 ? 'border-red-700/60 bg-red-950/10' : 'border-green-700/40 bg-green-950/10' ?> rounded-lg p-3">
                    <div class="text-[11px] text-ytMuted uppercase font-bold">Team Output Gap</div>
                    <div class="text-[18px] font-bold <?= $hoursDeficitOrSurplus < 0 ? 'text-red-400' : 'text-green-400' ?> mt-0.5">
                        <?= $hoursDeficitOrSurplus < 0 ? '-' . abs($hoursDeficitOrSurplus) . ' hrs Deficit' : '+' . $hoursDeficitOrSurplus . ' hrs Buffer' ?>
                    </div>
                    <div class="text-[10px] text-ytMuted mt-1">Capacity: <?= $totalDeliverableHours ?>h deliverable</div>
                </div>
            </div>
        </div>

        <!-- 2. Identified Risks & Recommended Actions -->
        <div class="bg-ytCard border border-ytBorder rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-amber-400">warning</span>
                    <h3 class="text-[16px] font-medium text-ytText">Identified Risks &amp; Issue Mitigations</h3>
                </div>
                <span class="text-[12px] font-mono text-ytMuted">Automated Studio Health Check</span>
            </div>

            <?php if(empty($risks)): ?>
                <div class="bg-green-950/30 border border-green-700/40 rounded-lg p-4 text-center text-green-300 text-[13px] flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-green-400">verified</span>
                    <span>No critical production or financial risks detected. Project is healthy!</span>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach($risks as $r): ?>
                        <div class="border <?= $r['level'] === 'CRITICAL' ? 'border-red-700/60 bg-red-950/20' : ($r['level'] === 'HIGH' ? 'border-amber-700/60 bg-amber-950/20' : 'border-ytBorder/60 bg-[#121212]') ?> rounded-lg p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono <?= $r['level'] === 'CRITICAL' ? 'bg-red-900 text-red-200' : ($r['level'] === 'HIGH' ? 'bg-amber-900 text-amber-200' : 'bg-ytHover text-ytText') ?>">
                                        <?= $r['level'] ?>
                                    </span>
                                    <h4 class="text-[14px] font-bold text-ytText"><?= esc($r['title']) ?></h4>
                                </div>
                            </div>
                            <p class="text-[12px] text-ytMuted mt-2 leading-relaxed">
                                <?= esc($r['description']) ?>
                            </p>
                            <div class="mt-3 pt-2.5 border-t border-ytBorder/40 flex items-center gap-2 text-[12px] text-ytBlue">
                                <span class="material-symbols-outlined text-[16px]">lightbulb</span>
                                <span><b>Recommended Mitigation:</b> <?= esc($r['action']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. Artist Workload & Capacity Table (Strictly Artists) -->
        <div class="bg-ytCard border border-ytBorder rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-400">group</span>
                    <h3 class="text-[16px] font-medium text-ytText">Assigned Production Artists Breakdown</h3>
                </div>
                <span class="text-[12px] font-mono text-purple-300 font-bold bg-purple-950/80 border border-purple-700/50 px-2 py-0.5 rounded">
                    <?= $activeArtistsCount ?> Artists Assigned
                </span>
            </div>

            <?php if(empty($artistWorkloadMap)): ?>
                <div class="bg-[#121212] border border-ytBorder/40 rounded-lg p-6 text-center text-ytMuted text-[13px]">
                    <span class="material-symbols-outlined text-[32px] text-ytMuted mb-2">person_off</span>
                    <p class="text-ytText font-medium">No artists assigned to this project yet.</p>
                    <p class="text-[12px] mt-1">Open the Breakdown Matrix to assign tasks to artists.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[13px]">
                        <thead>
                            <tr class="border-b border-ytBorder/50 text-ytMuted text-[11px] uppercase tracking-wider bg-[#141414]">
                                <th class="py-2.5 px-3">Artist</th>
                                <th class="py-2.5 px-3">Role</th>
                                <th class="py-2.5 px-3 text-center">Tasks</th>
                                <th class="py-2.5 px-3 text-right">Assigned Hours</th>
                                <th class="py-2.5 px-3 text-right">Workload Share</th>
                                <th class="py-2.5 px-3 text-right">Raw Payout</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ytBorder/30 font-mono">
                            <?php foreach($artistWorkloadMap as $art): 
                                $sharePct = $totalEstHours > 0 ? round(($art['total_hours'] / $totalEstHours) * 100, 1) : 0;
                            ?>
                            <tr class="hover:bg-[#181818] transition-colors">
                                <td class="py-2.5 px-3 font-medium text-ytText flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-purple-950 border border-purple-700/50 text-purple-300 flex items-center justify-center text-[10px] font-bold">
                                        <?= strtoupper(substr($art['name'], 0, 2)) ?>
                                    </div>
                                    <span><?= esc($art['name']) ?></span>
                                </td>
                                <td class="py-2.5 px-3 text-ytMuted text-[11px] capitalize"><?= esc($art['role']) ?></td>
                                <td class="py-2.5 px-3 text-center text-ytText font-bold"><?= $art['tasks_count'] ?></td>
                                <td class="py-2.5 px-3 text-right text-ytBlue font-bold"><?= round($art['total_hours'], 1) ?>h</td>
                                <td class="py-2.5 px-3 text-right text-ytMuted"><?= $sharePct ?>%</td>
                                <td class="py-2.5 px-3 text-right text-green-400 font-bold"><?= esc($studioCurrency) ?><?= number_format($art['artist_cost'], 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Right 1 Col: Financial Health & Profit / Loss Statement -->
    <div class="space-y-6">

        <!-- Financial Statement Card -->
        <div class="bg-ytCard border border-ytBorder rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-400">payments</span>
                    <h3 class="text-[16px] font-medium text-ytText">Project P&amp;L Statement</h3>
                </div>
            </div>

            <!-- Revenue Section -->
            <div class="space-y-3 mb-6">
                <div class="bg-[#121212] border border-ytBorder/60 rounded-lg p-3.5">
                    <div class="flex items-center justify-between text-[11px] text-ytMuted uppercase font-bold">
                        <span>Standard Benchmark Quote</span>
                        <span class="text-ytText font-mono"><?= esc($studioCurrency) ?><?= number_format($idealClientBudget, 0) ?></span>
                    </div>
                </div>

                <div class="bg-[#121212] border border-green-700/50 rounded-lg p-3.5">
                    <div class="flex items-center justify-between text-[11px] uppercase font-bold text-green-400">
                        <span>Client Agreed / Locked Budget</span>
                        <span class="font-mono text-[14px]"><?= esc($studioCurrency) ?><?= number_format($agreedBudget > 0 ? $agreedBudget : $idealClientBudget, 0) ?></span>
                    </div>
                    <?php if($agreedBudget > 0): ?>
                        <div class="text-[10px] text-ytMuted font-mono mt-1 flex justify-between">
                            <span>Quote Realization Rate:</span>
                            <span class="text-green-300 font-bold"><?= round($scaleFactor * 100, 1) ?>% of benchmark</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Direct Costs Breakdown -->
            <div class="border-t border-ytBorder/50 pt-4 mb-6">
                <div class="text-[11px] text-ytMuted uppercase font-bold tracking-wider mb-3">Direct Production Costs</div>
                <div class="space-y-2 text-[12px] font-mono">
                    <div class="flex justify-between text-ytMuted">
                        <span>Freelancer Artist Pool:</span>
                        <span class="text-ytText font-bold"><?= esc($studioCurrency) ?><?= number_format($rawArtistCost, 0) ?></span>
                    </div>
                    <div class="flex justify-between text-ytMuted">
                        <span>AI Tools &amp; Ops Recovery (<?= esc($studioCurrency) ?><?= $opsHourlyRate ?>/h):</span>
                        <span class="text-purple-300 font-bold"><?= esc($studioCurrency) ?><?= number_format($rawOpsCost, 0) ?></span>
                    </div>
                    <div class="flex justify-between text-ytText font-bold pt-2 border-t border-ytBorder/40">
                        <span>Total Raw Production Cost:</span>
                        <span class="text-ytText"><?= esc($studioCurrency) ?><?= number_format($rawTotalCost, 0) ?></span>
                    </div>
                </div>
            </div>

            <!-- Net Profit / Loss Bottom Line -->
            <div class="border-t border-ytBorder/50 pt-4">
                <div class="p-4 rounded-xl <?= $isLoss ? 'bg-red-950/40 border border-red-700/60' : 'bg-green-950/40 border border-green-700/60' ?>">
                    <div class="text-[11px] uppercase font-bold tracking-wider <?= $isLoss ? 'text-red-400' : 'text-green-400' ?>">
                        <?= $isLoss ? '🔴 Projected Net Deficit (Loss)' : '🟢 Projected Net Profit (Margin)' ?>
                    </div>
                    <div class="text-[26px] font-bold font-mono mt-1 <?= $isLoss ? 'text-red-300' : 'text-green-300' ?>">
                        <?= $isLoss ? '-' . esc($studioCurrency) . number_format($lossAmount, 0) : '+' . esc($studioCurrency) . number_format($projectedNetProfit, 0) ?>
                    </div>
                    <div class="text-[11px] font-mono mt-1 <?= $isLoss ? 'text-red-400' : 'text-green-400' ?>">
                        <?= $isLoss ? 'Direct cost overrun on locked budget' : 'Net Margin: ' . $projectedMarginPct . '% (Benchmark: ' . $commissionPct . '%)' ?>
                    </div>
                </div>
            </div>

            <!-- Quick Budget Actions -->
            <div class="mt-6 pt-4 border-t border-ytBorder/50">
                <a href="/admin/budgeting" class="text-[12px] font-medium text-ytBlue hover:text-blue-300 flex items-center justify-between">
                    <span>Manage Studio Rates &amp; Bills</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
        </div>

        <!-- Staffing Recommendation Box -->
        <div class="bg-ytCard border border-ytBorder rounded-xl p-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-purple-400">engineering</span>
                <h4 class="text-[14px] font-bold text-ytText">Studio Staffing Recommendation</h4>
            </div>
            <p class="text-[12px] text-ytMuted leading-relaxed">
                To deliver the remaining <b><?= $remainingHours ?> hours</b> within <b><?= $workingDaysRemaining ?> working days</b> at standard 8h/day shifts, your production pipeline needs:
            </p>
            <div class="my-4 p-3.5 bg-[#121212] border border-purple-700/40 rounded-lg flex items-center justify-between font-mono">
                <span class="text-[12px] text-ytMuted">Optimal Dedicated Artists:</span>
                <span class="text-[18px] font-bold text-purple-300"><?= $recommendedArtistsNeeded ?> Artists</span>
            </div>
            <p class="text-[11px] text-ytMuted">
                Currently assigned: <b class="text-ytText"><?= $activeArtistsCount ?> artists</b> (<?= $additionalArtistsNeeded > 0 ? "<span class='text-red-400 font-bold'>+{$additionalArtistsNeeded} more needed</span>" : "<span class='text-green-400 font-bold'>Fully staffed</span>" ?>).
            </p>
        </div>

    </div>

</div>

<?= $this->endSection() ?>
