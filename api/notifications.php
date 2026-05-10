<?php
require_once '../config.php';
require_once '../includes/notifications.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

$action = $_GET['action'] ?? '';
$uid = $_SESSION['user_id'];

if ($action === 'count') {
    echo json_encode(['count' => getUnreadCount($pdo, $uid)]);
    exit;
}

if ($action === 'list') {
    markAllRead($pdo, $uid);
    $notifs = getNotifications($pdo, $uid, 12);
    echo json_encode(['notifications' => $notifs]);
    exit;
}

if ($action === 'mark_read') {
    markAllRead($pdo, $uid);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
?>
