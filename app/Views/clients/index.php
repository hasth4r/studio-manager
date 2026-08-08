<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('message')) : ?>
    <div class="bg-[#122a15] border border-green-900 text-green-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="bg-[#2a1212] border border-red-900 text-red-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
        <span class="material-symbols-outlined mr-2 text-[18px]">error</span>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-orange-400 border border-orange-900/50">
                <span class="material-symbols-outlined">business</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Client Companies</h2>
                <p class="text-[13px] text-ytMuted mt-1">Manage external studios and clients</p>
            </div>
        </div>
        <a href="/admin/clients/create" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors flex items-center">
            <span class="material-symbols-outlined mr-1 text-[18px]">add</span> Add Client
        </a>
    </div>
</div>

<div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden">
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-ytBorder/50 text-[13px] text-ytMuted bg-[#1a1a1a]">
                    <th class="px-6 py-3 font-medium">Company Name</th>
                    <th class="px-6 py-3 font-medium">Primary Contact</th>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Phone</th>
                    <th class="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[14px] divide-y divide-ytBorder/50 text-ytText">
                <?php if(empty($clients)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-ytMuted text-[13px]">No clients found. Click "Add Client" to get started.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($clients as $client): ?>
                        <tr class="hover:bg-ytHover transition-colors">
                            <td class="px-6 py-4 font-medium flex items-center">
                                <div class="h-8 w-8 rounded bg-gray-800 text-white flex items-center justify-center text-xs mr-3 border border-ytBorder">
                                    <?= esc(strtoupper(substr($client->company_name, 0, 1))) ?>
                                </div>
                                <?= esc($client->company_name) ?>
                            </td>
                            <td class="px-6 py-4 text-ytMuted"><?= esc($client->contact_name ?: '-') ?></td>
                            <td class="px-6 py-4 text-ytMuted"><?= esc($client->email ?: '-') ?></td>
                            <td class="px-6 py-4 text-ytMuted"><?= esc($client->phone ?: '-') ?></td>
                            <td class="px-6 py-4 text-right flex items-center justify-end space-x-2">
                                <button onclick='openClientUserModal(<?= $client->id ?>, <?= json_encode($client->company_name) ?>)' class="text-ytBlue hover:text-blue-400 transition-colors p-1" title="Create Client Portal User">
                                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                                </button>
                                <button class="text-ytMuted hover:text-ytText transition-colors p-1" title="Edit Client">
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

<!-- MODAL: Create Client User -->
<div id="clientUserModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-md mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Create Client User</h3>
            <button type="button" onclick="closeClientUserModal()" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/clients/createUser" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="client_id" id="modal_client_id" value="">
            
            <p class="text-[13px] text-ytMuted mb-4">Provision a portal login for <span id="modal_client_name" class="font-bold text-white"></span>.</p>

            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">User Name <span class="text-ytRed">*</span></label>
                <input type="text" name="name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="e.g. Jane Doe">
            </div>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Login Email <span class="text-ytRed">*</span></label>
                <input type="email" name="email" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="jane@clientcompany.com">
            </div>

            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Temporary Password <span class="text-ytRed">*</span></label>
                <input type="text" name="password" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="Must be at least 5 characters">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeClientUserModal()" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Create User</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openClientUserModal(clientId, companyName) {
        document.getElementById('modal_client_id').value = clientId;
        document.getElementById('modal_client_name').textContent = companyName;
        document.getElementById('clientUserModal').classList.remove('hidden');
    }
    
    function closeClientUserModal() {
        document.getElementById('clientUserModal').classList.add('hidden');
    }
</script>

<?= $this->endSection() ?>
