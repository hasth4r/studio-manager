<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnsoFlow - <?= esc($type) ?> Login</title>
    <!-- Google Fonts: DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <!-- Google Material Symbols Outlined -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- Custom Theme Variables -->
    <link rel="stylesheet" href="/assets/css/roots.css">
    
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"DM Sans"', 'sans-serif'],
                    },
                    colors: {
                        ytBg: 'rgb(var(--color-bg) / <alpha-value>)',
                        ytCard: 'rgb(var(--color-card) / <alpha-value>)',
                        ytBorder: 'rgb(var(--color-border) / <alpha-value>)',
                        ytHover: 'rgb(var(--color-hover) / <alpha-value>)',
                        ytText: 'rgb(var(--color-text) / <alpha-value>)',
                        ytMuted: 'rgb(var(--color-muted) / <alpha-value>)',
                        ytRed: 'rgb(var(--color-error) / <alpha-value>)',
                        ytBlue: 'rgb(var(--color-accent) / <alpha-value>)',
                        enso8Navy: 'rgb(var(--color-accent-dark) / <alpha-value>)'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-ytBg text-ytText min-h-screen flex flex-col justify-center items-center font-sans antialiased p-4" style="background: radial-gradient(circle at 15% 0%, #060b1c 0%, transparent 40%), radial-gradient(circle at 85% 0%, #0a081a 0%, #010103 50%); background-color: #010103; background-attachment: fixed;">
    
    <div class="w-full max-w-sm">
        
        <!-- Header -->
        <div class="text-center mb-8 flex flex-col items-center">
            <img src="/assets/images/enso8_logo_Slim.png" alt="Enso8 Logo" class="h-16 w-16 object-contain mb-2">
            <h1 class="text-2xl font-medium text-ytText mb-1">EnsoFlow Studio</h1>
            <div class="text-[13px] text-ytMuted font-medium uppercase tracking-widest mt-1">
                <?= esc($type) ?> LOGIN
            </div>
        </div>

        <!-- Alerts -->
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="bg-[#2a1215] border border-red-900 text-red-200 px-4 py-3 rounded mb-6 text-sm flex items-center">
                <span class="material-symbols-outlined mr-2">error</span>
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('errors')) : ?>
            <div class="bg-[#2a1215] border border-red-900 text-red-200 px-4 py-3 rounded mb-6 text-sm">
                <ul class="list-disc list-inside">
                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?= esc($action) ?>" method="POST" class="bg-ytBg sm:bg-ytCard sm:border border-ytBorder rounded-xl p-0 sm:p-8">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="<?= esc($type) ?>">

            <div class="mb-5">
                <label for="email" class="block text-[13px] font-medium text-ytMuted mb-2">Email address</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-ytMuted text-xl">person</span>
                    <input type="email" name="email" id="email" value="<?= old('email') ?>" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-10 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]" placeholder="Enter your email">
                </div>
            </div>

            <div class="mb-8">
                <label for="password" class="block text-[13px] font-medium text-ytMuted mb-2">Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-ytMuted text-xl">lock</span>
                    <input type="password" name="password" id="password" required class="w-full bg-ytBg border border-ytBorder text-ytText rounded px-10 py-2.5 focus:outline-none focus:border-ytBlue transition-colors font-light text-[15px]" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-br from-[#0a2754] to-[#177bcf] text-white shadow-[0_0_15px_rgba(23,123,207,0.3)] border border-[#177bcf]/40 font-medium text-[15px] py-2.5 rounded-full hover:shadow-[0_0_25px_rgba(23,123,207,0.6)] hover:from-[#0d346e] hover:to-[#238bf2] transition-colors">
                Next
            </button>
            
            <div class="mt-6 text-center">
                <a href="#" class="text-[13px] font-medium text-ytBlue hover:text-blue-400">Forgot password?</a>
            </div>
        </form>

    </div>

</body>
</html>
