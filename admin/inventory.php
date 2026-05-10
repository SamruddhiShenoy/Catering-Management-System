<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Inventory Tracking';

// Fetch inventory
$stmt = $pdo->query("SELECT * FROM inventory");
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="table-card">
                    <div class="card-header">
                        <h3>Stock Levels</h3>
                        <div style="display: flex; gap: 10px;">
                            <button id="bulkRestock" class="btn btn-gold btn-sm"><i class="fas fa-magic"></i> One-Click Restock</button>
                            <button class="btn btn-ghost btn-sm"><i class="fas fa-plus"></i> Add Item</button>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Stock Level</th>
                                <th>Supplier</th>
                                <th>Unit Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($inventory as $i): ?>
                                <?php 
                                    $pct = min(100, ($i['stock'] / max(1, $i['min_stock'] * 2)) * 100);
                                    $color = $i['stock'] <= $i['min_stock'] ? 'var(--accent)' : 'var(--green)';
                                ?>
                                <tr>
                                    <td><strong style="color:var(--text)"><?= htmlspecialchars($i['item']) ?></strong></td>
                                    <td><?= htmlspecialchars($i['category']) ?></td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:12px">
                                            <div style="font-weight:600;color:<?= $color ?>;width:40px"><?= htmlspecialchars($i['stock']) ?> <?= htmlspecialchars($i['unit']) ?></div>
                                            <div style="flex:1;height:6px;background:var(--dark4);border-radius:3px;overflow:hidden">
                                                <div style="height:100%;background:<?= $color ?>;width:<?= $pct ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><i class="fas fa-truck" style="color:var(--text3);margin-right:6px"></i><?= htmlspecialchars($i['supplier']) ?></td>
                                    <td>Rs.<?= number_format($i['cost_per_unit'], 2) ?>/<?= htmlspecialchars($i['unit']) ?></td>
                                    <td><button class="btn btn-sm btn-ghost"><i class="fas fa-sync-alt"></i> Restock</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('bulkRestock').addEventListener('click', async function() {
    if(!confirm('Restock all low-stock items?')) return;
    
    try {
        const response = await fetch('../api/restock.php', { method: 'POST' });
        const result = await response.json();
        
        if (result.success) {
            toast(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            toast(result.message, 'error');
        }
    } catch (e) {
        toast('Restock failed.', 'error');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
