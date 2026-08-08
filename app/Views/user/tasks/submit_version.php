<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('content') ?>

<div class="px-6 py-8 w-full max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-8">
        <a href="/user/dashboard" class="text-ytMuted hover:text-ytText transition-colors p-2 rounded-full hover:bg-[#222]">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-ytText leading-tight">Submit New Version</h1>
            <p class="text-ytMuted text-sm mt-1">
                <?= esc($task->project_code) ?> / <span class="text-ytBlue">
                    <?php if(isset($task->shot_number)): ?>
                        <?= !empty($task->seq_name) ? esc($task->seq_name) . ' / ' : '' ?><?= esc($task->shot_number) ?>
                    <?php else: ?>
                        <?= esc($task->shot_number ?? $task->seq_name ?? 'Global') ?>
                    <?php endif; ?>
                </span> - <span class="text-ytBlue"><?= esc($task->category_name) ?></span>
            </p>
        </div>
    </div>

    <form action="/user/tasks/submitReview" method="POST" enctype="multipart/form-data" class="bg-ytCard border border-ytBorder rounded-xl p-6 shadow-sm">
        <?= csrf_field() ?>
        <input type="hidden" name="task_id" value="<?= $task->id ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <!-- Left: File Upload & Notes -->
            <div class="space-y-6">
                <div>
                    <label class="block text-[13px] font-medium text-ytMuted mb-2">Review Media (Optional)</label>
                    <input type="file" name="review_media" class="block w-full text-[13px] text-ytText
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-[13px] file:font-semibold
                        file:bg-ytBlue/10 file:text-ytBlue
                        hover:file:bg-ytBlue/20 transition-colors
                    ">
                </div>
                
                <div>
                    <label class="block text-[13px] font-medium text-ytMuted mb-2">Artist Notes</label>
                    <textarea name="artist_notes" rows="4" class="w-full bg-[#111111] border border-ytBorder text-ytText text-[13px] p-3 rounded-lg focus:outline-none focus:border-ytBlue transition-colors" placeholder="Any notes for the reviewer?"></textarea>
                </div>
            </div>

            <!-- Right: Resolutions -->
            <div>
                <label class="block text-[13px] font-medium text-ytMuted mb-2">Previous Version Feedback</label>
                
                <?php if(empty($comments)): ?>
                    <div class="bg-ytBg border border-ytBorder border-dashed rounded-lg p-6 text-center text-ytMuted text-[13px]">
                        No pending feedback to resolve.
                    </div>
                <?php else: ?>
                    <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach($comments as $idx => $comment): ?>
                        <div class="bg-[#111111] border border-ytBorder rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <?php if($comment->timecode !== null): ?>
                                <span class="text-[10px] bg-ytBlue/20 text-ytBlue px-2 py-0.5 rounded font-bold">
                                    <?= gmdate("i:s", $comment->timecode) ?>
                                </span>
                                <?php endif; ?>
                                <span class="text-[12px] font-medium text-ytMuted"><?= esc($comment->reviewer_name) ?></span>
                            </div>
                            <p class="text-[13px] text-ytText mb-4 leading-relaxed"><?= esc($comment->comment_text) ?></p>
                            
                            <div class="bg-[#1a1a1a] p-3 rounded border border-ytBorder/50 space-y-3">
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="resolutions[<?= $comment->id ?>]" value="done" class="text-ytBlue bg-black border-ytBorder focus:ring-ytBlue" required>
                                        <span class="text-[13px] text-ytText">Done</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="resolutions[<?= $comment->id ?>]" value="ignored" class="text-ytBlue bg-black border-ytBorder focus:ring-ytBlue" required>
                                        <span class="text-[13px] text-ytText">Ignored / Skipped</span>
                                    </label>
                                </div>
                                <input type="text" name="resolution_reasons[<?= $comment->id ?>]" class="w-full bg-black border border-ytBorder/50 text-ytText text-[12px] p-2 rounded focus:outline-none focus:border-ytBlue" placeholder="Reason (Optional for Done, required for Ignored)...">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end pt-6 border-t border-ytBorder">
            <button type="submit" class="bg-ytBlue text-white px-6 py-2 rounded-lg text-[14px] font-medium hover:bg-blue-600 transition-colors shadow-lg">
                Submit Review
            </button>
        </div>
    </form>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
</style>

<script>
    // Simple validation
    document.querySelector('form').addEventListener('submit', function(e) {
        let valid = true;
        const radios = document.querySelectorAll('input[type="radio"]:checked');
        radios.forEach(radio => {
            if (radio.value === 'ignored') {
                const commentId = radio.name.match(/\[(\d+)\]/)[1];
                const reasonInput = document.querySelector(`input[name="resolution_reasons[${commentId}]"]`);
                if (!reasonInput.value.trim()) {
                    valid = false;
                    reasonInput.classList.add('border-red-500');
                } else {
                    reasonInput.classList.remove('border-red-500');
                }
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please provide a reason for ignored feedback.');
        }
    });
</script>

<?= $this->endSection() ?>
