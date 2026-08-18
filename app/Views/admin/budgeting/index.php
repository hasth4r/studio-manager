<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Sticky Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2.5 bg-green-950/50 rounded-xl flex items-center justify-center text-green-400 border border-green-700/40 shadow-[0_0_15px_rgba(34,197,94,0.15)]">
                <span class="material-symbols-outlined text-[24px]">payments</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-[24px] font-semibold text-ytText">Studio Budgeting &amp; Economics</h2>
                    <span class="bg-green-950/60 border border-green-700/50 text-green-300 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold">
                        AUTO-CALCULATED
                    </span>
                </div>
                <p class="text-[13px] text-ytMuted mt-0.5">Input your actual monthly bills and profit margin — the system automates client quotes and artist payouts across all projects.</p>
            </div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('message')) : ?>
    <div class="bg-[#122a15] border border-green-900 text-green-200 px-4 py-3 rounded-xl mb-6 text-[13px] flex items-center shadow-lg">
        <span class="material-symbols-outlined mr-2 text-[18px]">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>

<!-- Top Economics KPIs: Horizontal Swipe Carousel on Mobile -->
<div class="flex overflow-x-auto gap-3 pb-2 snap-x custom-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible mb-6">
    <!-- Card 1: Total Monthly Bills -->
    <div class="min-w-[240px] sm:min-w-0 snap-center bg-ytCard border border-ytBorder rounded-2xl p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between text-ytMuted mb-2">
            <span class="text-[12px] uppercase font-bold tracking-wider">Monthly Studio Bills</span>
            <span class="material-symbols-outlined text-[20px] text-purple-400">receipt_long</span>
        </div>
        <div class="text-[26px] font-bold text-ytText font-mono">
            <?= esc($currency) ?><?= number_format($totalMonthlyBills, 0) ?><span class="text-[12px] text-ytMuted font-normal">/mo</span>
        </div>
        <div class="text-[11px] text-ytMuted mt-1">
            AI + R2 Storage + Software + VPS
        </div>
    </div>

    <!-- Card 2: Hourly Ops Cost -->
    <div class="min-w-[240px] sm:min-w-0 snap-center bg-ytCard border border-ytBorder rounded-2xl p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between text-ytMuted mb-2">
            <span class="text-[12px] uppercase font-bold tracking-wider">Ops Recovery Rate</span>
            <span class="material-symbols-outlined text-[20px] text-blue-400">speed</span>
        </div>
        <div class="text-[26px] font-bold text-blue-400 font-mono">
            <?= esc($currency) ?><?= number_format($opsHourlyRate, 2) ?><span class="text-[12px] text-ytMuted font-normal">/hr</span>
        </div>
        <div class="text-[11px] text-ytMuted mt-1">
            Based on <?= round($monthlyHours, 0) ?> active hrs/month
        </div>
    </div>

    <!-- Card 3: Studio Margin -->
    <div class="min-w-[240px] sm:min-w-0 snap-center bg-ytCard border border-ytBorder rounded-2xl p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between text-ytMuted mb-2">
            <span class="text-[12px] uppercase font-bold tracking-wider">Studio Margin</span>
            <span class="material-symbols-outlined text-[20px] text-amber-400">trending_up</span>
        </div>
        <div class="text-[26px] font-bold text-amber-300 font-mono">
            <?= esc($commissionPct) ?>%
        </div>
        <div class="text-[11px] text-ytMuted mt-1">
            Net studio commission on every task
        </div>
    </div>

    <!-- Card 4: Total Pipeline Budget -->
    <div class="min-w-[240px] sm:min-w-0 snap-center bg-ytCard border border-ytBorder rounded-2xl p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between text-ytMuted mb-2">
            <span class="text-[12px] uppercase font-bold tracking-wider">Total Active Pipeline</span>
            <span class="material-symbols-outlined text-[20px] text-green-400">account_balance</span>
        </div>
        <div class="text-[26px] font-bold text-green-400 font-mono">
            <?= esc($currency) ?><?= number_format($totalActiveClientBudget, 0) ?>
        </div>
        <div class="text-[11px] text-ytMuted mt-1">
            Across <?= count($projectBudgets) ?> active project(s)
        </div>
    </div>
</div>

<!-- Main Settings Form -->
<form action="/admin/budgeting/update" method="POST" id="budgetingForm">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        
        <!-- Left: Monthly Studio Bills Calculator -->
        <div class="lg:col-span-7 bg-ytCard border border-ytBorder rounded-xl overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-6 py-4 border-b border-ytBorder/60 bg-[#121212] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-400 text-[20px]">credit_card</span>
                        <h3 class="text-[15px] font-medium text-ytText">Actual Monthly Studio Bills &amp; Subscriptions</h3>
                    </div>
                    <span class="text-[11px] text-ytMuted font-mono">Enter your monthly credit card totals</span>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- AI Tools -->
                        <div>
                            <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-purple-400">smart_toy</span>
                                <span>AI Tools &amp; APIs (<?= esc($currency) ?>/mo)</span>
                            </label>
                            <input type="number" step="100" min="0" name="monthly_ai_bills" id="monthly_ai_bills" value="<?= esc($monthlyAiBills) ?>" required
                                   oninput="recalcLiveEconomics()"
                                   class="w-full bg-ytBg border border-ytBorder text-ytText font-mono font-bold text-[14px] rounded-lg px-3.5 py-2 focus:outline-none focus:border-ytBlue"
                                   placeholder="15000">
                            <p class="text-[10px] text-ytMuted mt-1">Midjourney, Claude, OpenAI, Cursor, Leonardo.</p>
                        </div>

                        <!-- Storage & Hosting -->
                        <div>
                            <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-blue-400">cloud</span>
                                <span>Storage &amp; VPS Servers (<?= esc($currency) ?>/mo)</span>
                            </label>
                            <input type="number" step="100" min="0" name="monthly_storage_bills" id="monthly_storage_bills" value="<?= esc($monthlyStorage) ?>" required
                                   oninput="recalcLiveEconomics()"
                                   class="w-full bg-ytBg border border-ytBorder text-ytText font-mono font-bold text-[14px] rounded-lg px-3.5 py-2 focus:outline-none focus:border-ytBlue"
                                   placeholder="8000">
                            <p class="text-[10px] text-ytMuted mt-1">Cloudflare R2, Hostinger VPS, AWS S3, Domains.</p>
                        </div>

                        <!-- Software Licenses -->
                        <div>
                            <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-amber-400">extension</span>
                                <span>Software &amp; Plugins (<?= esc($currency) ?>/mo)</span>
                            </label>
                            <input type="number" step="100" min="0" name="monthly_software_bills" id="monthly_software_bills" value="<?= esc($monthlySoftware) ?>" required
                                   oninput="recalcLiveEconomics()"
                                   class="w-full bg-ytBg border border-ytBorder text-ytText font-mono font-bold text-[14px] rounded-lg px-3.5 py-2 focus:outline-none focus:border-ytBlue"
                                   placeholder="10000">
                            <p class="text-[10px] text-ytMuted mt-1">Adobe CC, Blender addons, Nuke plugins, Slack.</p>
                        </div>

                        <!-- General Studio / Electricity / Internet -->
                        <div>
                            <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-emerald-400">bolt</span>
                                <span>Electricity &amp; Internet (<?= esc($currency) ?>/mo)</span>
                            </label>
                            <input type="number" step="100" min="0" name="monthly_ops_bills" id="monthly_ops_bills" value="<?= esc($monthlyOps) ?>" required
                                   oninput="recalcLiveEconomics()"
                                   class="w-full bg-ytBg border border-ytBorder text-ytText font-mono font-bold text-[14px] rounded-lg px-3.5 py-2 focus:outline-none focus:border-ytBlue"
                                   placeholder="5000">
                            <p class="text-[10px] text-ytMuted mt-1">High-speed fiber internet, power backup, misc.</p>
                        </div>
                    </div>

                    <!-- Expected Active Billable Hours -->
                    <div class="pt-3 border-t border-ytBorder/40">
                        <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-ytBlue">timer</span>
                            <span>Expected Active Studio Delivery Hours per Month</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" step="10" min="10" name="monthly_billable_hours" id="monthly_billable_hours" value="<?= esc($monthlyHours) ?>" required
                                   oninput="recalcLiveEconomics()"
                                   class="w-48 bg-ytBg border border-ytBorder text-ytText font-mono font-bold text-[14px] rounded-lg px-3.5 py-2 focus:outline-none focus:border-ytBlue"
                                   placeholder="300">
                            <span class="text-[12px] text-ytMuted font-mono">hours / month across all artists</span>
                        </div>
                        <p class="text-[10px] text-ytMuted mt-1">Total estimated hours delivered across active client projects each month (e.g. 2 artists x 150h = 300h).</p>
                    </div>
                </div>
            </div>

            <!-- Auto-Calculator Output Callout -->
            <div class="p-4 bg-[#0a0a0a] border-t border-ytBorder/60 flex items-center justify-between">
                <div>
                    <div class="text-[11px] text-ytMuted uppercase font-bold tracking-wider">Computed Hourly Studio Overhead</div>
                    <div class="text-[18px] font-bold text-blue-400 font-mono" id="live_ops_display">
                        <?= esc($currency) ?><?= number_format($opsHourlyRate, 2) ?> / hr
                    </div>
                </div>
                <div class="text-right text-[11px] text-ytMuted font-mono">
                    Total Bills: <span class="text-ytText font-bold" id="live_total_bills_display"><?= esc($currency) ?><?= number_format($totalMonthlyBills, 0) ?>/mo</span>
                </div>
            </div>
        </div>

        <!-- Right: Studio Margin & Fallback Rules -->
        <div class="lg:col-span-5 bg-ytCard border border-ytBorder rounded-xl overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-6 py-4 border-b border-ytBorder/60 bg-[#121212] flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-400 text-[20px]">percent</span>
                    <h3 class="text-[15px] font-medium text-ytText">Margin &amp; Pricing Formula</h3>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Currency Symbol -->
                    <div>
                        <label class="block text-[13px] font-medium text-ytText mb-1">Studio Currency</label>
                        <input type="text" name="studio_currency" id="studio_currency" value="<?= esc($currency) ?>" required
                               oninput="recalcLiveEconomics()"
                               class="w-full bg-ytBg border border-ytBorder text-ytText font-mono font-bold text-[14px] rounded-lg px-3.5 py-2 focus:outline-none focus:border-ytBlue"
                               placeholder="₹">
                    </div>

                    <!-- Studio Margin % -->
                    <div>
                        <label class="block text-[13px] font-medium text-ytText mb-1">Studio Profit Margin (%)</label>
                        <input type="number" step="1" min="0" max="200" name="studio_commission_pct" id="studio_commission_pct" value="<?= esc($commissionPct) ?>" required
                               oninput="recalcLiveEconomics()"
                               class="w-full bg-ytBg border border-ytBorder text-ytText font-mono font-bold text-[14px] rounded-lg px-3.5 py-2 focus:outline-none focus:border-ytBlue"
                               placeholder="30">
                        <p class="text-[10px] text-ytMuted mt-1">Added on top of freelancer costs and ops overhead.</p>
                    </div>

                    <!-- Default Artist Hourly Fallback -->
                    <div>
                        <label class="block text-[13px] font-medium text-ytText mb-1">Default Artist Fallback (<?= esc($currency) ?>/hr)</label>
                        <input type="number" step="25" min="0" name="default_artist_rate" id="default_artist_rate" value="<?= esc($defaultArtistRate) ?>" required
                               oninput="recalcLiveEconomics()"
                               class="w-full bg-ytBg border border-ytBorder text-ytText font-mono font-bold text-[14px] rounded-lg px-3.5 py-2 focus:outline-none focus:border-ytBlue"
                               placeholder="500">
                        <p class="text-[10px] text-ytMuted mt-1">Fallback rate used for unassigned tasks until a freelancer is assigned.</p>
                    </div>

                    <!-- Interactive Rate Preview Card -->
                    <div class="bg-[#0e0e0e] border border-ytBorder/60 rounded-xl p-4 text-[12px] font-mono space-y-1.5">
                        <div class="text-ytMuted uppercase font-bold text-[10px] tracking-wider mb-2 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px] text-ytBlue">calculate</span>
                            Standard Client Quote Preview
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ytMuted">Freelance Artist Payout:</span>
                            <span class="text-green-400 font-bold" id="preview_artist_rate"><?= esc($currency) ?><?= number_format($defaultArtistRate, 0) ?>/hr</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ytMuted">Studio AI &amp; Ops Overhead:</span>
                            <span class="text-purple-300 font-bold" id="preview_ops_rate"><?= esc($currency) ?><?= number_format($opsHourlyRate, 2) ?>/hr</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ytMuted">Studio Profit Margin (<span id="preview_margin_pct"><?= esc($commissionPct) ?></span>%):</span>
                            <span class="text-amber-300 font-bold" id="preview_margin_val"><?= esc($currency) ?><?= number_format(($defaultArtistRate + $opsHourlyRate) * ($commissionPct / 100), 2) ?>/hr</span>
                        </div>
                        <div class="pt-2 border-t border-ytBorder/50 flex justify-between items-center">
                            <span class="text-ytText font-bold">Standard Client Billable Rate:</span>
                            <span class="text-[15px] text-blue-400 font-bold" id="preview_client_rate">
                                <?= esc($currency) ?><?= number_format(round(($defaultArtistRate + $opsHourlyRate) * (1 + ($commissionPct / 100)), 0), 0) ?> / hr
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-ytBorder/50 flex justify-end">
                <button type="submit" class="bg-gradient-to-br from-green-700 to-emerald-600 text-white shadow-[0_0_15px_rgba(16,185,129,0.3)] border border-emerald-500/40 px-6 py-2.5 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(16,185,129,0.6)] hover:from-green-600 hover:to-emerald-500 transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Save Studio Economics
                </button>
            </div>
        </div>

    </div>
</form>

<!-- Active Projects Budget Matrix -->
<div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden mb-12">
    <div class="px-6 py-4 border-b border-ytBorder/60 bg-[#121212] flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-ytBlue text-[20px]">movie</span>
            <h3 class="text-[15px] font-medium text-ytText">Active Projects Economics &amp; Budget Overview</h3>
        </div>
        <span class="text-[12px] font-mono text-ytMuted">
            Total Pipeline: <b class="text-green-400"><?= esc($currency) ?><?= number_format($totalActiveClientBudget, 0) ?></b> (<?= $totalActiveHours ?> hrs)
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-ytBorder bg-[#141414] text-[11px] font-bold uppercase tracking-wider text-ytMuted font-mono">
                    <th class="px-6 py-3.5">Project Name</th>
                    <th class="px-4 py-3.5 text-center">Shots / Tasks</th>
                    <th class="px-4 py-3.5 text-right">Est. Hours</th>
                    <th class="px-4 py-3.5 text-right">Artist Pool</th>
                    <th class="px-4 py-3.5 text-right">AI &amp; Ops Pool</th>
                    <th class="px-4 py-3.5 text-right">Studio Margin</th>
                    <th class="px-6 py-3.5 text-right">Total Client Quote</th>
                    <th class="px-6 py-3.5 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-ytText divide-y divide-ytBorder/40 font-mono">
                <?php if(empty($projectBudgets)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-ytMuted">No projects found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($projectBudgets as $pb): ?>
                        <tr class="hover:bg-ytHover/60 transition-colors">
                            <td class="px-6 py-3.5 font-sans font-medium text-ytText">
                                <a href="/admin/projects/<?= $pb->id ?>/breakdown" class="hover:text-ytBlue transition-colors flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-ytBlue">video_library</span>
                                    <span><?= esc($pb->name) ?></span>
                                </a>
                            </td>
                            <td class="px-4 py-3.5 text-center text-ytMuted text-[12px]">
                                <?= $pb->shot_count ?> shots &bull; <?= $pb->task_count ?> tasks
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-ytBlue">
                                <?= $pb->total_hours ?> hrs
                            </td>
                            <td class="px-4 py-3.5 text-right text-ytText">
                                <?= esc($currency) ?><?= number_format($pb->artist_cost, 0) ?>
                            </td>
                            <td class="px-4 py-3.5 text-right text-purple-300">
                                <?= esc($currency) ?><?= number_format($pb->ops_cost, 0) ?>
                            </td>
                            <td class="px-4 py-3.5 text-right text-amber-300">
                                <?= esc($currency) ?><?= number_format($pb->margin_cost, 0) ?>
                            </td>
                            <td class="px-6 py-3.5 text-right text-[14px] font-bold text-green-400">
                                <?= esc($currency) ?><?= number_format($pb->client_budget, 0) ?>
                            </td>
                            <td class="px-6 py-3.5 text-right font-sans">
                                <a href="/admin/projects/<?= $pb->id ?>/breakdown" class="bg-ytCard border border-ytBorder hover:border-ytBlue text-ytText hover:text-ytBlue px-3 py-1 rounded-lg text-[11px] font-medium transition-all inline-flex items-center gap-1">
                                    <span>Breakdown Matrix</span>
                                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot class="border-t-2 border-ytBorder bg-[#111] text-[12px] font-bold font-mono">
                <tr>
                    <td class="px-6 py-3 text-ytText font-sans uppercase">Total Active Pipeline</td>
                    <td class="px-4 py-3 text-center text-ytMuted">-</td>
                    <td class="px-4 py-3 text-right text-ytBlue"><?= $totalActiveHours ?> hrs</td>
                    <td class="px-4 py-3 text-right text-ytText"><?= esc($currency) ?><?= number_format($totalActiveArtistCost, 0) ?></td>
                    <td class="px-4 py-3 text-right text-purple-300"><?= esc($currency) ?><?= number_format($totalActiveOpsCost, 0) ?></td>
                    <td class="px-4 py-3 text-right text-amber-300"><?= esc($currency) ?><?= number_format($totalActiveProfitMargin, 0) ?></td>
                    <td class="px-6 py-3 text-right text-[15px] text-green-400"><?= esc($currency) ?><?= number_format($totalActiveClientBudget, 0) ?></td>
                    <td class="px-6 py-3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
    function recalcLiveEconomics() {
        const cur = document.getElementById('studio_currency').value || '₹';
        const ai = parseFloat(document.getElementById('monthly_ai_bills').value) || 0;
        const storage = parseFloat(document.getElementById('monthly_storage_bills').value) || 0;
        const software = parseFloat(document.getElementById('monthly_software_bills').value) || 0;
        const ops = parseFloat(document.getElementById('monthly_ops_bills').value) || 0;
        const hours = Math.max(1, parseFloat(document.getElementById('monthly_billable_hours').value) || 300);

        const marginPct = parseFloat(document.getElementById('studio_commission_pct').value) || 0;
        const artistRate = parseFloat(document.getElementById('default_artist_rate').value) || 0;

        const totalMonthly = ai + storage + software + ops;
        const opsHourly = totalMonthly / hours;
        const marginVal = (artistRate + opsHourly) * (marginPct / 100);
        const clientRate = Math.round(artistRate + opsHourly + marginVal);

        // Update displays
        document.getElementById('live_ops_display').textContent = `${cur}${opsHourly.toFixed(2)} / hr`;
        document.getElementById('live_total_bills_display').textContent = `${cur}${totalMonthly.toLocaleString()}/mo`;

        document.getElementById('preview_artist_rate').textContent = `${cur}${artistRate.toLocaleString()}/hr`;
        document.getElementById('preview_ops_rate').textContent = `${cur}${opsHourly.toFixed(2)}/hr`;
        document.getElementById('preview_margin_pct').textContent = marginPct;
        document.getElementById('preview_margin_val').textContent = `${cur}${marginVal.toFixed(2)}/hr`;
        document.getElementById('preview_client_rate').textContent = `${cur}${clientRate.toLocaleString()} / hr`;
    }
</script>

<?= $this->endSection() ?>
