<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Client Messages';
$page_subtitle = 'Chat with clients about their bookings';

// Get all bookings that have messages OR fetch all bookings grouped for sidebar
$conversations = $pdo->query("
    SELECT b.id AS booking_id, b.event, b.date,
           u.name AS client_name, u.email AS client_email,
           COUNT(m.id) AS total_msgs,
           SUM(CASE WHEN m.is_read = 0 AND m.sender_role = 'client' THEN 1 ELSE 0 END) AS unread,
           MAX(m.created_at) AS last_msg_at,
           (SELECT message FROM messages WHERE booking_id = b.id ORDER BY created_at DESC LIMIT 1) AS last_msg
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    LEFT JOIN messages m ON m.booking_id = b.id
    GROUP BY b.id
    HAVING total_msgs > 0
    ORDER BY last_msg_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<style>
.chat-layout   { display:flex; height: calc(100vh - 160px); gap:0; background:var(--surface); border-radius:14px; border:1px solid var(--border2); overflow:hidden; }
.chat-sidebar  { width:300px; border-right:1px solid var(--border2); display:flex; flex-direction:column; flex-shrink:0; }
.chat-sidebar-header { padding:18px 16px; border-bottom:1px solid var(--border2); }
.chat-sidebar-header h3 { font-size:15px; margin:0; color:var(--text); }
.conv-list     { overflow-y:auto; flex:1; }
.conv-item     { padding:14px 16px; border-bottom:1px solid var(--border2); cursor:pointer; transition:.2s; }
.conv-item:hover, .conv-item.active { background:rgba(212,168,67,.08); }
.conv-name     { font-weight:600; font-size:13px; color:var(--text); }
.conv-sub      { font-size:11px; color:var(--text3); margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px; }
.conv-badge    { background:var(--gold); color:#000; font-size:10px; font-weight:700; border-radius:99px; padding:2px 7px; }
.chat-main     { flex:1; display:flex; flex-direction:column; }
.chat-topbar   { padding:14px 20px; border-bottom:1px solid var(--border2); display:flex; align-items:center; gap:10px; }
.chat-msgs     { flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:12px; }
.chat-empty    { flex:1; display:flex; align-items:center; justify-content:center; color:var(--text3); font-size:14px; flex-direction:column; gap:10px; }
.msg-bubble    { max-width:65%; padding:10px 14px; border-radius:14px; font-size:13px; line-height:1.5; position:relative; }
.msg-admin     { background:var(--gold); color:#1a1208; border-bottom-right-radius:4px; align-self:flex-end; }
.msg-client    { background:var(--dark3); color:var(--text); border-bottom-left-radius:4px; align-self:flex-start; border:1px solid var(--border2); }
.msg-meta      { font-size:10px; margin-top:4px; opacity:.6; }
.chat-input-bar { padding:14px 20px; border-top:1px solid var(--border2); display:flex; gap:10px; align-items:center; }
.chat-input-bar textarea { flex:1; background:var(--dark3); border:1px solid var(--border2); border-radius:10px; color:var(--text); padding:10px 14px; font-size:13px; resize:none; outline:none; font-family:inherit; height:42px; line-height:1.4; }
.chat-input-bar textarea:focus { border-color:var(--gold); }
</style>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            <div class="content-area">

                <div class="chat-layout">
                    <!-- Sidebar: conversation list -->
                    <div class="chat-sidebar">
                        <div class="chat-sidebar-header">
                            <h3>💬 Client Conversations</h3>
                            <div style="font-size:11px; color:var(--text3); margin-top:4px;"><?= count($conversations) ?> active threads</div>
                        </div>
                        <div class="conv-list">
                            <?php if(empty($conversations)): ?>
                                <div style="padding:30px 16px; text-align:center; color:var(--text3); font-size:13px;">
                                    No messages yet. Clients will appear here when they send a note.
                                </div>
                            <?php else: ?>
                                <?php foreach($conversations as $c): ?>
                                <div class="conv-item" onclick="loadChat('<?= $c['booking_id'] ?>', '<?= htmlspecialchars($c['client_name']) ?>', '<?= htmlspecialchars($c['event']) ?>')" id="conv-<?= $c['booking_id'] ?>">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                        <div class="conv-name"><?= htmlspecialchars($c['client_name']) ?></div>
                                        <?php if($c['unread'] > 0): ?>
                                            <span class="conv-badge"><?= $c['unread'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conv-sub" style="color:var(--gold); font-size:10px;"><?= htmlspecialchars($c['event']) ?> · <?= $c['booking_id'] ?></div>
                                    <div class="conv-sub" style="margin-top:4px;"><?= htmlspecialchars(substr($c['last_msg'] ?? '', 0, 50)) ?>...</div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Main chat area -->
                    <div class="chat-main" id="chatMain">
                        <div class="chat-empty" id="chatEmpty">
                            <i class="fas fa-comments" style="font-size:48px; color:var(--dark5);"></i>
                            <span>Select a conversation to start chatting</span>
                        </div>
                        <div id="chatActive" style="display:none; flex:1; display:none; flex-direction:column;">
                            <div class="chat-topbar" id="chatTopbar">
                                <div style="width:36px; height:36px; border-radius:50%; background:var(--gold); display:flex; align-items:center; justify-content:center; color:#1a1208; font-weight:700;" id="chatAvatar">A</div>
                                <div>
                                    <div style="font-weight:600; font-size:14px; color:var(--text);" id="chatClientName">Client</div>
                                    <div style="font-size:11px; color:var(--text3);" id="chatBookingLabel">Booking</div>
                                </div>
                            </div>
                            <div class="chat-msgs" id="chatMsgs"></div>
                            <div class="chat-input-bar">
                                <textarea id="adminMsgInput" placeholder="Type a reply..." onkeydown="handleAdminKey(event)"></textarea>
                                <button class="btn btn-gold" onclick="sendAdminMsg()" style="padding:10px 18px; height:42px;">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let activeBid = null;
let pollTimer = null;

function loadChat(bookingId, clientName, eventName) {
    activeBid = bookingId;

    // Mark conv item active
    document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
    const convEl = document.getElementById('conv-' + bookingId);
    if (convEl) { convEl.classList.add('active'); convEl.querySelector('.conv-badge') && (convEl.querySelector('.conv-badge').remove()); }

    document.getElementById('chatEmpty').style.display = 'none';
    const ca = document.getElementById('chatActive');
    ca.style.display = 'flex';
    ca.style.flexDirection = 'column';

    document.getElementById('chatClientName').textContent = clientName;
    document.getElementById('chatBookingLabel').textContent = eventName + ' · ' + bookingId;
    document.getElementById('chatAvatar').textContent = clientName.charAt(0).toUpperCase();

    fetchMessages();
    clearInterval(pollTimer);
    pollTimer = setInterval(fetchMessages, 4000);
}

function fetchMessages() {
    if (!activeBid) return;
    fetch('/Catering_Management_System/api/get_messages.php?booking_id=' + activeBid)
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        renderMessages(data.messages);
    });
}

function renderMessages(msgs) {
    const box = document.getElementById('chatMsgs');
    const wasAtBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 60;
    box.innerHTML = '';
    if (!msgs.length) {
        box.innerHTML = '<div style="text-align:center; color:var(--text3); font-size:13px; padding:30px;">No messages yet.</div>';
        return;
    }
    msgs.forEach(m => {
        const isAdmin = m.sender_role === 'admin';
        const div = document.createElement('div');
        div.style.display = 'flex';
        div.style.flexDirection = 'column';
        div.style.alignItems = isAdmin ? 'flex-end' : 'flex-start';
        const time = new Date(m.created_at).toLocaleTimeString('en-IN', {hour:'2-digit', minute:'2-digit'});
        const date = new Date(m.created_at).toLocaleDateString('en-IN', {day:'numeric', month:'short'});
        div.innerHTML = `
            <div class="msg-bubble ${isAdmin ? 'msg-admin' : 'msg-client'}">
                ${escHtml(m.message)}
                <div class="msg-meta">${isAdmin ? 'Admin' : escHtml(m.sender_name)} · ${date} ${time}</div>
            </div>`;
        box.appendChild(div);
    });
    if (wasAtBottom) box.scrollTop = box.scrollHeight;
}

function sendAdminMsg() {
    const input = document.getElementById('adminMsgInput');
    const msg = input.value.trim();
    if (!msg || !activeBid) return;
    input.value = '';

    const body = new FormData();
    body.append('booking_id', activeBid);
    body.append('message', msg);

    fetch('/Catering_Management_System/api/send_message.php', {method:'POST', body})
    .then(r => r.json())
    .then(data => {
        if (data.success) fetchMessages();
        else alert(data.message);
    });
}

function handleAdminKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendAdminMsg(); }
}

function escHtml(t) {
    return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require_once '../includes/footer.php'; ?>
