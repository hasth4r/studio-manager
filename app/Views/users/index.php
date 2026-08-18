<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-teal-400 border border-teal-900/50">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Team &amp; Freelancer Rates</h2>
                <p class="text-[13px] text-ytMuted mt-1">Manage artists, individual monthly salaries, and freelance payout rates.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="/admin/budgeting" class="bg-green-950/40 border border-green-700/50 hover:border-green-500 text-green-300 px-4 py-2 rounded-full font-medium text-[13px] hover:bg-green-900/30 transition-all flex items-center gap-1.5 shadow-[0_0_12px_rgba(34,197,94,0.15)]">
                <span class="material-symbols-outlined text-[18px] text-green-400">payments</span>
                <span>Studio Budgeting &amp; Bills</span>
            </a>
            <button onclick="openModal('userModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">add</span> Add Team Member
            </button>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('message')) : ?>
    <div class="bg-[#122a15] border border-green-900 text-green-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="bg-[#2a1215] border border-red-900 text-red-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">error</span>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-ytBorder/50 text-ytMuted text-[12px] uppercase tracking-wider font-medium">
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Experience</th>
                    <th class="px-6 py-4">Freelance Rate</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[14px] text-ytText divide-y divide-ytBorder/50">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-ytMuted text-[13px]">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($users as $user): ?>
                        <tr class="hover:bg-ytHover transition-colors">
                            <td class="px-6 py-4 font-medium flex items-center">
                                <div class="w-8 h-8 rounded-full bg-[#1a1a1a] border border-ytBorder flex items-center justify-center text-ytBlue font-bold mr-3">
                                    <?= strtoupper(substr($user->name, 0, 1)) ?>
                                </div>
                                <?= esc($user->name) ?>
                            </td>
                            <td class="px-6 py-4 text-ytMuted"><?= esc($user->email) ?></td>
                            <td class="px-6 py-4">
                                <?php 
                                    $userRoles = [];
                                    if (!empty($user->roles)) {
                                        $decoded = json_decode($user->roles, true);
                                        if (is_array($decoded)) $userRoles = $decoded;
                                    }
                                    if (empty($userRoles)) {
                                        $userRoles = [$user->global_role ?? 'artist'];
                                    }
                                ?>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <?php foreach($userRoles as $r): ?>
                                        <?php if($r === 'site_manager'): ?>
                                            <span class="bg-[#24122e] text-purple-300 border border-purple-800/60 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">Site Mgr</span>
                                        <?php elseif($r === 'admin'): ?>
                                            <span class="bg-[#2a1215] text-red-400 border border-red-900/50 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">Admin</span>
                                        <?php elseif($r === 'project_manager'): ?>
                                            <span class="bg-[#121c2a] text-blue-400 border border-blue-900/50 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">PM</span>
                                        <?php elseif($r === 'artist'): ?>
                                            <span class="bg-[#181818] text-slate-300 border border-ytBorder/60 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">Artist</span>
                                        <?php elseif($r === 'freelancer'): ?>
                                            <span class="bg-[#2a1d12] text-amber-300 border border-amber-800/50 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">Freelancer</span>
                                        <?php elseif($r === 'collaborator'): ?>
                                            <span class="bg-[#1c182a] text-indigo-300 border border-indigo-800/50 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">Collaborator</span>
                                        <?php elseif($r === 'client'): ?>
                                            <span class="bg-[#1a2e1f] text-green-400 border border-green-900/50 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">Client</span>
                                        <?php elseif($r === 'hr'): ?>
                                            <span class="bg-[#2e1a27] text-pink-300 border border-pink-800/50 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">HR</span>
                                        <?php elseif($r === 'it'): ?>
                                            <span class="bg-[#12282e] text-cyan-300 border border-cyan-800/50 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold">IT</span>
                                        <?php else: ?>
                                            <span class="bg-[#1a1a1a] text-ytMuted border border-ytBorder/50 px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold"><?= esc($r) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-[#1a1a1a] text-ytText border border-ytBorder px-2 py-0.5 rounded text-[11px] tracking-wider font-medium"><?= esc($user->experience_level ?? 'Mid') ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-green-400 font-bold text-[13px]">₹<?= number_format((float)($user->hourly_rate ?? 500), 0) ?><span class="text-[10px] text-ytMuted font-normal">/hr</span></span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($user->status === 'active'): ?>
                                    <span class="text-green-500 text-[13px] flex items-center"><span class="material-symbols-outlined text-[16px] mr-1">check_circle</span> Active</span>
                                <?php else: ?>
                                    <span class="text-ytMuted text-[13px] flex items-center"><span class="material-symbols-outlined text-[16px] mr-1">cancel</span> Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick='openEditModal(<?= json_encode($user) ?>)' class="text-ytMuted hover:text-ytBlue transition-colors p-1" title="Edit User">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- MODAL: Add User -->
<div id="userModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-lg mx-4 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center bg-[#141414]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-ytBlue text-[20px]">person_add</span>
                <h3 class="text-[16px] font-semibold text-ytText">Add Team Member</h3>
            </div>
            <button type="button" onclick="closeModal('userModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/users/store" method="POST" class="p-6 overflow-y-auto space-y-4">
            <?= csrf_field() ?>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-medium text-ytText mb-1">Full Name <span class="text-ytRed">*</span></label>
                    <input type="text" name="name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue" placeholder="e.g. Adith Satheesh">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-ytText mb-1">Email <span class="text-ytRed">*</span></label>
                    <input type="email" name="email" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue" placeholder="adith@studio.com">
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-medium text-ytText mb-1">Temporary Password <span class="text-ytRed">*</span></label>
                <input type="text" name="password" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue" placeholder="Must be at least 5 characters">
            </div>

            <!-- Assigned Roles Checkboxes (Multi-Role) -->
            <div>
                <label class="block text-[12px] font-medium text-ytText mb-1.5">Assigned Roles (Select All That Apply) <span class="text-ytRed">*</span></label>
                <div class="grid grid-cols-3 gap-2 bg-[#121212] border border-ytBorder/80 rounded-xl p-3 text-[12px]">
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="artist" checked class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Artist</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="project_manager" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Project Mgr</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="admin" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Admin</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="freelancer" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Freelancer</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="collaborator" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Collaborator</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="client" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Client</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="hr" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>HR</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="it" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>IT</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="site_manager" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Site Mgr</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-medium text-ytText mb-1">Experience Level</label>
                <select name="experience_level" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue">
                    <option value="Junior">Junior (1.5x Multiplier)</option>
                    <option value="Mid" selected>Mid (1.0x Benchmark)</option>
                    <option value="Senior">Senior (0.8x Fast)</option>
                </select>
            </div>

            <!-- Person Compensation & Monthly Pay Converter Box -->
            <div class="bg-[#121212] border border-ytBorder/80 rounded-xl p-3.5 space-y-2.5">
                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-ytMuted">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[15px] text-green-400">payments</span>
                        Rate / Monthly Pay Calculator
                    </span>
                    <span class="text-green-400 font-mono font-bold" id="add_calc_preview">₹500 / hr</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] text-ytMuted mb-0.5">Monthly Salary (₹)</label>
                        <input type="number" step="100" min="0" id="add_monthly_salary" oninput="convertSalaryToHourly('add')" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-2.5 py-1.5 text-[12px] font-mono" placeholder="e.g. 90640">
                    </div>
                    <div>
                        <label class="block text-[10px] text-ytMuted mb-0.5">Days / Month</label>
                        <input type="number" step="1" min="1" max="31" id="add_work_days" value="22" oninput="convertSalaryToHourly('add')" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-2.5 py-1.5 text-[12px] font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] text-ytMuted mb-0.5">Hours / Day</label>
                        <input type="number" step="1" min="1" max="24" id="add_work_hours" value="8" oninput="convertSalaryToHourly('add')" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-2.5 py-1.5 text-[12px] font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-ytText mb-1">Effective Payout Rate (₹ / hr) <span class="text-ytRed">*</span></label>
                    <input type="number" step="1" min="0" name="hourly_rate" id="add_hourly_rate" value="500" required class="w-full bg-ytBg border border-green-500/50 text-green-400 font-bold text-[14px] rounded px-3 py-1.5 font-mono focus:outline-none focus:border-ytBlue">
                    <p class="text-[10px] text-ytMuted mt-0.5">Auto-calculated or type directly for hourly freelancers.</p>
                </div>
            </div>
            
            <div class="pt-2 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('userModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Create Team Member</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function convertSalaryToHourly(prefix) {
        const salary = parseFloat(document.getElementById(`${prefix}_monthly_salary`).value) || 0;
        const days = parseFloat(document.getElementById(`${prefix}_work_days`).value) || 22;
        const hours = parseFloat(document.getElementById(`${prefix}_work_hours`).value) || 8;

        if (salary > 0 && days > 0 && hours > 0) {
            const totalHours = days * hours;
            const hourly = Math.round(salary / totalHours);
            document.getElementById(`${prefix}_hourly_rate`).value = hourly;
            document.getElementById(`${prefix}_calc_preview`).textContent = `₹${hourly.toLocaleString()} / hr`;
        }
    }

    function openEditModal(user) {
        const modal = document.getElementById('editUserModal');
        const form = document.getElementById('editUserForm');
        
        // Update form action with user ID
        form.action = `/admin/users/update/${user.id}`;
        
        // Populate fields
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_experience_level').value = user.experience_level || 'Mid';
        document.getElementById('edit_status').value = user.status;
        
        // Populate multi-role checkboxes
        let userRoles = [];
        if (user.roles) {
            try {
                userRoles = typeof user.roles === 'string' ? JSON.parse(user.roles) : user.roles;
            } catch(e) {}
        }
        if (!userRoles || userRoles.length === 0) {
            userRoles = [user.global_role || 'artist'];
        }

        const roleCheckboxes = form.querySelectorAll('input[name="roles[]"]');
        roleCheckboxes.forEach(cb => {
            cb.checked = userRoles.includes(cb.value);
        });

        const rate = user.hourly_rate || 500;
        document.getElementById('edit_hourly_rate').value = rate;
        document.getElementById('edit_calc_preview').textContent = `₹${Number(rate).toLocaleString()} / hr`;

        // Reverse estimate monthly salary for preview if not set
        document.getElementById('edit_monthly_salary').value = Math.round(rate * 22 * 8);

        // Clear password field
        document.getElementById('edit_password').value = '';
        
        modal.classList.remove('hidden');
    }
</script>

<!-- MODAL: Edit User -->
<div id="editUserModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-lg mx-4 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center bg-[#141414]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-ytBlue text-[20px]">edit</span>
                <h3 class="text-[16px] font-semibold text-ytText">Edit Team Member &amp; Roles</h3>
            </div>
            <button type="button" onclick="closeModal('editUserModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form id="editUserForm" method="POST" class="p-6 overflow-y-auto space-y-4">
            <?= csrf_field() ?>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-medium text-ytText mb-1">Full Name <span class="text-ytRed">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-ytText mb-1">Email <span class="text-ytRed">*</span></label>
                    <input type="email" name="email" id="edit_email" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue">
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-medium text-ytText mb-1">New Password (Optional)</label>
                <input type="text" name="password" id="edit_password" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue" placeholder="Leave blank to keep current password">
            </div>

            <!-- Multi-Role Checkboxes for Edit -->
            <div>
                <label class="block text-[12px] font-medium text-ytText mb-1.5">Assigned Roles <span class="text-ytRed">*</span></label>
                <div class="grid grid-cols-3 gap-2 bg-[#121212] border border-ytBorder/80 rounded-xl p-3 text-[12px]">
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="artist" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Artist</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="project_manager" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Project Mgr</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="admin" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Admin</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="freelancer" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Freelancer</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="collaborator" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Collaborator</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="client" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Client</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="hr" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>HR</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="it" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>IT</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-ytText cursor-pointer hover:text-white">
                        <input type="checkbox" name="roles[]" value="site_manager" class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                        <span>Site Mgr</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[12px] font-medium text-ytText mb-1">Status <span class="text-ytRed">*</span></label>
                    <select name="status" id="edit_status" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-ytText mb-1">Experience</label>
                    <select name="experience_level" id="edit_experience_level" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue">
                        <option value="Junior">Junior (1.5x)</option>
                        <option value="Mid">Mid (1.0x)</option>
                        <option value="Senior">Senior (0.8x)</option>
                    </select>
                </div>
            </div>

            <!-- Person Compensation & Monthly Pay Converter Box -->
            <div class="bg-[#121212] border border-ytBorder/80 rounded-xl p-3.5 space-y-2.5">
                <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-ytMuted">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[15px] text-green-400">payments</span>
                        Rate / Monthly Pay Calculator
                    </span>
                    <span class="text-green-400 font-mono font-bold" id="edit_calc_preview">₹500 / hr</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[10px] text-ytMuted mb-0.5">Monthly Pay (₹)</label>
                        <input type="number" step="100" min="0" id="edit_monthly_salary" oninput="convertSalaryToHourly('edit')" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-2.5 py-1.5 text-[12px] font-mono" placeholder="e.g. 90640">
                    </div>
                    <div>
                        <label class="block text-[10px] text-ytMuted mb-0.5">Days / Month</label>
                        <input type="number" step="1" min="1" max="31" id="edit_work_days" value="22" oninput="convertSalaryToHourly('edit')" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-2.5 py-1.5 text-[12px] font-mono">
                    </div>
                    <div>
                        <label class="block text-[10px] text-ytMuted mb-0.5">Hours / Day</label>
                        <input type="number" step="1" min="1" max="24" id="edit_work_hours" value="8" oninput="convertSalaryToHourly('edit')" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-2.5 py-1.5 text-[12px] font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-ytText mb-1">Effective Payout Rate (₹ / hr) <span class="text-ytRed">*</span></label>
                    <input type="number" step="1" min="0" name="hourly_rate" id="edit_hourly_rate" required class="w-full bg-ytBg border border-green-500/50 text-green-400 font-bold text-[14px] rounded px-3 py-1.5 font-mono focus:outline-none focus:border-ytBlue">
                    <p class="text-[10px] text-ytMuted mt-0.5">Directly used to calculate this person's task cost &amp; payouts.</p>
                </div>
            </div>
            
            <div class="pt-2 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editUserModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
