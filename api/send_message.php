<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']); exit;
}

$booking_id = trim($_POST['booking_id'] ?? '');
$message    = trim($_POST['message'] ?? '');
$role       = isAdmin() ? 'admin' : 'client';

if (!$booking_id || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']); exit;
}

// Verify booking exists and client owns it (skip for admin)
if ($role === 'client') {
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND client_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']); exit;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (booking_id, sender_id, sender_role, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$booking_id, $_SESSION['user_id'], $role, $message]);

    $newId = $pdo->lastInsertId();
    $stmt  = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$newId]);
    $msg   = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'message' => $msg]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>
