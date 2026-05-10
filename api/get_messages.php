<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'messages' => []]); exit;
}

$booking_id = trim($_GET['booking_id'] ?? '');
if (!$booking_id) {
    echo json_encode(['success' => false, 'messages' => []]); exit;
}

// Mark messages as read (the other side's messages)
$role = isAdmin() ? 'client' : 'admin';
$pdo->prepare("UPDATE messages SET is_read = 1 WHERE booking_id = ? AND sender_role = ? AND is_read = 0")
    ->execute([$booking_id, $role]);

// Fetch messages with sender name
$stmt = $pdo->prepare("
    SELECT m.*, u.name AS sender_name 
    FROM messages m 
    LEFT JOIN users u ON u.id = m.sender_id 
    WHERE m.booking_id = ? 
    ORDER BY m.created_at ASC
");
$stmt->execute([$booking_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'messages' => $messages]);
?>
