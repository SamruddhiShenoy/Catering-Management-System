<?php
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? "admin/index.php" : "client/index.php"));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, email, and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered. Try logging in.";
        } else {
            // Register user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $avatar = strtoupper(substr($name, 0, 1));
            $joined = date('Y-m-d');
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, avatar, joined) VALUES (?, ?, ?, ?, 'client', ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $hashed_password, $avatar, $joined])) {
                $_SESSION['toast'] = ['msg' => 'Registration successful! Please login.', 'type' => 'success'];
                header("Location: login.php");
                exit;
            } else {
                $error = "An error occurred. Please try again.";
            }
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div style="display:flex; justify-content:center; align-items:center; min-height:80vh; padding: 20px;">
    <div class="login-card" style="width: 100%; max-width: 500px; padding: 40px;">
        <div style="text-align:center; margin-bottom: 30px;">
            <h2 style="font-family:'Playfair Display',serif; color:var(--text); font-size: 28px;">Join CaterBook</h2>
            <p style="color:var(--text3); font-size: 14px;">Register to start planning your luxury events</p>
        </div>
        
        <?php if($error): ?>
            <div style="background: rgba(232, 64, 96, 0.1); border-left: 3px solid var(--accent); padding: 12px; margin-bottom: 20px; color: var(--text);">
                <i class="fas fa-exclamation-circle" style="color: var(--accent);"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-grid">
                <div class="form-group-dark">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-input-dark" placeholder="John Doe" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="form-group-dark">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-input-dark" placeholder="john@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group-dark">
                <label>Phone Number (Optional)</label>
                <input type="text" name="phone" class="form-input-dark" placeholder="+1 (555) 000-0000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            
            <div class="form-grid">
                <div class="form-group-dark">
                    <label>Password</label>
                    <input type="password" name="password" class="form-input-dark" placeholder="••••••••" required>
                </div>
                
                <div class="form-group-dark">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-input-dark" placeholder="••••••••" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-gold btn-full" style="margin-top: 10px;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div style="text-align:center; margin-top: 20px; color: var(--text3); font-size: 14px;">
            Already have an account? <a href="login.php" style="color:var(--gold); text-decoration:none;">Sign In</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
