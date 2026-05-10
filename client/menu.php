<?php
require_once '../config.php';
requireLogin();

$page_title = 'Browse Menu';
$page_subtitle = 'Discover our world-class culinary collection.';

// Fetch menu grouped by category
$stmt = $pdo->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY category, name");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$menu_by_cat = [];
foreach($items as $item) {
    $menu_by_cat[$item['category']][] = $item;
}

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="filter-row mb-28" style="justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <button class="filter-btn active" onclick="filterMenu('all', this)">All Categories</button>
                    <?php foreach(array_keys($menu_by_cat) as $cat): ?>
                        <button class="filter-btn" onclick="filterMenu('<?= str_replace([' ', '(', ')'], '_', $cat) ?>', this)"><?= $cat ?></button>
                    <?php endforeach; ?>
                </div>

                <?php foreach($menu_by_cat as $cat => $cat_items): ?>
                    <div class="menu-section reveal" id="sec-<?= str_replace([' ', '(', ')'], '_', $cat) ?>" style="margin-bottom: 50px;">
                        <h2 class="section-title" style="margin-bottom: 24px;">
                            <i class="fas fa-utensils" style="color:var(--gold); margin-right: 12px;"></i> <?= $cat ?>
                            <span class="tag tag-blue" style="margin-left: 10px;"><?= count($cat_items) ?> Selections</span>
                        </h2>
                        <div class="grid-4">
                            <?php foreach($cat_items as $m): ?>
                                <div class="table-card reveal" style="padding: 0; overflow: hidden; position: relative; cursor: pointer;" onclick="viewMenuDetail('<?= $m['id'] ?>')">
                                    <div style="height: 180px; background: var(--dark3); display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                                        <?php if($m['image_path']): ?>
                                            <img src="../<?= $m['image_path'] ?>" style="width: 100%; height: 100%; object-fit: cover; transition: 0.5s;" class="menu-thumb">
                                        <?php else: ?>
                                            <div style="font-size: 64px; opacity: 0.1;"><?= $m['emoji'] ?></div>
                                        <?php endif; ?>
                                        <div style="position: absolute; top: 10px; right: 10px; z-index: 2;"><?= $m['emoji'] ?></div>
                                    </div>
                                    <div style="padding: 16px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                            <strong style="color: var(--text); font-size: 15px;"><?= htmlspecialchars($m['name']) ?></strong>
                                            <span style="color: var(--gold); font-weight: 600;">Rs.<?= number_format($m['price'], 2) ?></span>
                                        </div>
                                        <p style="font-size: 12px; color: var(--text3); line-height: 1.5; height: 36px; overflow: hidden;"><?= htmlspecialchars($m['description'] ?: 'Chef\'s special gourmet preparation.') ?></p>
                                        <div style="margin-top: 12px; display: flex; gap: 4px;">
                                            <?php 
                                            $dietary_tags = json_decode($m['dietary'], true) ?: [];
                                            foreach($dietary_tags as $tag): 
                                            ?>
                                                <span class="tag tag-green" style="font-size: 9px;"><?= $tag ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.table-card:hover .menu-thumb { transform: scale(1.1); }
</style>

<script>
function filterMenu(catId, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    if (catId === 'all') {
        document.querySelectorAll('.menu-section').forEach(s => s.style.display = 'block');
    } else {
        document.querySelectorAll('.menu-section').forEach(s => s.style.display = 'none');
        document.getElementById('sec-' + catId).style.display = 'block';
    }
}

function viewMenuDetail(id) {
    // Placeholder for a detail view modal if needed
    toast('Excellent choice! You can add this to your booking in the "Book Event" section.', 'info');
}
</script>

<?php require_once '../includes/footer.php'; ?>
