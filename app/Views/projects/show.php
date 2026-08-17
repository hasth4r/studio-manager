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
        <div class="flex items-center space-x-2">
            <a href="/admin/projects/<?= $project->id ?>/briefing" class="bg-[#181818] border border-indigo-500/40 hover:border-indigo-400 text-indigo-200 px-3.5 py-2 rounded-full font-medium text-[13px] hover:bg-indigo-950/40 transition-all flex items-center gap-1.5 shadow-[0_0_12px_rgba(99,102,241,0.15)]" title="Open Client Shot Briefing & Reference Matrix">
                <span class="material-symbols-outlined text-[16px] text-indigo-400">edit_note</span>
                <span>Shot Briefing &amp; References</span>
            </a>
            <button onclick="autoGenerateAllThumbnails()" class="bg-[#181818] border border-purple-500/40 hover:border-purple-400 text-purple-200 px-3.5 py-2 rounded-full font-medium text-[13px] hover:bg-purple-950/40 transition-all flex items-center gap-1.5 shadow-[0_0_12px_rgba(168,85,247,0.15)]" title="Auto-extract mid-frame WebP thumbnails from all video previews">
                <span class="material-symbols-outlined text-[16px] text-purple-400">photo_camera</span>
                <span>Auto-Gen WebP Thumbnails</span>
            </button>
            <a href="/admin/projects/<?= $project->id ?>/breakdown" class="bg-[#181818] border border-ytBlue/50 hover:border-ytBlue text-ytText px-4 py-2 rounded-full font-medium text-[13px] hover:bg-ytHover transition-all flex items-center gap-1.5 shadow-[0_0_12px_rgba(23,123,207,0.15)]">
                <span class="material-symbols-outlined text-[16px] text-ytBlue">table_chart</span>
                Shot Breakdown &amp; Task Matrix
            </a>
            <button onclick="openModal('importShotsModal')" class="bg-ytCard border border-ytBorder text-ytText px-4 py-2 rounded-full font-medium text-[13px] hover:bg-ytHover transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-ytBlue">upload_file</span>
                Import Shots (AE / CSV)
            </button>
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
            <p class="text-ytMuted text-[13px] mt-1 mb-4">Break down your project into manageable sequences and shots, or import directly from After Effects.</p>
            <div class="flex justify-center gap-3">
                <a href="/admin/projects/<?= $project->id ?>/breakdown" class="bg-ytCard border border-ytBorder text-ytText px-5 py-2.5 rounded-full font-medium text-[13px] hover:bg-ytHover transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-ytBlue">table_chart</span> Shot Breakdown Matrix
                </a>
                <button onclick="openModal('importShotsModal')" class="bg-ytCard border border-ytBorder text-ytText px-5 py-2.5 rounded-full font-medium text-[13px] hover:bg-ytHover transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-ytBlue">upload_file</span> Import from AE / CSV
                </button>
                <button onclick="openModal('shotModal')" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2.5 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">Add Single Shot</button>
            </div>
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
                            <?php if(!empty($shot->frame_count)): ?>
                                <span class="absolute bottom-1.5 right-1.5 bg-black/80 backdrop-blur-xs text-white text-[10px] px-1.5 py-0.5 rounded font-mono border border-white/10">
                                    <?= !empty($shot->frame_in) && !empty($shot->frame_out) ? esc($shot->frame_in) . '–' . esc($shot->frame_out) : esc($shot->frame_count) . ' fr' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 flex justify-between items-start relative z-10">
                            <div>
                                <p class="text-[14px] font-medium text-ytText group-hover:text-ytBlue transition-colors leading-tight"><?= esc($shot->shot_number) ?></p>
                                <?php if(!empty($shot->frame_count) || !empty($shot->fps)): ?>
                                    <p class="text-[11px] text-ytMuted font-mono mt-0.5"><?= esc($shot->frame_count ?? '-') ?> fr &bull; <?= esc($shot->fps ?? $project->fps ?? 24) ?> FPS</p>
                                <?php endif; ?>
                            </div>
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
                            <div class="aspect-video bg-[#1a1a1a] relative overflow-hidden group/thumb"
                                 onmouseenter="playHoverVideo(this, '<?= !empty($shot->preview_video_path) ? base_url(esc($shot->preview_video_path)) : '' ?>')"
                                 onmouseleave="stopHoverVideo(this)">
                                <?php if($shot->thumbnail_path): ?>
                                    <img src="<?= base_url(esc($shot->thumbnail_path)) ?>" loading="lazy" class="shot-thumb-img-<?= $shot->id ?> w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-ytMuted">
                                        <span class="material-symbols-outlined text-[32px]">image</span>
                                    </div>
                                <?php endif; ?>

                                <?php if(!empty($shot->preview_video_path)): ?>
                                    <button type="button" 
                                            onclick="openVideoModal(event, '<?= base_url(esc($shot->preview_video_path)) ?>', '<?= esc($shot->shot_number) ?>')" 
                                            class="absolute top-1.5 left-1.5 bg-black/90 hover:bg-blue-600 backdrop-blur-xs border border-blue-500/50 text-blue-200 hover:text-white text-[10px] font-mono font-bold px-2 py-0.5 rounded flex items-center gap-1 z-20 cursor-pointer shadow-md transition-all">
                                        <span class="material-symbols-outlined text-[13px]">play_circle</span> Preview
                                    </button>
                                <?php endif; ?>

                                <?php if(!empty($shot->frame_count)): ?>
                                    <span class="absolute bottom-1.5 right-1.5 bg-black/80 backdrop-blur-xs text-white text-[10px] px-1.5 py-0.5 rounded font-mono border border-white/10 z-10">
                                        <?= !empty($shot->frame_in) && !empty($shot->frame_out) ? esc($shot->frame_in) . '–' . esc($shot->frame_out) : esc($shot->frame_count) . ' fr' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 flex justify-between items-start relative z-10">
                                <div>
                                    <p class="text-[14px] font-medium text-ytText group-hover:text-ytBlue transition-colors leading-tight"><?= esc($shot->shot_number) ?></p>
                                    <?php if(!empty($shot->frame_count) || !empty($shot->fps)): ?>
                                        <p class="text-[11px] text-ytMuted font-mono mt-0.5"><?= esc($shot->frame_count ?? '-') ?> fr &bull; <?= esc($shot->fps ?? $project->fps ?? 24) ?> FPS</p>
                                    <?php endif; ?>
                                </div>
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

    <?php if(!empty($orphanedShots)): ?>
        <div class="mt-8 border-t border-ytBorder pt-6">
            <h4 class="text-[15px] font-medium text-ytText mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-ytMuted">folder_off</span>
                Independent Shots
            </h4>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <?php foreach($orphanedShots as $shot): ?>
                    <a href="/admin/shots/<?= $shot->id ?>" class="bg-ytCard border border-ytBorder rounded-lg overflow-hidden hover:border-ytBlue transition-colors group">
                        <div class="aspect-video bg-[#1a1a1a] relative overflow-hidden group/thumb"
                             onmouseenter="playHoverVideo(this, '<?= !empty($shot->preview_video_path) ? base_url(esc($shot->preview_video_path)) : '' ?>')"
                             onmouseleave="stopHoverVideo(this)">
                            <?php if($shot->thumbnail_path): ?>
                                <img src="<?= base_url(esc($shot->thumbnail_path)) ?>" loading="lazy" class="shot-thumb-img-<?= $shot->id ?> w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-ytMuted">
                                    <span class="material-symbols-outlined text-[32px]">image</span>
                                </div>
                            <?php endif; ?>

                            <?php if(!empty($shot->preview_video_path)): ?>
                                <button type="button" 
                                        onclick="openVideoModal(event, '<?= base_url(esc($shot->preview_video_path)) ?>', '<?= esc($shot->shot_number) ?>')" 
                                        class="absolute top-1.5 left-1.5 bg-black/90 hover:bg-blue-600 backdrop-blur-xs border border-blue-500/50 text-blue-200 hover:text-white text-[10px] font-mono font-bold px-2 py-0.5 rounded flex items-center gap-1 z-20 cursor-pointer shadow-md transition-all">
                                    <span class="material-symbols-outlined text-[13px]">play_circle</span> Preview
                                </button>
                            <?php endif; ?>

                            <?php if(!empty($shot->frame_count)): ?>
                                <span class="absolute bottom-1.5 right-1.5 bg-black/80 backdrop-blur-xs text-white text-[10px] px-1.5 py-0.5 rounded font-mono border border-white/10 z-10">
                                    <?= !empty($shot->frame_in) && !empty($shot->frame_out) ? esc($shot->frame_in) . '–' . esc($shot->frame_out) : esc($shot->frame_count) . ' fr' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 flex justify-between items-start relative z-10">
                            <div>
                                <p class="text-[14px] font-medium text-ytText group-hover:text-ytBlue transition-colors leading-tight"><?= esc($shot->shot_number) ?></p>
                                <?php if(!empty($shot->frame_count) || !empty($shot->fps)): ?>
                                    <p class="text-[11px] text-ytMuted font-mono mt-0.5"><?= esc($shot->frame_count ?? '-') ?> fr &bull; <?= esc($shot->fps ?? $project->fps ?? 24) ?> FPS</p>
                                <?php endif; ?>
                            </div>
                            <button onclick="editShot(event, <?= $shot->id ?>, null, <?= htmlspecialchars(json_encode($shot->shot_number), ENT_QUOTES, 'UTF-8') ?>, <?= $shot->fps ?: 'null' ?>, <?= $shot->frame_count ?: 'null' ?>, <?= htmlspecialchars(json_encode($shot->description), ENT_QUOTES, 'UTF-8') ?>)" class="text-ytMuted hover:text-ytText transition-colors p-1 rounded-full hover:bg-ytHover opacity-0 group-hover:opacity-100">
                                <span class="material-symbols-outlined text-[16px] block">edit</span>
                            </button>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
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

<!-- MODAL: Bulk Import Shots (AE Essentials & CSV) -->
<div id="importShotsModal" class="fixed inset-0 z-50 hidden bg-black/75 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-2xl w-full max-w-xl mx-4 shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center bg-[#181818]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#9999ff]/20 to-[#6a11cb]/30 border border-[#9999ff]/30 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#9999ff] text-[18px]">movie_edit</span>
                </div>
                <div>
                    <h3 class="text-[17px] font-semibold text-ytText leading-tight">Bulk Import Shots</h3>
                    <p class="text-[11px] text-ytMuted">Import from After Effects Essentials or CSV file</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('importShotsModal')" class="text-ytMuted hover:text-ytText transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="importShotsForm" action="/admin/projects/importShots/<?= $project->id ?>" method="POST" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>

            <!-- Method Switcher Tabs -->
            <div id="importTabsRow" class="flex bg-ytBg border border-ytBorder rounded-lg p-1 mb-5">
                <button type="button" id="importTabBtn-folder" onclick="setImportMode('folder')" class="flex-1 py-1.5 px-3 rounded-md text-[13px] font-medium transition-all bg-ytCard text-ytText shadow-sm border border-ytBorder">
                    <span class="flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-ytBlue">folder_open</span> AE Export Folder
                    </span>
                </button>
                <button type="button" id="importTabBtn-upload" onclick="setImportMode('upload')" class="flex-1 py-1.5 px-3 rounded-md text-[13px] font-medium transition-all text-ytMuted hover:text-ytText">
                    <span class="flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">upload_file</span> CSV / ZIP Upload
                    </span>
                </button>
            </div>

            <!-- TAB 1: Local Folder (AE Essentials) -->
            <div id="importMode-folder" class="space-y-4">
                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-1.5">AE Export Folder Path <span class="text-ytRed">*</span></label>
                    <input type="text" name="folder_path" id="import_folder_path" class="w-full bg-ytBg border border-ytBorder text-ytText rounded-lg px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-ytBlue font-mono placeholder:text-ytMuted/50" placeholder="e.g. F:\STUDIO_PRODUCTION\PROJECTS\STUDIO_PROJECTS\MAHALAYA\post\war\lineup\project_file\export">
                    <p class="text-[12px] text-ytMuted mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px] text-ytBlue">auto_awesome</span>
                        EnsoFlow will automatically read the shotlist CSV and link all images in the <code class="bg-ytBg px-1 py-0.5 rounded text-[11px] text-ytText">thumbnails/</code> folder.
                    </p>
                </div>
            </div>

            <!-- TAB 2: File Upload (CSV / ZIP) -->
            <div id="importMode-upload" class="hidden space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-[13px] font-medium text-ytText">CSV or ZIP File <span class="text-ytRed">*</span></label>
                        <a href="/templates/shots_import_template.csv" download class="text-[11px] text-ytBlue hover:underline flex items-center gap-0.5 font-medium">
                            <span class="material-symbols-outlined text-[13px]">download</span> Sample CSV
                        </a>
                    </div>
                    <input type="file" id="import_csv_file" name="csv_file" accept=".csv,.txt,.zip" class="w-full bg-ytBg border border-ytBorder text-ytText rounded-lg px-3.5 py-2 text-[13px] focus:outline-none focus:border-ytBlue file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-medium file:bg-ytHover file:text-ytText hover:file:bg-[#3f3f3f]">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-1.5">Thumbnails (Optional Multiple Images)</label>
                    <input type="file" name="thumbnails[]" multiple accept="image/*" class="w-full bg-ytBg border border-ytBorder text-ytText rounded-lg px-3.5 py-2 text-[13px] focus:outline-none focus:border-ytBlue file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-medium file:bg-ytHover file:text-ytText hover:file:bg-[#3f3f3f]">
                    <p class="text-[11px] text-ytMuted mt-1">Multi-select images matching shot names or filenames in CSV.</p>
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-ytText mb-1.5">Video Previews (Optional Multiple .mp4 / .mov)</label>
                    <input type="file" name="video_previews[]" multiple accept="video/mp4,video/quicktime,video/webm" class="w-full bg-ytBg border border-ytBorder text-ytText rounded-lg px-3.5 py-2 text-[13px] focus:outline-none focus:border-ytBlue file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-medium file:bg-ytHover file:text-ytText hover:file:bg-[#3f3f3f]">
                    <p class="text-[11px] text-ytMuted mt-1">Upload video clips matching shot names (e.g. <code class="text-ytBlue">sh0010.mp4</code> or <code class="text-ytBlue">mhlya-1_war_sh0010_edit_v00.mp4</code>).</p>
                </div>
            </div>

            <!-- Common Configuration Section -->
            <div id="importConfigSection" class="pt-4 mt-4 border-t border-ytBorder/40 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-medium text-ytText mb-1">Import Range / Limit</label>
                        <select name="limit" class="w-full bg-ytBg border border-ytBorder text-ytText rounded-lg px-3 py-2 text-[13px] focus:outline-none focus:border-ytBlue">
                            <option value="5" selected>🧪 Test 5 Shots</option>
                            <option value="10">Test 10 Shots</option>
                            <option value="20">First 20 Shots</option>
                            <option value="0">All Shots (Full Production)</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer text-[12px] text-ytText">
                            <input type="checkbox" name="auto_create_folders" value="1" checked class="rounded border-ytBorder bg-ytBg text-ytBlue focus:ring-0">
                            <span>Create disk pipeline folders</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Live Upload Progress Card -->
            <div id="importProgressBox" class="hidden mt-5 p-4 rounded-xl bg-[#141414] border border-ytBorder space-y-3">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span id="progressSpinner" class="material-symbols-outlined text-ytBlue animate-spin text-[18px]">progress_activity</span>
                        <span id="progressStatusText" class="text-[13px] font-medium text-ytText">Uploading file...</span>
                    </div>
                    <span id="progressPercentBadge" class="text-[12px] font-mono font-bold text-ytBlue">0%</span>
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-[#222] h-2.5 rounded-full overflow-hidden border border-white/5">
                    <div id="progressBarFill" class="bg-gradient-to-r from-blue-600 via-indigo-500 to-sky-400 h-full rounded-full transition-all duration-150" style="width: 0%;"></div>
                </div>

                <div class="flex justify-between items-center text-[11px] text-ytMuted font-mono">
                    <span id="progressBytesText">0.0 MB / 0.0 MB</span>
                    <span id="progressSpeedText">-- MB/s</span>
                </div>

                <!-- Error Notice inside Progress Box -->
                <div id="importErrorAlert" class="hidden p-3 rounded-lg bg-red-950/60 border border-red-800/80 text-red-200 text-[12px] space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-red-400">
                        <span class="material-symbols-outlined text-[16px]">error</span>
                        <span>Upload Failed</span>
                    </div>
                    <p id="importErrorMsg">An error occurred during upload.</p>
                </div>
            </div>

            <div id="importActionButtons" class="flex justify-end items-center space-x-3 pt-5 mt-5 border-t border-ytBorder/50">
                <button type="button" onclick="closeModal('importShotsModal')" class="px-4 py-2 rounded-full text-[13px] text-ytText hover:bg-ytHover transition-colors">Cancel</button>
                <button type="submit" id="importSubmitBtn" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">bolt</span>
                    <span id="importSubmitBtnText">Run Import</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const savedTab = localStorage.getItem('activeProjectTab');
        if (savedTab) {
            switchTab(savedTab, false);
        }

        // Live Upload Progress Engine for Import Modal (Supports 4MB Chunking for Huge Files)
        const importForm = document.getElementById('importShotsForm');
        if (importForm) {
            importForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const progressBox = document.getElementById('importProgressBox');
                const progressBarFill = document.getElementById('progressBarFill');
                const progressPercentBadge = document.getElementById('progressPercentBadge');
                const progressBytesText = document.getElementById('progressBytesText');
                const progressSpeedText = document.getElementById('progressSpeedText');
                const progressStatusText = document.getElementById('progressStatusText');
                const progressSpinner = document.getElementById('progressSpinner');
                const importSubmitBtn = document.getElementById('importSubmitBtn');
                const importSubmitBtnText = document.getElementById('importSubmitBtnText');
                const errorAlert = document.getElementById('importErrorAlert');
                const errorMsg = document.getElementById('importErrorMsg');

                // Reset UI state
                errorAlert.classList.add('hidden');
                progressBox.classList.remove('hidden');
                progressBarFill.style.width = '0%';
                progressBarFill.className = 'bg-gradient-to-r from-blue-600 via-indigo-500 to-sky-400 h-full rounded-full transition-all duration-150';
                progressPercentBadge.className = 'text-[12px] font-mono font-bold text-ytBlue';
                progressPercentBadge.textContent = '0%';
                progressSpinner.textContent = 'progress_activity';
                progressSpinner.className = 'material-symbols-outlined text-ytBlue animate-spin text-[18px]';

                importSubmitBtn.disabled = true;
                importSubmitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                importSubmitBtnText.textContent = 'Importing...';

                function showImportError(msg) {
                    importSubmitBtn.disabled = false;
                    importSubmitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                    importSubmitBtnText.textContent = 'Retry Import';
                    progressSpinner.textContent = 'error';
                    progressSpinner.className = 'material-symbols-outlined text-red-400 text-[18px]';
                    progressStatusText.textContent = 'Import failed';
                    errorMsg.textContent = msg;
                    errorAlert.classList.remove('hidden');
                }

                const fileInput = document.getElementById('import_csv_file');
                const file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

                // If file is > 8MB, use Chunked Upload to guarantee 0 timeouts
                const CHUNK_THRESHOLD = 8 * 1024 * 1024; // 8MB
                if (file && file.size > CHUNK_THRESHOLD) {
                    const CHUNK_SIZE = 4 * 1024 * 1024; // 4MB chunks
                    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                    const uploadId = 'up_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    const startTime = Date.now();
                    const limitVal = importForm.querySelector('select[name="limit"]').value;
                    const autoFolders = importForm.querySelector('input[name="auto_create_folders"]').checked ? 1 : 0;

                    let totalBytesUploaded = 0;

                    for (let i = 0; i < totalChunks; i++) {
                        const start = i * CHUNK_SIZE;
                        const end = Math.min(file.size, start + CHUNK_SIZE);
                        const chunkBlob = file.slice(start, end);

                        let chunkSuccess = false;
                        let retries = 0;

                        while (!chunkSuccess && retries < 3) {
                            try {
                                const chunkFormData = new FormData();
                                chunkFormData.append('file_chunk', chunkBlob, file.name);
                                chunkFormData.append('upload_id', uploadId);
                                chunkFormData.append('chunk_index', i);
                                chunkFormData.append('total_chunks', totalChunks);
                                chunkFormData.append('file_name', file.name);
                                chunkFormData.append('limit', limitVal);
                                chunkFormData.append('auto_create_folders', autoFolders);

                                const res = await new Promise((resolve, reject) => {
                                    const xhr = new XMLHttpRequest();
                                    xhr.open('POST', '/admin/projects/chunkUpload/<?= $project->id ?>', true);
                                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                                    xhr.upload.onprogress = function(e) {
                                        if (e.lengthComputable) {
                                            const currentUploaded = totalBytesUploaded + e.loaded;
                                            const percent = Math.min(99, Math.round((currentUploaded / file.size) * 100));
                                            const loadedMB = (currentUploaded / (1024 * 1024)).toFixed(1);
                                            const totalMB = (file.size / (1024 * 1024)).toFixed(1);

                                            const elapsedSec = (Date.now() - startTime) / 1000;
                                            const speed = elapsedSec > 0 ? ((currentUploaded / (1024 * 1024)) / elapsedSec).toFixed(1) : '0.0';

                                            progressBarFill.style.width = percent + '%';
                                            progressPercentBadge.textContent = percent + '%';
                                            progressBytesText.textContent = `${loadedMB} MB / ${totalMB} MB (Part ${i + 1}/${totalChunks})`;
                                            progressSpeedText.textContent = `${speed} MB/s`;
                                            progressStatusText.textContent = `Uploading part ${i + 1} of ${totalChunks}...`;
                                        }
                                    };

                                    xhr.onload = function() {
                                        if (xhr.status >= 200 && xhr.status < 300) {
                                            try {
                                                resolve(JSON.parse(xhr.responseText));
                                            } catch (e) {
                                                resolve({ success: true });
                                            }
                                        } else {
                                            let err = `Chunk upload error (HTTP ${xhr.status})`;
                                            try {
                                                const j = JSON.parse(xhr.responseText);
                                                if (j.error) err = j.error;
                                            } catch(e) {}
                                            reject(new Error(err));
                                        }
                                    };

                                    xhr.onerror = function() {
                                        reject(new Error('Connection interrupted on chunk ' + (i + 1)));
                                    };

                                    xhr.send(chunkFormData);
                                });

                                if (i === totalChunks - 1) {
                                    // Final chunk processed!
                                    progressBarFill.style.width = '100%';
                                    progressBarFill.className = 'bg-green-500 h-full rounded-full transition-all';
                                    progressPercentBadge.textContent = '100%';
                                    progressPercentBadge.className = 'text-[12px] font-mono font-bold text-green-400';
                                    progressStatusText.textContent = res.message || 'Import completed!';
                                    progressSpinner.textContent = 'check_circle';
                                    progressSpinner.className = 'material-symbols-outlined text-green-400 text-[18px]';

                                    setTimeout(() => {
                                        window.location.href = res.redirect || window.location.href;
                                    }, 1200);
                                    return;
                                }

                                totalBytesUploaded += (end - start);
                                chunkSuccess = true;
                            } catch (err) {
                                retries++;
                                if (retries >= 3) {
                                    showImportError(err.message || 'Upload failed after retries.');
                                    return;
                                }
                                progressStatusText.textContent = `Retrying part ${i + 1} (Attempt ${retries + 1})...`;
                                await new Promise(r => setTimeout(r, 1000));
                            }
                        }
                    }
                    return;
                }

                // Standard Single-Request Upload for Folder mode / Small files
                const formData = new FormData(importForm);
                const xhr = new XMLHttpRequest();
                xhr.open('POST', importForm.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                const startTime = Date.now();

                xhr.upload.onprogress = function(event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        const loadedMB = (event.loaded / (1024 * 1024)).toFixed(1);
                        const totalMB = (event.total / (1024 * 1024)).toFixed(1);

                        const elapsedSec = (Date.now() - startTime) / 1000;
                        const speed = elapsedSec > 0 ? ((event.loaded / (1024 * 1024)) / elapsedSec).toFixed(1) : '0.0';

                        progressBarFill.style.width = percent + '%';
                        progressPercentBadge.textContent = percent + '%';
                        progressBytesText.textContent = `${loadedMB} MB / ${totalMB} MB`;
                        progressSpeedText.textContent = `${speed} MB/s`;

                        if (percent < 100) {
                            progressStatusText.textContent = 'Uploading file to server...';
                        } else {
                            progressStatusText.textContent = 'Upload complete! Server is extracting & linking shots...';
                            progressPercentBadge.textContent = 'Processing...';
                        }
                    }
                };

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.success) {
                                progressBarFill.style.width = '100%';
                                progressBarFill.className = 'bg-green-500 h-full rounded-full transition-all';
                                progressPercentBadge.textContent = '100%';
                                progressPercentBadge.className = 'text-[12px] font-mono font-bold text-green-400';
                                progressStatusText.textContent = res.message || 'Import successful!';
                                progressSpinner.textContent = 'check_circle';
                                progressSpinner.className = 'material-symbols-outlined text-green-400 text-[18px]';

                                setTimeout(() => {
                                    window.location.href = res.redirect || window.location.href;
                                }, 1200);
                                return;
                            } else {
                                showImportError(res.error || 'Import failed.');
                            }
                        } catch (err) {
                            window.location.reload();
                        }
                    } else {
                        let errDetail = `Server Error (HTTP ${xhr.status})`;
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.error) errDetail = res.error;
                        } catch (e) {}
                        showImportError(errDetail);
                    }
                };

                xhr.onerror = function() {
                    showImportError('Network error: Connection was lost or timed out during upload.');
                };

                xhr.send(formData);
            });
        }
    });

    function setImportMode(mode) {
        const folderTab = document.getElementById('importMode-folder');
        const uploadTab = document.getElementById('importMode-upload');
        const btnFolder = document.getElementById('importTabBtn-folder');
        const btnUpload = document.getElementById('importTabBtn-upload');

        const activeBtnClass = "flex-1 py-1.5 px-3 rounded-md text-[13px] font-medium transition-all bg-ytCard text-ytText shadow-sm border border-ytBorder";
        const inactiveBtnClass = "flex-1 py-1.5 px-3 rounded-md text-[13px] font-medium transition-all text-ytMuted hover:text-ytText";

        if (mode === 'folder') {
            folderTab.classList.remove('hidden');
            uploadTab.classList.add('hidden');
            btnFolder.className = activeBtnClass;
            btnUpload.className = inactiveBtnClass;
        } else {
            folderTab.classList.add('hidden');
            uploadTab.classList.remove('hidden');
            btnFolder.className = inactiveBtnClass;
            btnUpload.className = activeBtnClass;
        }
    }

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

    function openVideoModal(e, videoUrl, shotTitle) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const modal = document.getElementById('quickVideoModal');
        const video = document.getElementById('quickVideoPlayer');
        const title = document.getElementById('quickVideoTitle');
        if (!modal || !video) return;

        title.textContent = `Preview: Shot ${shotTitle}`;
        video.src = videoUrl;
        modal.classList.remove('hidden');
        video.play().catch(() => {});
    }

    function closeVideoModal() {
        const modal = document.getElementById('quickVideoModal');
        const video = document.getElementById('quickVideoPlayer');
        if (!modal || !video) return;

        video.pause();
        video.currentTime = 0;
        video.src = '';
        modal.classList.add('hidden');
    }

    // Auto-Generate WebP Thumbnails from Video Mid-Frames
    const shotsWithVideos = <?= json_encode(array_values(array_filter(array_map(function($s) {
        return !empty($s->preview_video_path) ? [
            'id' => $s->id,
            'shot_number' => $s->shot_number,
            'video_url' => base_url($s->preview_video_path)
        ] : null;
    }, $shots ?? [])))) ?>;

    async function autoGenerateAllThumbnails() {
        if (!shotsWithVideos || shotsWithVideos.length === 0) {
            alert('No shots with video previews found in this project. Please upload videos first.');
            return;
        }

        if (!confirm(`Extract crisp mid-frame WebP thumbnails for all ${shotsWithVideos.length} shot videos?`)) {
            return;
        }

        const btn = event.currentTarget;
        const origText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75');
        }

        let successCount = 0;
        const canvas = document.createElement('canvas');
        canvas.width = 640;
        canvas.height = 360;
        const ctx = canvas.getContext('2d');

        for (let i = 0; i < shotsWithVideos.length; i++) {
            const item = shotsWithVideos[i];
            if (btn) {
                btn.innerHTML = `<span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span> Extracting ${i + 1}/${shotsWithVideos.length}...`;
            }

            try {
                const dataUrl = await extractVideoMidFrame(item.video_url, canvas, ctx);
                if (dataUrl) {
                    const formData = new FormData();
                    formData.append('shot_id', item.id);
                    formData.append('image_data', dataUrl);

                    const res = await fetch('/admin/projects/saveAutoThumbnailAjax', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const result = await res.json();
                    if (result.success && result.thumbnail_url) {
                        successCount++;
                        document.querySelectorAll(`.shot-thumb-img-${item.id}`).forEach(img => {
                            img.src = result.thumbnail_url + '?t=' + Date.now();
                        });
                    }
                }
            } catch (err) {
                console.warn(`Failed extracting thumbnail for ${item.shot_number}:`, err);
            }
        }

        if (btn) {
            btn.innerHTML = `<span class="material-symbols-outlined text-[16px] text-green-400">check_circle</span> Done (${successCount} WebP)!`;
            setTimeout(() => {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
                btn.innerHTML = origText;
                window.location.reload();
            }, 1200);
        }
    }

    function extractVideoMidFrame(videoUrl, canvas, ctx) {
        return new Promise((resolve, reject) => {
            const video = document.createElement('video');
            video.crossOrigin = 'anonymous';
            video.muted = true;
            video.playsInline = true;
            video.preload = 'auto';

            const timeout = setTimeout(() => {
                video.src = '';
                reject(new Error('Seek timeout'));
            }, 12000);

            video.onloadedmetadata = () => {
                const midTime = video.duration > 0 ? video.duration / 2 : 0.5;
                video.currentTime = midTime;
            };

            video.onseeked = () => {
                clearTimeout(timeout);
                try {
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const dataUrl = canvas.toDataURL('image/webp', 0.85);
                    video.src = '';
                    resolve(dataUrl);
                } catch (err) {
                    reject(err);
                }
            };

            video.onerror = () => {
                clearTimeout(timeout);
                reject(new Error('Video load error'));
            };

            video.src = videoUrl;
        });
    }

    // Dynamic Zero-Lag Hover Video Preview Engine
    function playHoverVideo(container, videoSrc) {
        if (!videoSrc || container.querySelector('video')) return;
        const vid = document.createElement('video');
        vid.src = videoSrc;
        vid.muted = true;
        vid.loop = true;
        vid.playsInline = true;
        vid.className = 'w-full h-full object-cover absolute inset-0 z-10 animate-fadeIn pointer-events-none';
        container.appendChild(vid);
        vid.play().catch(() => {});
    }

    function stopHoverVideo(container) {
        const vid = container.querySelector('video');
        if (vid) {
            vid.pause();
            vid.currentTime = 0;
            vid.src = '';
            vid.remove();
        }
    }
</script>

<!-- Quick Video Player Modal -->
<div id="quickVideoModal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-md flex items-center justify-center p-4" onclick="if(event.target===this) closeVideoModal()">
    <div class="bg-ytCard border border-ytBorder rounded-2xl overflow-hidden shadow-2xl max-w-3xl w-full">
        <div class="px-5 py-3.5 border-b border-ytBorder/60 flex items-center justify-between bg-[#111]">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-ytBlue text-[20px]">play_circle</span>
                <h4 id="quickVideoTitle" class="text-[14px] font-bold text-ytText font-mono">Video Preview</h4>
            </div>
            <button type="button" onclick="closeVideoModal()" class="text-ytMuted hover:text-ytText p-1 rounded-full hover:bg-ytHover transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div class="aspect-video bg-black flex items-center justify-center">
            <video id="quickVideoPlayer" controls playsinline class="w-full h-full object-contain"></video>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
