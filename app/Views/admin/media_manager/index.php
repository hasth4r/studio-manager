<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>
<style>
    /* Custom Tree View Styles */
    .tree-node {
        display: flex;
        align-items: center;
        padding: 6px 8px;
        cursor: pointer;
        border-radius: 6px;
        transition: background-color 0.2s ease;
        user-select: none;
        color: var(--color-text);
        font-size: 14px;
        white-space: nowrap;
    }
    .tree-node:hover {
        background-color: rgba(255,255,255,0.05);
    }
    .tree-icon {
        margin-right: 8px;
        font-size: 18px;
        color: var(--color-muted);
        transition: transform 0.2s ease;
    }
    .tree-node.open > .tree-chevron {
        transform: rotate(90deg);
    }
    .tree-children {
        margin-left: 20px;
        border-left: 1px solid rgba(255,255,255,0.1);
        padding-left: 10px;
        display: none;
        animation: slideDown 0.2s ease forwards;
    }
    .tree-item-container.open > .tree-children {
        display: block;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Context Menu */
    #context-menu {
        position: absolute;
        background-color: #1a1a1a;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        padding: 4px 0;
        z-index: 1000;
        min-width: 160px;
        display: none;
    }
    .context-item {
        padding: 8px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 13px;
        color: #e2e8f0;
        transition: background 0.2s;
    }
    .context-item:hover {
        background-color: rgba(255,255,255,0.1);
    }
</style>

<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 mb-6 border-b border-ytBorder/50">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-gray-400 border border-gray-700/50">
                <span class="material-symbols-outlined">folder_open</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">Media Explorer</h2>
                <p class="text-[13px] text-ytMuted mt-1">Browse and manage all studio assets, shots, and versions.</p>
            </div>
        </div>
        <div>
            <button onclick="loadTree()" class="bg-ytHover text-ytText px-4 py-2 rounded-lg text-[13px] hover:bg-ytBorder transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">refresh</span> Refresh
            </button>
        </div>
    </div>
</div>

<div class="h-full flex flex-col">


    <!-- Main Explorer Area -->
    <div class="flex-1 overflow-auto p-6 bg-[#0a0a0f]">
        <div id="tree-root" class="max-w-4xl">
            <div class="flex items-center text-ytMuted text-sm">
                <span class="material-symbols-outlined animate-spin mr-2">sync</span> Loading media tree...
            </div>
        </div>
    </div>
</div>

<!-- Custom Context Menu -->
<div id="context-menu">
    <div class="context-item hover:text-blue-400" id="btn-replace-media">
        <span class="material-symbols-outlined text-[16px]">upload_file</span> Replace Media
    </div>
    <div class="context-item hover:text-green-400" id="btn-download-media">
        <span class="material-symbols-outlined text-[16px]">download</span> Download
    </div>
</div>

<!-- Replace Media Modal -->
<div id="replaceMediaModal" class="hidden fixed inset-0 bg-black/80 z-[100] flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder w-full max-w-md rounded-xl p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-ytText tracking-tight">Replace Media</h2>
                <p class="text-[12px] text-ytMuted" id="replace-target-name">Target: </p>
            </div>
            <button onclick="closeModal()" class="text-ytMuted hover:text-ytText transition-colors p-1">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="replaceForm" method="POST" enctype="multipart/form-data" class="m-0">
            <?= csrf_field() ?>
            <div class="mb-6">
                <label class="block text-[13px] font-medium text-ytMuted mb-2">New Media File</label>
                <input type="file" name="media_file" required accept="video/mp4,video/quicktime,image/jpeg,image/png,image/webp" 
                    class="w-full bg-[#111] border border-ytBorder/50 rounded-lg p-2 text-[14px] text-ytText file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[12px] file:font-semibold file:bg-ytBlue file:text-white hover:file:bg-blue-500 cursor-pointer">
                <p class="text-[11px] text-ytMuted mt-2">This replaces the actual proxy file on disk.</p>
            </div>
            
            <div class="flex justify-end gap-3 pt-2 border-t border-ytBorder/30">
                <button type="button" onclick="closeModal()" class="px-5 py-2 rounded-full text-[13px] font-medium text-ytMuted hover:text-ytText hover:bg-ytHover transition-all">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-full text-[13px] font-medium bg-ytBlue text-white hover:bg-blue-500 shadow-lg shadow-ytBlue/20 transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">upload</span> Upload & Replace
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentReviewId = null;
    let currentVersionName = "";

    function loadTree() {
        const root = document.getElementById('tree-root');
        root.innerHTML = '<div class="flex items-center text-ytMuted text-sm"><span class="material-symbols-outlined animate-spin mr-2">sync</span> Reloading...</div>';
        
        fetch('/media/tree')
            .then(res => res.json())
            .then(data => {
                root.innerHTML = '';
                data.forEach(node => {
                    root.appendChild(buildTreeNode(node));
                });
            });
    }

    function buildTreeNode(node) {
        const container = document.createElement('div');
        container.className = 'tree-item-container';
        container.id = 'node_' + node.id;
        
        // Restore state
        let openNodes = JSON.parse(localStorage.getItem('mediaExplorerOpenNodes') || '[]');
        if (openNodes.includes(container.id)) {
            container.classList.add('open');
        }

        const row = document.createElement('div');
        row.className = 'tree-node';

        // Icons based on type
        let icon = 'folder';
        let colorClass = 'text-blue-400';
        
        if (node.type === 'project') { icon = 'movie'; colorClass = 'text-purple-400'; }
        if (node.type === 'sequence') { icon = 'view_comfy'; colorClass = 'text-indigo-400'; }
        if (node.type === 'shot') { icon = 'theaters'; colorClass = 'text-pink-400'; }
        if (node.type === 'task') { icon = 'assignment'; colorClass = 'text-green-400'; }
        if (node.type === 'file') { icon = 'video_file'; colorClass = 'text-gray-300'; }

        // Chevron
        if (node.children && node.children.length > 0) {
            row.innerHTML += `<span class="material-symbols-outlined tree-chevron text-[14px] text-ytMuted mr-1">chevron_right</span>`;
        } else {
            row.innerHTML += `<span class="w-[14px] mr-1 inline-block"></span>`; // Spacer
        }

        row.innerHTML += `
            <span class="material-symbols-outlined tree-icon ${colorClass}">${icon}</span>
            <span class="${node.type === 'file' ? 'font-mono text-[13px]' : 'font-medium'}">${node.text}</span>
        `;

        // Interaction
        if (node.type === 'file') {
            row.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                showContextMenu(e.pageX, e.pageY, node.review_id, node.text, node.file_path);
            });
        } else {
            row.addEventListener('click', () => {
                container.classList.toggle('open');
                
                // Save state
                let currentOpen = JSON.parse(localStorage.getItem('mediaExplorerOpenNodes') || '[]');
                if (container.classList.contains('open')) {
                    if (!currentOpen.includes(container.id)) currentOpen.push(container.id);
                } else {
                    currentOpen = currentOpen.filter(id => id !== container.id);
                }
                localStorage.setItem('mediaExplorerOpenNodes', JSON.stringify(currentOpen));
            });
        }

        container.appendChild(row);

        // Children
        if (node.children && node.children.length > 0) {
            const childrenWrapper = document.createElement('div');
            childrenWrapper.className = 'tree-children';
            node.children.forEach(child => {
                childrenWrapper.appendChild(buildTreeNode(child));
            });
            container.appendChild(childrenWrapper);
        }

        return container;
    }

    // Context Menu Logic
    const contextMenu = document.getElementById('context-menu');
    let currentFilePath = '';

    function showContextMenu(x, y, reviewId, versionName, filePath) {
        currentReviewId = reviewId;
        currentVersionName = versionName;
        currentFilePath = filePath;
        
        contextMenu.style.left = x + 'px';
        contextMenu.style.top = y + 'px';
        contextMenu.style.display = 'block';
    }

    document.addEventListener('click', () => {
        contextMenu.style.display = 'none';
    });

    document.getElementById('btn-replace-media').addEventListener('click', () => {
        if (currentReviewId) {
            document.getElementById('replace-target-name').innerText = 'Target: ' + currentVersionName;
            document.getElementById('replaceForm').action = '/admin/media/replaceMedia/' + currentReviewId;
            document.getElementById('replaceMediaModal').classList.remove('hidden');
        }
    });
    
    document.getElementById('btn-download-media').addEventListener('click', () => {
        if (currentFilePath) {
            window.open('/' + currentFilePath, '_blank');
        }
    });

    function closeModal() {
        document.getElementById('replaceMediaModal').classList.add('hidden');
    }

    // Init
    document.addEventListener('DOMContentLoaded', loadTree);
</script>

<?= $this->endSection() ?>
