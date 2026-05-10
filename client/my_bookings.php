<?php
require_once '../config.php';
requireLogin();

$page_title    = 'My Bookings';
$page_subtitle = 'Track all your event bookings, view invoices and manage requests.';

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE client_id = ? ORDER BY created DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all menu items for the "View Details" mapping
$stmt = $pdo->query("SELECT id, name, emoji FROM menu_items");
$all_menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
$menu_map = [];
foreach($all_menu as $m) { $menu_map[$m['id']] = $m; }

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            <div class="content-area">

                <?php if(empty($bookings)): ?>
                <div style="text-align:center; padding:80px 20px; color:var(--text3);">
                    <i class="fas fa-calendar-times" style="font-size:56px; margin-bottom:20px; display:block; color:var(--dark5);"></i>
                    <h3 style="color:var(--text2); margin-bottom:8px;">No bookings yet</h3>
                    <p>Start planning your perfect event!</p>
                    <a href="book.php" class="btn btn-gold" style="margin-top:20px; display:inline-block; text-decoration:none;">
                        <i class="fas fa-magic"></i> Book Your First Event
                    </a>
                </div>
                <?php else: ?>
                <div class="table-card">
                    <div class="card-header">
                        <h3>🗓️ My Event Bookings</h3>
                        <a href="book.php" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> New Booking</a>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Event & Venue</th>
                                <th>Date</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $b): ?>
                                <?php
                                $status      = $b['status'] ?? 'pending';
                                $payStatus   = $b['payment_status'] ?? 'unpaid';
                                $approved    = ($status === 'confirmed' || $status === 'completed');
                                $hasPaidAtLeastAdvance = ($payStatus === 'paid' || $payStatus === 'advance_paid');
                                ?>
                                <tr>
                                    <td><span style="color:var(--gold);font-family:monospace;font-size:12px;"><?= htmlspecialchars($b['id']) ?></span></td>
                                    <td>
                                        <strong style="color:var(--text)"><?= htmlspecialchars($b['event']) ?></strong><br>
                                        <small style="color:var(--text3)"><?= htmlspecialchars($b['venue'] ?? '—') ?></small>
                                    </td>
                                    <td style="color:var(--gold)"><?= htmlspecialchars($b['date']) ?></td>
                                    <td>
                                        <div style="font-size:12px; color:var(--text2);">
                                            Guests: <?= $b['guests'] ?><br>
                                            Pkg: <span style="color:var(--gold)"><?= htmlspecialchars($b['package']) ?></span>
                                        </div>
                                        <button onclick='showMenu("<?= $b['id'] ?>", <?= json_encode($b['selected_menu']) ?>)' class="btn btn-xs btn-ghost" style="padding:2px 8px; margin-top:5px; font-size:10px;">View Menu</button>
                                    </td>
                                    <td>
                                        <?php if($status === 'pending'): ?>
                                            <span class="status-badge pending">⏳ Awaiting</span>
                                        <?php elseif($status === 'confirmed'): ?>
                                            <span class="status-badge completed">✅ Approved</span>
                                        <?php elseif($status === 'cancelled'): ?>
                                            <span class="status-badge cancelled">❌ Rejected</span>
                                        <?php else: ?>
                                            <span class="status-badge completed"><?= ucfirst($status) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($payStatus === 'paid'): ?>
                                            <span class="status-badge completed">✅ FULLY PAID</span>
                                        <?php elseif($payStatus === 'advance_paid'): ?>
                                            <div style="margin-bottom:5px;"><span class="status-badge completed" style="background:rgba(46,204,138,0.1); color:var(--green); font-size:9px;">ADVANCE PAID</span></div>
                                            <a href="checkout.php?id=<?= urlencode($b['id']) ?>" 
                                               class="btn btn-xs btn-gold" style="text-decoration:none; padding:4px 8px;">
                                                Pay Balance: Rs.<?= number_format($b['amount'] - $b['paid_amount']) ?>
                                            </a>
                                        <?php elseif($approved): ?>
                                            <a href="checkout.php?id=<?= urlencode($b['id']) ?>" 
                                               class="btn btn-xs btn-gold" style="text-decoration:none; padding:4px 8px;">
                                                Pay Rs.<?= number_format($b['amount']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color:var(--text3); font-size:12px;"><i class="fas fa-lock"></i> Locked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:8px;">
                                            <a href="../admin/generate_invoice.php?id=<?= $b['id'] ?>" target="_blank" class="btn btn-sm btn-icon" style="background:var(--dark3); color:var(--gold);" title="Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                            <?php if($hasPaidAtLeastAdvance): ?>
                                                <button onclick="openChat('<?= $b['id'] ?>')" class="btn btn-sm btn-icon" style="background:var(--dark3); color:var(--accent2);" title="Message Admin">
                                                    <i class="fas fa-comment-dots"></i>
                                                </button>
                                                <?php if($payStatus === 'paid'): ?>
                                                    <button onclick="openFeedbackModal('<?= $b['id'] ?>', '<?= addslashes($b['feedback'] ?? '') ?>', <?= $b['rating'] ?>)" class="btn btn-sm btn-icon" style="background:var(--dark3); color:var(--green);" title="Feedback">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- MODALS -->
<div id="menuModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
    <div class="modal-content" style="background:var(--surface); padding:30px; border-radius:15px; width:90%; max-width:500px; border:1px solid var(--border2);">
        <h3 id="modalTitle" style="margin-bottom:20px; color:var(--gold);">Selected Menu Items</h3>
        <div id="menuList" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px;"></div>
        <button onclick="closeModal('menuModal')" class="btn btn-ghost" style="width:100%">Close</button>
    </div>
</div>

<div id="noteModal" style="display:none;"></div><!-- legacy placeholder -->

<!-- CHAT MODAL -->
<style>
.chat-bubble-wrap { display:flex; flex-direction:column; gap:10px; }
.chat-bubble { max-width:75%; padding:9px 13px; border-radius:12px; font-size:13px; line-height:1.5; }
.chat-bubble.mine   { background:var(--gold); color:#1a1208; align-self:flex-end; border-bottom-right-radius:3px; }
.chat-bubble.theirs { background:var(--dark3); color:var(--text); align-self:flex-start; border:1px solid var(--border2); border-bottom-left-radius:3px; }
.chat-meta { font-size:10px; opacity:.6; margin-top:3px; }
</style>

<div id="chatModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:var(--surface); border-radius:15px; width:90%; max-width:560px; border:1px solid var(--border2); display:flex; flex-direction:column; overflow:hidden; max-height:85vh;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border2); display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h3 style="margin:0; color:var(--gold); font-size:15px;">💬 Message Admin</h3>
                <div style="font-size:11px; color:var(--text3); margin-top:2px;" id="chatBookingLabel">Booking #...</div>
            </div>
            <button onclick="closeChat()" style="background:none; border:none; color:var(--text3); font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div id="chatMsgsClient" class="chat-bubble-wrap" style="flex:1; overflow-y:auto; padding:18px; min-height:220px; max-height:400px;"></div>
        <div style="padding:12px 16px; border-top:1px solid var(--border2); display:flex; gap:8px; align-items:flex-end;">
            <textarea id="clientMsgInput" placeholder="Type your message... (Enter to send)" rows="2"
                style="flex:1; background:var(--dark3); border:1px solid var(--border2); border-radius:8px; color:var(--text); padding:10px 12px; font-size:13px; resize:none; outline:none; font-family:inherit;"
                onkeydown="handleClientKey(event)"></textarea>
            <button class="btn btn-gold" onclick="sendClientMsg()" style="padding:10px 16px; height:42px;"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<div id="feedbackModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
    <div class="modal-content" style="background:var(--surface); padding:30px; border-radius:15px; width:90%; max-width:500px; border:1px solid var(--border2);">
        <h3 style="margin-bottom:10px; color:var(--gold);">Share Your Feedback</h3>
        <p style="font-size:12px; color:var(--text3); margin-bottom:15px;">How was your experience with CaterBook?</p>
        <input type="hidden" id="feedback_booking_id">
        <div style="margin-bottom:15px; display:flex; gap:5px; justify-content:center;">
            <?php for($i=1; $i<=5; $i++): ?>
                <i class="fas fa-star rating-star" data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)" style="font-size:24px; cursor:pointer; color:var(--dark5);"></i>
            <?php endfor; ?>
        </div>
        <textarea id="feedback_text" style="width:100%; height:100px; background:var(--dark3); border:1px solid var(--border2); border-radius:8px; color:var(--text); padding:12px; outline:none; margin-bottom:20px;" placeholder="Tell us more..."></textarea>
        <div style="display:flex; gap:10px;">
            <button onclick="closeModal('feedbackModal')" class="btn btn-ghost" style="flex:1">Cancel</button>
            <button onclick="saveExtra('feedback', this)" class="btn btn-gold" style="flex:1">Submit Feedback</button>
        </div>
    </div>
</div>

<script>
const menuMap = <?= json_encode($menu_map) ?>;
let currentRating = 0;

function showMenu(id, selectedStr) {
    const list = document.getElementById('menuList');
    list.innerHTML = '';
    document.getElementById('modalTitle').textContent = 'Menu for ' + id;
    
    if(!selectedStr) {
        list.innerHTML = '<p style="grid-column:1/-1; color:var(--text3)">No items selected.</p>';
    } else {
        selectedStr.split(',').forEach(mid => {
            const item = menuMap[mid];
            if(item) {
                list.innerHTML += `<div style="background:var(--dark3); padding:8px 12px; border-radius:8px; display:flex; align-items:center; gap:8px;">
                    <span style="font-size:20px;">${item.emoji}</span>
                    <span style="font-size:12px; color:var(--text2);">${item.name}</span>
                </div>`;
            }
        });
    }
    document.getElementById('menuModal').style.display = 'flex';
}

function openNoteModal(id, current) {
    document.getElementById('note_booking_id').value = id;
    document.getElementById('edit_notes_text').value = current;
    document.getElementById('noteModal').style.display = 'flex';
}

function openFeedbackModal(id, current, rating) {
    document.getElementById('feedback_booking_id').value = id;
    document.getElementById('feedback_text').value = current;
    setRating(rating || 0);
    document.getElementById('feedbackModal').style.display = 'flex';
}

function setRating(v) {
    currentRating = v;
    document.querySelectorAll('.rating-star').forEach(s => {
        const val = parseInt(s.dataset.val);
        s.style.color = val <= v ? 'var(--gold)' : 'var(--dark5)';
    });
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function saveExtra(type, btn) {
    const id = type === 'note' ? document.getElementById('note_booking_id').value : document.getElementById('feedback_booking_id').value;
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = 'Sending...';

    const body = new FormData();
    body.append('booking_id', id);
    
    if(type === 'note') {
        body.append('edit_notes', document.getElementById('edit_notes_text').value);
    } else {
        body.append('feedback', document.getElementById('feedback_text').value);
        body.append('rating', currentRating);
    }

    fetch('/Catering_Management_System/api/save_booking_extra.php', { method: 'POST', body: body })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            console.error('Server response was not JSON:', text);
            throw new Error('Server error: ' + text.substring(0, 50));
        }
    })
    .then(data => {
        if(data.success) {
            toast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(data.message || 'Action failed.');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.textContent = originalText;
    });
}

// ── CHAT ─────────────────────────────────────────────────────────────
let chatBookingId = null;
let chatPollTimer = null;

function openChat(bookingId) {
    chatBookingId = bookingId;
    document.getElementById('chatBookingLabel').textContent = 'Booking #' + bookingId;
    document.getElementById('chatModal').style.display = 'flex';
    document.getElementById('clientMsgInput').focus();
    fetchClientMessages();
    clearInterval(chatPollTimer);
    chatPollTimer = setInterval(fetchClientMessages, 4000);
}

function closeChat() {
    document.getElementById('chatModal').style.display = 'none';
    clearInterval(chatPollTimer);
    chatBookingId = null;
}

function fetchClientMessages() {
    if (!chatBookingId) return;
    fetch('/Catering_Management_System/api/get_messages.php?booking_id=' + chatBookingId)
    .then(r => r.json())
    .then(data => { if (data.success) renderClientMessages(data.messages); });
}

function renderClientMessages(msgs) {
    const box = document.getElementById('chatMsgsClient');
    const atBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 60;
    box.innerHTML = '';
    if (!msgs.length) {
        box.innerHTML = '<div style="text-align:center;color:var(--text3);font-size:13px;padding:30px;">No messages yet. Start the conversation!</div>';
        return;
    }
    msgs.forEach(m => {
        const isMine = m.sender_role === 'client';
        const wrap = document.createElement('div');
        wrap.style.display = 'flex';
        wrap.style.flexDirection = 'column';
        wrap.style.alignItems = isMine ? 'flex-end' : 'flex-start';
        const time = new Date(m.created_at).toLocaleString('en-IN', {day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'});
        wrap.innerHTML = `<div class="chat-bubble ${isMine ? 'mine' : 'theirs'}">
            ${escHtml(m.message)}
            <div class="chat-meta">${isMine ? 'You' : 'Admin'} · ${time}</div>
        </div>`;
        box.appendChild(wrap);
    });
    if (atBottom) box.scrollTop = box.scrollHeight;
}

function sendClientMsg() {
    const input = document.getElementById('clientMsgInput');
    const msg = input.value.trim();
    if (!msg || !chatBookingId) return;
    input.value = '';
    const body = new FormData();
    body.append('booking_id', chatBookingId);
    body.append('message', msg);
    fetch('/Catering_Management_System/api/send_message.php', {method:'POST', body})
    .then(r => r.json())
    .then(data => { if (data.success) fetchClientMessages(); else alert(data.message); });
}

function handleClientKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendClientMsg(); }
}

function escHtml(t) {
    return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>

<?php require_once '../includes/footer.php'; ?>
