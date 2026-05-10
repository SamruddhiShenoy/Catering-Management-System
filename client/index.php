<?php
require_once '../config.php';
requireLogin();

$page_title = 'My Dashboard';
$page_subtitle = 'Welcome back, ' . htmlspecialchars($_SESSION['user_name']) . '!';

// Fetch client bookings
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE client_id = ? ORDER BY date ASC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div style="margin-bottom:20px; display:flex; gap:10px;">
                    <a href="book.php" class="btn btn-gold btn-sm"><i class="fas fa-magic"></i> Book New Event</a>
                    <a href="menu.php" class="btn btn-ghost btn-sm"><i class="fas fa-utensils"></i> Browse Menu</a>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div style="text-align:center;padding:80px;color:var(--text3);">
                        <i class="fas fa-calendar-times" style="font-size:48px;margin-bottom:20px;display:block;color:var(--dark5)"></i>
                        <h3 style="color:var(--text2);margin-bottom:8px">No bookings yet</h3>
                        <p>Start planning your perfect event!</p>
                        <a href="book.php" class="btn btn-gold" style="margin-top:20px;display:inline-block;text-decoration:none;"><i class="fas fa-plus"></i> Book Your First Event</a>
                    </div>
                <?php else: ?>
                    <div class="table-card">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Venue</th>
                                    <th>Guests</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bookings as $b): ?>
                                    <?php 
                                        $pkgColor = $b['package'] === 'Elite' ? 'gold' : ($b['package'] === 'Premium' ? 'blue' : 'green');
                                        $payColor = ($b['payment_status'] ?? 'unpaid') === 'paid' ? 'completed' : 'pending';
                                    ?>
                                    <tr>
                                        <td><span style="color:var(--gold);font-family:monospace"><?= htmlspecialchars($b['id']) ?></span></td>
                                        <td><strong style="color:var(--text)"><?= htmlspecialchars($b['event']) ?></strong></td>
                                        <td style="color:var(--gold)"><?= htmlspecialchars($b['date']) ?></td>
                                        <td><?= htmlspecialchars($b['venue']) ?></td>
                                        <td><?= htmlspecialchars($b['guests']) ?></td>
                                        <td><span class="tag tag-<?= $pkgColor ?>"><?= htmlspecialchars($b['package']) ?></span></td>
                                        <td style="color:var(--gold);font-weight:600">Rs.<?= number_format($b['amount'], 2) ?></td>
                                        <td>
                                            <?php 
                                            $payStat = $b['payment_status'] ?? 'unpaid';
                                            if($payStat === 'paid'): ?>
                                                <span class="status-badge completed">PAID</span>
                                            <?php elseif($payStat === 'advance_paid'): ?>
                                                <a href="checkout.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-gold" style="font-size:10px; padding:4px 8px;">Pay Balance: Rs.<?= number_format($b['amount'] - $b['paid_amount']) ?></a>
                                            <?php else: ?>
                                                <a href="checkout.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-gold">Pay Now</a>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="status-badge <?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
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

<?php require_once '../includes/footer.php'; ?>
