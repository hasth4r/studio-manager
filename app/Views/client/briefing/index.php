<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<!-- Header & Filter Bar -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-md pt-4 pb-3 border-b border-ytBorder/60 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <!-- Title & Breadcrumb -->
        <div class="flex items-center gap-3">
            <a href="/client/dashboard" class="p-2 bg-ytCard hover:bg-ytHover border border-ytBorder rounded-full text-ytMuted hover:text-ytText transition-colors" title="Back to Dashboard">
                <span class="material-symbols-outlined text-[20px] block">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-[20px] font-bold text-ytText leading-tight"><?= esc($project->name) ?></h2>
                    <span class="bg-blue-950/60 text-blue-400 border border-blue-800/40 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-medium">
                        <?= esc($project->project_code) ?> &bull; Shot Briefing Matrix
                    </span>
                </div>
                <div class="flex items-center gap-3 text-[12px] text-ytMuted mt-0.5 font-mono">
                    <span>Total Shots: <b class="text-ytText"><?= count($shots) ?></b></span>
                    <span>&bull;</span>
                    <span>Sequences: <b class="text-ytText"><?= count($sequences) ?></b></span>
                    <span>&bull;</span>
                    <span class="text-emerald-400 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">cloud_done</span> Live Auto-Save Active
                    </span>
                </div>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div class="flex items-center gap-2.5">
            <div class="relative w-64">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-ytMuted text-[16px]">search</span>
                <input type="text" id="briefSearchInput" oninput="filterBriefingTable()" placeholder="Search shot, notes, brief..." class="w-full bg-ytCard border border-ytBorder text-ytText rounded-lg pl-8 pr-3 py-1.5 text-[12px] focus:outline-none focus:border-ytBlue placeholder:text-ytMuted/50 font-mono">
            </div>

            <select id="sequenceFilter" onchange="filterBriefingTable()" class="bg-ytCard border border-ytBorder text-ytText rounded-lg px-3 py-1.5 text-[12px] focus:outline-none focus:border-ytBlue font-mono">
                <option value="">All Sequences (<?= count($sequences) ?>)</option>
                <?php foreach ($sequences as $seq): ?>
                    <option value="<?= esc($seq->id) ?>"><?= esc($seq->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- Main Briefing Matrix Table -->
<div class="bg-ytCard border border-ytBorder rounded-xl overflow-hidden shadow-2xl">
    <div class="overflow-x-auto custom-scrollbar max-h-[calc(100vh-210px)] overflow-y-auto">
        <table class="w-full text-left border-collapse" id="briefingTable">
            <thead class="sticky top-0 z-20 bg-[#121212] border-b border-ytBorder text-[11px] font-mono uppercase tracking-wider text-ytMuted select-none">
                <tr>
                    <th class="py-3 px-3 w-16 text-center">#</th>
                    <th class="py-3 px-3 w-36">Shot</th>
                    <th class="py-3 px-3 w-52">Visual Preview</th>
                    <th class="py-3 px-3 min-w-[340px]">Client Brief &amp; VFX Requirements</th>
                    <th class="py-3 px-3 min-w-[280px]">Reference Images &amp; Moodboard</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ytBorder/40 text-[13px] font-sans">
                <?php if (empty($shots)): ?>
                    <tr>
                        <td colspan="5" class="py-16 text-center text-ytMuted">
                            <span class="material-symbols-outlined text-[48px] opacity-30 mb-2 block">movie</span>
                            <p class="font-medium text-[15px] text-ytText">No shots found in this project yet.</p>
                            <p class="text-[12px] text-ytMuted mt-1">Shots will appear here once ingested by the VFX production team.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $idx = 1; foreach ($shots as $shot): ?>
                        <tr class="brief-shot-row hover:bg-[#161616]/80 transition-colors group" 
                            data-shot-id="<?= $shot->id ?>" 
                            data-seq-id="<?= $shot->sequence_id ?? '' ?>" 
                            data-search-text="<?= esc(strtolower($shot->shot_number . ' ' . ($shot->description ?? '') . ' ' . ($shot->client_notes ?? ''))) ?>">
                            
                            <!-- Index -->
                            <td class="py-3 px-3 text-center text-[11px] font-mono text-ytMuted/60 align-top pt-4">
                                <?= $idx++ ?>
                            </td>

                            <!-- Shot Number & Sequence -->
                            <td class="py-3 px-3 align-top pt-3.5">
                                <div class="font-mono font-bold text-white text-[14px] group-hover:text-ytBlue transition-colors flex items-center gap-1.5">
                                    <?= esc($shot->shot_number) ?>
                                </div>
                                <?php 
                                    $seqObj = null;
                                    foreach ($sequences as $s) { if ($s->id == $shot->sequence_id) { $seqObj = $s; break; } }
                                ?>
                                <?php if ($seqObj): ?>
                                    <span class="inline-block mt-1 bg-[#1a1a24] text-indigo-300 border border-indigo-900/40 text-[10px] font-mono px-2 py-0.5 rounded">
                                        <?= esc($seqObj->name) ?>
                                    </span>
                                <?php endif; ?>
                                <div class="text-[11px] text-ytMuted font-mono mt-2">
                                    <?= !empty($shot->frame_count) ? esc($shot->frame_count) . ' fr' : '' ?>
                                    <?= !empty($shot->fps) ? '&bull; ' . esc($shot->fps) . ' fps' : '' ?>
                                </div>
                            </td>

                            <!-- Visual Preview (Video & Thumbnail) -->
                            <td class="py-3 px-3 align-top pt-3">
                                <div class="w-48 aspect-video bg-[#0d0d0d] rounded-lg border border-ytBorder/80 overflow-hidden relative group/thumb shadow-sm"
                                     onmouseenter="playHoverVideo(this, '<?= !empty($shot->preview_video_path) ? base_url(esc($shot->preview_video_path)) : '' ?>')"
                                     onmouseleave="stopHoverVideo(this)">
                                    <?php if (!empty($shot->thumbnail_path)): ?>
                                        <img src="<?= base_url(esc($shot->thumbnail_path)) ?>" loading="lazy" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-ytMuted/40">
                                            <span class="material-symbols-outlined text-[28px]">image</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($shot->preview_video_path)): ?>
                                        <button type="button" 
                                                onclick="openVideoModal('<?= base_url(esc($shot->preview_video_path)) ?>', '<?= esc($shot->shot_number) ?>')" 
                                                class="absolute top-1.5 left-1.5 bg-black/85 hover:bg-blue-600 backdrop-blur-xs border border-blue-500/50 text-blue-200 hover:text-white text-[10px] font-mono font-bold px-2 py-0.5 rounded flex items-center gap-1 z-20 cursor-pointer shadow-md transition-all">
                                            <span class="material-symbols-outlined text-[13px]">play_circle</span> Preview
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Client Brief & Creative Notes (Auto-Saving Textarea) -->
                            <td class="py-3 px-3 align-top pt-3">
                                <div class="relative">
                                    <textarea 
                                        oninput="handleBriefInput(<?= $shot->id ?>)"
                                        onblur="saveBriefing(<?= $shot->id ?>)"
                                        id="brief_textarea_<?= $shot->id ?>"
                                        rows="4"
                                        placeholder="Type creative brief, VFX description, mood, lighting notes, or specific directions for this shot..."
                                        class="w-full bg-[#111114] border border-ytBorder hover:border-ytBlue/50 focus:border-ytBlue text-ytText rounded-xl p-3 text-[13px] focus:outline-none placeholder:text-ytMuted/40 resize-y transition-all leading-relaxed custom-scrollbar font-sans"><?= esc($shot->description ?? '') ?></textarea>
                                    
                                    <!-- Auto-Save Status Badge -->
                                    <div id="save_badge_<?= $shot->id ?>" class="absolute bottom-2.5 right-2.5 text-[10px] font-mono px-2 py-0.5 rounded opacity-0 transition-opacity pointer-events-none bg-black/80 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px]">check</span> Saved
                                    </div>
                                </div>
                            </td>

                            <!-- Reference Images & Moodboard Dropzone -->
                            <td class="py-3 px-3 align-top pt-3">
                                <div class="space-y-2">
                                    <!-- References Container -->
                                    <div id="ref_container_<?= $shot->id ?>" class="flex flex-wrap gap-2">
                                        <?php if (!empty($shot->references)): ?>
                                            <?php foreach ($shot->references as $ref): ?>
                                                <div class="relative group/ref w-16 h-16 rounded-lg border border-ytBorder bg-[#0a0a0a] overflow-hidden shrink-0 shadow-sm" id="ref_card_<?= md5($ref['path']) ?>">
                                                    <?php if (!empty($ref['is_image'])): ?>
                                                        <img src="<?= base_url(esc($ref['path'])) ?>" 
                                                             onclick="openLightbox('<?= base_url(esc($ref['path'])) ?>', '<?= esc($ref['name']) ?>')" 
                                                             class="w-full h-full object-cover cursor-pointer hover:scale-105 transition-transform" 
                                                             title="<?= esc($ref['name']) ?>">
                                                    <?php else: ?>
                                                        <a href="<?= base_url(esc($ref['path'])) ?>" target="_blank" class="w-full h-full flex flex-col items-center justify-center p-1 text-center text-ytMuted hover:text-ytText" title="<?= esc($ref['name']) ?>">
                                                            <span class="material-symbols-outlined text-[20px] text-ytBlue">description</span>
                                                            <span class="text-[8px] font-mono truncate w-full"><?= esc($ref['ext']) ?></span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <!-- Delete Button -->
                                                    <button type="button" 
                                                            onclick="deleteReference(<?= $shot->id ?>, '<?= esc($ref['path']) ?>', '<?= md5($ref['path']) ?>')" 
                                                            class="absolute top-0.5 right-0.5 bg-red-600/90 text-white rounded-full p-0.5 opacity-0 group-hover/ref:opacity-100 hover:bg-red-500 transition-all shadow" 
                                                            title="Delete attachment">
                                                        <span class="material-symbols-outlined text-[12px] block">close</span>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Upload Button / Dropzone -->
                                    <div>
                                        <input type="file" 
                                               id="ref_input_<?= $shot->id ?>" 
                                               onchange="uploadReference(<?= $shot->id ?>, this)" 
                                               accept="image/*,.pdf" 
                                               class="hidden">
                                        <button type="button" 
                                                onclick="document.getElementById('ref_input_<?= $shot->id ?>').click()" 
                                                class="w-full border border-dashed border-ytBorder hover:border-ytBlue/70 bg-[#111111]/60 hover:bg-[#181822] text-ytMuted hover:text-ytText rounded-lg py-2 px-3 text-[11px] font-mono flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                            <span class="material-symbols-outlined text-[15px] text-ytBlue">add_photo_alternate</span>
                                            <span>+ Attach Reference</span>
                                        </button>
                                    </div>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

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
        <div class="aspect-video bg-black relative flex items-center justify-center">
            <!-- Modal Loading Buffer Indicator -->
            <div id="quickVideoLoader" class="absolute inset-0 flex flex-col items-center justify-center bg-black/60 z-10 transition-opacity duration-300 pointer-events-none">
                <div class="relative w-10 h-10 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full border-2 border-blue-500/20"></div>
                    <div class="absolute inset-0 rounded-full border-2 border-t-blue-400 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
                    <span class="material-symbols-outlined text-[16px] text-blue-400">smart_display</span>
                </div>
                <span class="text-[11px] text-blue-300 font-mono mt-2 tracking-wider">Loading Video Stream...</span>
            </div>
            <video id="quickVideoPlayer" controls playsinline class="w-full h-full object-contain"></video>
        </div>
    </div>
</div>

<!-- Image Lightbox Modal -->
<div id="lightboxModal" class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-md flex items-center justify-center p-4" onclick="if(event.target===this) closeLightbox()">
    <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center">
        <div class="w-full flex justify-between items-center text-white mb-2 px-2">
            <span id="lightboxCaption" class="text-[13px] font-mono truncate font-medium">Reference Image</span>
            <button type="button" onclick="closeLightbox()" class="text-ytMuted hover:text-white p-1 rounded-full hover:bg-white/10 transition-colors">
                <span class="material-symbols-outlined text-[24px]">close</span>
            </button>
        </div>
        <img id="lightboxImg" src="" class="max-w-full max-h-[80vh] rounded-xl object-contain border border-white/10 shadow-2xl">
    </div>
</div>

<!-- Live Save Notification Toast -->
<div id="briefToast" class="fixed bottom-6 right-6 z-50 bg-[#064e3b] border border-emerald-500/40 text-emerald-200 px-4 py-2.5 rounded-xl shadow-2xl text-[13px] font-mono flex items-center gap-2 transition-all opacity-0 pointer-events-none transform translate-y-2">
    <span class="material-symbols-outlined text-[18px] text-emerald-400">check_circle</span>
    <span id="briefToastText">Auto-saved!</span>
</div>

<script>
    // 1. Debounced Auto-Save for Briefing Textareas
    const debounceTimers = {};

    function handleBriefInput(shotId) {
        if (debounceTimers[shotId]) clearTimeout(debounceTimers[shotId]);
        debounceTimers[shotId] = setTimeout(() => {
            saveBriefing(shotId);
        }, 800);
    }

    async function saveBriefing(shotId) {
        const textarea = document.getElementById(`brief_textarea_${shotId}`);
        const badge = document.getElementById(`save_badge_${shotId}`);
        if (!textarea) return;

        const description = textarea.value;

        try {
            const formData = new FormData();
            formData.append('shot_id', shotId);
            formData.append('description', description);

            const res = await fetch('/client/projects/saveBriefAjax', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();
            if (data.success) {
                if (badge) {
                    badge.classList.remove('opacity-0');
                    setTimeout(() => badge.classList.add('opacity-0'), 1800);
                }
            }
        } catch (err) {
            console.error('Error auto-saving brief:', err);
        }
    }

    // 2. Upload Reference Image or Document
    async function uploadReference(shotId, inputElement) {
        if (!inputElement.files || !inputElement.files[0]) return;

        const file = inputElement.files[0];
        const container = document.getElementById(`ref_container_${shotId}`);

        const formData = new FormData();
        formData.append('shot_id', shotId);
        formData.append('reference_file', file);

        showToast(`Uploading ${file.name}...`);

        try {
            const res = await fetch('/client/projects/uploadReferenceAjax', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();
            if (data.success && data.reference) {
                showToast(`Attached ${data.reference.name}!`);
                inputElement.value = '';

                // Append reference card to DOM
                const ref = data.reference;
                const cardId = 'ref_' + Math.random().toString(36).substr(2, 9);
                const card = document.createElement('div');
                card.id = cardId;
                card.className = 'relative group/ref w-16 h-16 rounded-lg border border-ytBorder bg-[#0a0a0a] overflow-hidden shrink-0 shadow-sm animate-fadeIn';

                if (ref.is_image) {
                    card.innerHTML = `
                        <img src="${ref.url}" onclick="openLightbox('${ref.url}', '${ref.name.replace(/'/g, "\\'")}')" class="w-full h-full object-cover cursor-pointer hover:scale-105 transition-transform" title="${ref.name}">
                        <button type="button" onclick="deleteReference(${shotId}, '${ref.path}', '${cardId}')" class="absolute top-0.5 right-0.5 bg-red-600/90 text-white rounded-full p-0.5 opacity-0 group-hover/ref:opacity-100 hover:bg-red-500 transition-all shadow">
                            <span class="material-symbols-outlined text-[12px] block">close</span>
                        </button>
                    `;
                } else {
                    card.innerHTML = `
                        <a href="${ref.url}" target="_blank" class="w-full h-full flex flex-col items-center justify-center p-1 text-center text-ytMuted hover:text-ytText" title="${ref.name}">
                            <span class="material-symbols-outlined text-[20px] text-ytBlue">description</span>
                            <span class="text-[8px] font-mono truncate w-full">${ref.ext}</span>
                        </a>
                        <button type="button" onclick="deleteReference(${shotId}, '${ref.path}', '${cardId}')" class="absolute top-0.5 right-0.5 bg-red-600/90 text-white rounded-full p-0.5 opacity-0 group-hover/ref:opacity-100 hover:bg-red-500 transition-all shadow">
                            <span class="material-symbols-outlined text-[12px] block">close</span>
                        </button>
                    `;
                }

                if (container) container.appendChild(card);
            } else {
                alert(data.error || 'Failed to upload attachment');
            }
        } catch (err) {
            console.error('Error uploading reference:', err);
            alert('Upload error occurred');
        }
    }

    // 3. Delete Reference Attachment
    async function deleteReference(shotId, refPath, cardId) {
        if (!confirm('Remove this reference attachment?')) return;

        try {
            const formData = new FormData();
            formData.append('shot_id', shotId);
            formData.append('ref_path', refPath);

            const res = await fetch('/client/projects/deleteReferenceAjax', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();
            if (data.success) {
                const card = document.getElementById(cardId) || document.getElementById(`ref_card_${cardId}`);
                if (card) card.remove();
                showToast('Reference removed');
            }
        } catch (err) {
            console.error('Error removing reference:', err);
        }
    }

    // 4. Live Table Filtering (Search & Sequence Filter)
    function filterBriefingTable() {
        const query = (document.getElementById('briefSearchInput').value || '').toLowerCase().trim();
        const seqFilter = document.getElementById('sequenceFilter').value;
        const rows = document.querySelectorAll('.brief-shot-row');

        rows.forEach(row => {
            const rowSeq = row.getAttribute('data-seq-id');
            const rowText = row.getAttribute('data-search-text') || '';

            const matchesSeq = !seqFilter || rowSeq === seqFilter;
            const matchesQuery = !query || rowText.includes(query);

            if (matchesSeq && matchesQuery) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // 5. Video Player Modal with Loading Animation
    function openVideoModal(videoUrl, shotTitle) {
        const modal = document.getElementById('quickVideoModal');
        const video = document.getElementById('quickVideoPlayer');
        const title = document.getElementById('quickVideoTitle');
        const loader = document.getElementById('quickVideoLoader');
        if (!modal || !video) return;

        title.textContent = `Preview: Shot ${shotTitle}`;
        if (loader) {
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }

        video.onplaying = () => {
            if (loader) {
                loader.classList.remove('opacity-100');
                loader.classList.add('opacity-0', 'pointer-events-none');
            }
        };
        video.onwaiting = () => {
            if (loader) {
                loader.classList.remove('opacity-0', 'pointer-events-none');
                loader.classList.add('opacity-100');
            }
        };

        video.src = videoUrl;
        modal.classList.remove('hidden');
        video.play().catch(() => {});
    }

    function closeVideoModal() {
        const modal = document.getElementById('quickVideoModal');
        const video = document.getElementById('quickVideoPlayer');
        const loader = document.getElementById('quickVideoLoader');
        if (!modal || !video) return;

        video.pause();
        video.currentTime = 0;
        video.src = '';
        video.onplaying = null;
        video.onwaiting = null;
        if (loader) {
            loader.classList.remove('opacity-100');
            loader.classList.add('opacity-0', 'pointer-events-none');
        }
        modal.classList.add('hidden');
    }

    // 6. Lightbox
    function openLightbox(imgUrl, caption) {
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImg');
        const cap = document.getElementById('lightboxCaption');
        if (!modal || !img) return;

        img.src = imgUrl;
        if (cap) cap.textContent = caption || 'Reference Image';
        modal.classList.remove('hidden');
    }

    function closeLightbox() {
        const modal = document.getElementById('lightboxModal');
        if (modal) modal.classList.add('hidden');
    }

    // 7. Toast Notification Helper
    let toastTimeout = null;
    function showToast(msg) {
        const toast = document.getElementById('briefToast');
        const text = document.getElementById('briefToastText');
        if (!toast || !text) return;

        text.textContent = msg;
        toast.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');

        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'pointer-events-none', 'translate-y-2');
        }, 2200);
    }

    // 8. Dynamic Zero-Lag Hover Video Preview Engine with Loading Animation
    function playHoverVideo(container, videoSrc) {
        if (!videoSrc || container.querySelector('video')) return;

        // Add glowing loader spinner
        const loader = document.createElement('div');
        loader.className = 'hover-video-loader absolute inset-0 flex flex-col items-center justify-center bg-black/60 backdrop-blur-xs z-20 pointer-events-none transition-opacity duration-300';
        loader.innerHTML = `
            <div class="relative w-8 h-8 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border-2 border-blue-500/20"></div>
                <div class="absolute inset-0 rounded-full border-2 border-t-blue-400 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
                <span class="material-symbols-outlined text-[13px] text-blue-400 animate-pulse">play_arrow</span>
            </div>
            <span class="text-[9px] text-blue-300 font-mono mt-1 uppercase tracking-widest font-semibold">Buffering...</span>
        `;
        container.appendChild(loader);

        const vid = document.createElement('video');
        vid.src = videoSrc;
        vid.muted = true;
        vid.loop = true;
        vid.playsInline = true;
        vid.className = 'w-full h-full object-cover absolute inset-0 z-10 opacity-0 transition-opacity duration-300 pointer-events-none';

        vid.onplaying = () => {
            vid.classList.remove('opacity-0');
            loader.classList.add('opacity-0');
            setTimeout(() => loader.remove(), 300);
        };
        vid.onwaiting = () => {
            loader.classList.remove('opacity-0');
        };

        container.appendChild(vid);
        vid.play().catch(() => {});
    }

    function stopHoverVideo(container) {
        const loader = container.querySelector('.hover-video-loader');
        if (loader) loader.remove();
        const vid = container.querySelector('video');
        if (vid) {
            vid.pause();
            vid.currentTime = 0;
            vid.src = '';
            vid.remove();
        }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #121212; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #333338; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #55555e; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-fadeIn { animation: fadeIn 0.2s ease-out forwards; }
</style>

<?= $this->endSection() ?>
