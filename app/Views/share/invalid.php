<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Presentation Error') ?> - Studio Inphenix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0b0f19; color: #e2e8f0; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-[#131b2e]/90 border border-slate-800/80 rounded-2xl p-8 text-center shadow-2xl backdrop-blur-xl">
        <div class="w-16 h-16 rounded-full <?= !empty($expired) ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' ?> flex items-center justify-center mx-auto mb-5">
            <span class="material-symbols-outlined text-[32px]"><?= !empty($expired) ? 'timer_off' : 'lock' ?></span>
        </div>
        
        <h1 class="text-xl font-bold text-white mb-2"><?= esc($title) ?></h1>
        <p class="text-sm text-slate-400 leading-relaxed mb-6"><?= esc($message) ?></p>
        
        <div class="pt-6 border-t border-slate-800 flex flex-col items-center gap-2">
            <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Studio Inphenix Media Delivery</span>
            <span class="text-[11px] text-slate-600">Protected & Encrypted Stream</span>
        </div>
    </div>
</body>
</html>
