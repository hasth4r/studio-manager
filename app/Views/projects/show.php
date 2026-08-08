<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 border-b border-ytBorder/50">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="/admin/projects" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center text-ytMuted">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-[24px] font-medium text-ytText"><?= esc($project->name) ?></h2>
                    <button onclick="openModal('projectSettingsModal')" class="p-1 text-ytMuted hover:text-ytText transition-colors mt-1" title="Project Settings">
                        <span class="material-symbols-outlined text-[20px]">settings</span>
                    </button>
                    <span class="bg-[#1a1a1a] text-ytMuted border border-ytBorder/50 px-2 py-0.5 rounded text-[11px] font-mono"><?= esc($project->project_code) ?></span>
                </div>
                <p class="text-[13px] text-ytMuted mt-1">Client: <span class="text-ytBlue"><?= esc($project->client_name) ?></span> &bull; Type: <?= esc($project->project_type_name) ?></p>
            </div>
        </div>
        
        <div class="flex space-x-3">
            <?php if (session()->get('userRole') === 'admin' || session()->get('userRole') === 'project_manager'): ?>
                <form action="/admin/projects/syncFolders/<?= $project->id ?>" method="POST" class="m-0 p-0">
                    <?= csrf_field() ?>
                    <button type="submit" title="Force generate missing folders" class="bg-[#1a1a1a] text-ytText border border-ytBorder px-3 py-2 rounded-full font-medium text-[13px] hover:bg-ytHover transition-colors flex items-center">
                        <span class="material-symbols-outlined text-[18px]">sync</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <div class="bg-[#122a15] border border-green-900 text-green-200 px-4 py-3 rounded mb-4 text-[13px] flex items-center">
            <span class="material-symbols-outlined mr-2 text-[18px]">check_circle</span>
            <?= esc(session()->getFlashdata('message')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-[#2a1215] border border-red-900 text-red-200 px-4 py-3 rounded mb-4 text-[13px] flex items-center">
            <span class="material-symbols-outlined mr-2 text-[18px]">error</span>
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Analytics Widget -->
    <div class="grid grid-cols-4 gap-4 mt-6 mb-6">
        <div class="bg-ytCard border border-ytBorder rounded-lg p-4 flex flex-col justify-center">
            <span class="text-[12px] text-ytMuted font-medium uppercase tracking-wider mb-1">Sequences</span>
            <span class="text-[28px] font-bold text-ytText leading-none"><?= esc($analytics['sequences']) ?></span>
        </div>
        <div class="bg-ytCard border border-ytBorder rounded-lg p-4 flex flex-col justify-center">
            <span class="text-[12px] text-ytMuted font-medium uppercase tracking-wider mb-1">Total Shots</span>
            <span class="text-[28px] font-bold text-ytText leading-none"><?= esc($analytics['shots']) ?></span>
        </div>
        <div class="bg-ytCard border border-ytBorder rounded-lg p-4 flex flex-col justify-center">
            <span class="text-[12px] text-ytMuted font-medium uppercase tracking-wider mb-1">3D Assets</span>
            <span class="text-[28px] font-bold text-ytText leading-none"><?= esc($analytics['assets']) ?></span>
        </div>
        <div class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] shadow-[0_0_15px_rgba(23,123,207,0.2)] border border-[#177bcf]/40 rounded-lg p-4 flex flex-col justify-center relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10 flex justify-between items-end">
                <div class="flex flex-col">
                    <span class="text-[12px] text-blue-200 font-medium uppercase tracking-wider mb-1">Task Progress</span>
                    <span class="text-[28px] font-bold text-white leading-none"><?= esc($analytics['progress']) ?>%</span>
                </div>
                <span class="material-symbols-outlined text-[36px] text-blue-300/50">trending_up</span>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-ytBorder/30">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button onclick="switchTab('tab-sequences')" id="btn-tab-sequences" class="border-ytBlue text-ytBlue border-b-2 py-3 px-1 text-[14px] font-medium outline-none">
                Sequences &amp; Shots
            </button>
            <button onclick="switchTab('tab-assets')" id="btn-tab-assets" class="border-transparent text-ytMuted hover:text-ytText border-b-2 py-3 px-1 text-[14px] font-medium outline-none">
                3D Assets
            </button>
            <button onclick="switchTab('tab-benchmarks')" id="btn-tab-benchmarks" class="border-transparent text-ytMuted hover:text-ytText border-b-2 py-3 px-1 text-[14px] font-medium outline-none flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">timer</span> Benchmarks
            </button>
        </nav>
    </div>
</div>

<!-- TAB: Sequences & Shots -->
<div id="tab-sequences" class="block pt-4">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-[16px] font-medium text-ytText">Production Sequences</h3>
        <div class="space-x-2">
            <button onclick="openModal('sequenceModal')" class="bg-ytCard border border-ytBorder text-ytText px-4 py-2 rounded-full font-medium text-[13px] hover:bg-ytHover transition-colors">
                + Add Sequence
            </button>
            <button onclick="openModal('shotModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">
                + Add Shot
            </button>
        </div>
    </div>

    <?php if(empty($sequences) && empty($shots)): ?>
        <div class="bg-ytCard border border-ytBorder border-dashed rounded-xl p-12 text-center">
            <span class="material-symbols-outlined text-[48px] text-ytMuted mb-3">movie</span>
            <p class="text-ytText font-medium">No sequences or shots yet</p>
            <p class="text-ytMuted text-[13px] mt-1 mb-4">Break down your project into manageable sequences and shots.</p>
            <button onclick="openModal('shotModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">Add First Shot</button>
        </div>
    <?php endif; ?>

    <?php 
    $orphanedShots = [];
    $groupedShots = [];
    foreach($sequences as $seq) {
        $groupedShots[$seq->id] = [];
    }
    foreach($shots as $shot) {
        if ($shot->sequence_id) {
            $groupedShots[$shot->sequence_id][] = $shot;
        } else {
            $orphanedShots[] = $shot;
        }
    }
    ?>

    <?php if(!empty($orphanedShots)): ?>
        <div class="mb-8">
            <h4 class="text-[14px] text-ytMuted mb-3 uppercase tracking-wider font-medium">Independent Shots</h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <?php foreach($orphanedShots as $shot): ?>
                    <a href="/admin/shots/<?= $shot->id ?>" class="bg-ytCard border border-ytBorder rounded-lg overflow-hidden hover:border-ytBlue transition-colors group">
                        <div class="aspect-video bg-[#1a1a1a] relative">
                            <?php if($shot->thumbnail_path): ?>
                                <img src="/<?= esc($shot->thumbnail_path) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-ytMuted">
                                    <span class="material-symbols-outlined text-[32px]">image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 flex justify-between items-start relative z-10">
                            <p class="text-[14px] font-medium text-ytText group-hover:text-ytBlue transition-colors"><?= esc($shot->shot_number) ?></p>
                            <button onclick="editShot(event, <?= $shot->id ?>, <?= $shot->sequence_id ?: 'null' ?>, <?= htmlspecialchars(json_encode($shot->shot_number), ENT_QUOTES, 'UTF-8') ?>, <?= $shot->fps ?: 'null' ?>, <?= $shot->frame_count ?: 'null' ?>, <?= htmlspecialchars(json_encode($shot->description), ENT_QUOTES, 'UTF-8') ?>)" class="text-ytMuted hover:text-ytText transition-colors p-1 rounded-full hover:bg-ytHover opacity-0 group-hover:opacity-100">
                                <span class="material-symbols-outlined text-[16px] block">edit</span>
                            </button>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach($sequences as $seq): ?>
        <div class="mb-8">
            <h4 class="text-[15px] text-ytText mb-3 font-medium bg-[#1a1a1a] px-4 py-2 rounded border border-ytBorder/50 flex items-center">
                <span class="material-symbols-outlined text-ytMuted mr-2 text-[18px]">folder</span>
                <?= esc($seq->name) ?>
                <div class="ml-auto flex items-center space-x-2">
                    <button onclick="editSequence(<?= $seq->id ?>, <?= htmlspecialchars(json_encode($seq->name), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($seq->description), ENT_QUOTES, 'UTF-8') ?>)" class="flex items-center gap-1 bg-[#1a1a1a] border border-ytBorder hover:border-ytText hover:text-ytText transition-colors px-3 py-1 rounded-full text-[12px] font-medium text-ytMuted" title="Edit Sequence">
                        <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                    </button>
                    <a href="/admin/reviews/sequence/<?= $seq->id ?>" class="flex items-center gap-1 bg-[#1a1a1a] border border-ytBorder hover:border-ytBlue hover:text-ytBlue transition-colors px-3 py-1 rounded-full text-[12px] font-medium text-ytMuted">
                        <span class="material-symbols-outlined text-[16px]">movie_edit</span> Lineup Editor
                    </a>
                </div>
            </h4>
            
            <?php if(empty($groupedShots[$seq->id])): ?>
                <p class="text-[13px] text-ytMuted italic px-4">No shots in this sequence yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <?php foreach($groupedShots[$seq->id] as $shot): ?>
                        <a href="/admin/shots/<?= $shot->id ?>" class="bg-ytCard border border-ytBorder rounded-lg overflow-hidden hover:border-ytBlue transition-colors group">
                            <div class="aspect-video bg-[#1a1a1a] relative">
                                <?php if($shot->thumbnail_path): ?>
                                    <img src="/<?= esc($shot->thumbnail_path) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-ytMuted">
                                        <span class="material-symbols-outlined text-[32px]">image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 flex justify-between items-start relative z-10">
                                <p class="text-[14px] font-medium text-ytText group-hover:text-ytBlue transition-colors"><?= esc($shot->shot_number) ?></p>
                                <button onclick="editShot(event, <?= $shot->id ?>, <?= $shot->sequence_id ?: 'null' ?>, <?= htmlspecialchars(json_encode($shot->shot_number), ENT_QUOTES, 'UTF-8') ?>, <?= $shot->fps ?: 'null' ?>, <?= $shot->frame_count ?: 'null' ?>, <?= htmlspecialchars(json_encode($shot->description), ENT_QUOTES, 'UTF-8') ?>)" class="text-ytMuted hover:text-ytText transition-colors p-1 rounded-full hover:bg-ytHover opacity-0 group-hover:opacity-100">
                                    <span class="material-symbols-outlined text-[16px] block">edit</span>
                                </button>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- TAB: 3D Assets -->
<div id="tab-assets" class="hidden pt-4">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-[16px] font-medium text-ytText">3D Assets</h3>
        <button onclick="openModal('assetModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">
            + Add Asset
        </button>
    </div>

    <?php if(empty($assets)): ?>
        <div class="bg-ytCard border border-ytBorder border-dashed rounded-xl p-12 text-center">
            <span class="material-symbols-outlined text-[48px] text-ytMuted mb-3">view_in_ar</span>
            <p class="text-ytText font-medium">No assets created yet</p>
            <p class="text-ytMuted text-[13px] mt-1 mb-4">Manage characters, props, and environments globally.</p>
            <button onclick="openModal('assetModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">Add First Asset</button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <?php foreach($assets as $asset): ?>
                <a href="/admin/assets/<?= $asset->id ?>" class="bg-ytCard border border-ytBorder rounded-lg overflow-hidden hover:border-ytBlue transition-colors group">
                    <div class="aspect-video bg-[#1a1a1a] relative">
                        <?php if($asset->thumbnail_path): ?>
                            <img src="/<?= esc($asset->thumbnail_path) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-ytMuted">
                                <span class="material-symbols-outlined text-[32px]">view_in_ar</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <p class="text-[14px] font-medium text-ytText group-hover:text-ytBlue transition-colors truncate"><?= esc($asset->name) ?></p>
                        <p class="text-[11px] text-ytMuted uppercase tracking-wider mt-1"><?= esc($asset->type) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- TAB: Benchmarks -->
<div id="tab-benchmarks" class="hidden pt-4">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-[16px] font-medium text-ytText">Project Benchmarks</h3>
        <p class="text-[13px] text-ytMuted">Define base hours for tasks.</p>
    </div>

    <form action="/admin/projects/storeBenchmarks" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="project_id" value="<?= $project->id ?>">

        <div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden mb-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-ytBorder/50 text-ytMuted text-[12px] uppercase tracking-wider font-medium">
                        <th class="px-6 py-4">Task Type</th>
                        <th class="px-6 py-4 w-32">Simple (hrs)</th>
                        <th class="px-6 py-4 w-32">Medium (hrs)</th>
                        <th class="px-6 py-4 w-32">Complex (hrs)</th>
                    </tr>
                </thead>
                <tbody class="text-[14px] text-ytText divide-y divide-ytBorder/50">
                    <?php 
                    $bmMap = [];
                    foreach($benchmarks as $bm) {
                        $bmMap[$bm->task_type_id] = $bm;
                    }
                    ?>
                    <?php foreach($taskTypes as $type): ?>
                        <?php 
                        $cur = isset($bmMap[$type->id]) ? $bmMap[$type->id] : null; 
                        ?>
                        <tr class="hover:bg-ytHover transition-colors">
                            <td class="px-6 py-3 font-medium">
                                <?= esc($type->name) ?>
                                <?php if(isset($type->description) && $type->description): ?>
                                    <div class="text-[11px] text-ytMuted font-normal mt-0.5"><?= esc($type->description) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <input type="number" step="0.1" min="0" name="benchmarks[<?= $type->id ?>][simple]" value="<?= $cur ? $cur->simple_hours : '1' ?>" class="w-20 bg-ytBg border border-ytBorder text-ytText rounded px-2 py-1.5 focus:outline-none focus:border-ytBlue text-[13px] text-center">
                            </td>
                            <td class="px-6 py-3">
                                <input type="number" step="0.1" min="0" name="benchmarks[<?= $type->id ?>][medium]" value="<?= $cur ? $cur->medium_hours : '3' ?>" class="w-20 bg-ytBg border border-ytBorder text-ytText rounded px-2 py-1.5 focus:outline-none focus:border-ytBlue text-[13px] text-center">
                            </td>
                            <td class="px-6 py-3">
                                <input type="number" step="0.1" min="0" name="benchmarks[<?= $type->id ?>][complex]" value="<?= $cur ? $cur->complex_hours : '6' ?>" class="w-20 bg-ytBg border border-ytBorder text-ytText rounded px-2 py-1.5 focus:outline-none focus:border-ytBlue text-[13px] text-center">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="sticky bottom-0 bg-[#121212] py-4 border-t border-ytBorder/50 flex justify-end z-10 mt-6 -mx-6 px-6">
            <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-8 py-3 rounded-full font-medium text-[14px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Benchmarks</button>
        </div>
    </form>
</div>


<!-- MODAL: Add Sequence -->
<div id="sequenceModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-md mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Add Sequence</h3>
            <button type="button" onclick="closeModal('sequenceModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/projects/storeSequence" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $project->id ?>">
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Sequence Name <span class="text-ytRed">*</span></label>
                <input type="text" name="name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="e.g. SQ010 or Intro">
            </div>
            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('sequenceModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Sequence</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Add Shot -->
<div id="shotModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-lg mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Add Shot</h3>
            <button type="button" onclick="closeModal('shotModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/projects/storeShot" method="POST" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $project->id ?>">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Sequence</label>
                    <select name="sequence_id" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                        <option value="">(None - Independent Shot)</option>
                        <?php foreach($sequences as $seq): ?>
                            <option value="<?= $seq->id ?>"><?= esc($seq->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Shot Number <span class="text-ytRed">*</span></label>
                    <input type="text" name="shot_number" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="e.g. SH010">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Frame Count</label>
                    <input type="number" name="frame_count" min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="e.g. 240">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">FPS (Override)</label>
                    <input type="number" name="fps" min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="Default: <?= esc($project->fps ?? 24) ?>">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Thumbnail</label>
                <input type="file" name="thumbnail" accept="image/*" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-1.5 focus:outline-none focus:border-ytBlue text-[13px] file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-[13px] file:font-medium file:bg-ytHover file:text-ytText hover:file:bg-[#3f3f3f]">
            </div>

            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('shotModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Shot</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Add Asset -->
<div id="assetModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-lg mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Add 3D Asset</h3>
            <button type="button" onclick="closeModal('assetModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/projects/storeAsset" method="POST" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $project->id ?>">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="col-span-2">
                    <label class="block text-[13px] font-medium text-ytText mb-2">Asset Name <span class="text-ytRed">*</span></label>
                    <input type="text" name="name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="e.g. Hero Character">
                </div>
                <div class="col-span-2">
                    <label class="block text-[13px] font-medium text-ytText mb-2">Asset Type <span class="text-ytRed">*</span></label>
                    <select name="type" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                        <option value="character">Character</option>
                        <option value="prop">Prop</option>
                        <option value="environment">Environment</option>
                        <option value="vehicle">Vehicle</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Thumbnail</label>
                <input type="file" name="thumbnail" accept="image/*" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-1.5 focus:outline-none focus:border-ytBlue text-[13px] file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-[13px] file:font-medium file:bg-ytHover file:text-ytText hover:file:bg-[#3f3f3f]">
            </div>

            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('assetModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Asset</button>
            </div>
        </form>
    </div>
</div>


<!-- MODAL: Edit Project Settings -->
<div id="projectSettingsModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-sm mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[16px] font-medium text-ytText">Project Settings</h3>
            <button type="button" onclick="closeModal('projectSettingsModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form action="/admin/projects/updateSettings/<?= $project->id ?>" method="POST" class="p-6">
            <?= csrf_field() ?>
            
            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Project FPS Default</label>
                <input type="number" name="fps" min="1" value="<?= esc($project->fps) ?>" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-3 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('projectSettingsModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Sequence -->
<div id="editSequenceModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-md mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Edit Sequence</h3>
            <button type="button" onclick="closeModal('editSequenceModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form id="editSequenceForm" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $project->id ?>">
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">Sequence Name <span class="text-ytRed">*</span></label>
                <input type="text" id="edit_sequence_name" name="name" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
            </div>
            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Description</label>
                <textarea id="edit_sequence_description" name="description" rows="3" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" onclick="deleteSequence()" class="text-red-500 hover:text-red-400 text-[13px] font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">delete</span> Delete</button>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeModal('editSequenceModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                    <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Update Sequence</button>
                </div>
            </div>
        </form>
        <form id="deleteSequenceForm" method="POST" class="hidden">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $project->id ?>">
        </form>
    </div>
</div>

<!-- MODAL: Edit Shot -->
<div id="editShotModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-lg mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[18px] font-medium text-ytText">Edit Shot</h3>
            <button type="button" onclick="closeModal('editShotModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form id="editShotForm" method="POST" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $project->id ?>">
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Sequence</label>
                    <select id="edit_shot_sequence" name="sequence_id" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                        <option value="">(None - Independent Shot)</option>
                        <?php foreach($sequences as $seq): ?>
                            <option value="<?= $seq->id ?>"><?= esc($seq->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Shot Number <span class="text-ytRed">*</span></label>
                    <input type="text" id="edit_shot_number" name="shot_number" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">Frame Count</label>
                    <input type="number" id="edit_shot_frame_count" name="frame_count" min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-2">FPS (Override)</label>
                    <input type="number" id="edit_shot_fps" name="fps" min="1" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue" placeholder="Default: <?= esc($project->fps ?? 24) ?>">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-ytText mb-2">New Thumbnail (Optional)</label>
                <input type="file" name="thumbnail" accept="image/*" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-1.5 focus:outline-none focus:border-ytBlue text-[13px] file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-[13px] file:font-medium file:bg-ytHover file:text-ytText hover:file:bg-[#3f3f3f]">
            </div>

            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytText mb-2">Description</label>
                <textarea id="edit_shot_description" name="description" rows="3" class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-4 py-2 focus:outline-none focus:border-ytBlue"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" onclick="deleteShot()" class="text-red-500 hover:text-red-400 text-[13px] font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">delete</span> Delete</button>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeModal('editShotModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                    <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Update Shot</button>
                </div>
            </div>
        </form>
        <form id="deleteShotForm" method="POST" class="hidden">
            <?= csrf_field() ?>
            <input type="hidden" name="project_id" value="<?= $project->id ?>">
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const savedTab = localStorage.getItem('activeProjectTab');
        if (savedTab) {
            switchTab(savedTab, false);
        }
    });

    function switchTab(tabId, save = true) {
        if (save) {
            localStorage.setItem('activeProjectTab', tabId);
        }
        
        // Hide all
        document.getElementById('tab-sequences').classList.add('hidden');
        document.getElementById('tab-assets').classList.add('hidden');
        document.getElementById('tab-benchmarks').classList.add('hidden');
        
        // Unstyle all buttons
        const btnSeq = document.getElementById('btn-tab-sequences');
        const btnAst = document.getElementById('btn-tab-assets');
        const btnBmk = document.getElementById('btn-tab-benchmarks');
        
        const inactiveClass = "border-transparent text-ytMuted hover:text-ytText border-b-2 py-4 px-1 text-[14px] font-medium outline-none flex items-center gap-1 transition-colors";
        const activeClass = "border-ytBlue text-ytBlue border-b-2 py-4 px-1 text-[14px] font-medium outline-none flex items-center gap-1 transition-colors";
        
        btnSeq.className = inactiveClass;
        btnAst.className = inactiveClass;
        btnBmk.className = inactiveClass;
        
        // Show target & style active
        document.getElementById(tabId).classList.remove('hidden');
        if (tabId === 'tab-sequences') btnSeq.className = activeClass;
        if (tabId === 'tab-assets') btnAst.className = activeClass;
        if (tabId === 'tab-benchmarks') btnBmk.className = activeClass;
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    let activeSequenceId = null;
    function editSequence(id, name, description) {
        activeSequenceId = id;
        document.getElementById('editSequenceForm').action = '/admin/projects/updateSequence/' + id;
        document.getElementById('edit_sequence_name').value = name;
        document.getElementById('edit_sequence_description').value = description;
        openModal('editSequenceModal');
    }
    function deleteSequence() {
        if(confirm('Are you sure you want to delete this sequence? Its shots will be kept as independent shots.')) {
            document.getElementById('deleteSequenceForm').action = '/admin/projects/deleteSequence/' + activeSequenceId;
            document.getElementById('deleteSequenceForm').submit();
        }
    }

    let activeShotId = null;
    function editShot(e, id, sequence_id, shot_number, fps, frame_count, description) {
        e.preventDefault();
        e.stopPropagation();
        activeShotId = id;
        document.getElementById('editShotForm').action = '/admin/projects/updateShot/' + id;
        document.getElementById('edit_shot_sequence').value = sequence_id || '';
        document.getElementById('edit_shot_number').value = shot_number;
        document.getElementById('edit_shot_fps').value = fps || '';
        document.getElementById('edit_shot_frame_count').value = frame_count || '';
        document.getElementById('edit_shot_description').value = description;
        openModal('editShotModal');
    }
    function deleteShot() {
        if(confirm('Are you sure you want to delete this shot? All associated tasks and reviews will be lost.')) {
            document.getElementById('deleteShotForm').action = '/admin/projects/deleteShot/' + activeShotId;
            document.getElementById('deleteShotForm').submit();
        }
    }
</script>

<?= $this->endSection() ?>
