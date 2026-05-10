<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Client Directory';

// Fetch clients
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'client' ORDER BY joined DESC");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="table-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Contact</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($clients as $c): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:12px">
                                            <div class="user-avatar client"><?= htmlspecialchars($c['avatar'] ?: substr($c['name'],0,1)) ?></div>
                                            <div>
                                                <div style="font-weight:500;color:var(--text)"><?= htmlspecialchars($c['name']) ?></div>
                                                <div style="font-size:12px;color:var(--text3)">ID: <?= htmlspecialchars($c['id']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="color:var(--text2)"><i class="fas fa-envelope" style="width:16px;color:var(--text3)"></i> <?= htmlspecialchars($c['email']) ?></div>
                                        <div style="color:var(--text2);margin-top:4px"><i class="fas fa-phone" style="width:16px;color:var(--text3)"></i> <?= htmlspecialchars($c['phone'] ?: 'N/A') ?></div>
                                    </td>
                                    <td style="color:var(--text2)"><?= htmlspecialchars($c['joined']) ?></td>
                                    <td><span class="status-badge completed">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-ghost btn-icon"><i class="fas fa-edit"></i></button>
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

<?php require_once '../includes/footer.php'; ?>
