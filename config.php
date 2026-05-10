<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password
$db_name = 'luxecater_pro';

try {
    // We first connect without database to allow setup.php to create it if it doesn't exist
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Select the database if it exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
    if($stmt->fetch()) {
        $pdo->exec("USE $db_name");
    }
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Require login redirect
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /Catering_Management_System/login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: /Catering_Management_System/client/index.php");
        exit;
    }
}
?>
