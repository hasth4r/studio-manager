<!-- Floating Notifications Widget (Mobile Only) -->
<div class="fixed top-[7px] right-[60px] md:hidden z-[60]">
    <div class="relative">
        <button id="notification-btn" class="p-2.5 bg-ytCard border border-ytBorder hover:bg-ytHover hover:border-ytBlue rounded-full shadow-lg transition-all relative flex items-center justify-center group outline-none">
            <span class="material-symbols-outlined text-ytMuted group-hover:text-ytText text-[22px]">notifications</span>
            <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center hidden shadow-[0_0_8px_rgba(239,68,68,0.5)]">0</span>
        </button>

        <!-- Dropdown Panel -->
        <div id="notification-panel" class="absolute -right-[44px] mt-3 w-[calc(100vw-32px)] bg-ytCard border border-ytBorder rounded-xl shadow-2xl hidden flex-col overflow-hidden transform origin-top-right transition-all opacity-0 scale-95" style="backdrop-filter: blur(10px);">
            <div class="px-4 py-3 border-b border-ytBorder/50 flex justify-between items-center bg-[#0a0d17]/50">
                <h3 class="text-[14px] font-bold text-ytText">Notifications</h3>
                <span class="text-[11px] text-ytMuted uppercase tracking-wider">Unread</span>
            </div>
            <div id="notification-list" class="max-h-96 overflow-y-auto">
                <!-- Items injected here -->
                <div class="p-6 text-center text-ytMuted text-[13px]">
                    <span class="material-symbols-outlined text-[32px] mb-2 opacity-50">notifications_paused</span>
                    <p>All caught up!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Mobile Elements
    const mobBtn = document.getElementById('notification-btn');
    const mobPanel = document.getElementById('notification-panel');
    const mobBadge = document.getElementById('notification-badge');
    const mobList = document.getElementById('notification-list');

    // Desktop Sidebar Elements
    const deskBtn = document.getElementById('sidebar-notification-btn');
    const deskPanel = document.getElementById('sidebar-notification-panel');
    const deskBadge = document.getElementById('sidebar-notification-badge');
    const deskList = document.getElementById('sidebar-notification-list');
    
    let lastSeenId = 0;

    function setupDropdown(trigger, panel) {
        if (!trigger || !panel) return;
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                setTimeout(() => {
                    panel.classList.remove('opacity-0', 'scale-95');
                    panel.classList.add('opacity-100', 'scale-100');
                }, 10);
            } else {
                panel.classList.remove('opacity-100', 'scale-100');
                panel.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    panel.classList.add('hidden');
                }, 150);
            }
        });

        document.addEventListener('click', (e) => {
            if (!trigger.contains(e.target) && !panel.contains(e.target) && !panel.classList.contains('hidden')) {
                panel.classList.remove('opacity-100', 'scale-100');
                panel.classList.add('opacity-0', 'scale-95');
                setTimeout(() => panel.classList.add('hidden'), 150);
            }
        });
    }

    setupDropdown(mobBtn, mobPanel);
    setupDropdown(deskBtn, deskPanel);

    // Request Windows/Browser Notification Permission
    if ("Notification" in window) {
        if (Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    function updateBadges(unreadCount) {
        const text = unreadCount > 9 ? '9+' : unreadCount;
        [mobBadge, deskBadge].forEach(b => {
            if (b) {
                if (unreadCount > 0) {
                    b.textContent = text;
                    b.classList.remove('hidden');
                } else {
                    b.classList.add('hidden');
                }
            }
        });
    }

    function updateLists(html) {
        [mobList, deskList].forEach(l => {
            if (l) l.innerHTML = html;
        });
    }

    function fetchNotifications() {
        fetch('/notifications/getUnread')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.notifications) {
                    const unreadCount = data.notifications.length;
                    updateBadges(unreadCount);
                    
                    if (unreadCount > 0) {
                        let html = '';
                        let highestIdThisFetch = lastSeenId;
                        let hasNew = false;
                        
                        data.notifications.forEach(n => {
                            if (parseInt(n.id) > lastSeenId) {
                                hasNew = true;
                                highestIdThisFetch = Math.max(highestIdThisFetch, parseInt(n.id));
                            }
                            
                            // Map types to icons/colors
                            let icon = 'notifications';
                            let color = 'text-ytBlue';
                            
                            if (n.type === 'task_assigned') {
                                icon = 'assignment_ind';
                                color = 'text-blue-400';
                            } else if (n.type === 'review_submitted') {
                                icon = 'rate_review';
                                color = 'text-purple-400';
                            } else if (n.type === 'review_status') {
                                icon = n.message.includes('Approved') ? 'check_circle' : 'change_circle';
                                color = n.message.includes('Approved') ? 'text-green-400' : 'text-orange-400';
                            }
                            
                            const link = n.link ? `/notifications/read/${n.id}` : '#';
                            
                            html += `
                                <a href="${link}" class="block p-3.5 border-b border-ytBorder/30 hover:bg-ytHover transition-colors">
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <span class="material-symbols-outlined ${color} text-[18px] bg-[#1a1a1a] p-1.5 rounded-full border border-ytBorder">${icon}</span>
                                        </div>
                                        <div class="overflow-hidden flex-1 min-w-0">
                                            <p class="text-[13px] font-bold text-ytText truncate">${n.title}</p>
                                            <p class="text-[11px] text-ytMuted leading-tight mt-0.5 line-clamp-2">${n.message}</p>
                                        </div>
                                    </div>
                                </a>
                            `;
                        });
                        
                        updateLists(html);
                        
                        // Fire native Windows popup for new notifications
                        if (hasNew && lastSeenId !== 0 && "Notification" in window && Notification.permission === "granted") {
                            const newest = data.notifications.find(n => parseInt(n.id) === highestIdThisFetch);
                            if (newest) {
                                new Notification(newest.title, {
                                    body: newest.message,
                                    icon: '/assets/images/enso8_logo_Slim.png'
                                });
                            }
                        }
                        
                        lastSeenId = highestIdThisFetch;
                        
                    } else {
                        updateLists(`
                            <div class="p-6 text-center text-ytMuted text-[13px]">
                                <span class="material-symbols-outlined text-[32px] mb-2 opacity-50">notifications_paused</span>
                                <p>All caught up!</p>
                            </div>
                        `);
                    }
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
    }

    // Initial fetch
    fetchNotifications();

    // Poll every 15 seconds
    setInterval(fetchNotifications, 15000);
});
</script>
