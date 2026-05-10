<?php
require_once '../config.php';

// Only admins can upload
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    
    if (empty($booking_id)) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required.']);
        exit;
    }

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
        exit;
    }

    $file = $_FILES['photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WebP are allowed.']);
        exit;
    }

    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
        exit;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $booking_id . '_' . time() . '_' . uniqid() . '.' . $extension;
    $upload_dir = '../uploads/events/';
    $upload_path = $upload_dir . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Save to database
        $db_path = 'uploads/events/' . $filename;
        $stmt = $pdo->prepare("INSERT INTO event_gallery (booking_id, file_path) VALUES (?, ?)");
        $stmt->execute([$booking_id, $db_path]);

        echo json_encode(['success' => true, 'message' => 'Photo uploaded successfully.', 'path' => $db_path]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
