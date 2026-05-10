<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Admin Dashboard';
$page_subtitle = 'Overview of all catering operations.';

// Fetch some metrics
$stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$total_bookings = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(amount) FROM bookings WHERE status IN ('completed', 'confirmed')");
$revenue = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'");
$total_clients = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT * FROM bookings ORDER BY date ASC LIMIT 5");
$recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="stats-grid mb-28">
                    <div class="stat-card gold">
                        <div class="stat-icon gold"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-value">Rs.<?= number_format($revenue / 1000, 1) ?>K</div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-card blue">
                        <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
                        <div class="stat-value"><?= $total_bookings ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-icon green"><i class="fas fa-users"></i></div>
                        <div class="stat-value"><?= $total_clients ?></div>
                        <div class="stat-label">Active Clients</div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="card-header">
                        <h3>📋 Recent Upcoming Events</h3>
                        <a href="bookings.php" class="btn btn-ghost btn-sm">View All</a>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Guests</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_bookings as $b): ?>
                                <tr>
                                    <td><strong style="color:var(--text)"><?= htmlspecialchars($b['event']) ?></strong><br><small style="color:var(--text3)"><?= htmlspecialchars($b['venue']) ?></small></td>
                                    <td><?= htmlspecialchars($b['date']) ?></td>
                                    <td><i class="fas fa-users" style="color:var(--text3);margin-right:6px"></i><?= htmlspecialchars($b['guests']) ?></td>
                                    <td style="color:var(--gold);font-weight:600">Rs.<?= number_format($b['amount']) ?></td>
                                    <td><span class="status-badge <?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
