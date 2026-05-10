<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Categorized Menu Manager';
$page_subtitle = 'Organize and edit your culinary offerings with visual assets.';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ADD ITEM
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $id = 'M' . rand(1000, 9999);
        $name = trim($_POST['name']);
        $cat = trim($_POST['category']);
        $price = (float)$_POST['price'];
        $desc = trim($_POST['description']);
        $emoji = trim($_POST['emoji']) ?: '🍴';
        $image_path = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'menu_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/menu/' . $filename)) {
                $image_path = 'uploads/menu/' . $filename;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO menu_items (id, name, category, price, description, image_path, emoji, available, dietary) VALUES (?, ?, ?, ?, ?, ?, ?, 1, '[]')");
        $stmt->execute([$id, $name, $cat, $price, $desc, $image_path, $emoji]);
        $_SESSION['toast'] = ['msg' => 'New item added successfully!', 'type' => 'success'];
        header("Location: menu.php");
        exit;
    }

    // EDIT ITEM
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = $_POST['edit_id'];
        $name = trim($_POST['name']);
        $cat = trim($_POST['category']);
        $price = (float)$_POST['price'];
        $desc = trim($_POST['description']);
        $emoji = trim($_POST['emoji']);
        
        // Handle image update
        $stmt = $pdo->prepare("SELECT image_path FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $current_image = $stmt->fetchColumn();

        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            if ($current_image && file_exists('../' . $current_image)) unlink('../' . $current_image);
            $current_image = null;
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($current_image && file_exists('../' . $current_image)) unlink('../' . $current_image);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'menu_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/menu/' . $filename)) {
                $current_image = 'uploads/menu/' . $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE menu_items SET name=?, category=?, price=?, description=?, image_path=?, emoji=? WHERE id=?");
        $stmt->execute([$name, $cat, $price, $desc, $current_image, $emoji, $id]);
        $_SESSION['toast'] = ['msg' => 'Item updated successfully!', 'type' => 'success'];
        header("Location: menu.php");
        exit;
    }

    // DELETE ITEM
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare("SELECT image_path FROM menu_items WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        $img = $stmt->fetchColumn();
        if ($img && file_exists('../' . $img)) unlink('../' . $img);
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        $_SESSION['toast'] = ['msg' => 'Item removed.', 'type' => 'info'];
        header("Location: menu.php");
        exit;
    }
}

// Fetch menu grouped by category
$stmt = $pdo->query("SELECT * FROM menu_items ORDER BY category, name");
$all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$menu_categories = [];
foreach ($all_items as $item) {
    $menu_categories[$item['category']][] = $item;
}

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <!-- Add Form Toggle -->
                <button class="btn btn-gold mb-28" onclick="toggleAddForm()"><i class="fas fa-plus-circle"></i> Add New Menu Item</button>

                <div id="addFormContainer" class="table-card mb-28 reveal" style="display:none;">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle" style="color:var(--gold); margin-right:10px;"></i> Add New Item</h3>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="grid-3" style="gap:20px; padding:20px;">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group-dark">
                            <label>Item Name</label>
                            <input type="text" name="name" class="form-input-dark" placeholder="e.g. Paneer Tikka" required>
                        </div>
                        <div class="form-group-dark">
                            <label>Category</label>
                            <select name="category" class="form-input-dark" required>
                                <option>Starters (Veg)</option>
                                <option>Starters (Non-Veg)</option>
                                <option>Main Course (Veg)</option>
                                <option>Main Course (Non-Veg)</option>
                                <option>Snacks</option>
                                <option>Ice Creams</option>
                                <option>Chocolate Specials</option>
                                <option>Desserts</option>
                                <option>Beverages</option>
                            </select>
                        </div>
                        <div class="form-group-dark">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" class="form-input-dark" placeholder="0.00" required>
                        </div>
                        <div class="form-group-dark">
                            <label>Description</label>
                            <input type="text" name="description" class="form-input-dark" placeholder="Brief details">
                        </div>
                        <div class="form-group-dark">
                            <label>Emoji Placeholder</label>
                            <input type="text" name="emoji" class="form-input-dark" placeholder="e.g. 🥘" value="🍴">
                        </div>
                        <div class="form-group-dark">
                            <label>Upload Image</label>
                            <input type="file" name="image" class="form-input-dark" accept="image/*">
                        </div>
                        <div style="grid-column: span 3; text-align: right;">
                            <button type="submit" class="btn btn-gold">Save Menu Item</button>
                        </div>
                    </form>
                </div>

                <!-- Categorized Display -->
                <?php foreach($menu_categories as $category => $items): ?>
                    <div class="section-title" style="margin-top:40px;">
                        <i class="fas fa-folder-open" style="color:var(--gold)"></i> <?= htmlspecialchars($category) ?> 
                        <span class="tag tag-blue" style="margin-left:10px; font-size:10px;"><?= count($items) ?> Items</span>
                    </div>
                    <div class="grid-4" style="margin-bottom:30px;">
                        <?php foreach($items as $m): ?>
                            <div class="table-card" style="padding:0; overflow:hidden;">
                                <div style="height:150px; background:var(--dark3); display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                                    <?php if(!empty($m['image_path'])): ?>
                                        <img src="../<?= $m['image_path'] ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="font-size:50px; opacity:0.8;"><?= $m['emoji'] ?></div>
                                    <?php endif; ?>
                                    <div style="position:absolute; top:10px; right:10px; z-index:2;"><?= $m['emoji'] ?></div>
                                </div>
                                <div style="padding:15px;">
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:5px;">
                                        <strong style="color:var(--text); font-size:14px;"><?= htmlspecialchars($m['name']) ?></strong>
                                        <span style="color:var(--gold); font-weight:600; font-size:14px;">Rs.<?= number_format($m['price'], 2) ?></span>
                                    </div>
                                    <p style="font-size:11px; color:var(--text3); line-height:1.4; height:32px; overflow:hidden;"><?= htmlspecialchars($m['description'] ?: 'No description.') ?></p>
                                    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px; border-top:1px solid var(--border2); padding-top:10px;">
                                        <button class="btn btn-sm btn-ghost btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)" title="Edit Item"><i class="fas fa-edit" style="color:var(--gold)"></i></button>
                                        <form method="POST" onsubmit="return confirm('Delete this item?');">
                                            <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-ghost btn-icon" style="color:var(--accent)"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
    <div class="table-card" style="width:600px; padding:20px; position:relative;">
        <button onclick="closeEditModal()" style="position:absolute; top:15px; right:15px; background:none; border:none; color:var(--text3); cursor:pointer; font-size:20px;">&times;</button>
        <h3 style="margin-bottom:20px;"><i class="fas fa-edit" style="color:var(--gold)"></i> Edit Menu Item</h3>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="grid-2" style="gap:15px;">
                <div class="form-group-dark">
                    <label>Name</label>
                    <input type="text" name="name" id="edit_name" class="form-input-dark" required>
                </div>
                <div class="form-group-dark">
                    <label>Category</label>
                    <select name="category" id="edit_category" class="form-input-dark" required>
                        <option>Starters (Veg)</option>
                        <option>Starters (Non-Veg)</option>
                        <option>Main Course (Veg)</option>
                        <option>Main Course (Non-Veg)</option>
                        <option>Snacks</option>
                        <option>Ice Creams</option>
                        <option>Chocolate Specials</option>
                        <option>Desserts</option>
                        <option>Beverages</option>
                    </select>
                </div>
                <div class="form-group-dark">
                    <label>Price ($)</label>
                    <input type="number" step="0.01" name="price" id="edit_price" class="form-input-dark" required>
                </div>
                <div class="form-group-dark">
                    <label>Emoji</label>
                    <input type="text" name="emoji" id="edit_emoji" class="form-input-dark">
                </div>
            </div>
            <div class="form-group-dark" style="margin-top:15px;">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="form-input-dark" style="height:60px;"></textarea>
            </div>
            <div class="form-group-dark" style="margin-top:15px;">
                <label>Update Image</label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="file" name="image" class="form-input-dark" accept="image/*">
                    <div id="image_preview_btn"></div>
                </div>
                <div style="margin-top:5px; font-size:11px;">
                    <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                        <input type="checkbox" name="remove_image" value="1"> Remove current image (revert to emoji)
                    </label>
                </div>
            </div>
            <div style="text-align:right; margin-top:25px;">
                <button type="button" class="btn btn-ghost" onclick="closeEditModal()" style="margin-right:10px;">Cancel</button>
                <button type="submit" class="btn btn-gold">Update Item</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAddForm() {
    const f = document.getElementById('addFormContainer');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

function openEditModal(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_category').value = item.category;
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_emoji').value = item.emoji;
    document.getElementById('edit_description').value = item.description;
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        closeEditModal();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
