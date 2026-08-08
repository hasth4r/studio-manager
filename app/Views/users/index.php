<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-teal-400 border border-teal-900/50">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Team Management</h2>
                <p class="text-[13px] text-ytMuted mt-1">Manage artists, supervisors, and admins</p>
            </div>
        </div>
        <button onclick="openModal('userModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors flex items-center">
            <span class="material-symbols-outlined mr-1 text-[18px]">add</span> Add User
        </button>
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
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[14px] text-ytText divide-y divide-ytBorder/50">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-ytMuted text-[13px]">No users found.</td>
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
                                <?php if($user->global_role === 'admin'): ?>
                                    <span class="bg-[#2a1215] text-red-400 border border-red-900/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">Admin</span>
                                <?php elseif($user->global_role === 'project_manager'): ?>
                                    <span class="bg-[#121c2a] text-blue-400 border border-blue-900/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">PM</span>
                                <?php elseif($user->global_role === 'client'): ?>
                                    <span class="bg-[#1a2e1f] text-green-400 border border-green-900/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">Client</span>
                                <?php else: ?>
                                    <span class="bg-[#1a1a1a] text-ytMuted border border-ytBorder/50 px-2 py-0.5 rounded text-[11px] uppercase tracking-wider font-medium">Artist</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-[#1a1a1a] text-ytText border border-ytBorder px-2 py-0.5 rounded text-[11px] tracking-wider font-medium"><?= esc($user->experience_level ?? 'Mid') ?></span>
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
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-md mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Add Team Member</h3>
            <button type="button" onclick="closeModal('userModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/users/store" method="POST" class="p-6">
            <?= csrf_field() ?>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Full Name <span class="text-ytRed">*</span></label>
                <input type="text" name="name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="e.g. John Doe">
            </div>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Email <span class="text-ytRed">*</span></label>
                <input type="email" name="email" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="john@example.com">
            </div>

            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Temporary Password <span class="text-ytRed">*</span></label>
                <input type="text" name="password" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="Must be at least 5 characters">
            </div>

            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Global Role <span class="text-ytRed">*</span></label>
                <select name="global_role" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                    <option value="artist">Artist</option>
                    <option value="project_manager">Project Manager</option>
                    <option value="client">Client</option>
                    <option value="admin">Administrator</option>
                </select>
                <p class="text-[11px] text-ytMuted mt-1">Artists have limited dashboard views. Admins have full access.</p>
            </div>

            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Experience Level</label>
                <select name="experience_level" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                    <option value="Junior">Junior (1.5x Task Time)</option>
                    <option value="Mid" selected>Mid (1.0x Task Time)</option>
                    <option value="Senior">Senior (0.8x Task Time)</option>
                </select>
                <p class="text-[11px] text-ytMuted mt-1">Used to multiply project task benchmarks for estimated completion time.</p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('userModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Create User</button>
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

    function openEditModal(user) {
        const modal = document.getElementById('editUserModal');
        const form = document.getElementById('editUserForm');
        
        // Update form action with user ID
        form.action = `/admin/users/update/${user.id}`;
        
        // Populate fields
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_global_role').value = user.global_role;
        document.getElementById('edit_experience_level').value = user.experience_level;
        document.getElementById('edit_status').value = user.status;
        
        // Clear password field
        document.getElementById('edit_password').value = '';
        
        modal.classList.remove('hidden');
    }
</script>

<!-- MODAL: Edit User -->
<div id="editUserModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-md mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Edit Team Member</h3>
            <button type="button" onclick="closeModal('editUserModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form id="editUserForm" method="POST" class="p-6">
            <?= csrf_field() ?>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Full Name <span class="text-ytRed">*</span></label>
                <input type="text" name="name" id="edit_name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Email <span class="text-ytRed">*</span></label>
                <input type="email" name="email" id="edit_email" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>

            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">New Password (Optional)</label>
                <input type="text" name="password" id="edit_password" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="Leave blank to keep current password">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Global Role <span class="text-ytRed">*</span></label>
                    <select name="global_role" id="edit_global_role" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                        <option value="artist">Artist</option>
                        <option value="project_manager">Project Manager</option>
                        <option value="client">Client</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Status <span class="text-ytRed">*</span></label>
                    <select name="status" id="edit_status" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Experience Level</label>
                <select name="experience_level" id="edit_experience_level" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                    <option value="Junior">Junior (1.5x Task Time)</option>
                    <option value="Mid">Mid (1.0x Task Time)</option>
                    <option value="Senior">Senior (0.8x Task Time)</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editUserModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
