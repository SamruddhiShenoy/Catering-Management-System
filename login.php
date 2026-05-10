<?php
require_once 'config.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin/index.php");
    } else {
        header("Location: client/index.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_avatar'] = $user['avatar'];
            
            $_SESSION['toast'] = ['msg' => 'Welcome back, ' . $user['name'] . '!', 'type' => 'success'];
            
            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: client/index.php");
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div style="display:flex; justify-content:center; align-items:center; min-height:80vh; padding: 20px;">
    <div class="login-card" style="width: 100%; max-width: 400px; padding: 40px;">
        <div style="text-align:center; margin-bottom: 30px;">
            <h2 style="font-family:'Playfair Display',serif; color:var(--text); font-size: 28px;">Welcome Back</h2>
            <p style="color:var(--text3); font-size: 14px;">Sign in to your CaterBook account</p>
        </div>
        
        <?php if($error): ?>
            <div style="background: rgba(232, 64, 96, 0.1); border-left: 3px solid var(--accent); padding: 12px; margin-bottom: 20px; color: var(--text);">
                <i class="fas fa-exclamation-circle" style="color: var(--accent);"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group-dark">
                <label>Email Address</label>
                <input type="email" name="email" class="form-input-dark" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? 'admin@luxecater.com') ?>">
            </div>
            
            <div class="form-group-dark">
                <label>Password</label>
                <input type="password" name="password" class="form-input-dark" placeholder="••••••••" required value="admin123">
            </div>
            
            <button type="submit" class="btn btn-gold btn-full" style="margin-top: 10px;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
            <div style="text-align:center; margin-top: 15px; font-size: 14px;">
                <span style="color:var(--text3);">Don't have an account?</span> 
                <a href="register.php" style="color:var(--gold); text-decoration:none; font-weight:500;">Register Now</a>
            </div>
        </form>
        
        <div style="text-align:center; margin-top: 20px; color: var(--text3); font-size: 13px;">
            Demo Accounts:<br>
            <span style="cursor:pointer; color:var(--gold);" onclick="document.querySelector('input[name=email]').value='admin@luxecater.com'; document.querySelector('input[name=password]').value='admin123';">Admin</span> | 
            <span style="cursor:pointer; color:var(--gold);" onclick="document.querySelector('input[name=email]').value='client@example.com'; document.querySelector('input[name=password]').value='client123';">Client</span>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
