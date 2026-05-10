<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Package Management';

// Fetch packages
$stmt = $pdo->query("SELECT * FROM packages");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="grid-3">
                    <?php foreach($packages as $p): ?>
                        <?php 
                            $includes = json_decode($p['includes'], true) ?: []; 
                            $color = $p['color'] === 'gold' ? 'gold' : ($p['color'] === 'purple' ? 'purple' : 'accent2');
                        ?>
                        <div class="package-card" style="border-top:4px solid var(--<?= $color ?>)">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
                                <div>
                                    <h3 style="color:var(--<?= $color ?>);margin-bottom:4px"><?= htmlspecialchars($p['name']) ?></h3>
                                    <div style="font-size:12px;color:var(--text3)">Min. Guests: <?= htmlspecialchars($p['min_guests']) ?></div>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:var(--text)">
                                    Rs.<?= number_format($p['price'], 2) ?><span style="font-size:12px;font-weight:400;color:var(--text3)">/pax</span>
                                </div>
                            </div>
                            <p style="font-size:13px;color:var(--text2);margin-bottom:16px;line-height:1.5">
                                <?= htmlspecialchars($p['description']) ?>
                            </p>
                            <div style="margin-bottom:16px">
                                <?php foreach($includes as $inc): ?>
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;font-size:13px;color:var(--text2)">
                                        <i class="fas fa-check" style="color:var(--green);font-size:12px"></i>
                                        <?= htmlspecialchars($inc) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="display:flex;gap:8px">
                                <button class="btn btn-ghost btn-sm" style="flex:1"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-ghost btn-sm btn-icon" style="color:var(--accent)"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="package-card" style="border:2px dashed var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;min-height:300px;background:transparent">
                        <div style="text-align:center">
                            <i class="fas fa-plus-circle" style="font-size:32px;color:var(--text3);margin-bottom:12px"></i>
                            <div style="color:var(--text2);font-weight:500">Create New Package</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
