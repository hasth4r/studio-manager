<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('message')) : ?>
    <div id="flash-msg" class="flex items-center gap-3 bg-[#122a15] border border-green-900/60 text-green-300 px-4 py-3 rounded-lg mb-6 text-[13px]">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        <?= esc(session()->getFlashdata('message')) ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div id="flash-err" class="flex items-center gap-3 bg-[#2a1215] border border-red-900/60 text-red-300 px-4 py-3 rounded-lg mb-6 text-[13px]">
        <span class="material-symbols-outlined text-[18px]">error</span>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<!-- Page Title Header -->
<div class="sticky top-0 z-30 bg-ytBg/95 backdrop-blur-sm pt-6 pb-4 border-b border-ytBorder/50 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-[#1a122a] rounded-full flex items-center justify-center text-blue-400 border border-blue-900/50">
                <span class="material-symbols-outlined">task_alt</span>
            </div>
            <div>
                <h2 class="text-[24px] font-medium text-ytText">My Tasks Workbench</h2>
                <p class="text-[13px] text-ytMuted mt-1">Overview of your assigned VFX tasks &amp; submissions</p>
            </div>
        </div>
        <?php if(has_role('project_manager') || is_any_supervisor()): ?>
        <div class="flex items-center gap-2">
            <a href="/pm/dashboard" class="bg-gradient-to-r from-amber-500/20 to-amber-600/20 border border-amber-500/40 text-amber-300 hover:text-white px-3.5 py-1.5 rounded-xl text-[12px] font-medium flex items-center gap-1.5 transition-all shadow-md">
                <span class="material-symbols-outlined text-[16px] text-amber-400">shield_person</span>
                <span>Go to PM Hub</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Stats Row -->
<?php
$total    = count($myTasks ?? []);
$wip      = count(array_filter($myTasks ?? [], fn($t) => $t->status === 'in_progress'));
$review   = count(array_filter($myTasks ?? [], fn($t) => $t->status === 'ready_for_review'));
$revision = count(array_filter($myTasks ?? [], fn($t) => $t->status === 'revision_needed'));
$done     = count(array_filter($myTasks ?? [], fn($t) => $t->status === 'completed'));
?>
<!-- Summary Stats Row: Mobile Swipeable Horizontal Carousel / Desktop 5-Col Grid -->
<div class="flex md:grid overflow-x-auto md:overflow-visible gap-2.5 md:gap-3 mb-4 md:mb-6 pb-2 md:pb-0 snap-x snap-mandatory scroll-smooth custom-scrollbar -mx-4 px-4 md:mx-0 md:px-0 md:grid-cols-5">
    <div class="bg-ytCard border border-ytBorder rounded-2xl p-3.5 md:p-4 flex flex-col gap-0.5 shrink-0 w-[130px] sm:w-[150px] md:w-auto snap-start shadow-lg shadow-black/10">
        <span class="text-ytMuted text-[10px] md:text-[11px] uppercase tracking-wider font-semibold">Total</span>
        <span class="text-xl md:text-2xl font-bold text-ytText"><?= $total ?></span>
    </div>
    <div class="bg-ytCard border border-yellow-900/40 rounded-2xl p-3.5 md:p-4 flex flex-col gap-0.5 shrink-0 w-[130px] sm:w-[150px] md:w-auto snap-start shadow-lg shadow-black/10">
        <span class="text-yellow-400/80 text-[10px] md:text-[11px] uppercase tracking-wider font-semibold">Not Started</span>
        <span class="text-xl md:text-2xl font-bold text-yellow-400"><?= count(array_filter($myTasks ?? [], fn($t) => $t->status === 'pending')) ?></span>
    </div>
    <div class="bg-ytCard border border-blue-900/40 rounded-2xl p-3.5 md:p-4 flex flex-col gap-0.5 shrink-0 w-[130px] sm:w-[150px] md:w-auto snap-start shadow-lg shadow-black/10">
        <span class="text-blue-400/80 text-[10px] md:text-[11px] uppercase tracking-wider font-semibold">In Progress</span>
        <span class="text-xl md:text-2xl font-bold text-blue-400"><?= $wip ?></span>
    </div>
    <div class="bg-ytCard border border-purple-900/40 rounded-2xl p-3.5 md:p-4 flex flex-col gap-0.5 shrink-0 w-[130px] sm:w-[150px] md:w-auto snap-start shadow-lg shadow-black/10">
        <span class="text-purple-400/80 text-[10px] md:text-[11px] uppercase tracking-wider font-semibold">In Review</span>
        <span class="text-xl md:text-2xl font-bold text-purple-400"><?= $review ?></span>
    </div>
    <div class="bg-ytCard border border-green-900/40 rounded-2xl p-3.5 md:p-4 flex flex-col gap-0.5 shrink-0 w-[130px] sm:w-[150px] md:w-auto snap-start shadow-lg shadow-black/10">
        <span class="text-green-400/80 text-[10px] md:text-[11px] uppercase tracking-wider font-semibold">Completed</span>
        <span class="text-xl md:text-2xl font-bold text-green-400"><?= $done ?></span>
    </div>
</div>

<!-- Table Card -->
<div class="bg-ytCard border border-ytBorder rounded-2xl overflow-hidden shadow-xl shadow-black/10">

    <!-- Table Header / Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 md:px-5 py-3.5 md:py-4 border-b border-ytBorder/50">
        <h3 class="text-[14px] md:text-[15px] font-bold text-ytText">My Assigned Tasks</h3>
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 max-w-full flex-nowrap custom-scrollbar">
            <button onclick="filterTasks('all')"              data-filter="all"              class="filter-btn px-3 py-1 rounded-full text-[11px] font-semibold border border-ytBorder bg-ytHover text-ytText transition-colors shrink-0">All</button>
            <button onclick="filterTasks('pending')"          data-filter="pending"          class="filter-btn px-3 py-1 rounded-full text-[11px] font-semibold border border-ytBorder text-ytMuted hover:bg-ytHover hover:text-ytText transition-colors shrink-0">Not Started</button>
            <button onclick="filterTasks('in_progress')"      data-filter="in_progress"      class="filter-btn px-3 py-1 rounded-full text-[11px] font-semibold border border-ytBorder text-ytMuted hover:bg-ytHover hover:text-ytText transition-colors shrink-0">In Progress</button>
            <button onclick="filterTasks('ready_for_review')" data-filter="ready_for_review" class="filter-btn px-3 py-1 rounded-full text-[11px] font-semibold border border-ytBorder text-ytMuted hover:bg-ytHover hover:text-ytText transition-colors shrink-0">In Review</button>
            <button onclick="filterTasks('revision_needed')"  data-filter="revision_needed"  class="filter-btn px-3 py-1 rounded-full text-[11px] font-semibold border border-ytBorder text-ytMuted hover:bg-ytHover hover:text-ytText transition-colors shrink-0">Revision</button>
            <button onclick="filterTasks('completed')"        data-filter="completed"        class="filter-btn px-3 py-1 rounded-full text-[11px] font-semibold border border-ytBorder text-ytMuted hover:bg-ytHover hover:text-ytText transition-colors shrink-0">Completed</button>
        </div>
    </div>

    <?php if(empty($myTasks)): ?>
        <div class="p-16 text-center">
            <span class="material-symbols-outlined text-[48px] text-ytMuted mb-3 block">task_alt</span>
            <p class="text-ytText font-semibold text-[15px]">All caught up!</p>
            <p class="text-ytMuted text-[13px] mt-1">No tasks assigned to you right now.</p>
        </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-ytBorder/50 text-ytMuted text-[11px] uppercase tracking-wider font-medium bg-[#111111]">
                    <th class="px-5 py-3 min-w-[250px]">Project & Task</th>
                    <th class="px-4 py-3 min-w-[140px]">Timeline</th>
                    <th class="px-4 py-3 min-w-[110px]">Est. Time</th>
                    <th class="px-4 py-3 min-w-[120px]">Status</th>
                    <th class="px-4 py-3 min-w-[60px] text-center">Notes</th>
                    <th class="px-4 py-3 min-w-[150px]">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[13px] text-ytText divide-y divide-ytBorder/40" id="task-tbody">
                <?php foreach($myTasks as $task): ?>
                    <?php
                        // Due date logic
                        $isOverdue = false;
                        $daysLeft  = null;
                        if (!empty($task->due_date)) {
                            $due  = new DateTime($task->due_date);
                            $now  = new DateTime('today');
                            $diff = $now->diff($due);
                            $daysLeft = (int)$diff->days * ($due < $now ? -1 : 1);
                            $isOverdue = $daysLeft < 0 && $task->status !== 'completed';
                        }

                        $statusClass = match($task->status) {
                            'in_progress'      => 'bg-[#121c2a] text-blue-400 border-blue-900/50',
                            'ready_for_review' => 'bg-[#2a122a] text-purple-400 border-purple-900/50',
                            'revision_needed'  => 'bg-[#2a1212] text-red-400 border-red-900/50',
                            'completed'        => 'bg-[#122a15] text-green-400 border-green-900/50',
                            default            => 'bg-[#2a2a12] text-yellow-400 border-yellow-900/50',
                        };
                        $statusLabel = match($task->status) {
                            'in_progress'      => 'In Progress',
                            'ready_for_review' => 'Ready for Review',
                            'revision_needed'  => 'Revision Needed',
                            'completed'        => 'Completed',
                            default            => 'Not Started',
                        };
                    ?>
                    <tr class="task-row hover:bg-ytHover/40 transition-colors <?= $isOverdue ? 'border-l-2 border-l-red-500' : '' ?>" data-status="<?= $task->status ?>">

                        <!-- Project & Task (Stacked + Thumbnail) -->
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <!-- Thumbnail Placeholder -->
                                <div class="w-12 h-12 flex-shrink-0 bg-[#1a1a1a] border border-ytBorder/50 rounded flex items-center justify-center relative overflow-hidden">
                                    <?php if(!empty($task->shot_thumb)): ?>
                                        <img src="/<?= esc($task->shot_thumb) ?>" alt="Thumbnail" class="w-full h-full object-cover">
                                    <?php elseif($task->shot_id): ?>
                                        <span class="material-symbols-outlined text-[20px] text-ytMuted">movie</span>
                                    <?php elseif($task->asset_id): ?>
                                        <span class="material-symbols-outlined text-[20px] text-ytMuted">category</span>
                                    <?php else: ?>
                                        <span class="material-symbols-outlined text-[20px] text-ytMuted">assignment</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Text Details -->
                                <div class="flex flex-col justify-center">
                                    <div class="text-ytText font-semibold text-[13px] flex items-center gap-1.5">
                                        <?= esc($task->project_name) ?> 
                                        <span class="text-ytMuted font-normal">—</span> 
                                        <?php if($task->shot_id): ?>
                                            <?= !empty($task->sequence_name) ? esc($task->sequence_name) . ' / ' : '' ?><?= esc($task->shot_number) ?>
                                        <?php elseif($task->asset_id): ?>
                                            <?= esc($task->asset_name) ?>
                                        <?php else: ?>
                                            General
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-ytBlue text-[11px] font-medium mt-0.5">
                                        <?= esc($task->task_name) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Timeline (Stacked Dates) -->
                        <td class="px-4 py-4">
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center gap-1 text-[11px] text-ytMuted">
                                    <span class="material-symbols-outlined text-[13px]">play_arrow</span>
                                    Assigned: <?= date('d M Y', strtotime($task->created_at)) ?>
                                </div>
                                <div class="flex items-center gap-1 text-[11px] <?= $isOverdue ? 'text-red-400 font-medium' : 'text-ytText' ?>">
                                    <span class="material-symbols-outlined text-[13px]"><?= $isOverdue ? 'warning' : 'event' ?></span>
                                    Due: 
                                    <?php if(!empty($task->due_date)): ?>
                                        <?= date('d M Y', strtotime($task->due_date)) ?>
                                        <?php if($isOverdue): ?>
                                            <span class="text-[9px] bg-red-900/30 text-red-400 px-1 py-0.5 rounded ml-1 uppercase">Overdue</span>
                                        <?php elseif($daysLeft !== null && $daysLeft <= 3 && $task->status !== 'completed'): ?>
                                            <span class="text-[9px] bg-yellow-900/30 text-yellow-400 px-1 py-0.5 rounded ml-1"><?= $daysLeft ?>d left</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-ytMuted">Not set</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <!-- Est. Time -->
                        <td class="px-4 py-4">
                            <?php if(!empty($task->estimated_hours)): ?>
                                <span class="flex items-center gap-1 text-[12px] text-ytText">
                                    <span class="material-symbols-outlined text-[13px] text-ytMuted">schedule</span>
                                    <?= $task->estimated_hours ?> hrs
                                </span>
                            <?php else: ?>
                                <span class="text-ytMuted text-[12px]">Not set</span>
                            <?php endif; ?>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-4">
                            <span class="<?= $statusClass ?> border px-2.5 py-1 rounded-md text-[11px] font-semibold uppercase tracking-wider block w-fit"><?= $statusLabel ?></span>
                        </td>

                        <!-- Notes -->
                        <td class="px-4 py-4 text-center">
                            <button onclick="openNotes(<?= $task->id ?>, `<?= esc(addslashes($task->notes ?? '')) ?>`)"
                                class="p-1.5 rounded-md hover:bg-ytHover transition-colors <?= !empty($task->notes) ? 'text-ytBlue bg-blue-900/10' : 'text-ytMuted hover:text-ytText' ?>"
                                title="<?= !empty($task->notes) ? 'View / Edit Notes' : 'Add Notes' ?>">
                                <span class="material-symbols-outlined text-[18px]">sticky_note_2</span>
                            </button>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-4">
                            <?php if(in_array($task->status, ['pending', 'revision_needed'])): ?>
                                <div class="flex items-center gap-2">
                                    <?php if ($task->status === 'revision_needed' && !empty($task->latest_review_id)): ?>
                                        <a href="/reviews/player/<?= $task->latest_review_id ?>" class="flex items-center gap-1.5 bg-purple-900/20 text-purple-400 border border-purple-700/50 px-3 py-1.5 rounded-full font-semibold text-[12px] hover:bg-purple-900/40 transition-colors whitespace-nowrap">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span> View Corrections
                                        </a>
                                    <?php endif; ?>
                                    <form action="/user/tasks/updateStatus/<?= $task->id ?>" method="POST" class="m-0 p-0 inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="flex items-center gap-1.5 bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-3 py-1.5 rounded-full font-semibold text-[12px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-all whitespace-nowrap">
                                            <span class="material-symbols-outlined text-[14px]">play_circle</span>
                                            <?= $task->status === 'revision_needed' ? 'Restart Task' : 'Start Task' ?>
                                        </button>
                                    </form>
                                </div>
                            <?php elseif($task->status === 'in_progress'): ?>
                                <div class="flex items-center gap-2">
                                    <?php if (!empty($task->latest_review_id)): ?>
                                        <a href="/reviews/player/<?= $task->latest_review_id ?>" class="flex items-center gap-1.5 bg-purple-900/20 text-purple-400 border border-purple-700/50 px-3 py-1.5 rounded-full font-semibold text-[12px] hover:bg-purple-900/40 transition-colors whitespace-nowrap" title="View past corrections">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" onclick="window.location.href='/user/tasks/submitVersionForm/<?= $task->id ?>'" class="flex items-center gap-1.5 bg-[#1a122a] border border-purple-700 text-purple-300 px-3 py-1.5 rounded-full font-semibold text-[12px] hover:bg-purple-900/40 transition-all whitespace-nowrap">
                                        <span class="material-symbols-outlined text-[14px]">send</span> Submit for Review
                                    </button>
                                </div>
                            <?php elseif($task->status === 'ready_for_review'): ?>
                                <span class="flex items-center gap-1.5 text-purple-400 text-[12px] whitespace-nowrap bg-purple-900/10 px-3 py-1.5 rounded border border-purple-900/30">
                                    <span class="material-symbols-outlined text-[14px] animate-pulse">hourglass_top</span>
                                    Awaiting Approval
                                </span>
                            <?php elseif($task->status === 'completed'): ?>
                                <span class="flex items-center gap-1.5 text-green-400 text-[12px] whitespace-nowrap bg-green-900/10 px-3 py-1.5 rounded border border-green-900/30">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                    Completed
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ============ MODALS ============ -->

<!-- Notes Modal -->
<div id="notesModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-md mx-4 shadow-2xl">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center">
            <h3 class="text-[15px] font-medium text-ytText flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-ytMuted">sticky_note_2</span> Task Notes</h3>
            <button type="button" onclick="closeModal('notesModal')" class="text-ytMuted hover:text-ytText"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form id="notesForm" action="" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="field" value="notes">
            <div class="mb-5">
                <label class="block text-[12px] font-medium text-ytMuted uppercase tracking-wider mb-2">Personal Notes</label>
                <textarea name="value" id="notesInput" rows="5"
                    placeholder="Add private notes about this task (references, links, progress details)..."
                    class="w-full bg-ytBg border border-ytBorder text-ytText rounded-lg px-4 py-2.5 focus:outline-none focus:border-ytBlue text-[13px] resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('notesModal')" class="px-4 py-2 rounded-full-lg text-[13px] text-ytText hover:bg-ytHover">Cancel</button>
                <button type="submit" class="bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 px-5 py-2 rounded-full-lg font-semibold text-[13px] hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2]">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Review Submit Modal -->
<div id="reviewSubmitModal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-ytCard border border-ytBorder rounded-xl w-full max-w-lg mx-4 shadow-2xl flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-ytBorder/50 flex justify-between items-center shrink-0">
            <h3 class="text-[16px] font-medium text-ytText flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400">upload_file</span> 
                Submit for Review
            </h3>
            <button type="button" onclick="closeModal('reviewSubmitModal')" class="text-ytMuted hover:text-ytText transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <form id="reviewSubmitForm" action="/user/tasks/submitReview" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-y-auto">
            <?= csrf_field() ?>
            <input type="hidden" name="task_id" id="review_task_id" value="">
            
            <div class="p-6 flex-1 space-y-6">
                <!-- Dropzone -->
                <div>
                    <label class="block text-[12px] font-medium text-ytMuted uppercase tracking-wider mb-2">Media File <span class="text-ytBlue lowercase">(auto V01+)</span></label>
                    <div class="relative w-full h-40 border-2 border-dashed border-ytBorder hover:border-purple-500/50 rounded-xl bg-[#1a1a1a] flex flex-col items-center justify-center transition-colors group cursor-pointer overflow-hidden">
                        
                        <input type="file" name="review_media" id="review_media_input" accept="image/*,video/mp4,video/quicktime" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>
                        
                        <!-- Default State -->
                        <div id="filePrompt" class="flex flex-col items-center pointer-events-none transition-opacity duration-200">
                            <span class="material-symbols-outlined text-[32px] text-ytMuted group-hover:text-purple-400 mb-2 transition-colors">cloud_upload</span>
                            <span class="text-[13px] font-medium text-ytText">Drop image or video here</span>
                            <span class="text-[11px] text-ytMuted mt-1">or click to browse</span>
                        </div>

                        <!-- Selected State -->
                        <div id="filePreview" class="absolute inset-0 bg-[#121212] hidden flex-col items-center justify-center pointer-events-none p-4">
                            <span class="material-symbols-outlined text-[32px] text-purple-400 mb-2">movie</span>
                            <span id="fileNameDisplay" class="text-[13px] font-medium text-ytText text-center max-w-[90%] truncate">filename.mp4</span>
                            <button type="button" id="removeFileBtn" class="mt-3 text-[11px] text-red-400 hover:text-red-300 pointer-events-auto z-20 px-3 py-1 rounded bg-red-900/20 border border-red-900/50">Remove</button>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-[12px] font-medium text-ytMuted uppercase tracking-wider mb-2">Artist Notes</label>
                    <textarea name="artist_notes" rows="3" placeholder="What changed in this version? What should the supervisor focus on?" class="w-full bg-[#1a1a1a] border border-ytBorder text-ytText rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500/50 text-[13px] resize-none"></textarea>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-ytBorder/50 flex justify-end gap-3 shrink-0 bg-ytCard">
                <button type="button" onclick="closeModal('reviewSubmitModal')" class="px-4 py-2 rounded-full font-medium text-[13px] text-ytText hover:bg-ytHover transition-colors">Cancel</button>
                <button type="submit" class="flex items-center gap-1.5 bg-gradient-to-br from-purple-700 to-purple-500 text-white shadow-[0_0_15px_rgba(126,34,206,0.3)] border border-purple-500/40 px-5 py-2 rounded-full font-medium text-[13px] hover:shadow-[0_0_25px_rgba(126,34,206,0.6)] hover:from-purple-600 hover:to-purple-400 transition-all">
                    <span class="material-symbols-outlined text-[16px]">send</span> Submit Version
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Filter logic
function filterTasks(status) {
    document.querySelectorAll('.task-row').forEach(row => {
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
    document.querySelectorAll('.filter-btn').forEach(btn => {
        const active = btn.dataset.filter === status;
        btn.classList.toggle('bg-ytHover', active);
        btn.classList.toggle('text-ytText', active);
        btn.classList.toggle('text-ytMuted', !active);
    });
}

// Modal helpers
function openNotes(taskId, currentNotes) {
    document.getElementById('notesForm').action = '/user/tasks/updateMeta/' + taskId;
    document.getElementById('notesInput').value = currentNotes || '';
    document.getElementById('notesModal').classList.remove('hidden');
}

function openReviewModal(taskId) {
    document.getElementById('review_task_id').value = taskId;
    // Reset form
    document.getElementById('reviewSubmitForm').reset();
    document.getElementById('filePreview').classList.add('hidden');
    document.getElementById('filePreview').classList.remove('flex');
    
    document.getElementById('reviewSubmitModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

// Dropzone File Handling
let selectedFile = null;
const fileInput = document.getElementById('review_media_input');
const filePreview = document.getElementById('filePreview');
const fileNameDisplay = document.getElementById('fileNameDisplay');
const removeBtn = document.getElementById('removeFileBtn');

if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            selectedFile = this.files[0];
            fileNameDisplay.textContent = selectedFile.name;
            filePreview.classList.remove('hidden');
            filePreview.classList.add('flex');
            
            // Regex Parse: [PROJECT]_[SHOT]_[Task]_[Artist]_[Version].[ext]
            const regex = /^([^_]+)_([^_]+)_([^_]+)_([^_]+)_(V\d+)\.(.+)$/i;
            const match = selectedFile.name.match(regex);
            
            if (match) {
                let metaSpan = document.getElementById('parsedMetaSpan');
                if(!metaSpan) {
                    metaSpan = document.createElement('span');
                    metaSpan.id = 'parsedMetaSpan';
                    metaSpan.className = 'text-[11px] text-green-300/80 mt-1 font-medium bg-green-900/20 px-2 py-0.5 rounded block text-center max-w-[90%] truncate';
                    fileNameDisplay.parentNode.insertBefore(metaSpan, removeBtn);
                }
                metaSpan.textContent = `Parsed format: ${match[5]}`;
            }
        }
    });
}

if (removeBtn) {
    removeBtn.addEventListener('click', function(e) {
        e.preventDefault(); 
        fileInput.value = ''; 
        selectedFile = null;
        filePreview.classList.add('hidden');
        filePreview.classList.remove('flex');
        
        let metaSpan = document.getElementById('parsedMetaSpan');
        if(metaSpan) metaSpan.remove();
    });
}

// Intercept Submit for Image Compression
document.getElementById('reviewSubmitForm').addEventListener('submit', async function(e) {
    if (selectedFile && selectedFile.type.startsWith('image/')) {
        e.preventDefault();
        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">sync</span> Compressing...';
        submitBtn.disabled = true;

        try {
            const compressedBlob = await compressImage(selectedFile, 1920, 0.8);
            
            // Create a new File object from the Blob
            const compressedFile = new File([compressedBlob], selectedFile.name.replace(/\.[^/.]+$/, ".jpg"), {
                type: 'image/jpeg',
                lastModified: Date.now()
            });

            // Replace the file input with the compressed file via DataTransfer
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressedFile);
            fileInput.files = dataTransfer.files;

            form.submit();
        } catch (error) {
            console.error("Compression failed", error);
            form.submit(); // Submit original if compression fails
        }
    }
});

// Canvas Image Compression logic
function compressImage(file, maxWidth, quality) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = event => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                if (width > maxWidth) {
                    height = Math.round((height * maxWidth) / width);
                    width = maxWidth;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob((blob) => {
                    resolve(blob);
                }, 'image/jpeg', quality);
            };
            img.onerror = error => reject(error);
        };
        reader.onerror = error => reject(error);
    });
}

// Close modals on backdrop click
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(modal.id); });
});

// Auto-dismiss flash messages
setTimeout(() => {
    ['flash-msg', 'flash-err'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.style.transition = 'opacity 0.4s'; el.style.opacity = '0'; setTimeout(() => el?.remove(), 400); }
    });
}, 4000);
</script>

<?= $this->endSection() ?>
