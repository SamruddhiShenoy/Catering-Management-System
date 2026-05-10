<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Staff Roster';

// Fetch staff
$stmt = $pdo->query("SELECT * FROM staff");
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="grid-3 mb-28">
                    <?php foreach($staff as $s): ?>
                        <div class="table-card">
                            <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:16px">
                                <div style="width:48px;height:48px;border-radius:12px;background:var(--dark4);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:600;color:var(--text)">
                                    <?= substr(htmlspecialchars($s['name']),0,1) ?>
                                </div>
                                <div style="flex:1">
                                    <h4 style="font-size:16px;color:var(--text);margin-bottom:4px"><?= htmlspecialchars($s['name']) ?></h4>
                                    <div style="font-size:13px;color:var(--gold);margin-bottom:4px"><?= htmlspecialchars($s['role']) ?></div>
                                    <span class="status-badge <?= htmlspecialchars($s['status']) === 'active' ? 'completed' : 'pending' ?>"><?= htmlspecialchars($s['status']) ?></span>
                                </div>
                            </div>
                            <div style="font-size:13px;color:var(--text2);margin-bottom:8px">
                                <i class="fas fa-phone" style="width:20px;color:var(--text3)"></i> <?= htmlspecialchars($s['phone']) ?>
                            </div>
                            <div style="font-size:13px;color:var(--text2);margin-bottom:8px">
                                <i class="fas fa-star" style="width:20px;color:var(--text3)"></i> <?= htmlspecialchars($s['speciality']) ?>
                            </div>
                            <div style="font-size:13px;color:var(--text2);margin-bottom:16px">
                                <i class="fas fa-briefcase" style="width:20px;color:var(--text3)"></i> <?= htmlspecialchars($s['experience']) ?>
                            </div>
                            <button class="btn btn-ghost btn-sm btn-full"><i class="fas fa-calendar-alt"></i> View Schedule</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
