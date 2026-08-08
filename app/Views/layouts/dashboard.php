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
    <link rel="stylesheet" href="/assets/css/roots.css?v=<?= time() ?>">
    
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
    <style>
        /* Force hide ALL scrollbars across the entire app, overriding Tailwind */
        ::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        * {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
    </style>
</head>
<body class="bg-ytBg text-ytText font-sans antialiased" style="background: radial-gradient(circle at 15% 0%, #060b1c 0%, transparent 40%), radial-gradient(circle at 85% 0%, #0a081a 0%, #010103 50%); background-color: #010103; background-attachment: fixed;">
    
    <!-- Left Sidebar: FIXED position, completely independent from content -->
    <aside id="sidebar" class="hidden md:flex flex-col fixed top-0 left-0 h-screen z-50" style="width: 256px; background-color: #000107;">
            <!-- Sidebar Header / Logo -->
            <div class="h-14 flex items-center px-4 flex-shrink-0 pt-2 mb-2">
                <button id="menuToggle" class="p-2 hover:bg-ytHover rounded-full transition-colors flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-ytText">menu</span>
                </button>
                <div class="flex items-center space-x-2 ml-3 cursor-pointer nav-text overflow-hidden">
                    <img src="/assets/images/enso8_logo_Slim.png" alt="Enso8 Logo" class="h-8 w-8 object-contain flex-shrink-0">
                    <span class="text-xl font-bold tracking-tighter text-ytText">EnsoFlow</span>
                </div>
            </div>

            <nav class="flex-1 px-3 mt-4 space-y-6">
                <?php $currentUri = uri_string(); ?>
                
                <!-- Main Navigation Group -->
                <div class="nav-section">
                    <p class="section-title px-4 text-[11px] font-bold uppercase tracking-wider text-ytMuted mb-2">Main</p>
                    <div class="space-y-0.5">
                        <?php 
                            $dashLink = '/admin/dashboard';
                            if (session()->get('userRole') === 'client') $dashLink = '/client/dashboard';
                            elseif (session()->get('userRole') === 'internal_artist') $dashLink = '/user/dashboard';
                            $isActive = (strpos($currentUri, ltrim($dashLink, '/')) === 0);
                        ?>
                        <a href="<?= $dashLink ?>" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Dashboard">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">dashboard</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        
                        <?php if(session()->get('userRole') !== 'client'): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/projects') === 0 || strpos($currentUri, 'admin/shots') === 0); ?>
                        <a href="/admin/projects" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Projects">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">video_library</span>
                            <span class="nav-text">Projects</span>
                        </a>
                        
                        <?php $isActive = (strpos($currentUri, 'admin/reviews') === 0); ?>
                        <a href="/admin/reviews" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors relative" title="Review Inbox">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">rate_review</span>
                            <span class="nav-text">Review Inbox</span>
                            <!-- We can add a notification badge here later if needed -->
                        </a>
                        <?php $isActive = (strpos($currentUri, 'admin/scheduling') === 0); ?>
                        <a href="/admin/scheduling" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors relative" title="AI Scheduler">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">view_timeline</span>
                            <span class="nav-text">AI Scheduler</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if(session()->get('userRole') === 'admin'): ?>
                <!-- Admin Settings Group -->
                <div class="nav-section">
                    <p class="section-title px-4 text-[11px] font-bold uppercase tracking-wider text-ytMuted mb-2">Settings</p>
                    <div class="space-y-0.5">
                        <?php $isActive = (strpos($currentUri, 'admin/users') === 0); ?>
                        <a href="/admin/users" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Team">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">manage_accounts</span>
                            <span class="nav-text">Team</span>
                        </a>
                        <?php $isActive = (strpos($currentUri, 'admin/clients') === 0); ?>
                        <a href="/admin/clients" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Clients">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">group</span>
                            <span class="nav-text">Clients</span>
                        </a>
                        <?php $isActive = (strpos($currentUri, 'admin/project-types') === 0); ?>
                        <a href="/admin/project-types" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Project Types">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">category</span>
                            <span class="nav-text">Project Types</span>
                        </a>
                        <?php $isActive = (strpos($currentUri, 'admin/database') === 0); ?>
                        <a href="/admin/database" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Database Manager">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">storage</span>
                            <span class="nav-text">Database Manager</span>
                        </a>
                        <?php $isActive = (strpos($currentUri, 'admin/notifications/generate') === 0); ?>
                        <a href="/admin/notifications/generate" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Notification Tools">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">notifications_active</span>
                            <span class="nav-text">Notification Tools</span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(in_array(session()->get('userRole'), ['admin', 'project_manager', 'internal_artist'])): ?>
                <!-- Studio Group -->
                <div class="nav-section">
                    <p class="section-title px-4 text-[11px] font-bold uppercase tracking-wider text-ytMuted mb-2">Studio</p>
                    <div class="space-y-0.5">
                        <?php $isActive = (strpos($currentUri, 'admin/chat') === 0); ?>
                        <a href="#" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Studio Chat">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">forum</span>
                            <span class="nav-text">Studio Chat</span>
                        </a>
                        
                        <?php if(session()->get('userRole') === 'admin' || session()->get('userRole') === 'project_manager'): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/media') === 0); ?>
                        <a href="/admin/media" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Media Explorer">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">folder_open</span>
                            <span class="nav-text">Media Explorer</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </nav>
            
            <div class="p-3 mt-auto">
                <!-- User Profile -->
                <div class="flex items-center justify-between px-3 py-3 mb-2 rounded-lg hover:bg-ytHover transition-colors cursor-pointer group">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('userName')) ?>&background=8b5cf6&color=fff&size=64&rounded=true" alt="Profile" class="h-9 w-9 rounded-full flex-shrink-0">
                        <div class="nav-text flex flex-col overflow-hidden">
                            <span class="text-[14px] font-medium text-ytText truncate leading-tight"><?= esc(session()->get('userName')) ?></span>
                            <span class="text-[11px] text-ytMuted uppercase truncate leading-tight mt-0.5"><?= esc(str_replace('_', ' ', session()->get('userRole'))) ?></span>
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

                <?php if(session()->get('userRole') === 'admin'): ?>
                <a href="/admin/settings" class="<?= (current_url() == site_url('settings')) ? 'bg-ytHover text-white font-medium' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg transition-colors mt-1" title="Settings">
                    <span class="material-symbols-outlined text-ytMuted">settings</span>
                    <span class="nav-text">Server Settings</span>
                </a>
                <?php endif; ?>
            </div>
    </aside>

    <!-- Main Content Area: margin-left matches sidebar width, scrolls independently -->
    <main id="main-content" class="overflow-y-auto bg-ytBg <?= isset($fullScreen) && $fullScreen ? '' : 'px-8' ?>" style="margin-left: 256px; height: 100vh;">
        <!-- Page Content injected here -->
        <?= $this->renderSection('content') ?>
    </main>

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
                    if (mainContent) mainContent.style.marginLeft = SIDEBAR_COLLAPSED;
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                    if (mainContent) mainContent.style.marginLeft = SIDEBAR_FULL;
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
