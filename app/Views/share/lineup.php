<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Lineup Presentation') ?> - Studio Inphenix</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        ytBg: '#0b0f19',
                        ytCard: '#111827',
                        ytBorder: '#1f293d',
                        ytBlue: '#3b82f6',
                        ytHover: '#1e293b'
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #080c14; color: #f1f5f9; overflow: hidden; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #0b0f19; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        /* Diagonal Repeating Watermark */
        .watermark-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 25;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .watermark-text {
            transform: rotate(-25deg);
            font-size: clamp(20px, 3.5vw, 42px);
            font-weight: 800;
            letter-spacing: 0.15em;
            color: rgba(255, 255, 255, 0.08);
            text-transform: uppercase;
            user-select: none;
            white-space: nowrap;
            text-shadow: 0 0 20px rgba(0,0,0,0.8);
        }
    </style>
</head>
<body class="h-screen w-screen flex flex-col bg-[#080c14] select-none" oncontextmenu="return false;">

    <!-- Top Header -->
    <header class="h-[56px] px-6 bg-[#0f172a] border-b border-slate-800 flex items-center justify-between shrink-0 z-20 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/20 font-bold text-white text-xs">
                    EP
                </div>
                <div>
                    <h1 class="text-[14px] font-bold text-white leading-tight flex items-center gap-2">
                        <?= esc($sequence->project_name) ?> 
                        <span class="text-slate-500">/</span> 
                        <span class="text-blue-400"><?= esc($sequence->name) ?></span>
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium">Editorial Sequence Lineup • <?= count($playlist) ?> Shots</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <?php if(!empty($shareToken->expires_at)): ?>
                <div class="flex items-center gap-1.5 bg-amber-500/10 border border-amber-500/20 px-3 py-1 rounded-full text-amber-400 text-xs font-medium">
                    <span class="material-symbols-outlined text-[14px]">timer</span>
                    <span>Expires <?= date('M d, H:i', strtotime($shareToken->expires_at)) ?></span>
                </div>
            <?php endif; ?>

            <div class="flex items-center gap-1.5 bg-blue-500/10 border border-blue-500/20 px-3 py-1 rounded-full text-blue-400 text-xs font-semibold">
                <span class="material-symbols-outlined text-[14px]">visibility</span>
                <span>View-Only Presentation</span>
            </div>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden w-full h-[calc(100vh-56px)] bg-black">
        
        <!-- Video Container with Security Overlays -->
        <div class="flex-1 min-w-0 min-h-0 flex items-center justify-center relative overflow-hidden bg-black" id="videoContainer">
            
            <!-- Video Player -->
            <video id="mediaElement" class="w-full h-full object-contain" disablePictureInPicture controlsList="nodownload" oncontextmenu="return false;" playsinline>
                Your browser does not support the video tag.
            </video>

            <!-- Diagonal Anti-Theft Watermark -->
            <div class="watermark-overlay">
                <div class="watermark-text"><?= esc($watermarkText) ?></div>
            </div>

            <!-- Corner Studio Protection Badge -->
            <div class="absolute bottom-4 right-4 z-20 pointer-events-none opacity-40 hover:opacity-80 transition-opacity flex items-center gap-2 bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-lg border border-white/10">
                <span class="text-[10px] font-mono text-slate-300 font-semibold tracking-wider uppercase">STUDIO INPHENIX • PROTECTED</span>
            </div>
        </div>

        <!-- DJV 1.1.0 Replica Playback Controls -->
        <div class="bg-[#1e222d] flex flex-col shrink-0 z-10 relative border-t border-[#111] min-w-0 w-full">
            
            <!-- Sequence Lineup Mini-Track -->
            <div class="flex-1 flex flex-col bg-[#161a23] relative border-b border-[#2d3446] min-w-0 w-full overflow-hidden" id="timelineContainer">
                
                <div class="h-[60px] bg-[#0c1017] flex overflow-x-auto overflow-y-hidden custom-scrollbar border-t border-[#232938] select-none min-w-0 w-full <?= count($playlist) <= 6 ? 'justify-center' : '' ?>" id="sequenceLineupTrack">
                    <?php foreach($playlist as $idx => $clip): ?>
                        <div class="border-r border-[#232938] relative flex items-stretch cursor-pointer transition-colors seq-clip-block group shrink-0 w-[150px] md:w-[175px]" data-idx="<?= $idx ?>" id="seq-clip-<?= $idx ?>">
                            <!-- Hover overlay -->
                            <div class="absolute inset-0 bg-white/0 group-hover:bg-white/5 transition-colors pointer-events-none z-10"></div>
                            
                            <!-- Thumbnail -->
                            <?php if(!empty($clip['thumbnail_path'])): ?>
                                <img src="<?= esc($clip['thumbnail_path']) ?>" loading="lazy" class="h-full w-[70px] md:w-[80px] object-cover shrink-0 border-r border-[#232938]">
                            <?php else: ?>
                                <div class="h-full w-[70px] md:w-[80px] shrink-0 bg-[#161a23] border-r border-[#232938] flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[#475569] text-[20px]">image</span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Text Info -->
                            <div class="relative z-10 flex flex-col justify-center px-2.5 overflow-hidden flex-1 min-w-[70px]">
                                <span class="text-[11px] font-bold text-white pointer-events-none leading-tight truncate"><?= esc($clip['shot_number']) ?></span>
                                <span class="text-[9px] font-medium text-slate-400 pointer-events-none leading-tight mt-0.5 truncate"><?= esc($clip['task_name'] ?? '') ?> • <span class="text-indigo-400"><?= esc($clip['version_string'] ?? '') ?></span></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Scrub Track Wrap -->
                <div class="relative h-[24px] cursor-pointer" id="timelineTrackWrap">
                    <div class="absolute top-0 inset-x-0 h-[10px] flex justify-between px-2 opacity-30 text-[8px] font-mono text-[#94a3b8] overflow-hidden pointer-events-none" id="timelineTicks">
                        <span>0</span><span>24</span><span>48</span><span>72</span><span>96</span><span>120</span><span>144</span><span>168</span><span>192</span>
                    </div>
                    <div class="absolute bottom-0 inset-x-0 h-[16px] bg-[#0c1017] border-t border-[#232938]" id="timelineTrack">
                        <div id="timelineProgress" class="absolute top-0 bottom-0 left-0 bg-blue-600/80 border-r-2 border-white w-0 pointer-events-none transition-all duration-75"></div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Controls & Counters -->
            <div class="flex flex-col md:flex-row md:items-stretch h-auto md:h-[38px] bg-[#161a23] text-[#94a3b8]">
                <!-- Left: Playback Controls Cluster -->
                <div class="flex justify-center h-[38px] md:h-full border-b md:border-b-0 md:border-r border-[#232938]">
                    <button id="stopBtn" class="w-[38px] h-full flex justify-center items-center hover:bg-[#232938] border-r border-[#232938] transition-colors" title="Stop">
                        <div class="w-3 h-3 bg-[#cbd5e1] rounded-sm"></div>
                    </button>
                    <button id="playPauseBtn" class="w-[38px] h-full flex justify-center items-center hover:bg-[#232938] border-r border-[#232938] transition-colors" title="Play / Pause (Space)">
                        <span class="material-symbols-outlined text-[20px] text-[#cbd5e1]" id="playPauseIcon">play_arrow</span>
                    </button>
                    <button id="loopBtn" class="w-[38px] h-full flex justify-center items-center hover:bg-[#232938] border-r border-[#232938] transition-colors text-blue-400" title="Loop Toggle">
                        <span class="material-symbols-outlined text-[18px]">loop</span>
                    </button>
                    <button id="goToStartBtn" class="w-[38px] h-full flex justify-center items-center hover:bg-[#232938] border-r border-[#232938] transition-colors" title="Go to Start (Home)">
                        <span class="material-symbols-outlined text-[18px] text-[#cbd5e1]">first_page</span>
                    </button>
                    <button id="stepBackBtn" class="w-[38px] h-full flex justify-center items-center hover:bg-[#232938] border-r border-[#232938] transition-colors" title="Step Back 1 Frame (Left Arrow)">
                        <span class="material-symbols-outlined text-[18px] text-[#cbd5e1]">chevron_left</span>
                    </button>
                    <button id="stepForwardBtn" class="w-[38px] h-full flex justify-center items-center hover:bg-[#232938] border-r border-[#232938] transition-colors" title="Step Forward 1 Frame (Right Arrow)">
                        <span class="material-symbols-outlined text-[18px] text-[#cbd5e1]">chevron_right</span>
                    </button>
                    <button id="goToEndBtn" class="w-[38px] h-full flex justify-center items-center hover:bg-[#232938] transition-colors" title="Go to End (End)">
                        <span class="material-symbols-outlined text-[18px] text-[#cbd5e1]">last_page</span>
                    </button>
                </div>

                <!-- Center: Frame & Timecode Readouts -->
                <div class="flex-1 flex items-center justify-between px-4 py-1.5 md:py-0 border-b md:border-b-0 md:border-r border-[#232938] font-mono text-[12px]">
                    <div class="flex items-center gap-3">
                        <span class="bg-[#0c1017] border border-[#232938] px-2 py-0.5 rounded text-blue-400 font-semibold" id="fpsDisplay">24.00 FPS</span>
                        <div class="flex items-center gap-1.5 text-slate-300">
                            <span class="text-slate-500">FRAME</span>
                            <span id="frameDisplay" class="text-white font-bold">0</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-300">
                        <span class="text-slate-500">TC</span>
                        <span id="timecodeDisplay" class="text-white font-bold">00:00:00:00</span>
                    </div>
                </div>

                <!-- Right: Fullscreen -->
                <div class="hidden md:flex w-[40px] h-full justify-center items-center cursor-pointer border-l border-[#232938] hover:bg-[#232938] transition-colors" id="fullscreenBtn" title="Toggle Fullscreen (F)">
                    <span class="material-symbols-outlined text-[20px] text-[#cbd5e1]">crop_free</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Presentation Player JS -->
    <script>
        const playlist = <?= json_encode($playlist) ?>;
        let currentPlaylistIndex = 0;
        const media = document.getElementById('mediaElement');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const playPauseIcon = document.getElementById('playPauseIcon');
        const stopBtn = document.getElementById('stopBtn');
        const goToStartBtn = document.getElementById('goToStartBtn');
        const goToEndBtn = document.getElementById('goToEndBtn');
        const stepBackBtn = document.getElementById('stepBackBtn');
        const stepForwardBtn = document.getElementById('stepForwardBtn');
        const timelineProgress = document.getElementById('timelineProgress');
        const timecodeDisplay = document.getElementById('timecodeDisplay');
        const frameDisplay = document.getElementById('frameDisplay');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const lineupTrack = document.getElementById('sequenceLineupTrack');

        const FPS = 24.0;
        let totalSequenceDuration = playlist.reduce((sum, c) => sum + (c.duration || 0), 0);
        let isLooping = true;
        let preloadVideoPool = null;

        function preloadNextClip(currentIndex) {
            if (!playlist || currentIndex + 1 >= playlist.length) return;
            const nextClip = playlist[currentIndex + 1];
            if (nextClip && nextClip.proxy_url) {
                if (!preloadVideoPool) {
                    preloadVideoPool = document.createElement('video');
                    preloadVideoPool.preload = 'auto';
                    preloadVideoPool.muted = true;
                }
                preloadVideoPool.src = nextClip.proxy_url;
            }
        }

        function playSequenceClip(index) {
            if (!playlist || index < 0 || index >= playlist.length) return;
            if (!playlist[index].proxy_url) {
                if (index + 1 < playlist.length) {
                    playSequenceClip(index + 1);
                } else {
                    currentPlaylistIndex = 0;
                    playSequenceClip(0);
                    media.pause();
                }
                return;
            }

            currentPlaylistIndex = index;

            // UI Active block
            document.querySelectorAll('.seq-clip-block').forEach(el => el.classList.remove('bg-blue-600/20', 'border-blue-500', 'ring-1', 'ring-blue-500'));
            const activeBlock = document.getElementById('seq-clip-' + index);
            if (activeBlock) {
                activeBlock.classList.add('bg-blue-600/20', 'border-blue-500', 'ring-1', 'ring-blue-500');
                activeBlock.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }

            if (media.src !== playlist[index].proxy_url) {
                media.src = playlist[index].proxy_url;
                media.load();
            }

            const playPromise = media.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    playPauseIcon.textContent = 'pause';
                }).catch(e => {
                    playPauseIcon.textContent = 'play_arrow';
                });
            }

            preloadNextClip(index);
        }

        // Initialize First Clip
        if (playlist.length > 0) {
            media.src = playlist[0].proxy_url;
            const firstBlock = document.getElementById('seq-clip-0');
            if (firstBlock) firstBlock.classList.add('bg-blue-600/20', 'border-blue-500', 'ring-1', 'ring-blue-500');
        }

        function togglePlay() {
            if (media.paused) {
                media.play().then(() => {
                    playPauseIcon.textContent = 'pause';
                }).catch(e => console.log('Play blocked', e));
            } else {
                media.pause();
                playPauseIcon.textContent = 'play_arrow';
            }
        }

        function formatTimecode(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            const f = Math.floor((seconds % 1) * FPS);
            return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}:${String(f).padStart(2,'0')}`;
        }

        // Timeupdate Progress
        media.addEventListener('timeupdate', () => {
            let globalTime = 0;
            for (let i = 0; i < currentPlaylistIndex; i++) {
                globalTime += (playlist[i].duration || 0);
            }
            globalTime += media.currentTime;

            if (totalSequenceDuration > 0) {
                const pct = (globalTime / totalSequenceDuration) * 100;
                timelineProgress.style.width = `${pct}%`;
            }

            timecodeDisplay.textContent = formatTimecode(globalTime);
            frameDisplay.textContent = Math.round(globalTime * FPS);
        });

        // Sequence Track Click
        document.querySelectorAll('.seq-clip-block').forEach(el => {
            el.addEventListener('click', (e) => {
                const idx = parseInt(e.currentTarget.dataset.idx);
                playSequenceClip(idx);
            });
        });

        // Timeline Scrubbing
        const trackWrap = document.getElementById('timelineTrackWrap');
        if (trackWrap) {
            trackWrap.addEventListener('click', (e) => {
                const rect = trackWrap.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const pct = Math.max(0, Math.min(1, clickX / rect.width));
                const targetGlobalTime = pct * totalSequenceDuration;

                // Find matching clip
                let accumulated = 0;
                for (let i = 0; i < playlist.length; i++) {
                    const dur = playlist[i].duration || 0;
                    if (targetGlobalTime <= accumulated + dur || i === playlist.length - 1) {
                        const localTime = Math.max(0, targetGlobalTime - accumulated);
                        if (currentPlaylistIndex !== i) {
                            playSequenceClip(i);
                            media.addEventListener('loadeddata', () => {
                                media.currentTime = localTime;
                            }, { once: true });
                        } else {
                            media.currentTime = localTime;
                        }
                        break;
                    }
                    accumulated += dur;
                }
            });
        }

        // End of Clip Auto-Advance
        media.addEventListener('ended', () => {
            if (currentPlaylistIndex + 1 < playlist.length) {
                playSequenceClip(currentPlaylistIndex + 1);
            } else if (isLooping) {
                playSequenceClip(0);
            } else {
                media.pause();
                playPauseIcon.textContent = 'play_arrow';
            }
        });

        // Mousewheel Horizontal Scrolling
        if (lineupTrack) {
            lineupTrack.addEventListener('wheel', (e) => {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    lineupTrack.scrollLeft += e.deltaY * 1.5;
                }
            }, { passive: false });
        }

        // Playback Buttons
        playPauseBtn.addEventListener('click', togglePlay);
        stopBtn.addEventListener('click', () => {
            media.pause();
            media.currentTime = 0;
            currentPlaylistIndex = 0;
            playSequenceClip(0);
            media.pause();
            playPauseIcon.textContent = 'play_arrow';
        });
        goToStartBtn.addEventListener('click', () => {
            currentPlaylistIndex = 0;
            playSequenceClip(0);
        });
        goToEndBtn.addEventListener('click', () => {
            currentPlaylistIndex = playlist.length - 1;
            playSequenceClip(currentPlaylistIndex);
        });
        stepBackBtn.addEventListener('click', () => {
            media.pause();
            playPauseIcon.textContent = 'play_arrow';
            media.currentTime = Math.max(0, media.currentTime - (1 / FPS));
        });
        stepForwardBtn.addEventListener('click', () => {
            media.pause();
            playPauseIcon.textContent = 'play_arrow';
            media.currentTime = Math.min(media.duration || 0, media.currentTime + (1 / FPS));
        });
        fullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => console.log(err));
            } else {
                document.exitFullscreen();
            }
        });

        // Keyboard Shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space') {
                e.preventDefault();
                togglePlay();
            } else if (e.code === 'ArrowLeft') {
                e.preventDefault();
                stepBackBtn.click();
            } else if (e.code === 'ArrowRight') {
                e.preventDefault();
                stepForwardBtn.click();
            } else if (e.key === 'f' || e.key === 'F') {
                fullscreenBtn.click();
            }
        });
    </script>
</body>
</html>
