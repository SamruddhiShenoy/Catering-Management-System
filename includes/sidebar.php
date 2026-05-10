<?php
$role = $_SESSION['user_role'] ?? 'client';
$name = $_SESSION['user_name'] ?? 'User';
$avatar = $_SESSION['user_avatar'] ?? 'U';
$base_url = '/Catering_Management_System';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo">
            <div class="logo-icon">🍴</div>
            <div class="logo-text">
                <span class="brand">CaterBook</span>
                <span class="version">v3.0 Premium</span>
            </div>
        </div>
    </div>
    <div class="sidebar-user" id="sidebarUser">
        <div class="user-avatar <?= $role ?>" id="sidebarAvatar"><?= htmlspecialchars($avatar) ?></div>
        <div>
            <span class="user-name" id="sidebarName"><?= htmlspecialchars($name) ?></span>
            <span class="user-role" id="sidebarRole"><?= $role === 'admin' ? 'Administrator' : 'Client' ?></span>
        </div>
    </div>
    <nav class="sidebar-nav" id="sidebarNav">
        <?php if ($role === 'admin'): ?>
            <div class="nav-section">Overview</div>
            <a href="<?= $base_url ?>/admin/index.php" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="<?= $base_url ?>/admin/analytics.php" class="nav-item"><i class="fas fa-chart-pie"></i> Analytics & Reports</a>
            <div class="nav-section">Operations</div>
            <a href="<?= $base_url ?>/admin/bookings.php" class="nav-item"><i class="fas fa-calendar-check"></i> Bookings</a>
            <a href="<?= $base_url ?>/admin/menu.php" class="nav-item"><i class="fas fa-utensils"></i> Menu Manager</a>
            <a href="<?= $base_url ?>/admin/packages.php" class="nav-item"><i class="fas fa-box-open"></i> Packages</a>
            <div class="nav-section">Management</div>
            <a href="<?= $base_url ?>/admin/clients.php" class="nav-item"><i class="fas fa-users"></i> Clients</a>
            <?php 
                $unread_chat = $pdo->query("SELECT COUNT(*) FROM messages WHERE sender_role = 'client' AND is_read = 0")->fetchColumn();
            ?>
            <a href="<?= $base_url ?>/admin/messages.php" class="nav-item">
                <i class="fas fa-comments"></i> Messages
                <?php if($unread_chat > 0): ?>
                    <span style="background:var(--accent); color:white; border-radius:10px; padding:2px 6px; font-size:10px; margin-left:auto;"><?= $unread_chat ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= $base_url ?>/admin/staff.php" class="nav-item"><i class="fas fa-user-tie"></i> Staff</a>
            <a href="<?= $base_url ?>/admin/inventory.php" class="nav-item"><i class="fas fa-boxes"></i> Inventory</a>
            <a href="<?= $base_url ?>/admin/gallery.php" class="nav-item"><i class="fas fa-images"></i> Event Gallery</a>
            <a href="<?= $base_url ?>/admin/finance.php" class="nav-item"><i class="fas fa-dollar-sign"></i> Finance</a>
        <?php else: ?>
            <div class="nav-section">My Portal</div>
            <a href="<?= $base_url ?>/client/index.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
            <a href="<?= $base_url ?>/client/menu.php" class="nav-item"><i class="fas fa-utensils"></i> Browse Menu</a>
            <a href="<?= $base_url ?>/client/book.php" class="nav-item"><i class="fas fa-magic"></i> Book Event</a>
            <a href="<?= $base_url ?>/client/my_bookings.php" class="nav-item"><i class="fas fa-calendar-alt"></i> My Bookings</a>
            <a href="<?= $base_url ?>/client/gallery.php" class="nav-item"><i class="fas fa-images"></i> My Gallery</a>
            <a href="<?= $base_url ?>/client/profile.php" class="nav-item"><i class="fas fa-user"></i> Profile Settings</a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= $base_url ?>/logout.php" class="logout-btn" style="text-decoration:none; display:block; text-align:center;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </div>
</aside>
