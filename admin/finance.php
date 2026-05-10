<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Financial Overview';

$stmt = $pdo->query("SELECT SUM(amount) FROM bookings WHERE status IN ('completed', 'confirmed')");
$revenue = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT SUM(amount) FROM bookings WHERE status = 'pending'");
$pending = $stmt->fetchColumn() ?: 0;

$expenses = $revenue * 0.4; // rough estimate for demo
$profit = $revenue - $expenses;

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="grid-3 mb-28">
                    <div class="stat-card gold"><div class="stat-icon gold"><i class="fas fa-dollar-sign"></i></div><div class="stat-value">Rs.<?= number_format($revenue, 2) ?></div><div class="stat-label">Total Revenue</div></div>
                    <div class="stat-card blue"><div class="stat-icon blue"><i class="fas fa-chart-line"></i></div><div class="stat-value">Rs.<?= number_format($profit, 2) ?></div><div class="stat-label">Net Profit (Est.)</div></div>
                    <div class="stat-card orange"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div class="stat-value">Rs.<?= number_format($pending, 2) ?></div><div class="stat-label">Pending Payments</div></div>
                </div>

                <div class="table-card">
                    <div class="card-header">
                        <h3>💰 Recent Transactions</h3>
                    </div>
                    <div style="text-align:center;padding:40px;color:var(--text3);">
                        <i class="fas fa-file-invoice-dollar" style="font-size:48px;margin-bottom:16px;color:var(--dark5)"></i>
                        <p>Detailed transaction history is synced with your accounting software.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
