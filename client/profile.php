<?php
require_once '../config.php';
requireLogin();

$page_title = 'Profile Settings';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    if ($name) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $_SESSION['user_id']]);
        $_SESSION['user_name'] = $name;
        $_SESSION['toast'] = ['msg' => 'Profile updated successfully!', 'type' => 'success'];
        header("Location: profile.php");
        exit;
    }
}

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="grid-2">
                    <div class="profile-card mb-20">
                        <div class="profile-banner"></div>
                        <div style="position:relative;height:36px">
                            <div class="profile-avatar"><?= htmlspecialchars($u['avatar']) ?></div>
                        </div>
                        <div class="profile-info">
                            <h2 style="font-size:22px;margin-bottom:4px"><?= htmlspecialchars($u['name']) ?></h2>
                            <div style="font-size:13px;color:var(--text3);margin-bottom:16px"><?= htmlspecialchars($u['email']) ?></div>
                            <div class="grid-2">
                                <div><div style="font-size:11px;color:var(--text3);text-transform:uppercase">Phone</div><div style="font-size:14px;color:var(--text);margin-top:4px"><?= htmlspecialchars($u['phone'] ?: 'Not set') ?></div></div>
                                <div><div style="font-size:11px;color:var(--text3);text-transform:uppercase">Member Since</div><div style="font-size:14px;color:var(--text);margin-top:4px"><?= htmlspecialchars($u['joined']) ?></div></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-card">
                        <h3 style="margin-bottom:20px">✏️ Edit Profile</h3>
                        <form method="POST" action="profile.php">
                            <div class="form-group-dark"><label>Full Name</label><input type="text" name="name" class="form-input-dark" value="<?= htmlspecialchars($u['name']) ?>" required></div>
                            <div class="form-group-dark"><label>Phone</label><input type="text" name="phone" class="form-input-dark" value="<?= htmlspecialchars($u['phone']) ?>"></div>
                            <div class="form-group-dark"><label>Email</label><input type="text" class="form-input-dark" value="<?= htmlspecialchars($u['email']) ?>" disabled style="opacity:0.5"></div>
                            <button type="submit" class="btn btn-gold" style="margin-top:10px"><i class="fas fa-save"></i> Save Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
