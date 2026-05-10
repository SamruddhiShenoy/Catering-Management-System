<?php
header('Content-Type: application/json');
require_once '../config.php';

// Check if user is logged in
$response = [
    'low_stock_count' => 0,
    'is_admin' => isAdmin(),
    'logged_in' => isLoggedIn()
];

if (isLoggedIn()) {
    try {
        // Count items where stock is below min_stock
        $stmt = $pdo->query("SELECT COUNT(*) FROM inventory WHERE stock <= min_stock");
        $response['low_stock_count'] = (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // If table doesn't exist yet or other error, just return 0
        $response['low_stock_count'] = 0;
    }
}

echo json_encode($response);
?>
