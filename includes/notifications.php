<?php
/**
 * Notification Helper
 * Usage: createNotification($pdo, $user_id, 'Title', 'Message', 'success', '/link/here')
 */
function createNotification($pdo, $user_id, $title, $message, $type = 'info', $link = null) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $message, $type, $link]);
}

function getUnreadCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

function getNotifications($pdo, $user_id, $limit = 10) {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markAllRead($pdo, $user_id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
}
?>
