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
    
    <!-- Mobile Top Bar: EnsoFlow Header -->
    <header class="md:hidden flex items-center justify-between px-4 h-14 fixed top-0 w-full z-40 bg-ytBg/90 border-b border-ytBorder backdrop-blur-xl">
        <div class="flex items-center space-x-2.5">
            <button id="mobileTopMenuToggle" class="text-ytMuted hover:text-ytText flex items-center justify-center p-1 rounded-lg hover:bg-ytHover transition-colors">
                <span class="material-symbols-outlined text-[24px]">menu</span>
            </button>
            <div class="flex items-center space-x-2">
                <img src="/assets/images/enso8_logo_Slim.png" alt="Enso8 Logo" class="h-7 w-7 object-contain">
                <span class="text-lg font-bold tracking-tighter text-ytText">EnsoFlow</span>
            </div>
        </div>
        
        <div class="flex items-center space-x-2.5">
            <!-- Notification Bell (Mobile) -->
            <div class="relative">
                <button id="notification-btn" class="p-2 bg-ytCard border border-ytBorder hover:bg-ytHover hover:border-ytBlue rounded-full shadow-lg transition-all relative flex items-center justify-center group outline-none">
                    <span class="material-symbols-outlined text-ytMuted group-hover:text-ytText text-[20px]">notifications</span>
                    <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full min-w-[16px] text-center hidden shadow-[0_0_8px_rgba(239,68,68,0.5)]">0</span>
                </button>
                <!-- Mobile Dropdown Panel: Fixed within viewport boundaries so it never cuts off -->
                <div id="notification-panel" class="fixed left-3 right-3 top-16 sm:absolute sm:left-auto sm:right-0 sm:top-full sm:mt-3 sm:w-80 bg-ytCard border border-ytBorder rounded-2xl shadow-2xl hidden flex-col overflow-hidden transform origin-top transition-all opacity-0 scale-95 z-[100]" style="backdrop-filter: blur(16px); background-color: rgba(17, 24, 39, 0.98);">
                    <div class="px-4 py-3 border-b border-ytBorder/50 flex justify-between items-center bg-[#0a0d17]">
                        <h3 class="text-[14px] font-bold text-ytText">Notifications</h3>
                        <span class="text-[11px] text-ytMuted uppercase tracking-wider">Unread</span>
                    </div>
                    <div id="notification-list" class="max-h-[70vh] sm:max-h-80 overflow-y-auto">
                        <div class="p-6 text-center text-ytMuted text-[13px]">
                            <span class="material-symbols-outlined text-[32px] mb-2 opacity-50">notifications_paused</span>
                            <p>All caught up!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Avatar -->
            <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('userName')) ?>&background=3880ff&color=fff&size=64&rounded=true" alt="Profile" class="h-7 w-7 rounded-full border border-ytBorder">
        </div>
    </header>

    <!-- Mobile Bottom Navigation (EnsoFlow Theme) -->
    <nav class="md:hidden fixed bottom-0 w-full z-40 bg-[#000107]/95 backdrop-blur-xl border-t border-ytBorder flex justify-around items-center h-16 pb-safe">
        <?php 
            $dashLink = '/user/dashboard';
            if (has_any_role(['site_manager', 'admin'])) $dashLink = '/admin/dashboard';
            elseif (has_role('client')) $dashLink = '/client/dashboard';
            $isActiveDash = (strpos(uri_string(), ltrim($dashLink, '/')) === 0 || uri_string() === '' || uri_string() === 'admin');
        ?>
        <a href="<?= $dashLink ?>" class="flex flex-col items-center justify-center w-full h-full space-y-1 <?= $isActiveDash ? 'text-ytBlue font-bold' : 'text-ytMuted hover:text-ytText' ?>">
            <span class="material-symbols-outlined text-[22px]">dashboard</span>
            <span class="text-[10px] tracking-tight">Dashboard</span>
        </a>
        
        <?php if(has_any_role(['site_manager', 'admin', 'project_manager']) || (!has_role('client') && is_any_supervisor())): ?>
        <?php $isActiveProj = (strpos(uri_string(), 'admin/projects') === 0 || strpos(uri_string(), 'admin/shots') === 0); ?>
        <a href="/admin/projects" class="flex flex-col items-center justify-center w-full h-full space-y-1 <?= $isActiveProj ? 'text-ytBlue font-bold' : 'text-ytMuted hover:text-ytText' ?>">
            <span class="material-symbols-outlined text-[22px]">video_library</span>
            <span class="text-[10px] tracking-tight">Projects</span>
        </a>
        
        <?php $isActiveRev = (strpos(uri_string(), 'admin/reviews') === 0); ?>
        <a href="/admin/reviews" class="flex flex-col items-center justify-center w-full h-full space-y-1 <?= $isActiveRev ? 'text-ytBlue font-bold' : 'text-ytMuted hover:text-ytText' ?>">
            <span class="material-symbols-outlined text-[22px]">rate_review</span>
            <span class="text-[10px] tracking-tight">Reviews</span>
        </a>

        <?php if(has_any_role(['site_manager', 'admin'])): ?>
        <?php $isActiveBudget = (strpos(uri_string(), 'admin/budgeting') === 0); ?>
        <a href="/admin/budgeting" class="flex flex-col items-center justify-center w-full h-full space-y-1 <?= $isActiveBudget ? 'text-ytBlue font-bold' : 'text-ytMuted hover:text-ytText' ?>">
            <span class="material-symbols-outlined text-[22px]">payments</span>
            <span class="text-[10px] tracking-tight">Economics</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <button type="button" onclick="document.getElementById('mobileTopMenuToggle').click()" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-ytMuted hover:text-ytText">
            <span class="material-symbols-outlined text-[22px]">menu</span>
            <span class="text-[10px] tracking-tight">More</span>
        </button>
    </nav>

    <!-- Mobile Sidebar Drawer Overlay -->
    <div id="mobileSidebarOverlay" class="fixed inset-0 bg-black/60 z-[60] hidden md:hidden transition-opacity opacity-0"></div>
    <div id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-[#000107] z-[70] transform -translate-x-full transition-transform duration-300 md:hidden flex flex-col">
        <div class="h-14 flex items-center justify-between px-4 border-b border-ytBorder flex-shrink-0">
            <div class="flex items-center space-x-2">
                <img src="/assets/images/enso8_logo_Slim.png" alt="Enso8 Logo" class="h-8 w-8 object-contain">
                <span class="text-xl font-bold tracking-tighter text-ytText">EnsoFlow</span>
            </div>
            <button id="mobileMenuClose" class="p-1 hover:bg-ytHover rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-ytText">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto py-2" id="mobileNavContainer">
            <!-- Populated by JS -->
        </div>
    </div>
    <!-- Left Sidebar: FIXED position, completely independent from content -->
    <aside id="sidebar" class="hidden md:flex flex-col fixed top-0 left-0 h-screen z-50" style="width: 256px; background-color: #000107;">
            <!-- Sidebar Header / Logo -->
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
            </div>

            <nav class="flex-1 px-3 mt-4 space-y-6 overflow-y-auto custom-scrollbar">
                <?php $currentUri = uri_string(); ?>
                
                <!-- Main Navigation Group -->
                <div class="nav-section">
                    <p class="section-title px-4 text-[11px] font-bold uppercase tracking-wider text-ytMuted mb-2">Main</p>
                    <div class="space-y-0.5">
                        <?php 
                            $dashLink = '/user/dashboard';
                            if (has_any_role(['site_manager', 'admin', 'project_manager'])) $dashLink = '/admin/dashboard';
                            elseif (has_role('client')) $dashLink = '/client/dashboard';
                            $isActive = (strpos($currentUri, ltrim($dashLink, '/')) === 0 || $currentUri === '' || $currentUri === 'admin');
                        ?>
                        <?php if(has_any_role(['site_manager', 'admin'])): ?>
                        <a href="/admin/dashboard" class="<?= (strpos($currentUri, 'admin/dashboard') === 0 || $currentUri === '' || $currentUri === 'admin') ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Executive Dashboard">
                            <span class="material-symbols-outlined <?= (strpos($currentUri, 'admin/dashboard') === 0 || $currentUri === '' || $currentUri === 'admin') ? 'text-ytBlue' : 'text-ytMuted' ?>">dashboard</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                        <?php endif; ?>

                        <?php if(has_role('project_manager') || is_any_supervisor()): ?>
                        <?php $isActive = (strpos($currentUri, 'pm/dashboard') === 0); ?>
                        <a href="/pm/dashboard" class="<?= $isActive ? 'active-nav-item bg-ytHover text-amber-400' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Project Manager Hub">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-amber-400' : 'text-amber-400/80' ?>">shield_person</span>
                            <span class="nav-text">PM Hub</span>
                        </a>
                        <?php endif; ?>

                        <?php if(!has_role('client') && !has_any_role(['admin', 'site_manager'])): ?>
                        <?php $isActive = (strpos($currentUri, 'user/dashboard') === 0); ?>
                        <a href="/user/dashboard" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="My Tasks Workbench">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">task_alt</span>
                            <span class="nav-text">My Tasks</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if(has_any_role(['site_manager', 'admin', 'project_manager', 'collaborator']) || is_any_supervisor()): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/projects') === 0 || strpos($currentUri, 'admin/shots') === 0); ?>
                        <a href="/admin/projects" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Projects">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">video_library</span>
                            <span class="nav-text">Projects</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if(has_any_role(['site_manager', 'admin', 'project_manager', 'collaborator', 'artist'])): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/reviews') === 0); ?>
                        <a href="/admin/reviews" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors relative" title="Review Inbox">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">rate_review</span>
                            <span class="nav-text">Review Inbox</span>
                        </a>
                        <?php endif; ?>

                        <?php if(has_any_role(['site_manager', 'admin', 'project_manager', 'hr'])): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/scheduling') === 0); ?>
                        <a href="/admin/scheduling" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors relative" title="AI Scheduler">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">view_timeline</span>
                            <span class="nav-text">AI Scheduler</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if(has_any_role(['site_manager', 'admin', 'hr', 'it'])): ?>
                <!-- Management & Operations Group -->
                <div class="nav-section">
                    <p class="section-title px-4 text-[11px] font-bold uppercase tracking-wider text-ytMuted mb-2">Operations</p>
                    <div class="space-y-0.5">
                        <?php if(has_any_role(['site_manager', 'admin', 'hr'])): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/users') === 0); ?>
                        <a href="/admin/users" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Team & Roles">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">manage_accounts</span>
                            <span class="nav-text">Team &amp; Roles</span>
                        </a>
                        <?php endif; ?>

                        <?php if(has_any_role(['site_manager', 'admin'])): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/budgeting') === 0); ?>
                        <a href="/admin/budgeting" class="<?= $isActive ? 'active-nav-item bg-ytHover text-green-400' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Studio Budgeting & Monthly Bills">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-green-400' : 'text-green-400/80' ?>">payments</span>
                            <span class="nav-text">Budgeting &amp; Bills</span>
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
                        <?php endif; ?>

                        <?php if(has_any_role(['site_manager', 'it'])): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/database') === 0); ?>
                        <a href="/admin/database" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Database Manager">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">storage</span>
                            <span class="nav-text">Database Center</span>
                        </a>
                        <?php endif; ?>

                        <?php if(has_any_role(['site_manager', 'admin'])): ?>
                        <?php $isActive = (strpos($currentUri, 'admin/notifications/generate') === 0); ?>
                        <a href="/admin/notifications/generate" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Notification Tools">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">notifications_active</span>
                            <span class="nav-text">Notification Tools</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(has_any_role(['site_manager', 'admin', 'project_manager'])): ?>
                <!-- Studio Group -->
                <div class="nav-section">
                    <p class="section-title px-4 text-[11px] font-bold uppercase tracking-wider text-ytMuted mb-2">Studio</p>
                    <div class="space-y-0.5">
                        <?php $isActive = (strpos($currentUri, 'admin/media') === 0); ?>
                        <a href="/admin/media" class="<?= $isActive ? 'active-nav-item bg-ytHover text-ytBlue' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2.5 rounded-lg font-medium text-[15px] transition-colors" title="Media Explorer">
                            <span class="material-symbols-outlined <?= $isActive ? 'text-ytBlue' : 'text-ytMuted' ?>">folder_open</span>
                            <span class="nav-text">Media Explorer</span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            </nav>
            
            <div class="p-3 mt-auto border-t border-ytBorder/40">
                <!-- User Profile & Roles -->
                <div class="flex items-center justify-between px-3 py-2 mb-1 rounded-lg hover:bg-ytHover transition-colors cursor-pointer group">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('userName')) ?>&background=8b5cf6&color=fff&size=64&rounded=true" alt="Profile" class="h-9 w-9 rounded-full flex-shrink-0">
                        <div class="nav-text flex flex-col overflow-hidden min-w-0">
                            <span class="text-[13px] font-medium text-ytText truncate leading-tight"><?= esc(session()->get('userName')) ?></span>
                            <?php $rolesList = get_user_roles(); ?>
                            <span class="text-[10px] text-ytBlue font-mono uppercase truncate leading-tight mt-0.5" title="<?= esc(implode(', ', $rolesList)) ?>">
                                <?= esc(str_replace('_', ' ', implode(' • ', $rolesList))) ?>
                            </span>
                        </div>
                    </div>
                    <a href="/logout" class="nav-text text-ytMuted hover:text-ytRed transition-colors ml-2 flex-shrink-0" title="Sign Out">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </a>
                </div>

                <?php
                    $db = \Config\Database::connect();
                    $currentUserTG = $db->table('users')->where('id', session()->get('userId'))->get()->getRow();
                    $isTelegramLinked = !empty($currentUserTG->telegram_chat_id);
                ?>
                <a href="/telegram/link" target="_blank" class="flex items-center space-x-4 px-4 py-2 rounded-lg transition-colors <?= $isTelegramLinked ? 'text-green-400 hover:bg-green-400/10' : 'text-[#1DA1F2] hover:bg-[#1DA1F2]/10' ?> text-[13px]" title="<?= $isTelegramLinked ? 'Telegram Connected' : 'Connect Telegram' ?>">
                    <span class="material-symbols-outlined text-[18px]"><?= $isTelegramLinked ? 'check_circle' : 'send' ?></span>
                    <span class="nav-text"><?= $isTelegramLinked ? 'Telegram Connected' : 'Connect Telegram' ?></span>
                </a>

                <?php if(has_role('site_manager')): ?>
                <a href="/admin/settings" class="<?= (current_url() == site_url('settings')) ? 'bg-ytHover text-white font-medium' : 'text-ytText hover:bg-ytHover' ?> flex items-center space-x-4 px-4 py-2 rounded-lg transition-colors mt-0.5 text-[13px]" title="Server Settings">
                    <span class="material-symbols-outlined text-ytMuted text-[18px]">settings</span>
                    <span class="nav-text">Site Settings</span>
                </a>
                <?php endif; ?>
            </div>
    </aside>

    <!-- Main Content Area: margin-left matches sidebar width, scrolls independently -->
    <main id="main-content" class="overflow-y-auto bg-ytBg pt-14 pb-16 md:pt-4 md:pb-6 md:ml-[256px] <?= isset($fullScreen) && $fullScreen ? '' : 'px-4 md:px-8' ?>" style="height: 100vh;">
        <!-- Desktop Top Header Bar for Global Notifications & Quick Info -->
        <div class="hidden md:flex items-center justify-end h-10 mb-2 gap-3">
            <div class="relative">
                <button id="sidebar-notification-btn" class="p-2 hover:bg-ytHover rounded-full transition-all relative flex items-center justify-center text-ytMuted hover:text-white outline-none border border-transparent hover:border-ytBorder" title="Notifications">
                    <span class="material-symbols-outlined text-[20px]">notifications</span>
                    <span id="sidebar-notification-badge" class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full min-w-[16px] text-center hidden shadow-[0_0_8px_rgba(239,68,68,0.5)]">0</span>
                </button>
                <!-- Desktop Dropdown Panel -->
                <div id="sidebar-notification-panel" class="absolute right-0 top-full mt-2 w-80 bg-ytCard border border-ytBorder rounded-2xl shadow-2xl hidden flex-col overflow-hidden transform origin-top-right transition-all opacity-0 scale-95 z-[100]" style="backdrop-filter: blur(16px); background-color: rgba(17, 24, 39, 0.98);">
                    <div class="px-4 py-3 border-b border-ytBorder/50 flex justify-between items-center bg-[#0a0d17]/80">
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
                if (window.innerWidth < 768) {
                    if (mainContent) mainContent.style.marginLeft = '0px';
                    return;
                }
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
            
            window.addEventListener('resize', () => {
                applyCollapsedState(sidebar.classList.contains('sidebar-collapsed'));
            });
            
            // Mobile Menu Logic
            const mobileMenuToggle = document.getElementById('mobileTopMenuToggle');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            const mobileSidebar = document.getElementById('mobileSidebar');
            const mobileSidebarOverlay = document.getElementById('mobileSidebarOverlay');
            
            // Copy desktop nav to mobile nav
            const desktopNav = document.querySelector('aside#sidebar nav');
            const mobileNavContainer = document.getElementById('mobileNavContainer');
            if(desktopNav && mobileNavContainer) {
                mobileNavContainer.appendChild(desktopNav.cloneNode(true));
            }
            
            function openMobileMenu() {
                mobileSidebarOverlay.classList.remove('hidden');
                setTimeout(() => mobileSidebarOverlay.classList.remove('opacity-0'), 10);
                mobileSidebar.classList.remove('-translate-x-full');
            }
            
            function closeMobileMenu() {
                mobileSidebar.classList.add('-translate-x-full');
                mobileSidebarOverlay.classList.add('opacity-0');
                setTimeout(() => mobileSidebarOverlay.classList.add('hidden'), 300);
            }
            
            if(mobileMenuToggle) mobileMenuToggle.addEventListener('click', openMobileMenu);
            if(mobileMenuClose) mobileMenuClose.addEventListener('click', closeMobileMenu);
            if(mobileSidebarOverlay) mobileSidebarOverlay.addEventListener('click', closeMobileMenu);
            
            if(menuToggle) {
                menuToggle.addEventListener('click', () => {
                    const nowCollapsed = !sidebar.classList.contains('sidebar-collapsed');
                    applyCollapsedState(nowCollapsed);
                    localStorage.setItem('sidebarCollapsed', nowCollapsed);
                });
            }

            // Profile Dropdown Toggle
            const profileToggle = document.getElementById('profileToggle');
            const profileDropdown = document.getElementById('profileDropdown');

            if(profileToggle && profileDropdown) {
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
            }
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
