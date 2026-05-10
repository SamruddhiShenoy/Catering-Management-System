<?php
require_once '../config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Restock all items that are at or below minimum stock
        // For simulation, we'll reset them to min_stock + 50
        $stmt = $pdo->query("
            UPDATE inventory 
            SET stock = min_stock + 50 
            WHERE stock <= min_stock
        ");
        
        $count = $stmt->rowCount();
        
        echo json_encode([
            'success' => true, 
            'message' => "Successfully restocked $count items.",
            'restocked_count' => $count
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
