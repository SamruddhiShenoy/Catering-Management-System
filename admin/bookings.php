<?php
require_once '../config.php';
requireAdmin();

$page_title    = 'Manage Bookings';
$page_subtitle = 'Review, approve and manage all event bookings.';

// Delete booking
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    $_SESSION['toast'] = ['msg' => 'Booking deleted', 'type' => 'info'];
    header("Location: bookings.php");
    exit;
}

// Fetch bookings
$stmt = $pdo->query("
    SELECT b.*, u.name as client_name, u.email as client_email
    FROM bookings b 
    JOIN users u ON b.client_id = u.id 
    ORDER BY b.created DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all menu items for mapping
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
                <!-- Stats strip -->
                <div style="display:flex; gap:16px; margin-bottom:24px;">
                    <?php
                    $counts = ['pending'=>0,'confirmed'=>0,'completed'=>0,'cancelled'=>0];
                    foreach($bookings as $b) { if(isset($counts[$b['status']])) $counts[$b['status']]++; }
                    $colors = ['pending'=>'var(--orange)','confirmed'=>'var(--green)','completed'=>'var(--accent2)','cancelled'=>'var(--accent)'];
                    foreach($counts as $status => $count):
                    ?>
                    <div style="flex:1; background:var(--surface); border:1px solid var(--border2); border-radius:10px; padding:16px 20px;">
                        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:1px;"><?= $status ?></div>
                        <div style="font-size:28px; font-weight:700; color:<?= $colors[$status] ?>;"><?= $count ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="table-card">
                    <div class="card-header">
                        <h3>📋 All Booking Requests</h3>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client Details</th>
                                <th>Event & Venue</th>
                                <th>Date & Guests</th>
                                <th>Package</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $b): ?>
                                <?php
                                    $pkgColor = $b['package'] === 'Elite' ? 'gold' : ($b['package'] === 'Premium' ? 'blue' : 'green');
                                    $payStatus = $b['payment_status'] ?? 'unpaid';
                                    $payColor  = $payStatus === 'paid' ? 'completed' : 'pending';
                                ?>
                                <tr id="row-<?= $b['id'] ?>">
                                    <td><span style="color:var(--gold);font-family:monospace;font-size:12px;"><?= htmlspecialchars($b['id']) ?></span></td>
                                    <td>
                                        <strong style="color:var(--text)"><?= htmlspecialchars($b['client_name']) ?></strong><br>
                                        <small style="color:var(--text3)"><?= htmlspecialchars($b['client_email']) ?></small>
                                    </td>
                                    <td>
                                        <strong style="color:var(--text)"><?= htmlspecialchars($b['event']) ?></strong><br>
                                        <small style="color:var(--text3)"><?= htmlspecialchars($b['venue'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span style="color:var(--gold)"><?= htmlspecialchars($b['date']) ?></span><br>
                                        <small style="color:var(--text3)"><?= $b['guests'] ?> guests</small>
                                    </td>
                                    <td><span class="tag tag-<?= $pkgColor ?>"><?= htmlspecialchars($b['package']) ?></span></td>
                                    <td>
                                        <span style="color:var(--gold);font-weight:600;">Rs.<?= number_format($b['amount']) ?></span><br>
                                        <span class="status-badge <?= $payColor ?>" style="font-size:9px; padding:2px 6px;"><?= strtoupper($payStatus) ?></span>
                                    </td>
                                    <td id="status-<?= $b['id'] ?>">
                                        <span class="status-badge <?= $b['status'] ?>"><?= $b['status'] ?></span>
                                    </td>
                                    <td>
                                        <div class="row-actions" style="flex-wrap:nowrap; gap:6px;">
                                            <button onclick='showDetails(<?= json_encode($b) ?>)' class="btn btn-sm btn-icon" style="background:var(--dark3); color:var(--text2);" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if($b['status'] === 'pending'): ?>
                                                <button onclick="updateStatus('<?= $b['id'] ?>', 'confirmed')" 
                                                        class="btn btn-sm btn-icon" 
                                                        style="background:rgba(46,204,138,0.15); border:1px solid var(--green); color:var(--green);"
                                                        title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button onclick="updateStatus('<?= $b['id'] ?>', 'cancelled')" 
                                                        class="btn btn-sm btn-icon" 
                                                        style="background:rgba(232,64,96,0.15); border:1px solid var(--red); color:var(--red);"
                                                        title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>

                                            <a href="generate_invoice.php?id=<?= htmlspecialchars($b['id']) ?>" 
                                               target="_blank" class="btn btn-sm btn-gold btn-icon" title="Invoice">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </a>
                                            
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this booking?');">
                                                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($b['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DETAILS MODAL -->
<div id="detailsModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
    <div class="modal-content" style="background:var(--surface); padding:30px; border-radius:15px; width:95%; max-width:700px; border:1px solid var(--border2); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="color:var(--gold);">Booking Details — <span id="det_id"></span></h3>
            <button onclick="closeModal()" class="btn btn-xs btn-ghost">&times;</button>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="info-group">
                <label style="display:block; font-size:11px; color:var(--text3); text-transform:uppercase; margin-bottom:5px;">Selected Menu Items</label>
                <div id="det_menu" style="display:flex; flex-wrap:wrap; gap:8px;"></div>
            </div>
            
            <div class="info-group">
                <label style="display:block; font-size:11px; color:var(--text3); text-transform:uppercase; margin-bottom:5px;">Client Requests / Notes</label>
                <div style="background:var(--dark3); padding:12px; border-radius:8px; font-size:13px; color:var(--text2); min-height:60px;">
                    <strong>Original:</strong> <span id="det_notes"></span><br><br>
                    <strong>Update Note:</strong> <span id="det_edit_notes" style="color:var(--gold);"></span>
                </div>
            </div>
        </div>

        <div style="margin-top:20px; border-top:1px solid var(--border2); padding-top:20px;">
            <label style="display:block; font-size:11px; color:var(--text3); text-transform:uppercase; margin-bottom:10px;">Client Feedback & Rating</label>
            <div id="det_feedback_wrap" style="background:rgba(46,204,138,0.05); border:1px solid rgba(46,204,138,0.2); padding:15px; border-radius:10px; display:none;">
                <div id="det_rating" style="margin-bottom:10px;"></div>
                <p id="det_feedback" style="font-style:italic; color:var(--text);"></p>
            </div>
            <p id="no_feedback" style="color:var(--text3); font-size:13px;">No feedback received yet.</p>
        </div>

        <div style="margin-top:30px; text-align:right;">
            <button onclick="closeModal()" class="btn btn-ghost">Close Window</button>
        </div>
    </div>
</div>

<script>
const menuMap = <?= json_encode($menu_map) ?>;

function showDetails(b) {
    document.getElementById('det_id').textContent = b.id;
    document.getElementById('det_notes').textContent = b.notes || 'None';
    document.getElementById('det_edit_notes').textContent = b.edit_notes || 'No change requests yet.';
    
    // Menu list
    const menuDiv = document.getElementById('det_menu');
    menuDiv.innerHTML = '';
    if(b.selected_menu) {
        b.selected_menu.split(',').forEach(mid => {
            const item = menuMap[mid];
            if(item) {
                menuDiv.innerHTML += `<span style="background:var(--dark3); padding:4px 10px; border-radius:15px; font-size:11px; color:var(--text2); border:1px solid var(--border2);">${item.emoji} ${item.name}</span>`;
            }
        });
    } else {
        menuDiv.innerHTML = '<span style="color:var(--text3)">No items selected.</span>';
    }

    // Feedback
    if(b.feedback) {
        document.getElementById('det_feedback_wrap').style.display = 'block';
        document.getElementById('no_feedback').style.display = 'none';
        document.getElementById('det_feedback').textContent = `"${b.feedback}"`;
        let stars = '';
        for(let i=1; i<=5; i++) {
            stars += `<i class="fas fa-star" style="color:${i <= b.rating ? 'var(--gold)' : 'var(--dark5)'}; margin-right:3px;"></i>`;
        }
        document.getElementById('det_rating').innerHTML = stars;
    } else {
        document.getElementById('det_feedback_wrap').style.display = 'none';
        document.getElementById('no_feedback').style.display = 'block';
    }

    document.getElementById('detailsModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

function updateStatus(id, newStatus) {
    if(!confirm('Are you sure you want to ' + (newStatus === 'confirmed' ? 'Approve' : 'Reject') + ' this booking?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', newStatus);
    
    fetch('api_update_status.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            toast(data.msg, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            toast(data.msg, 'error');
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
