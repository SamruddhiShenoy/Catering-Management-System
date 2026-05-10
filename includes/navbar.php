<?php
$base_url = '/Catering_Management_System';
?>
<nav class="public-navbar">
    <div class="logo">
        <div class="logo-icon">🍴</div>
        <div class="logo-text">
            <span class="brand">CaterBook</span>
        </div>
    </div>
    <div class="public-nav-links">
        <a href="<?= $base_url ?>/index.php">Home</a>
        <a href="<?= $base_url ?>/index.php#packages">Packages</a>
        <a href="<?= $base_url ?>/index.php#menu">Menu</a>
        <button class="theme-toggle-btn" onclick="toggleTheme()" title="Toggle Light/Dark Mode" style="margin-left: 30px;">
            <i class="fas fa-moon"></i>
        </button>
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['user_role'] === 'admin'): ?>
                <a href="<?= $base_url ?>/admin/index.php" class="btn btn-gold btn-sm" style="color:#000;">Admin Dashboard</a>
            <?php else: ?>
                <a href="<?= $base_url ?>/client/index.php" class="btn btn-gold btn-sm" style="color:#000;">My Dashboard</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= $base_url ?>/login.php" class="btn btn-gold btn-sm" style="color:#000;">Log In / Register</a>
        <?php endif; ?>
    </div>
</nav>
