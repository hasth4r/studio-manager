<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="max-w-4xl">
    
    <div class="flex items-center space-x-3 mb-6">
        <a href="/admin/projects" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center text-ytMuted">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h2 class="text-[20px] font-medium text-ytText">Create New Project</h2>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-[#2a1215] border border-red-900 text-red-200 px-4 py-3 rounded mb-6 text-[13px] flex items-center">
            <span class="material-symbols-outlined mr-2 text-[18px]">error</span>
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="bg-[#2a1215] border border-red-900 text-red-200 px-4 py-3 rounded mb-6 text-[13px]">
            <ul class="list-disc list-inside">
            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/admin/projects/store" method="POST" class="bg-ytCard border border-ytBorder rounded-xl p-6 sm:p-8">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <!-- Project Name -->
            <div>
                <label for="name" class="block text-[13px] font-medium text-ytText mb-2">Project Name <span class="text-ytRed">*</span></label>
                <input type="text" name="name" id="name" value="<?= old('name') ?>" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]" placeholder="e.g. Nike Summer Campaign">
            </div>

            <!-- Project Code & FPS -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="project_code" class="block text-[13px] font-medium text-ytText mb-2">Project Code <span class="text-ytRed">*</span></label>
                    <input type="text" name="project_code" id="project_code" value="<?= old('project_code') ?>" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px] uppercase" placeholder="e.g. NIKE-SUMMER-24">
                    <p class="text-[12px] text-ytMuted mt-1.5 flex items-center"><span class="material-symbols-outlined text-[14px] mr-1">info</span> Spaces will be automatically converted to hyphens.</p>
                </div>
                <div>
                    <label for="fps" class="block text-[13px] font-medium text-ytText mb-2">Project FPS <span class="text-ytRed">*</span></label>
                    <input type="number" name="fps" id="fps" value="<?= old('fps', 24) ?>" required min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]">
                    <p class="text-[12px] text-ytMuted mt-1.5 flex items-center"><span class="material-symbols-outlined text-[14px] mr-1">speed</span> Default FPS for shots and tasks.</p>
                </div>
            </div>

            <!-- Project Type -->
            <div class="md:col-span-2">
                <label for="project_type_id" class="block text-[13px] font-medium text-ytText mb-2">Project Type <span class="text-ytRed">*</span></label>
                <select name="project_type_id" id="project_type_id" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]">
                    <option value="" disabled <?= old('project_type_id') ? '' : 'selected' ?>>Select project type...</option>
                    <?php foreach($project_types as $type): ?>
                        <option value="<?= esc($type->id) ?>" <?= old('project_type_id') == $type->id ? 'selected' : '' ?>><?= esc($type->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Client / Studio -->
            <div class="bg-[#1a1a1a] p-5 rounded-lg border border-ytBorder/50">
                <label for="client_id" class="block text-[13px] font-medium text-ytText mb-2">Client / Studio <span class="text-ytRed">*</span></label>
                <select name="client_id" id="client_id" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px] mb-2">
                    <option value="" disabled <?= old('client_id') ? '' : 'selected' ?>>Select a client...</option>
                    <?php foreach($clients as $client): ?>
                        <option value="<?= esc($client->id) ?>" <?= old('client_id') == $client->id ? 'selected' : '' ?>><?= esc($client->company_name) ?></option>
                    <?php endforeach; ?>
                    <option value="new_client" class="text-ytBlue font-medium">+ Create New Client</option>
                </select>
            </div>

            <!-- Collaborator -->
            <div class="bg-[#1a1a1a] p-5 rounded-lg border border-ytBorder/50">
                <label for="collaborator_id" class="block text-[13px] font-medium text-ytText mb-2">Collaborator (Optional)</label>
                <select name="collaborator_id" id="collaborator_id" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px] mb-2">
                    <option value="" <?= old('collaborator_id') ? '' : 'selected' ?>>None</option>
                    <?php foreach($collaborators as $collab): ?>
                        <option value="<?= esc($collab->id) ?>" <?= old('collaborator_id') == $collab->id ? 'selected' : '' ?>><?= esc($collab->company_name) ?></option>
                    <?php endforeach; ?>
                    <option value="new_collaborator" class="text-ytBlue font-medium">+ Create New Collaborator</option>
                </select>
            </div>

            <!-- Dates & Priority -->
            <div>
                <label for="priority" class="block text-[13px] font-medium text-ytText mb-2">Priority</label>
                <select name="priority" id="priority" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]">
                    <option value="low" <?= old('priority') == 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="normal" <?= old('priority') == 'normal' ? 'selected' : '' ?> selected>Normal</option>
                    <option value="high" <?= old('priority') == 'high' ? 'selected' : '' ?>>High</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-[13px] font-medium text-ytText mb-2">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="<?= old('start_date') ?>" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px] [color-scheme:dark]">
                </div>
                
                <div>
                    <label for="deadline" class="block text-[13px] font-medium text-ytText mb-2">Deadline</label>
                    <input type="date" name="deadline" id="deadline" value="<?= old('deadline') ?>" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px] [color-scheme:dark]">
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-ytBorder/50">
            <a href="/admin/projects" class="px-5 py-2.5 rounded font-medium text-[13px] text-ytText hover:bg-ytHover transition-colors">Cancel</a>
            <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2.5 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">Create Project</button>
        </div>
    </form>
</div>


<!-- CLIENT MODAL -->
<div id="clientModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-lg mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Add New Client</h3>
            <button type="button" onclick="closeModal('clientModal', 'client_id')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="p-6">
            <div id="client_error" class="hidden bg-[#2a1215] border border-red-900 text-red-200 px-4 py-2 rounded mb-4 text-[13px]"></div>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Company Name <span class="text-ytRed">*</span></label>
                <input type="text" id="mc_company_name" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Primary Contact</label>
                <input type="text" id="mc_contact_name" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Contact Email</label>
                <input type="email" id="mc_email" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Contact Phone</label>
                <input type="text" id="mc_phone" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('clientModal', 'client_id')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="button" onclick="submitModal('clients', 'mc', 'client_id', 'clientModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Client</button>
            </div>
        </div>
    </div>
</div>

<!-- COLLABORATOR MODAL -->
<div id="collabModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-lg mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Add New Collaborator</h3>
            <button type="button" onclick="closeModal('collabModal', 'collaborator_id')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="p-6">
            <div id="collab_error" class="hidden bg-[#2a1215] border border-red-900 text-red-200 px-4 py-2 rounded mb-4 text-[13px]"></div>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Company / Studio Name <span class="text-ytRed">*</span></label>
                <input type="text" id="mco_company_name" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Primary Contact</label>
                <input type="text" id="mco_contact_name" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Contact Email</label>
                <input type="email" id="mco_email" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Contact Phone</label>
                <input type="text" id="mco_phone" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('collabModal', 'collaborator_id')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="button" onclick="submitModal('collaborators', 'mco', 'collaborator_id', 'collabModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Collaborator</button>
            </div>
        </div>
    </div>
</div>


<script>
    // 1. Project Code Formatting (replace spaces with hyphens instantly)
    const codeInput = document.getElementById('project_code');
    codeInput.addEventListener('input', function() {
        this.value = this.value.replace(/\s+/g, '-').toUpperCase();
    });

    // 2. Dropdown Logic to open modals
    document.getElementById('client_id').addEventListener('change', function() {
        if (this.value === 'new_client') {
            document.getElementById('clientModal').classList.remove('hidden');
        }
    });

    document.getElementById('collaborator_id').addEventListener('change', function() {
        if (this.value === 'new_collaborator') {
            document.getElementById('collabModal').classList.remove('hidden');
        }
    });

    function closeModal(modalId, selectId) {
        document.getElementById(modalId).classList.add('hidden');
        // Reset select back to empty if they cancel
        const select = document.getElementById(selectId);
        if (select.value === 'new_client' || select.value === 'new_collaborator') {
            select.value = '';
        }
    }

    // 3. AJAX Submission for Modals
    function submitModal(endpoint, prefix, selectId, modalId) {
        const companyName = document.getElementById(prefix + '_company_name').value;
        const contactName = document.getElementById(prefix + '_contact_name').value;
        const email = document.getElementById(prefix + '_email').value;
        const phone = document.getElementById(prefix + '_phone').value;
        
        const errorDiv = modalId === 'clientModal' ? document.getElementById('client_error') : document.getElementById('collab_error');
        errorDiv.classList.add('hidden');
        errorDiv.innerHTML = '';

        if (!companyName.trim()) {
            errorDiv.innerHTML = 'Company Name is required.';
            errorDiv.classList.remove('hidden');
            return;
        }

        const formData = new FormData();
        formData.append('company_name', companyName);
        formData.append('contact_name', contactName);
        formData.append('email', email);
        formData.append('phone', phone);
        // Include CSRF
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('/api/' + endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json().then(data => ({ status: response.status, data: data }));
            }
            return response.json().then(data => ({ status: response.status, data: data }));
        })
        .then(result => {
            if (result.status === 201 || result.data.id) {
                // Success - Add to dropdown
                const select = document.getElementById(selectId);
                const newOption = new Option(result.data.company_name, result.data.id, true, true);
                
                // Insert before the last option (which is the "+ Create New" option)
                select.insertBefore(newOption, select.lastElementChild);
                
                // Close Modal & Clear Inputs
                document.getElementById(modalId).classList.add('hidden');
                document.getElementById(prefix + '_company_name').value = '';
                document.getElementById(prefix + '_contact_name').value = '';
                document.getElementById(prefix + '_email').value = '';
                document.getElementById(prefix + '_phone').value = '';
            } else if (result.data.messages) {
                // Validation error
                errorDiv.innerHTML = Object.values(result.data.messages).join('<br>');
                errorDiv.classList.remove('hidden');
            } else {
                errorDiv.innerHTML = 'An unexpected error occurred.';
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorDiv.innerHTML = 'A server error occurred.';
            errorDiv.classList.remove('hidden');
        });
    }
</script>

<?= $this->endSection() ?>
