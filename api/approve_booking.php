<?php
require_once '../config.php';
require_once '../includes/notifications.php';

// MUST be before any output
header('Content-Type: application/json');

// Auth check — return JSON error instead of redirect (AJAX-safe)
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in as admin.']);
    exit;
}

$booking_id = trim($_POST['booking_id'] ?? '');
$action     = trim($_POST['action'] ?? '');

if (!$booking_id || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
    exit;
}

try {
    // Fetch booking + client info
    $stmt = $pdo->prepare("SELECT b.*, u.name AS client_name FROM bookings b JOIN users u ON b.client_id = u.id WHERE b.id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => "Booking {$booking_id} not found."]);
        exit;
    }

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->execute([$booking_id]);

        // Notify client
        createNotification(
            $pdo,
            $booking['client_id'],
            '✅ Booking Approved!',
            "Your booking {$booking_id} for \"{$booking['event']}\" has been approved. You can now proceed to payment.",
            'success',
            '/Catering_Management_System/client/my_bookings.php'
        );

        // Notify admins
        $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($admins as $adminId) {
            createNotification($pdo, $adminId, '📋 Booking Confirmed',
                "Booking {$booking_id} for {$booking['client_name']} has been confirmed.",
                'info', '/Catering_Management_System/admin/bookings.php');
        }

        echo json_encode(['success' => true, 'message' => "Booking {$booking_id} approved! Client notified.", 'new_status' => 'confirmed']);

    } else {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);

        // Notify client
        createNotification(
            $pdo,
            $booking['client_id'],
            '❌ Booking Declined',
            "Your booking {$booking_id} for \"{$booking['event']}\" has been declined. Please contact us.",
            'error',
            '/Catering_Management_System/client/my_bookings.php'
        );

        echo json_encode(['success' => true, 'message' => "Booking {$booking_id} rejected. Client notified.", 'new_status' => 'cancelled']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
