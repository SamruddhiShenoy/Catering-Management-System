<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    
    if (empty($booking_id)) {
        echo json_encode(['success' => false, 'message' => 'Booking ID required']);
        exit;
    }

    try {
        if (isset($_POST['edit_notes'])) {
            $edit_notes = $_POST['edit_notes'];
            $stmt = $pdo->prepare("UPDATE bookings SET edit_notes = ? WHERE id = ? AND client_id = ?");
            $stmt->execute([$edit_notes, $booking_id, $_SESSION['user_id']]);
            echo json_encode(['success' => true, 'message' => "Notes sent to admin."]);
            exit;
        } 
        
        if (isset($_POST['feedback'])) {
            $feedback = $_POST['feedback'];
            $rating = (int)($_POST['rating'] ?? 0);
            $stmt = $pdo->prepare("UPDATE bookings SET feedback = ?, rating = ? WHERE id = ? AND client_id = ?");
            $stmt->execute([$feedback, $rating, $booking_id, $_SESSION['user_id']]);
            echo json_encode(['success' => true, 'message' => "Thank you for your feedback!"]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'No data provided']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
