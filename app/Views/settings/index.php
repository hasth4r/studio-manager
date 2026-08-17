<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-gray-400 border border-gray-700/50">
                <span class="material-symbols-outlined">settings</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Server Settings</h2>
                <p class="text-[13px] text-ytMuted mt-1">Configure global server and pipeline settings.</p>
            </div>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('message')) : ?>
    <div class="bg-[#122a15] border border-green-900 text-green-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>

<div class="space-y-6 max-w-3xl">
    <!-- Server Settings Card -->
    <div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ytBorder/60 bg-[#121212] flex items-center gap-2">
            <span class="material-symbols-outlined text-ytBlue text-[20px]">hard_drive</span>
            <h3 class="text-[15px] font-medium text-ytText">Server & Pipeline Storage</h3>
        </div>
        <div class="p-6">
            <form action="/admin/settings/update" method="POST">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="block text-[13px] font-medium text-ytText mb-1.5">Production Drive Path</label>
                    <p class="text-[11px] text-ytMuted mb-2.5">
                        Root directory where pipeline folders for Projects, Sequences, Shots, and Assets are generated.
                    </p>
                    <div class="flex items-center space-x-3">
                        <span class="material-symbols-outlined text-ytMuted">folder</span>
                        <input type="text" name="production_drive_path" value="<?= esc($production_drive_path) ?>" required 
                               class="flex-1 bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue font-mono text-[13px]" 
                               placeholder="e.g. F:\STUDIO_PRODUCTION\PROJECTS">
                    </div>
                </div>
                
                <div class="pt-3 border-t border-ytBorder/50 flex justify-end">
                    <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">
                        Save Storage Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Freelance Studio Economics & Budgeting Rates Card -->
    <div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ytBorder/60 bg-[#121212] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-green-400 text-[20px]">payments</span>
                <h3 class="text-[15px] font-medium text-ytText">Studio Budgeting & Hourly Rates (Freelance Model)</h3>
            </div>
            <span class="text-[10px] font-mono bg-green-950/60 border border-green-700/50 text-green-300 px-2 py-0.5 rounded font-bold">
                AUTO-RECALCULATED
            </span>
        </div>
        <div class="p-6">
            <form action="/admin/settings/update" method="POST">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <!-- Currency Symbol -->
                    <div>
                        <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1">
                            <span>Studio Currency Symbol</span>
                        </label>
                        <input type="text" name="studio_currency" value="<?= esc($studio_currency ?? '₹') ?>" required
                               class="w-full bg-ytBg border border-ytBorder text-ytText font-bold text-[14px] rounded px-3.5 py-2 focus:outline-none focus:border-ytBlue font-mono"
                               placeholder="₹">
                        <p class="text-[10px] text-ytMuted mt-1">Default currency for all project budgets & payouts.</p>
                    </div>

                    <!-- AI, Storage & Ops Overhead per Hour -->
                    <div>
                        <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1">
                            <span>AI, Storage & Ops Rate (<?= esc($studio_currency ?? '₹') ?> / hr)</span>
                        </label>
                        <input type="number" step="1" min="0" name="studio_ops_hourly_rate" value="<?= esc($studio_ops_hourly_rate ?? '100.00') ?>" required
                               class="w-full bg-ytBg border border-ytBorder text-ytText font-bold text-[14px] rounded px-3.5 py-2 focus:outline-none focus:border-ytBlue font-mono"
                               placeholder="100">
                        <p class="text-[10px] text-ytMuted mt-1">Recovers AI tools (Claude/Midjourney), Cloudflare R2 storage, VPS & software per billable hr.</p>
                    </div>

                    <!-- Studio Commission / Margin % -->
                    <div>
                        <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1">
                            <span>Studio Commission Margin (%)</span>
                        </label>
                        <input type="number" step="1" min="0" max="200" name="studio_commission_pct" value="<?= esc($studio_commission_pct ?? '30') ?>" required
                               class="w-full bg-ytBg border border-ytBorder text-ytText font-bold text-[14px] rounded px-3.5 py-2 focus:outline-none focus:border-ytBlue font-mono"
                               placeholder="30">
                        <p class="text-[10px] text-ytMuted mt-1">Studio profit margin added on top of freelance artist & ops costs.</p>
                    </div>

                    <!-- Default Artist Rate -->
                    <div>
                        <label class="block text-[13px] font-medium text-ytText mb-1 flex items-center gap-1">
                            <span>Default Artist Rate (<?= esc($studio_currency ?? '₹') ?> / hr)</span>
                        </label>
                        <input type="number" step="1" min="0" name="default_artist_rate" value="<?= esc($default_artist_rate ?? '500.00') ?>" required
                               class="w-full bg-ytBg border border-ytBorder text-ytText font-bold text-[14px] rounded px-3.5 py-2 focus:outline-none focus:border-ytBlue font-mono"
                               placeholder="500">
                        <p class="text-[10px] text-ytMuted mt-1">Fallback freelance payout rate when a task is unassigned.</p>
                    </div>
                </div>

                <!-- Rate Breakdown Formula Preview Box -->
                <div class="bg-[#0e0e0e] border border-ytBorder/60 rounded-lg p-3.5 mb-5 text-[12px] font-mono">
                    <div class="text-ytMuted font-bold mb-1 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px] text-ytBlue">calculate</span>
                        Hourly Rate Calculation Preview
                    </div>
                    <div class="text-ytText space-y-0.5">
                        <div>• Freelancer Payout: <span class="text-green-400 font-bold"><?= esc($studio_currency ?? '₹') ?><?= esc($default_artist_rate ?? '500') ?>/hr</span></div>
                        <div>• AI, Cloudflare R2 &amp; Hosting: <span class="text-purple-300 font-bold"><?= esc($studio_currency ?? '₹') ?><?= esc($studio_ops_hourly_rate ?? '100') ?>/hr</span></div>
                        <div>• Studio Net Margin: <span class="text-amber-300 font-bold"><?= esc($studio_commission_pct ?? '30') ?>%</span></div>
                        <div class="pt-1.5 border-t border-ytBorder/40 text-blue-300 font-bold">
                            = Client Billable Rate: <?= esc($studio_currency ?? '₹') ?><?= round(((float)($default_artist_rate ?? 500) + (float)($studio_ops_hourly_rate ?? 100)) * (1 + ((float)($studio_commission_pct ?? 30) / 100)), 0) ?> / hr
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-ytBorder/50 flex justify-end">
                    <button type="submit" class="bg-gradient-to-br from-green-700 to-emerald-600 text-white shadow-[0_0_15px_rgba(16,185,129,0.3)] border border-emerald-500/40 px-6 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(16,185,129,0.6)] hover:from-green-600 hover:to-emerald-500 transition-colors">
                        Save Rate Economics
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
