<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnsoFlow - <?= esc($pageTitle ?? 'Dashboard') ?></title>
    <!-- Google Fonts: DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <!-- Google Material Symbols Outlined -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- Custom Theme Variables -->
    <link rel="stylesheet" href="/assets/css/roots.css">
    
    <!-- Tailwind CSS (CDN for development) -->
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
<body class="bg-ytBg text-ytText min-h-screen flex flex-col font-sans antialiased overflow-hidden" style="background: radial-gradient(circle at 15% 0%, #060b1c 0%, transparent 40%), radial-gradient(circle at 85% 0%, #0a081a 0%, #010103 50%); background-color: #010103; background-attachment: fixed;">
    
    <div class="flex flex-1 overflow-hidden h-screen">
        <!-- Left Sidebar -->
        <aside id="sidebar" class="w-64 flex-shrink-0 flex flex-col overflow-y-auto hidden md:flex">
            <!-- Sidebar Header / Logo & Notifications -->
            <div class="h-14 flex items-center justify-between px-3.5 flex-shrink-0 pt-2 mb-2">
                <div class="flex items-center min-w-0">
                    <button id="menuToggle" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-ytText">menu</span>
                    </button>
                    <div class="flex items-center space-x-2 ml-2 cursor-pointer nav-text overflow-hidden">
                        <img src="/assets/images/enso8_logo_Slim.png" alt="Enso8 Logo" class="h-7 w-7 object-contain flex-shrink-0">
                        <span class="text-lg font-bold tracking-tighter text-ytText">EnsoFlow</span>
                    </div>
                </div>

                <!-- Desktop Sidebar Notification Bell -->
                <div class="relative flex-shrink-0 nav-text">
                    <button id="sidebar-notification-btn" class="p-2 hover:bg-ytHover rounded-full transition-all relative flex items-center justify-center text-ytMuted hover:text-white outline-none" title="Notifications">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        <span id="sidebar-notification-badge" class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full min-w-[16px] text-center hidden shadow-[0_0_8px_rgba(239,68,68,0.5)]">0</span>
                    </button>

                    <!-- Dropdown Panel -->
                    <div id="sidebar-notification-panel" class="absolute left-0 top-full mt-2 w-80 bg-ytCard border border-ytBorder rounded-2xl shadow-2xl hidden flex-col overflow-hidden transform origin-top-left transition-all opacity-0 scale-95 z-[100]" style="backdrop-filter: blur(16px); background-color: rgba(17, 24, 39, 0.95);">
                        <div class="px-4 py-3 border-b border-ytBorder/50 flex justify-between items-center bg-[#0a0d17]/50">
                            <h3 class="text-[14px] font-bold text-ytText">Notifications</h3>
                            <span class="text-[11px] text-ytMuted uppercase tracking-wider">Unread</span>
                        </div>
                        <div id="sidebar-notification-list" class="max-h-96 overflow-y-auto">
                            <div class="p-6 text-center text-ytMuted text-[13px]">
                                <span class="material-symbols-outlined text-[32px] mb-2 opacity-50">notifications_paused</span>
                                <p>All caught up!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 mt-4 space-y-6">
                <?php $currentUri = uri_string(); ?>
                
                <!-- Main Navigation Group -->
                <div class="nav-section">
                    <p class="section-title px-4 text-[11px] font-bold uppercase tracking-wider text-ytMuted mb-2">Main</p>
                    <div class="space-y-0.5">
                        <?php $isActive = (strpos($currentUri, 'user/dashboard') === 0); ?>
                        <a href="/user/dashboard" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="My Assigned Tasks">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">task_alt</span>
                            <span class="nav-text">My Tasks</span>
                        </a>

                        <?php if(has_any_role(['site_manager', 'admin', 'project_manager']) || is_any_supervisor()): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/projects') === 0 || strpos($currentUri, 'admin/shots') === 0); ?>
                        <a href="/admin/projects" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Projects & Breakdowns">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">video_library</span>
                            <span class="nav-text">Projects</span>
                        </a>

                        <?php $isActive = (strpos($currentUri, 'admin/reviews') === 0); ?>
                        <a href="/admin/reviews" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Review Inbox">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">rate_review</span>
                            <span class="nav-text">Review Inbox</span>
                        </a>

                        <?php endif; ?>
                    </div>
                </div>
            </nav>
            
            <div class="p-3 mt-auto">
                <!-- User Profile -->
                <div class="flex items-center justify-between px-3 py-3 mb-2 rounded-lg hover:bg-ytHover transition-colors cursor-pointer group">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('userName')) ?>&background=8b5cf6&color=fff&size=64&rounded=true" alt="Profile" class="h-9 w-9 rounded-full flex-shrink-0">
                        <div class="nav-text flex flex-col overflow-hidden">
                            <span class="text-[14px] font-medium text-ytText truncate leading-tight"><?= esc(session()->get('userName')) ?></span>
                            <span class="text-[10px] text-amber-400 font-bold uppercase truncate leading-tight mt-0.5 font-mono">
                                <?= (has_role('project_manager') || is_any_supervisor()) ? 'SUPERVISOR / PM' : esc(str_replace('_', ' ', session()->get('userRole'))) ?>
                            </span>
                        </div>
                    </div>
                    <a href="/logout" class="nav-text text-ytMuted hover:text-ytRed transition-colors ml-2 flex-shrink-0 opacity-0 group-hover:opacity-100" title="Sign Out">
                        <span class="material-symbols-outlined text-[20px]">logout</span>
                    </a>
                </div>
                <?php
                    $db = \Config\Database::connect();
                    $currentUserTG = $db->table('users')->where('id', session()->get('userId'))->get()->getRow();
                    $isTelegramLinked = !empty($currentUserTG->telegram_chat_id);
                ?>
                <a href="/telegram/link" target="_blank" class="flex items-center space-x-4 px-4 py-2.5 rounded-lg transition-colors <?= $isTelegramLinked ? 'text-green-400 hover:bg-green-400/10' : 'text-[#1DA1F2] hover:bg-[#1DA1F2]/10' ?>" title="<?= $isTelegramLinked ? 'Telegram Connected' : 'Connect Telegram' ?>">
                    <span class="material-symbols-outlined"><?= $isTelegramLinked ? 'check_circle' : 'send' ?></span>
                    <span class="nav-text"><?= $isTelegramLinked ? 'Telegram Connected' : 'Connect Telegram' ?></span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main id="main-content" class="overflow-y-auto bg-ytBg <?= isset($fullScreen) && $fullScreen ? '' : 'px-8 py-6' ?>" style="margin-left: 256px; height: 100vh; width: calc(100% - 256px); transition: margin-left 0.6s cubic-bezier(0.16, 1, 0.3, 1), width 0.6s cubic-bezier(0.16, 1, 0.3, 1);">
            <!-- Page Content injected here -->
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Interactive Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Toggle
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            const SIDEBAR_FULL = '256px';
            const SIDEBAR_COLLAPSED = '76px';

            function applyCollapsedState(collapsed) {
                if (collapsed) {
                    sidebar.classList.add('sidebar-collapsed');
                    if (mainContent) {
                        mainContent.style.marginLeft = SIDEBAR_COLLAPSED;
                        mainContent.style.width = 'calc(100% - ' + SIDEBAR_COLLAPSED + ')';
                    }
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                    if (mainContent) {
                        mainContent.style.marginLeft = SIDEBAR_FULL;
                        mainContent.style.width = 'calc(100% - ' + SIDEBAR_FULL + ')';
                    }
                }
            }
            
            // Restore state from LocalStorage
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            applyCollapsedState(isCollapsed);
            
            menuToggle.addEventListener('click', () => {
                const nowCollapsed = !sidebar.classList.contains('sidebar-collapsed');
                applyCollapsedState(nowCollapsed);
                localStorage.setItem('sidebarCollapsed', nowCollapsed);
            });

            // Profile Dropdown Toggle
            const profileToggle = document.getElementById('profileToggle');
            const profileDropdown = document.getElementById('profileDropdown');

            profileToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                profileDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });
        });
    </script>
    <!-- Global Notifications Widget -->
    <?= view('partials/notifications') ?>

    <?php if (!$isTelegramLinked && !empty(env('TELEGRAM_BOT_TOKEN'))): ?>
    <script>
        // Poll for Telegram link completion every 5 seconds
        setInterval(async () => {
            try {
                const response = await fetch('/telegram/poll');
                const data = await response.json();
                if (data.status === 'success' && data.processed > 0) {
                    window.location.reload(); // Reload to show connected status
                }
            } catch(e) {}
        }, 5000);
    </script>
    <?php endif; ?>
</body>
</html>
