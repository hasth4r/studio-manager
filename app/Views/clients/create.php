<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="max-w-2xl">
    
    <div class="flex items-center space-x-3 mb-6">
        <a href="/admin/clients" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center text-ytMuted">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h2 class="text-[20px] font-medium text-ytText">Add new client</h2>
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

    <form action="/admin/clients/store" method="POST" class="bg-ytCard border border-ytBorder rounded-xl p-6 sm:p-8">
        <?= csrf_field() ?>
        
        <div class="mb-6">
            <label for="company_name" class="block text-[13px] font-medium text-ytText mb-2">Company Name <span class="text-ytRed">*</span></label>
            <input type="text" name="company_name" id="company_name" value="<?= old('company_name') ?>" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]" placeholder="e.g. Nike, Adidas">
            <p class="text-[12px] text-ytMuted mt-1.5">The official name of the client's studio or company.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label for="contact_name" class="block text-[13px] font-medium text-ytText mb-2">Primary Contact Person</label>
                <input type="text" name="contact_name" id="contact_name" value="<?= old('contact_name') ?>" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]" placeholder="e.g. Jane Doe">
            </div>
            
            <div>
                <label for="email" class="block text-[13px] font-medium text-ytText mb-2">Contact Email</label>
                <input type="email" name="email" id="email" value="<?= old('email') ?>" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]" placeholder="jane@company.com">
            </div>
            
            <div class="md:col-span-2">
                <label for="phone" class="block text-[13px] font-medium text-ytText mb-2">Contact Phone</label>
                <input type="text" name="phone" id="phone" value="<?= old('phone') ?>" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]" placeholder="+1 234 567 8900">
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-ytBorder/50">
            <a href="/admin/clients" class="px-5 py-2.5 rounded font-medium text-[13px] text-ytText hover:bg-ytHover transition-colors">Cancel</a>
            <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2.5 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">Save Client</button>
        </div>
    </form>

</div>

<?= $this->endSection() ?>
