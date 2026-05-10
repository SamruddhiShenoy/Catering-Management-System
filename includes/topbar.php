<?php
$title    = $page_title    ?? 'Dashboard';
$subtitle = $page_subtitle ?? "Welcome back! Here's what's happening today.";
$base_url = '/Catering_Management_System';
?>
<div class="topbar">
    <div class="topbar-title">
        <h2 id="topbarTitle"><?= htmlspecialchars($title) ?></h2>
        <p id="topbarSub"><?= htmlspecialchars($subtitle) ?></p>
    </div>
    <div class="topbar-actions">
        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search anything..." id="globalSearch">
        </div>
        <button class="theme-toggle-btn" onclick="toggleTheme()" title="Toggle Light/Dark Mode">
            <i class="fas fa-moon"></i>
        </button>

        <!-- NOTIFICATION BELL -->
        <div style="position:relative;" id="notif-wrapper">
            <button id="notif-btn" onclick="toggleNotifPanel()" 
                    style="background:var(--dark3); border:1px solid var(--border2); border-radius:50%; width:40px; height:40px;
                           display:flex; align-items:center; justify-content:center; cursor:pointer; color:var(--text2);
                           font-size:16px; position:relative; transition:.2s;"
                    onmouseover="this.style.borderColor='var(--gold)'"
                    onmouseout="this.style.borderColor='var(--border2)'">
                <i class="fas fa-bell"></i>
                <span id="notif-count" style="display:none; position:absolute; top:-4px; right:-4px;
                      background:var(--accent); color:#fff; border-radius:50%; width:18px; height:18px;
                      font-size:10px; font-weight:700; align-items:center; justify-content:center;
                      border:2px solid var(--dark2); line-height:1;">0</span>
            </button>

            <!-- DROPDOWN PANEL -->
            <div id="notif-panel" style="display:none; position:absolute; right:0; top:50px; width:360px;
                 background:var(--surface); border:1px solid var(--border2); border-radius:12px;
                 box-shadow:0 20px 60px rgba(0,0,0,0.5); z-index:9999; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--border2); display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:600; font-size:14px; color:var(--text);">🔔 Notifications</span>
                    <button onclick="markAllRead()" style="background:none; border:none; color:var(--gold); font-size:11px; cursor:pointer; font-weight:500;">Mark all read</button>
                </div>
                <div id="notif-list" style="max-height:400px; overflow-y:auto;">
                    <div style="text-align:center; padding:40px 20px; color:var(--text3);">
                        <i class="fas fa-bell-slash" style="font-size:28px; margin-bottom:10px; display:block;"></i>
                        No notifications yet
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
            <a href="<?= $base_url ?>/client/book.php" class="btn btn-gold btn-sm" style="text-decoration:none;">
                <i class="fas fa-plus"></i> New Booking
            </a>
        <?php endif; ?>
    </div>
</div>

<style>
.notif-item { padding:14px 20px; border-bottom:1px solid var(--border2); display:flex; gap:12px; align-items:flex-start; transition:.2s; cursor:default; }
.notif-item:hover { background:var(--dark3); }
.notif-item.unread { background:rgba(212,168,67,0.04); }
.notif-icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; margin-top:2px; }
.notif-icon.success { background:rgba(46,204,138,0.15); }
.notif-icon.info    { background:rgba(91,106,240,0.15); }
.notif-icon.error   { background:rgba(232,64,96,0.15); }
.notif-icon.warning { background:rgba(240,132,76,0.15); }
</style>

<script>
let notifOpen = false;

function toggleNotifPanel() {
    notifOpen = !notifOpen;
    const panel = document.getElementById('notif-panel');
    panel.style.display = notifOpen ? 'block' : 'none';
    if (notifOpen) loadNotifications();
}

// Close on outside click
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('notif-wrapper');
    if (wrapper && !wrapper.contains(e.target) && notifOpen) {
        notifOpen = false;
        document.getElementById('notif-panel').style.display = 'none';
    }
});

function loadNotifications() {
    fetch('/Catering_Management_System/api/notifications.php?action=list')
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('notif-list');
        const notifs = data.notifications || [];
        if (notifs.length === 0) {
            list.innerHTML = '<div style="text-align:center;padding:40px 20px;color:var(--text3);"><i class="fas fa-bell-slash" style="font-size:28px;margin-bottom:10px;display:block;"></i>No notifications yet</div>';
            return;
        }
        const icons = { success:'✅', info:'ℹ️', error:'❌', warning:'⚠️' };
        list.innerHTML = notifs.map(n => `
            <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" ${n.link ? `onclick="window.location='${n.link}'"` : ''} style="${n.link?'cursor:pointer':''}">
                <div class="notif-icon ${n.type}" style="font-size:18px;">${icons[n.type] || 'ℹ️'}</div>
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:600; font-size:13px; color:var(--text); margin-bottom:3px;">${n.title}</div>
                    <div style="font-size:12px; color:var(--text3); line-height:1.4;">${n.message}</div>
                    <div style="font-size:10px; color:var(--text3); margin-top:6px;">${timeAgo(n.created_at)}</div>
                </div>
                ${n.is_read == 0 ? '<div style="width:8px;height:8px;background:var(--gold);border-radius:50%;flex-shrink:0;margin-top:6px;"></div>' : ''}
            </div>
        `).join('');
        // Reset badge after viewing
        document.getElementById('notif-count').style.display = 'none';
    });
}

function markAllRead() {
    fetch('/Catering_Management_System/api/notifications.php?action=mark_read')
    .then(() => { loadNotifications(); checkNotifCount(); });
}

function checkNotifCount() {
    fetch('/Catering_Management_System/api/notifications.php?action=count')
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notif-count');
        if (data.count > 0) {
            badge.textContent = data.count > 9 ? '9+' : data.count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(() => {});
}

function timeAgo(dateStr) {
    const diff = Math.floor((new Date() - new Date(dateStr)) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff/60) + ' min ago';
    if (diff < 86400) return Math.floor(diff/3600) + ' hr ago';
    return Math.floor(diff/86400) + ' day(s) ago';
}

// Poll for new notifications every 30 seconds
checkNotifCount();
setInterval(checkNotifCount, 30000);
</script>
