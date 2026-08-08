<?php
$routePrefix = (isset($userRole) && $userRole === 'client') ? 'client' : 'admin';
if (!function_exists('renderCommentBox')) {
    function renderCommentBox($comment, $isReply = false) {
        $marginClass = $isReply ? 'mt-2' : 'mt-4';
        $snippet = substr(str_replace(["\r", "\n"], ' ', $comment->comment_text), 0, 30) . '...';
        $replyCall = htmlspecialchars(sprintf("replyTo(%d, '%s', '%s', %s)", $comment->id, addslashes($comment->reviewer_name), addslashes($snippet), $comment->timecode !== null ? $comment->timecode : 'null'));
        $escapedCanvasData = htmlspecialchars($comment->canvas_data ?? '', ENT_QUOTES, 'UTF-8');
?>
        <div draggable="true" ondragstart="event.dataTransfer.setData('text/plain', <?= $comment->id ?>); event.dataTransfer.effectAllowed = 'copy';" class="bg-ytBg border border-ytBorder rounded-lg p-3 relative group <?= $marginClass ?> cursor-move" id="comment-box-<?= $comment->id ?>" data-comment-id="<?= $comment->id ?>" data-timecode="<?= $comment->timecode ?>" data-role="<?= esc($comment->reviewer_role) ?>" data-canvas="<?= $escapedCanvasData ?>">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($comment->reviewer_name) ?>&background=177bcf&color=fff&size=32&rounded=true" class="w-5 h-5 rounded-full">
                    <span class="text-[12px] font-medium text-ytText"><?= esc($comment->reviewer_name) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <?php if($comment->timecode !== null): ?>
                        <button class="jump-btn text-[10px] bg-ytBlue/20 text-ytBlue px-2 py-0.5 rounded font-bold hover:bg-ytBlue hover:text-white transition-colors" onclick="jumpTo(<?= $comment->timecode ?>)"><?= gmdate('i:s', $comment->timecode) ?></button>
                    <?php endif; ?>
                    <div class="flex gap-1 ml-1 z-20 relative text-ytMuted opacity-70 group-hover:opacity-100 transition-opacity">
                        <button class="text-[10px] font-bold uppercase tracking-wide hover:text-ytText transition-colors flex items-center gap-1 bg-[#1a122a] border border-purple-900/50 px-2 py-0.5 rounded" onclick="<?= $replyCall ?>" title="Reply to this">
                            <span class="material-symbols-outlined text-[12px]">reply</span> Reply
                        </button>
                        <?php if($comment->user_id == session()->get('userId')): ?>
                            <button class="text-ytMuted hover:text-ytText transition-colors" onclick="editComment(<?= $comment->id ?>)"><span class="material-symbols-outlined text-[14px]">edit</span></button>
                            <button class="text-ytMuted hover:text-red-500 transition-colors" onclick="deleteComment(<?= $comment->id ?>)"><span class="material-symbols-outlined text-[14px]">delete</span></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="comment-text-container" id="comment-text-<?= $comment->id ?>">
                <p class="text-[13px] text-ytMuted mt-1 whitespace-pre-wrap"><?= esc($comment->comment_text) ?></p>
            </div>
            <?php if($comment->canvas_data): ?>
                <span class="material-symbols-outlined text-[14px] text-ytMuted absolute bottom-3 right-3" title="Contains drawing">draw</span>
            <?php endif; ?>
        </div>
<?php
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Canvas Player') ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                    },
                    colors: {
                        ytBg: 'rgb(var(--color-bg) / <alpha-value>)',
                        ytCard: 'rgb(var(--color-card) / <alpha-value>)',
                        ytBorder: 'rgb(var(--color-border) / <alpha-value>)',
                        ytHover: 'rgb(var(--color-hover) / <alpha-value>)',
                        ytText: 'rgb(var(--color-text) / <alpha-value>)',
                        ytMuted: 'rgb(var(--color-muted) / <alpha-value>)',
                        ytRed: 'rgb(var(--color-error) / <alpha-value>)',
                        ytBlue: 'rgb(var(--color-accent) / <alpha-value>)'
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <!-- Root Styles -->
    <link rel="stylesheet" href="<?= base_url('assets/css/roots.css') ?>">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: var(--color-bg); color: var(--color-text); overflow: hidden; }
        .media-container { position: relative; display: inline-block; width: 100%; height: 100%; background: #000; }
        video, img { width: 100%; height: 100%; object-fit: contain; }
        .canvas-container { position: absolute !important; top: 0 !important; left: 0 !important; }
        .canvas-container, .upper-canvas { pointer-events: none !important; }
        .media-container.active .canvas-container, .media-container.active .upper-canvas { pointer-events: auto !important; }
        
        /* Custom Scrollbar for Comments */
        .comments-list::-webkit-scrollbar { width: 6px; }
        .comments-list::-webkit-scrollbar-track { background: transparent; }
        .comments-list::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
    </style>
</head>
<body class="h-screen w-screen flex flex-col bg-ytBg">

    <!-- Top Bar -->
    <header class="h-auto md:h-[60px] p-4 md:px-6 md:py-0 bg-ytCard border-b border-ytBorder/50 flex flex-col md:flex-row items-start md:items-center justify-between shrink-0 z-10 shadow-md gap-4 md:gap-0 overflow-hidden">
        <div class="flex items-center gap-4 w-full md:w-auto">
            <a href="javascript:void(0)" onclick="window.history.length > 1 ? window.history.back() : window.location.href='<?= ($userRole ?? 'artist') === 'artist' ? '/user/dashboard' : '/' . $routePrefix . '/reviews' ?>'" class="text-ytMuted hover:text-ytText transition-colors p-1.5 rounded-full hover:bg-ytHover">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="text-[15px] font-bold text-ytText leading-tight">
                    <?= esc($review->project_name) ?> / <span class="text-ytBlue">
                        <?php if(isset($review->shot_number) && $review->shot_number !== 'Multiple'): ?>
                            <?= !empty($review->seq_name) ? esc($review->seq_name) . ' / ' : '' ?><?= esc($review->shot_number) ?>
                        <?php else: ?>
                            <?= esc($review->shot_number ?? $review->seq_name ?? 'Global') ?>
                        <?php endif; ?>
                    </span>
                </h1>
                <p class="text-[12px] text-ytMuted font-medium"><?= esc($review->task_name) ?> - <span class="text-purple-400"><?= esc($review->version_string) ?></span></p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto overflow-hidden">
            <?php if(isset($versions) && count($versions) > 1): ?>
            <div class="flex items-center mr-2 bg-[#111] border border-ytBorder/50 rounded-lg px-2 py-1">
                <span class="material-symbols-outlined text-[14px] text-ytMuted mr-1">history</span>
                <select class="bg-transparent text-ytText text-[12px] font-medium focus:outline-none cursor-pointer" onchange="window.location.href='/<?= $routePrefix ?>/reviews/player/' + this.value">
                    <?php foreach($versions as $v): ?>
                        <option value="<?= $v->id ?>" <?= $v->id == $review->id ? 'selected' : '' ?>>
                            <?= esc($v->version_string) ?> 
                            (<?= date('M d', strtotime($v->created_at)) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Role Filter UI -->
            <div class="flex items-center mr-2 bg-[#111] border border-ytBorder/50 rounded-lg px-2 py-1">
                <span class="material-symbols-outlined text-[14px] text-ytMuted mr-1">filter_list</span>
                <select id="roleFilterSelect" class="bg-transparent text-ytText text-[12px] font-medium focus:outline-none cursor-pointer" onchange="window.filterFeedback()">
                    <option value="All">All Feedback</option>
                    <option value="System Admin">Admin Only</option>
                    <option value="Project Manager">PM Only</option>
                    <option value="Client">Client Only</option>
                    <option value="Internal Artist">Artist Only</option>
                </select>
            </div>

            <div class="flex items-center gap-2 mr-4">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($review->artist_name) ?>&background=8b5cf6&color=fff&size=64&rounded=true" class="w-8 h-8 rounded-full border border-ytBorder/50">
                <div class="text-[12px]">
                    <span class="text-ytMuted block leading-none mb-0.5">Artist</span>
                    <span class="text-ytText font-medium block leading-none"><?= esc($review->artist_name) ?></span>
                </div>
            </div>

            <!-- Decision Buttons: Admin/Supervisor only -->
            <?php 
                $isAdminOrPM = in_array(strtolower($userRole ?? ''), ['admin', 'project_manager', 'system admin', 'project manager', 'supervisor']);
            ?>
            <?php if($isAdminOrPM): ?>
            <form action="/<?= $routePrefix ?>/reviews/updateStatus/<?= $review->id ?>" method="POST" class="m-0 flex gap-2">
                <input type="hidden" name="task_id" value="<?= $review->vfx_task_assignment_id ?>">
                <input type="hidden" name="<?= csrf_token() ?>" id="decisionCsrf" value="<?= csrf_hash() ?>">
                
                <button type="submit" name="status" value="revision_needed" onclick="document.getElementById('decisionCsrf').value = window.getCsrfToken ? window.getCsrfToken() : '<?= csrf_hash() ?>';" class="flex items-center gap-1.5 bg-red-900/20 border border-red-700/50 text-red-400 px-4 py-1.5 rounded-full text-[13px] font-medium hover:bg-red-900/40 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">close</span> Reject / Revise
                </button>
                
                <button type="submit" name="status" value="approved" onclick="document.getElementById('decisionCsrf').value = window.getCsrfToken ? window.getCsrfToken() : '<?= csrf_hash() ?>';" class="flex items-center gap-1.5 bg-green-900/20 border border-green-700/50 text-green-400 px-4 py-1.5 rounded-full text-[13px] font-medium hover:bg-green-900/40 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">check</span> Approve
                </button>
            </form>
            <?php else: ?>
            <!-- Artist view: show start revision if rejected -->
            <?php if($review->status === 'revision_needed'): ?>
                <?php if(($review->task_status ?? '') === 'in_progress'): ?>
                    <span class="flex items-center gap-1.5 text-blue-400 text-[12px] bg-blue-900/10 border border-blue-900/30 px-3 py-1.5 rounded-full mr-2">
                        <span class="material-symbols-outlined text-[14px]">edit</span> Revision in Progress
                    </span>
                    <a href="/user/tasks/submitVersionForm/<?= $review->vfx_task_assignment_id ?>" class="flex items-center gap-1.5 bg-gradient-to-br from-[#0a5427] to-[#17cf4b] text-white shadow-[0_0_15px_rgba(23,207,75,0.3)] border border-[#17cf4b]/40 px-4 py-1.5 rounded-full text-[13px] font-semibold hover:shadow-[0_0_25px_rgba(23,207,75,0.6)] hover:from-[#0d6e34] hover:to-[#23f25c] transition-all">
                        <span class="material-symbols-outlined text-[16px]">upload</span> Upload New Version
                    </a>
                <?php else: ?>
                    <form action="/user/tasks/updateStatus/<?= $review->vfx_task_assignment_id ?>" method="POST" class="m-0 flex gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="in_progress">
                        <span class="flex items-center gap-1.5 text-red-400 text-[12px] bg-red-900/10 border border-red-900/30 px-3 py-1.5 rounded-full mr-2">
                            <span class="material-symbols-outlined text-[14px]">rate_review</span> Revision Requested
                        </span>
                        <button type="submit" class="flex items-center gap-1.5 bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-4 py-1.5 rounded-full text-[13px] font-semibold hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-all">
                            <span class="material-symbols-outlined text-[16px]">play_circle</span> Start Revision
                        </button>
                    </form>
                <?php endif; ?>
            <?php elseif($review->status === 'pending'): ?>
            <span class="flex items-center gap-1.5 text-yellow-400 text-[12px] bg-yellow-900/10 border border-yellow-900/30 px-3 py-1.5 rounded-full">
                <span class="material-symbols-outlined text-[14px] animate-pulse">hourglass_top</span> Awaiting Review
            </span>
            <?php elseif($review->status === 'approved'): ?>
            <span class="flex items-center gap-1.5 text-green-400 text-[12px] bg-green-900/10 border border-green-900/30 px-3 py-1.5 rounded-full">
                <span class="material-symbols-outlined text-[14px]">check_circle</span> Approved!
            </span>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="flex flex-col md:flex-row flex-1 overflow-y-auto md:overflow-hidden">
        
        <!-- Player Area -->
        <div class="flex-1 flex flex-col bg-black relative group">
            
            <!-- Global Hide Feedback Toggle -->
            <button id="toggleFeedbackBtn" onclick="window.toggleAllFeedback()" class="absolute top-4 right-4 bg-black/60 hover:bg-black/80 border border-white/20 text-white px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1.5 z-30 transition-all opacity-0 group-hover:opacity-100">
                <span class="material-symbols-outlined text-[18px]" id="toggleFeedbackIcon">visibility</span>
                <span class="text-[12px] font-medium" id="toggleFeedbackText">Hide Feedback</span>
            </button>

            <!-- Toolbar Overlay -->
            <div id="drawingToolbar" class="absolute top-4 left-1/2 -translate-x-1/2 w-[95%] md:w-auto bg-ytCard border border-ytBorder/80 px-2 md:px-4 py-2 rounded-xl md:rounded-full shadow-2xl flex flex-wrap justify-center items-center gap-2 md:gap-4 z-20 hidden backdrop-blur-md bg-opacity-90 transition-opacity">
                <!-- Tool Selection -->
                <div class="flex gap-2 mr-2">
                    <button id="toolSelectBtn" class="text-ytMuted hover:text-ytText p-1.5 rounded transition-colors tool-btn" data-tool="select" title="Selection Tool">
                        <span class="material-symbols-outlined text-[18px]">near_me</span>
                    </button>
                    <button id="toolBrushBtn" class="text-ytBlue bg-ytBlue/10 p-1.5 rounded transition-colors tool-btn" data-tool="brush" title="Brush Tool">
                        <span class="material-symbols-outlined text-[18px]">brush</span>
                    </button>
                    <button id="toolTextBtn" class="text-ytMuted hover:text-ytText p-1.5 rounded transition-colors tool-btn" data-tool="text" title="Text Tool">
                        <span class="material-symbols-outlined text-[18px]">match_case</span>
                    </button>
                    <button id="toolImageBtn" class="text-ytMuted hover:text-ytText p-1.5 rounded transition-colors tool-btn" data-tool="image" title="Add Image Reference">
                        <span class="material-symbols-outlined text-[18px]">image</span>
                    </button>
                    <button id="toolRectBtn" class="text-ytMuted hover:text-ytText p-1.5 rounded transition-colors tool-btn" data-tool="rectangle" title="Rectangle Tool">
                        <span class="material-symbols-outlined text-[18px]">rectangle</span>
                    </button>
                    <button id="toolCircleBtn" class="text-ytMuted hover:text-ytText p-1.5 rounded transition-colors tool-btn" data-tool="circle" title="Circle Tool">
                        <span class="material-symbols-outlined text-[18px]">circle</span>
                    </button>
                    <!-- Hidden File Input for Image Upload -->
                    <input type="file" id="imageUploadInput" accept="image/*" class="hidden">
                </div>
                <div class="w-px h-6 bg-ytBorder"></div>

                <!-- Color Picker -->
                <div class="flex gap-1">
                    <button class="color-btn w-6 h-6 rounded-full bg-[#ef4444] border-2 border-white ring-2 ring-transparent transition-all" data-color="#ef4444"></button>
                    <button class="color-btn w-6 h-6 rounded-full bg-[#3b82f6] border-2 border-transparent transition-all" data-color="#3b82f6"></button>
                    <button class="color-btn w-6 h-6 rounded-full bg-[#22c55e] border-2 border-transparent transition-all" data-color="#22c55e"></button>
                    <button class="color-btn w-6 h-6 rounded-full bg-[#eab308] border-2 border-transparent transition-all" data-color="#eab308"></button>
                </div>
                <div class="w-px h-6 bg-ytBorder"></div>
                <!-- Controls -->
                <button id="clearBtn" class="text-ytMuted hover:text-ytText text-[13px] font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[18px]">delete</span> Clear</button>
                <button id="cancelDrawBtn" class="text-ytMuted hover:text-red-400 text-[13px] font-medium flex items-center gap-1 ml-2"><span class="material-symbols-outlined text-[18px]">close</span> Cancel</button>
            </div>

            <!-- Media Container -->
            <div class="media-container flex-1 flex items-center justify-center relative">
                <?php $mediaUrl = base_url('writable/uploads/' . $review->proxy_path) . '?v=' . strtotime($review->file_updated_at ?? $review->updated_at ?? time()); ?>
                
                <div id="loadingOverlay" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center z-50">
                    <span class="material-symbols-outlined text-[48px] text-ytBlue animate-spin mb-4">progress_activity</span>
                    <span class="text-white text-[14px] font-medium tracking-wide">Decrypting Secure Stream...</span>
                    <span id="loadingProgress" class="text-ytMuted text-[12px] mt-2">0%</span>
                </div>

                <?php if($review->file_type === 'video'): ?>
                    <video id="mediaElement" class="w-full h-full object-contain hidden" disablePictureInPicture controlsList="nodownload" oncontextmenu="return false;">
                        <!-- Source is injected via secure Blob JS -->
                        Your browser does not support the video tag.
                    </video>
                <?php else: ?>
                    <img id="mediaElement" class="w-full h-full object-contain hidden" oncontextmenu="return false;">
                <?php endif; ?>
                
                <!-- The Drawing Canvas -->
                <canvas id="drawCanvas"></canvas>
            </div>
            
            <!-- DJV 1.1.0 Replica Controls -->
            <?php if($review->file_type === 'video'): ?>
            <div class="bg-[#383838] flex flex-col shrink-0 z-10 relative border-t border-[#111]">
                
                <!-- Top Row: Timeline and Sequence Track (Full width) -->
                <div class="flex-1 flex flex-col bg-[#2a2a2a] relative border-b border-[#444]" id="timelineContainer">
                    
                    <?php if(isset($isSequenceMode) && $isSequenceMode): ?>
                        <!-- Sequence Lineup Mini-Track (Taller for thumbnails) -->
                        <div class="h-[60px] bg-[#111] flex overflow-hidden border-t border-[#333]" id="sequenceLineupTrack">
                            <?php foreach($playlist as $idx => $clip): ?>
                                <div class="border-r border-[#333] relative flex items-stretch cursor-pointer transition-colors seq-clip-block group" data-idx="<?= $idx ?>" id="seq-clip-<?= $idx ?>">
                                    <!-- Hover overlay -->
                                    <div class="absolute inset-0 bg-white/0 group-hover:bg-white/5 transition-colors pointer-events-none z-10"></div>
                                    
                                    <!-- Thumbnail on the left -->
                                    <?php if(!empty($clip['thumbnail_path'])): ?>
                                        <img src="<?= esc($clip['thumbnail_path']) ?>" class="h-full w-[100px] object-cover shrink-0 border-r border-[#333]">
                                    <?php else: ?>
                                        <div class="h-full w-[100px] shrink-0 bg-[#222] border-r border-[#333] flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[#444] text-[24px]">image</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Text Info -->
                                    <div class="relative z-10 flex flex-col justify-center px-3 overflow-hidden flex-1 min-w-[80px]">
                                        <span class="text-[12px] font-bold text-white pointer-events-none leading-tight truncate"><?= esc($clip['shot_number']) ?></span>
                                        <span class="text-[10px] font-medium text-gray-300 pointer-events-none leading-tight mt-0.5 truncate"><?= esc($clip['task_name'] ?? '') ?> &bull; <span class="text-purple-400"><?= esc($clip['version_string'] ?? '') ?></span></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="relative h-[24px] cursor-pointer" id="timelineTrackWrap">
                        <!-- Tick marks (visual only) -->
                        <div class="absolute top-0 inset-x-0 h-[10px] flex justify-between px-2 opacity-30 text-[8px] font-mono text-[#ccc] overflow-hidden pointer-events-none" id="timelineTicks">
                            <span>0</span><span>24</span><span>48</span><span>72</span><span>96</span><span>120</span><span>144</span>
                        </div>
                        <div class="absolute bottom-0 inset-x-0 h-[16px] bg-[#1a1a1a] border-t border-[#333]" id="timelineTrack">
                            <!-- Comment Markers -->
                            <div id="timelineMarkers" class="absolute inset-0 pointer-events-none"></div>
                            <!-- Current progress / Thumb -->
                            <div id="timelineProgress" class="absolute top-0 bottom-0 left-0 bg-[#5b5b5b] border-r-2 border-[#ccc] w-0 pointer-events-none transition-all duration-75"></div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: Playback Controls & Data -->
                <div class="flex flex-col md:flex-row md:items-stretch h-auto md:h-[36px] bg-[#2d2d2d] text-[#ccc]">
                    <!-- Left: Playback Cluster -->
                    <div class="flex justify-center h-[36px] md:h-full border-b md:border-b-0 md:border-r border-[#222]">
                        <button id="stopBtn" class="w-[36px] h-full flex justify-center items-center hover:bg-[#4a4a4a] border-r border-[#2d2d2d] transition-colors" title="Stop">
                            <div class="w-3 h-3 bg-[#ccc] rounded-sm"></div>
                        </button>
                        <button id="playPauseBtn" class="w-[36px] h-full flex justify-center items-center hover:bg-[#4a4a4a] border-r border-[#2d2d2d] transition-colors" title="Play">
                            <span class="material-symbols-outlined text-[20px] text-[#ccc]" id="playPauseIcon">play_arrow</span>
                        </button>
                        <button class="w-[36px] h-full flex justify-center items-center hover:bg-[#4a4a4a] border-r border-[#2d2d2d] transition-colors" title="Loop">
                            <span class="material-symbols-outlined text-[18px] text-[#ccc]">loop</span>
                        </button>
                        <button id="goToStartBtn" class="w-[36px] h-full flex justify-center items-center hover:bg-[#4a4a4a] border-r border-[#2d2d2d] transition-colors" title="Go to Start">
                            <span class="material-symbols-outlined text-[20px] text-[#ccc]">skip_previous</span>
                        </button>
                        <button id="stepBackBtn" class="w-[36px] h-full flex justify-center items-center hover:bg-[#4a4a4a] border-r border-[#2d2d2d] transition-colors" title="Previous Frame">
                            <span class="material-symbols-outlined text-[20px] text-[#ccc]">navigate_before</span>
                        </button>
                        <button id="stepForwardBtn" class="w-[36px] h-full flex justify-center items-center hover:bg-[#4a4a4a] border-r border-[#2d2d2d] transition-colors" title="Next Frame">
                            <span class="material-symbols-outlined text-[20px] text-[#ccc]">navigate_next</span>
                        </button>
                        <button id="goToEndBtn" class="w-[36px] h-full flex justify-center items-center hover:bg-[#4a4a4a] border-r border-[#2d2d2d] transition-colors" title="Go to End">
                            <span class="material-symbols-outlined text-[20px] text-[#ccc]">skip_next</span>
                        </button>
                    </div>

                    <div class="flex-1 flex justify-between items-center px-4 py-2 md:py-0 font-mono text-[11px] overflow-x-auto">
                        <div class="flex items-center gap-4">
                            <!-- FPS Selector -->
                            <div class="flex items-center gap-1 bg-[#1a1a1a] border border-[#111] rounded-[2px] px-1.5 py-0.5 cursor-pointer hover:bg-[#333]">
                                <span>24.00</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_drop_down</span>
                            </div>
                            
                            <!-- Current Frame Display -->
                            <div class="flex items-center gap-2">
                                <span class="opacity-50">Frame</span>
                                <div class="bg-[#1a1a1a] border border-[#111] rounded-[2px] px-2 py-0.5 min-w-[50px] text-center" id="frameDisplay">
                                    0
                                </div>
                            </div>
                            
                            <!-- Timecode -->
                            <div class="flex items-center gap-2 ml-2 border-l border-[#444] pl-4">
                                <span class="opacity-50">Timecode</span>
                                <span id="timecodeDisplay">00:00:00:00</span>
                            </div>
                        </div>

                        <!-- Fake Image Info -->
                        <div class="opacity-40 text-[10px] hidden md:block">
                            Image: 1920x1080:1.00 RGBA U8 Cache: 0%
                        </div>
                    </div>
                    
                    <div class="hidden md:flex w-[40px] h-full justify-center items-center cursor-pointer border-l border-[#222] hover:bg-[#4a4a4a] transition-colors" id="fullscreenBtn">
                        <span class="material-symbols-outlined text-[20px] text-[#ccc]">crop_free</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar (Comments) -->
        <div class="w-full md:w-[350px] min-h-[400px] md:min-h-0 bg-ytCard md:border-l border-t md:border-t-0 border-ytBorder/50 flex flex-col shrink-0 shadow-[-10px_0_30px_rgba(0,0,0,0.5)] z-10">
            
            <div class="px-5 py-4 border-b border-ytBorder/50">
                <h3 class="text-[14px] font-semibold text-ytText flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-ytMuted">forum</span> Annotations & Notes
                </h3>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4 comments-list" id="commentsList">
                
                <!-- Artist's Initial Note -->
                <?php if(!empty($review->artist_notes)): ?>
                <div class="bg-[#1a122a]/50 border border-purple-900/30 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-[14px] text-purple-400">info</span>
                        <span class="text-[11px] text-purple-400 font-bold uppercase tracking-wider">Artist Note</span>
                    </div>
                    <p class="text-[13px] text-ytText leading-relaxed">"<?= esc($review->artist_notes) ?>"</p>
                </div>
                <?php endif; ?>

                <?php
                $topLevelComments = array_filter($comments, fn($c) => $c->parent_id === null);
                $replyMap = [];
                foreach($comments as $c) {
                    if ($c->parent_id !== null) {
                        $replyMap[$c->parent_id][] = $c;
                    }
                }
                ?>

                <!-- Existing Comments -->
                <?php if(empty($comments)): ?>
                    <div class="text-xs text-gray-500 p-4">No comments found for reviewIds: <?= implode(', ', (isset($playlist) && $playlist) ? array_column($playlist, 'review_id') : []) ?></div>
                <?php endif; ?>
                <?php foreach($topLevelComments as $comment): ?>
                    <?php renderCommentBox($comment); ?>
                    
                    <?php if(!empty($replyMap[$comment->id])): ?>
                        <div class="ml-6 border-l-2 border-purple-900/30 pl-3">
                            <?php foreach($replyMap[$comment->id] as $reply): ?>
                                <?php renderCommentBox($reply, true); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
            </div>

            <!-- New Annotation Form -->
            <div class="p-4 border-t border-ytBorder/50 bg-[#111111]">
                <button type="button" id="startDrawBtn" class="w-full bg-ytHover border border-ytBorder text-ytText rounded-lg py-2 text-[13px] font-medium mb-3 hover:bg-[#2a2a2a] transition-colors flex justify-center items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">draw</span> Draw on Frame
                </button>
                
                <form id="annotationForm" class="flex flex-col gap-2 relative">
                    <input type="hidden" id="timecode" name="timecode" value="">
                    <input type="hidden" id="canvas_data" name="canvas_data" value="">
                    <input type="hidden" id="parent_id" name="parent_id" value="">
                    
                    <div id="drawingIndicator" class="hidden text-center mb-1">
                        <span class="bg-red-500/20 text-red-400 text-[10px] uppercase font-bold px-2 py-1 rounded border border-red-500/30 animate-pulse">Drawing Mode Active</span>
                    </div>

                    <div id="replyBanner" class="hidden text-[11px] text-ytMuted bg-[#1a122a] border border-purple-900/30 rounded p-2 mb-1 flex justify-between items-center">
                        <div>
                            <span class="font-semibold text-purple-400">Replying to <span id="replyName"></span>:</span>
                            <span id="replySnippet" class="ml-1 opacity-70"></span>
                        </div>
                        <button type="button" onclick="cancelReply()" class="text-ytMuted hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-[14px]">close</span>
                        </button>
                    </div>

                    <textarea id="comment_text" name="comment_text" rows="2" class="w-full bg-ytBg border border-ytBorder rounded-lg p-3 text-[13px] text-ytText placeholder-ytMuted focus:outline-none focus:border-ytBlue resize-none" placeholder="Add a comment..."></textarea>
                    <button type="button" id="saveAnnotationBtn" class="bg-ytBlue text-white font-medium text-[13px] py-2 rounded-lg hover:bg-[#1366af] transition-colors flex justify-center items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">send</span> Save Annotation
                    </button>
                </form>
            </div>

        </div>
    </div>

    <!-- DJV Player JS Logic -->
    <script>
        const isSequenceMode = <?= isset($isSequenceMode) && $isSequenceMode ? 'true' : 'false' ?>;
        const playlist = <?= isset($playlist) ? json_encode($playlist) : '[]' ?>;
        let currentPlaylistIndex = 0;
        let activeReviewId = <?= isset($review->id) ? $review->id : 'null' ?>;

        const media = document.getElementById('mediaElement');
        const canvasEl = document.getElementById('drawCanvas');
        const startDrawBtn = document.getElementById('startDrawBtn');
        const cancelDrawBtn = document.getElementById('cancelDrawBtn');
        const clearBtn = document.getElementById('clearBtn');
        const toolbar = document.getElementById('drawingToolbar');
        const drawingIndicator = document.getElementById('drawingIndicator');
        const annotationForm = document.getElementById('annotationForm');
        
        let isDrawingMode = false;
        let currentTool = 'select'; // 'select', 'brush', 'text', 'rectangle', 'circle'
        let currentColor = '#ef4444'; // default red
        
        // --- Feedback Filtering State ---
        window.showAllFeedback = true;
        window.activeRoleFilter = 'All'; // 'All', 'System Admin', 'Project Manager', 'Client', 'Internal Artist'

        window.toggleAllFeedback = function() {
            window.showAllFeedback = !window.showAllFeedback;
            const btn = document.getElementById('toggleFeedbackBtn');
            const icon = document.getElementById('toggleFeedbackIcon');
            const text = document.getElementById('toggleFeedbackText');
            
            if (window.showAllFeedback) {
                btn.classList.remove('bg-purple-600', 'hover:bg-purple-500');
                btn.classList.add('bg-black/60', 'hover:bg-black/80');
                icon.textContent = 'visibility';
                text.textContent = 'Hide Feedback';
                icon.classList.remove('text-purple-200');
            } else {
                btn.classList.remove('bg-black/60', 'hover:bg-black/80');
                btn.classList.add('bg-purple-600', 'hover:bg-purple-500');
                icon.textContent = 'visibility_off';
                text.textContent = 'Feedback Hidden';
                icon.classList.add('text-purple-200');
                
                // Clear canvas immediately if hiding
                if(canvasEl) canvas.clear();
            }
            window.filterFeedback(); // Apply sidebar visibility changes
        };

        window.filterFeedback = function() {
            const selectEl = document.getElementById('roleFilterSelect');
            if (selectEl) {
                window.activeRoleFilter = selectEl.value;
            }
            
            // Rebuild annotatedFrames index based on new filters
            annotatedFrames = null; 
            
            // Update UI in sidebar
            document.querySelectorAll('.group[data-timecode]').forEach(el => {
                const role = el.dataset.role;
                const matchesRole = window.activeRoleFilter === 'All' || role === window.activeRoleFilter;
                
                if (window.showAllFeedback && matchesRole) {
                    el.style.opacity = '1';
                    // Re-enable jumping
                    const jumpBtn = el.querySelector('.jump-btn');
                    if (jumpBtn) {
                        jumpBtn.style.pointerEvents = 'auto';
                        jumpBtn.style.opacity = '1';
                    }
                } else {
                    el.style.opacity = '0.4';
                    // Disable jumping when hidden
                    const jumpBtn = el.querySelector('.jump-btn');
                    if (jumpBtn) {
                        jumpBtn.style.pointerEvents = 'none';
                        jumpBtn.style.opacity = '0.5';
                    }
                }
            });
            
            // Force re-check of current frame to clear/redraw canvas if paused
            lastCheckedFrame = -1;
            if (media.tagName === 'VIDEO' && media.paused && !isDrawingMode) {
                // Find nearest annotation that is visible and draw it
                const currentFrame = Math.round(media.currentTime * 24); // Assuming 24fps
                let foundDrawing = false;
                
                if (window.showAllFeedback) {
                    document.querySelectorAll('.group[data-timecode]').forEach(el => {
                        const tc = parseFloat(el.dataset.timecode);
                        if (!isNaN(tc)) {
                            const f = Math.round(tc * 24);
                            if (f === currentFrame) {
                                const role = el.dataset.role;
                                if (window.activeRoleFilter === 'All' || role === window.activeRoleFilter) {
                                    const savedData = el.dataset.canvas;
                                    if(savedData) {
                                        canvas.loadFromJSON(savedData, canvas.renderAll.bind(canvas));
                                        foundDrawing = true;
                                    }
                                }
                            }
                        }
                    });
                }
                
                if (!foundDrawing) {
                    canvas.clear();
                }
            }
            
            if (typeof renderMarkers === 'function') {
                renderMarkers();
            }
        };

        // Initialize Fabric.js Canvas
        const canvas = new fabric.Canvas('drawCanvas', {
            isDrawingMode: false,
            selection: true
        });

        // Tool Selection
        document.querySelectorAll('.tool-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.currentTarget;
                const toolType = target.dataset.tool;
                
                // Handle one-off Image Tool
                if (toolType === 'image') {
                    if(!isDrawingMode) startDrawingMode();
                    document.getElementById('imageUploadInput').click();
                    return;
                }
                
                // Normal Tools
                document.querySelectorAll('.tool-btn').forEach(b => {
                    b.classList.remove('text-ytBlue', 'bg-ytBlue/10');
                    b.classList.add('text-ytMuted');
                });
                target.classList.remove('text-ytMuted');
                target.classList.add('text-ytBlue', 'bg-ytBlue/10');
                currentTool = toolType;
                
                // Fabric logic
                canvas.isDrawingMode = (currentTool === 'brush');
                if (currentTool === 'brush') {
                    canvas.freeDrawingBrush.color = currentColor;
                    canvas.freeDrawingBrush.width = 4;
                    canvas.defaultCursor = 'crosshair';
                } else if (currentTool === 'text') {
                    canvas.defaultCursor = 'text';
                } else if (currentTool === 'select') {
                    canvas.defaultCursor = 'default';
                } else {
                    canvas.defaultCursor = 'crosshair';
                }
            });
        });

        let currentCsrfHash = '<?= csrf_hash() ?>';

        function getCsrfToken() {
            return currentCsrfHash;
        }

        // Handle Image Upload
        document.getElementById('imageUploadInput').addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('reference_image', file);
            formData.append('<?= csrf_token() ?>', getCsrfToken());
            
            try {
                const response = await fetch('<?= base_url($routePrefix . '/reviews/uploadReference') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                
                if (data.csrf) {
                    currentCsrfHash = data.csrf;
                }

                if (data.status === 'success') {
                    const imgUrl = '/media/serve/' + data.url;
                    fabric.Image.fromURL(imgUrl, function(img) {
                        let w = img.width;
                        let h = img.height;
                        if (w > canvas.width * 0.5) {
                            img.scaleToWidth(canvas.width * 0.5);
                        }
                        img.set({
                            left: canvas.width / 2,
                            top: canvas.height / 2,
                            originX: 'center',
                            originY: 'center',
                            borderColor: currentColor,
                            cornerColor: currentColor,
                            transparentCorners: false
                        });
                        canvas.add(img);
                        canvas.setActiveObject(img);
                    });
                } else {
                    alert('Upload failed: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Upload error');
            }
            e.target.value = ''; // reset
        });
        
        // Match canvas size to media size
        function resizeCanvas() {
            canvas.setWidth(media.clientWidth);
            canvas.setHeight(media.clientHeight);
            canvas.renderAll();
        }
        window.addEventListener('resize', resizeCanvas);
        // Wait for media metadata to load to get dimensions
        if(media.tagName === 'VIDEO') {
            media.addEventListener('loadedmetadata', resizeCanvas);
        } else {
            media.addEventListener('load', resizeCanvas);
        }
        // Force initial resize
        setTimeout(resizeCanvas, 100);

        // --- SECURE BLOB FETCH LOGIC ---
        async function loadSecureMedia() {
            const mediaUrl = '<?= base_url('media/serve/' . esc($review->proxy_path)) ?>';
            const loadingOverlay = document.getElementById('loadingOverlay');
            const loadingProgress = document.getElementById('loadingProgress');
            const mediaEl = document.getElementById('mediaElement');

            try {
                const response = await fetch(mediaUrl);
                if (!response.ok) throw new Error('Network response was not ok');

                const reader = response.body.getReader();
                const contentLength = +response.headers.get('Content-Length');
                let receivedLength = 0;
                let chunks = [];

                while(true) {
                    const {done, value} = await reader.read();
                    if (done) break;
                    chunks.push(value);
                    receivedLength += value.length;
                    
                    if (contentLength) {
                        const percent = Math.round((receivedLength / contentLength) * 100);
                        loadingProgress.textContent = percent + '%';
                    }
                }

                const blob = new Blob(chunks, { type: '<?= $review->file_type === 'video' ? 'video/mp4' : 'image/jpeg' ?>' });
                const blobUrl = URL.createObjectURL(blob);
                
                mediaEl.src = blobUrl;
                mediaEl.classList.remove('hidden');
                loadingOverlay.classList.add('hidden');
                
                // For images, we need to trigger resize after load
                if (mediaEl.tagName !== 'VIDEO') {
                    mediaEl.onload = resizeCanvas;
                }
                
            } catch (error) {
                console.error('Error securely loading media:', error);
                loadingOverlay.innerHTML = '<span class="text-red-500 font-bold">Error loading secure stream. Please refresh.</span>';
            }
        }
        
        // Start secure load immediately
        loadSecureMedia();

        // --- DJV PLAYBACK LOGIC ---
        if(media.tagName === 'VIDEO') {
            const FPS = 24;
            const FRAME_TIME = 1 / FPS;
            
            const playPauseBtn = document.getElementById('playPauseBtn');
            const playPauseIcon = document.getElementById('playPauseIcon');
            const stopBtn = document.getElementById('stopBtn');
            const goToStartBtn = document.getElementById('goToStartBtn');
            const goToEndBtn = document.getElementById('goToEndBtn');
            const stepBackBtn = document.getElementById('stepBackBtn');
            const stepForwardBtn = document.getElementById('stepForwardBtn');
            const timelineContainer = document.getElementById('timelineContainer');
            const timelineProgress = document.getElementById('timelineProgress');
            const timecodeDisplay = document.getElementById('timecodeDisplay');
            const frameDisplay = document.getElementById('frameDisplay');
            const fullscreenBtn = document.getElementById('fullscreenBtn');

            let autoResumeTimeout = null;
            function clearAutoResume() {
                if (autoResumeTimeout) {
                    clearTimeout(autoResumeTimeout);
                    autoResumeTimeout = null;
                }
            }

            function togglePlay() {
                clearAutoResume(); // User interacted
                if (media.paused) {
                    media.play();
                    playPauseIcon.textContent = 'pause';
                } else {
                    media.pause();
                    playPauseIcon.textContent = 'play_arrow';
                }
            }

            function stopPlayback() {
                clearAutoResume();
                media.pause();
                media.currentTime = 0;
                playPauseIcon.textContent = 'play_arrow';
            }

            function stepFrame(forward = true) {
                clearAutoResume();
                if (!media.paused) togglePlay();
                media.currentTime = Math.max(0, Math.min(media.duration, media.currentTime + (forward ? FRAME_TIME : -FRAME_TIME)));
            }

            // Controls Events
            playPauseBtn.addEventListener('click', togglePlay);
            stopBtn.addEventListener('click', stopPlayback);
            goToStartBtn.addEventListener('click', () => { 
                if (isSequenceMode && sequenceLoaded && currentPlaylistIndex !== 0) {
                    playSequenceClip(0);
                }
                media.currentTime = 0; 
                if(!media.paused) togglePlay(); 
            });
            goToEndBtn.addEventListener('click', () => { 
                if (isSequenceMode && sequenceLoaded && currentPlaylistIndex !== playlist.length - 1) {
                    playSequenceClip(playlist.length - 1);
                    media.addEventListener('canplay', function seekEnd() {
                        media.currentTime = media.duration;
                        media.removeEventListener('canplay', seekEnd);
                    });
                } else {
                    media.currentTime = media.duration; 
                }
                if(!media.paused) togglePlay(); 
            });
            stepForwardBtn.addEventListener('click', () => stepFrame(true));
            stepBackBtn.addEventListener('click', () => stepFrame(false));
            
            // Format Timecode HH:MM:SS:FF
            function formatTimecode(seconds) {
                const h = Math.floor(seconds / 3600);
                const m = Math.floor((seconds % 3600) / 60);
                const s = Math.floor(seconds % 60);
                const f = Math.floor((seconds % 1) * FPS);
                return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}:${f.toString().padStart(2, '0')}`;
            }

            let lastCheckedFrame = -1;
            let annotatedFrames = null;

            function highlightSidebarComments(timecodeStrs) {
                // Remove highlight from all comments
                document.querySelectorAll('.group[data-timecode]').forEach(el => {
                    el.classList.remove('border-purple-500', 'bg-[#1a122a]');
                    el.classList.add('border-ytBorder', 'bg-ytBg');
                });
                // Add highlight to all active comments
                let firstEl = null;
                timecodeStrs.forEach(tcStr => {
                    document.querySelectorAll(`.group[data-timecode="${tcStr}"]`).forEach(el => {
                        el.classList.remove('border-ytBorder', 'bg-ytBg');
                        el.classList.add('border-purple-500', 'bg-[#1a122a]');
                        if(!firstEl) firstEl = el;
                    });
                });
                
                if (firstEl) {
                    firstEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            media.addEventListener('timeupdate', () => {
                let globalTime = media.currentTime;
                
                if (isSequenceMode && sequenceLoaded && totalSequenceDuration > 0) {
                    globalTime = 0;
                    for (let i = 0; i < currentPlaylistIndex; i++) {
                        globalTime += (playlist[i].duration || 0);
                    }
                    globalTime += media.currentTime;
                    
                    const percent = (globalTime / totalSequenceDuration) * 100;
                    timelineProgress.style.width = `${percent}%`;
                } else {
                    const percent = (media.currentTime / media.duration) * 100;
                    timelineProgress.style.width = `${percent}%`;
                }
                
                timecodeDisplay.textContent = formatTimecode(globalTime);
                const currentFrame = Math.round(globalTime * FPS);
                frameDisplay.textContent = currentFrame;

                // Build index of annotations on first run or when filter changes
                if (annotatedFrames === null) {
                    annotatedFrames = {};
                    document.querySelectorAll('.group[data-timecode]').forEach(el => {
                        const tc = parseFloat(el.dataset.timecode);
                        const role = el.dataset.role;
                        const matchesRole = window.activeRoleFilter === 'All' || role === window.activeRoleFilter;
                        
                        if (!isNaN(tc) && matchesRole) {
                            const frame = Math.round(tc * FPS);
                            if (!annotatedFrames[frame]) {
                                annotatedFrames[frame] = [];
                            }
                            annotatedFrames[frame].push({
                                tc: tc,
                                tcStr: el.dataset.timecode, // for selector
                                data: el.dataset.canvas
                            });
                        }
                    });
                }

                if (window.showAllFeedback && !media.paused && !isDrawingMode && lastCheckedFrame !== -1) {
                    // Check if we passed any annotated frame since the last check
                    if (currentFrame > lastCheckedFrame) {
                        for (let f = lastCheckedFrame + 1; f <= currentFrame; f++) {
                            if (annotatedFrames[f]) {
                                // Smooth pause (no jump to exact timecode)
                                media.pause(); // DO NOT call togglePlay() here as it would clear auto-resume
                                playPauseIcon.textContent = 'play_arrow';
                                
                                let combinedObjects = [];
                                let tcStrs = [];
                                
                                annotatedFrames[f].forEach(anno => {
                                    tcStrs.push(anno.tcStr);
                                    if (anno.data) {
                                        try {
                                            const parsed = JSON.parse(anno.data);
                                            if (parsed && parsed.objects) {
                                                combinedObjects = combinedObjects.concat(parsed.objects);
                                            }
                                        } catch (e) {
                                            console.error('Error parsing canvas data');
                                        }
                                    }
                                });
                                
                                if (combinedObjects.length > 0) {
                                    const fakeJson = JSON.stringify({ version: "5.3.0", objects: combinedObjects });
                                    canvas.loadFromJSON(fakeJson, canvas.renderAll.bind(canvas));
                                } else {
                                    canvas.clear();
                                }

                                highlightSidebarComments(tcStrs);

                                // Auto-resume after 2 seconds
                                clearAutoResume();
                                autoResumeTimeout = setTimeout(() => {
                                    if (media.paused && !isDrawingMode) {
                                        media.play();
                                        playPauseIcon.textContent = 'pause';
                                    }
                                }, 2000);
                                
                                lastCheckedFrame = f; // prevent re-triggering
                                return;
                            }
                        }
                    }
                }
                
                // Keep track of the last frame we processed
                lastCheckedFrame = currentFrame;
            });

            function renderMarkers() {
                const markersContainer = document.getElementById('timelineMarkers');
                markersContainer.innerHTML = ''; // clear
                if (!media.duration || !window.showAllFeedback) return;

                document.querySelectorAll('.group[data-timecode]').forEach(el => {
                    const role = el.dataset.role;
                    const matchesRole = window.activeRoleFilter === 'All' || role === window.activeRoleFilter;
                    
                    if (matchesRole) {
                        const tc = parseFloat(el.dataset.timecode);
                        if (!isNaN(tc)) {
                            const percent = (tc / media.duration) * 100;
                            const marker = document.createElement('div');
                            marker.className = 'absolute top-0 bottom-0 w-[2px] bg-green-500 z-10 shadow-[0_0_4px_rgba(34,197,94,0.8)]';
                            marker.style.left = `${percent}%`;
                            markersContainer.appendChild(marker);
                        }
                    }
                });
            }

            media.addEventListener('loadedmetadata', () => {
                if (!isSequenceMode) {
                    timecodeDisplay.textContent = formatTimecode(0);
                    frameDisplay.textContent = '0';
                    renderMarkers();
                }
            });
            if (media.readyState >= 1 && !isSequenceMode) {
                renderMarkers();
            }

            // Global Timeline State
            let totalSequenceDuration = 0;
            let sequenceLoaded = false;
            
            if (isSequenceMode) {
                let loadedCount = 0;
                playlist.forEach((clip, i) => {
                    if (clip.proxy_url) {
                        const v = document.createElement('video');
                        v.src = clip.proxy_url;
                        v.addEventListener('loadedmetadata', () => {
                            clip.duration = v.duration;
                            checkAllLoaded();
                        });
                        v.addEventListener('error', () => {
                            clip.duration = 0;
                            checkAllLoaded();
                        });
                    } else {
                        clip.duration = 0;
                        checkAllLoaded();
                    }
                });
                
                function checkAllLoaded() {
                    loadedCount++;
                    if (loadedCount === playlist.length) {
                        totalSequenceDuration = playlist.reduce((sum, c) => sum + (c.duration || 0), 0);
                        sequenceLoaded = true;
                        
                        // Set proportional widths for sequence track blocks
                        if (totalSequenceDuration > 0) {
                            playlist.forEach((clip, i) => {
                                const block = document.getElementById('seq-clip-' + i);
                                if (block) {
                                    const pct = ((clip.duration || 0) / totalSequenceDuration) * 100;
                                    block.classList.remove('flex-1');
                                    block.style.width = pct + '%';
                                }
                            });
                        }
                        
                        renderMarkers(); // render markers now that global duration is known
                    }
                }
            }

            // Sequence Mode Playback Logic
            function playSequenceClip(index) {
                if (!isSequenceMode || !playlist || index < 0 || index >= playlist.length) return;
                
                // Skip empty clips
                if (!playlist[index].proxy_path) {
                    if (index + 1 < playlist.length) {
                        playSequenceClip(index + 1);
                    } else {
                        // End of sequence
                        togglePlay(); // pause
                        currentPlaylistIndex = 0;
                        playSequenceClip(0);
                        media.pause();
                    }
                    return;
                }
                
                currentPlaylistIndex = index;
                activeReviewId = playlist[index].review_id;
                
                // Update UI active state
                document.querySelectorAll('.seq-clip-block').forEach(el => el.classList.remove('bg-ytBlue/20', 'border-ytBlue'));
                const activeBlock = document.getElementById('seq-clip-' + index);
                if(activeBlock) {
                    activeBlock.classList.add('bg-ytBlue/20', 'border-ytBlue');
                }
                
                // Load new source
                media.src = playlist[index].proxy_url;
                media.load();
                media.play().catch(e => console.log('Autoplay prevented:', e));
                playPauseIcon.textContent = 'pause';
                
                // Clear annotations and reload
                canvas.clear();
                annotatedFrames = null; // force rebuild of annotation index for new clip
                renderMarkers();
            }

            media.addEventListener('ended', () => {
                if (isSequenceMode && currentPlaylistIndex + 1 < playlist.length) {
                    playSequenceClip(currentPlaylistIndex + 1);
                } else {
                    stopPlayback();
                }
            });

            // Sequence Lineup Track Clicks
            if (isSequenceMode) {
                document.querySelectorAll('.seq-clip-block').forEach(el => {
                    el.addEventListener('click', (e) => {
                        const idx = parseInt(e.currentTarget.dataset.idx);
                        playSequenceClip(idx);
                    });
                });
                // Initialize first active block
                const firstBlock = document.getElementById('seq-clip-0');
                if(firstBlock) firstBlock.classList.add('bg-ytBlue/20', 'border-ytBlue');
            }

            // Scrubber Logic
            let isScrubbing = false;
            function updateScrubber(e) {
                const rect = timelineContainer.getBoundingClientRect();
                const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                
                if (isSequenceMode && sequenceLoaded && totalSequenceDuration > 0) {
                    let targetGlobalTime = pos * totalSequenceDuration;
                    let accum = 0;
                    let targetIndex = 0;
                    
                    for (let i = 0; i < playlist.length; i++) {
                        const d = playlist[i].duration || 0;
                        if (targetGlobalTime <= accum + d) {
                            targetIndex = i;
                            break;
                        }
                        accum += d;
                        targetIndex = i; // fallback to last
                    }
                    
                    const localTime = targetGlobalTime - accum;
                    
                    if (targetIndex !== currentPlaylistIndex) {
                        playSequenceClip(targetIndex);
                        media.addEventListener('canplay', function seekOnLoad() {
                            media.currentTime = localTime;
                            media.removeEventListener('canplay', seekOnLoad);
                        });
                    } else {
                        media.currentTime = localTime;
                    }
                    
                } else {
                    media.currentTime = pos * media.duration;
                }
            }
            
            timelineContainer.addEventListener('mousedown', (e) => {
                isScrubbing = true;
                if(!media.paused) togglePlay();
                updateScrubber(e);
            });
            window.addEventListener('mousemove', (e) => {
                if (isScrubbing) updateScrubber(e);
            });
            window.addEventListener('mouseup', () => {
                isScrubbing = false;
            });

            // Keyboard Hotkeys
            window.addEventListener('keydown', (e) => {
                // Ignore if typing in an input or textarea
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                
                if (e.code === 'Space') {
                    e.preventDefault();
                    togglePlay();
                } else if (e.code === 'ArrowRight') {
                    e.preventDefault();
                    stepFrame(true);
                } else if (e.code === 'ArrowLeft') {
                    e.preventDefault();
                    stepFrame(false);
                }
            });

            // Fullscreen
            fullscreenBtn.addEventListener('click', () => {
                const container = document.querySelector('.canvas-container');
                if (!document.fullscreenElement) {
                    container.requestFullscreen().catch(err => {
                        console.error(`Error attempting to enable fullscreen: ${err.message}`);
                    });
                } else {
                    document.exitFullscreen();
                }
            });
        }

        // --- DRAWING LOGIC ---

        function startDrawingMode() {
            isDrawingMode = true;
            document.querySelector('.media-container').classList.add('active');
            toolbar.classList.remove('hidden');
            drawingIndicator.classList.remove('hidden');
            
            if(media.tagName === 'VIDEO') {
                media.pause();
                document.getElementById('timecode').value = media.currentTime;
            } else {
                document.getElementById('timecode').value = 0;
            }
            
            canvas.clear();
            document.getElementById('comment_text').focus();
            
            // Enable interaction default
            document.getElementById('toolSelectBtn').click();
        }

        function stopDrawingMode() {
            isDrawingMode = false;
            document.querySelector('.media-container').classList.remove('active');
            toolbar.classList.add('hidden');
            drawingIndicator.classList.add('hidden');
            canvas.clear();
        }

        startDrawBtn.addEventListener('click', startDrawingMode);
        cancelDrawBtn.addEventListener('click', stopDrawingMode);
        
        clearBtn.addEventListener('click', () => {
            canvas.clear();
        });

        // Color selection
        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.color-btn').forEach(b => {
                    b.classList.remove('border-white', 'ring-2');
                    b.classList.add('border-transparent');
                });
                const target = e.target;
                target.classList.remove('border-transparent');
                target.classList.add('border-white', 'ring-2');
                currentColor = target.dataset.color;
                
                canvas.freeDrawingBrush.color = currentColor;
                const activeObject = canvas.getActiveObject();
                if (activeObject) {
                    if (activeObject.type === 'path' || activeObject.type === 'i-text') {
                        activeObject.set('fill', currentColor);
                        if(activeObject.type === 'path') activeObject.set('stroke', currentColor);
                    } else if (activeObject.type === 'rect' || activeObject.type === 'circle') {
                        activeObject.set('stroke', currentColor);
                    }
                    canvas.renderAll();
                }
            });
        });

        // Delete active object with keyboard
        window.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (e.key === 'Backspace' || e.key === 'Delete') {
                const activeObjects = canvas.getActiveObjects();
                if (activeObjects.length) {
                    canvas.discardActiveObject();
                    activeObjects.forEach(function(object) {
                        canvas.remove(object);
                    });
                }
            }
        });

        // Fabric.js Shapes (drag to create)
        let isDrawingShape = false;
        let shapeObj = null;
        let startX = 0, startY = 0;

        canvas.on('mouse:down', function(o) {
            if (!isDrawingMode || (currentTool !== 'rectangle' && currentTool !== 'circle' && currentTool !== 'text')) return;
            
            const pointer = canvas.getPointer(o.e);
            startX = pointer.x;
            startY = pointer.y;

            if (currentTool === 'text') {
                const textObj = new fabric.IText('', {
                    left: startX,
                    top: startY,
                    fill: currentColor,
                    fontFamily: 'Inter, sans-serif',
                    fontSize: 24,
                    fontWeight: 600,
                    borderColor: currentColor,
                    cornerColor: currentColor,
                    transparentCorners: false
                });
                canvas.add(textObj);
                canvas.setActiveObject(textObj);
                textObj.enterEditing();
                
                // Revert to select tool
                document.getElementById('toolSelectBtn').click();
                return;
            }

            isDrawingShape = true;
            if (currentTool === 'rectangle') {
                shapeObj = new fabric.Rect({
                    left: startX,
                    top: startY,
                    originX: 'left',
                    originY: 'top',
                    width: 0,
                    height: 0,
                    fill: 'transparent',
                    stroke: currentColor,
                    strokeWidth: 3,
                    borderColor: currentColor,
                    cornerColor: currentColor,
                    transparentCorners: false
                });
                canvas.add(shapeObj);
            } else if (currentTool === 'circle') {
                shapeObj = new fabric.Circle({
                    left: startX,
                    top: startY,
                    originX: 'center',
                    originY: 'center',
                    radius: 0,
                    fill: 'transparent',
                    stroke: currentColor,
                    strokeWidth: 3,
                    borderColor: currentColor,
                    cornerColor: currentColor,
                    transparentCorners: false
                });
                canvas.add(shapeObj);
            }
        });

        canvas.on('mouse:move', function(o) {
            if (!isDrawingShape || !shapeObj) return;
            const pointer = canvas.getPointer(o.e);
            
            if (currentTool === 'rectangle') {
                if(startX > pointer.x) {
                    shapeObj.set({ left: Math.abs(pointer.x) });
                }
                if(startY > pointer.y) {
                    shapeObj.set({ top: Math.abs(pointer.y) });
                }
                shapeObj.set({ width: Math.abs(startX - pointer.x) });
                shapeObj.set({ height: Math.abs(startY - pointer.y) });
            } else if (currentTool === 'circle') {
                const radius = Math.max(Math.abs(startX - pointer.x), Math.abs(startY - pointer.y)) / 2;
                shapeObj.set({ radius: radius });
            }
            canvas.renderAll();
        });

        canvas.on('mouse:up', function(o) {
            if(isDrawingShape) {
                isDrawingShape = false;
                if (shapeObj) {
                    // if too small, just remove it
                    if (shapeObj.width < 5 && shapeObj.height < 5 && currentTool === 'rectangle') {
                        canvas.remove(shapeObj);
                    }
                    if (shapeObj.radius < 5 && currentTool === 'circle') {
                        canvas.remove(shapeObj);
                    }
                    canvas.setActiveObject(shapeObj);
                }
                shapeObj = null;
                // revert to select tool after drawing shape
                document.getElementById('toolSelectBtn').click();
            }
        });

        // Clear canvas when video plays
        if(media.tagName === 'VIDEO') {
            media.addEventListener('play', () => {
                if(!isDrawingMode) {
                    canvas.clear();
                }
            });
        }

        // --- SUBMIT ANNOTATION ---
        
        document.getElementById('saveAnnotationBtn').addEventListener('click', async () => {
            const text = document.getElementById('comment_text').value;
            const timecode = document.getElementById('timecode').value;
            const parentId = document.getElementById('parent_id').value;
            
            // Serialize canvas drawing
            const canvasData = canvas.isEmpty() ? '' : JSON.stringify(canvas.toJSON());
            
            if(text.trim() === '' && canvasData === '') {
                alert('Please add a comment or draw something.');
                return;
            }

            const formData = new FormData();
            formData.append('timecode', timecode);
            formData.append('comment_text', text);
            formData.append('canvas_data', canvasData);
            formData.append('parent_id', parentId);
            formData.append('<?= csrf_token() ?>', getCsrfToken());

            try {
                const btn = document.getElementById('saveAnnotationBtn');
                const origHtml = btn.innerHTML;
                btn.innerHTML = 'Saving...';
                
                const response = await fetch('<?= base_url($routePrefix . '/reviews/saveAnnotation/') ?>/' + activeReviewId, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const data = await response.json();
                if (data.csrf) {
                    currentCsrfHash = data.csrf;
                }
                
                if(data.status === 'success') {
                    window.location.reload();
                } else {
                    alert('Error saving annotation');
                    btn.innerHTML = origHtml;
                }
            } catch (err) {
                console.error(err);
                alert('Request failed');
            }
        });
        
        // --- REPLY LOGIC ---
        window.replyTo = function(commentId, authorName, textSnippet, timecode) {
            document.getElementById('parent_id').value = commentId;
            document.getElementById('replyName').textContent = authorName;
            document.getElementById('replySnippet').textContent = textSnippet;
            document.getElementById('replyBanner').classList.remove('hidden');
            
            // Set timecode to parent's timecode so drawing maps correctly
            if (timecode !== null) {
                document.getElementById('timecode').value = timecode;
                window.jumpTo(timecode);
            }
            
            document.getElementById('comment_text').focus();
        };
        
        window.cancelReply = function() {
            document.getElementById('parent_id').value = '';
            document.getElementById('replyName').textContent = '';
            document.getElementById('replySnippet').textContent = '';
            document.getElementById('replyBanner').classList.add('hidden');
        };

        // --- PLAYBACK LOGIC ---

        // Jump to timecode
        window.jumpTo = function(timecode) {
            if(media.tagName === 'VIDEO') {
                media.currentTime = timecode;
                media.pause();
            }
            
            let combinedObjects = [];
            let tcStrs = [];
            
            document.querySelectorAll('.group').forEach(el => {
                if(parseFloat(el.dataset.timecode) === timecode) {
                    const role = el.dataset.role;
                    const matchesRole = window.activeRoleFilter === 'All' || role === window.activeRoleFilter;
                    
                    if (matchesRole) {
                        tcStrs.push(el.dataset.timecode);
                        const savedData = el.dataset.canvas;
                        if(savedData) {
                            try {
                                const parsed = JSON.parse(savedData);
                                if (parsed && parsed.objects) {
                                    combinedObjects = combinedObjects.concat(parsed.objects);
                                }
                            } catch(e){}
                        }
                    }
                }
            });
            
            if (combinedObjects.length > 0 && window.showAllFeedback) {
                const fakeJson = JSON.stringify({ version: "5.3.0", objects: combinedObjects });
                canvas.loadFromJSON(fakeJson, canvas.renderAll.bind(canvas));
            } else {
                canvas.clear();
            }
            
            if (window.showAllFeedback) {
                highlightSidebarComments(tcStrs);
            }
        };

        window.deleteComment = async function(id) {
            if(!confirm('Are you sure you want to delete this comment?')) return;
            try {
                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', getCsrfToken());
                const res = await fetch('<?= base_url($routePrefix . '/reviews/deleteComment/') ?>' + id, {
                    method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                if(data.status === 'success') {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error deleting');
                }
            } catch(e) {
                alert('Request failed');
            }
        };

        window.editComment = function(id) {
            const container = document.getElementById('comment-text-' + id);
            const p = container.querySelector('p');
            const currentText = p.innerText;
            
            container.innerHTML = `
                <div class="relative mt-1 border-2 border-transparent border-dashed rounded p-1 transition-colors"
                     ondragover="event.preventDefault(); this.classList.add('border-purple-500', 'bg-purple-900/20');"
                     ondragleave="this.classList.remove('border-purple-500', 'bg-purple-900/20');"
                     ondrop="event.preventDefault(); this.classList.remove('border-purple-500', 'bg-purple-900/20'); const pid = event.dataTransfer.getData('text/plain'); document.getElementById('edit-parent-${id}').value = pid; document.getElementById('edit-reply-badge-${id}').classList.remove('hidden');">
                    
                    <input type="hidden" id="edit-parent-${id}" value="">
                    
                    <div id="edit-reply-badge-${id}" class="hidden mb-1 flex items-center justify-between bg-purple-900/30 text-purple-400 text-[10px] px-2 py-1 border border-purple-500/30 rounded">
                        <span><span class="material-symbols-outlined text-[12px] align-middle mr-1">link</span>Linked as Reply</span>
                        <button type="button" class="hover:text-white" onclick="document.getElementById('edit-parent-${id}').value=''; document.getElementById('edit-reply-badge-${id}').classList.add('hidden');">Undo</button>
                    </div>

                    <textarea class="w-full bg-[#111111] border border-ytBorder text-ytText text-[13px] p-2 rounded focus:outline-none focus:border-ytBlue min-h-[60px]" id="edit-input-${id}">${currentText}</textarea>
                    
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-[10px] text-ytMuted opacity-60">Drag a comment here to reply</span>
                        <div class="flex gap-2">
                            <button class="text-[11px] text-ytMuted hover:text-white" onclick="cancelEdit(${id}, \`${currentText.replace(/`/g, '\\`')}\`)">Cancel</button>
                            <button class="text-[11px] bg-ytBlue text-white px-2 py-1 rounded" onclick="saveEdit(${id})">Save</button>
                        </div>
                    </div>
                </div>
            `;
        };

        window.cancelEdit = function(id, originalText) {
            const container = document.getElementById('comment-text-' + id);
            container.innerHTML = `<p class="text-[13px] text-ytMuted mt-1 whitespace-pre-wrap">${originalText}</p>`;
        };

        window.saveEdit = async function(id) {
            const text = document.getElementById('edit-input-' + id).value;
            const parentId = document.getElementById('edit-parent-' + id).value;
            try {
                const formData = new FormData();
                formData.append('comment_text', text);
                if (parentId) formData.append('parent_id', parentId);
                formData.append('<?= csrf_token() ?>', getCsrfToken());
                
                const res = await fetch('<?= base_url($routePrefix . '/reviews/updateComment/') ?>' + id, {
                    method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                if(data.status === 'success') {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error updating');
                }
            } catch(e) {
                alert('Request failed');
            }
        };
    </script>
</body>
</html>
