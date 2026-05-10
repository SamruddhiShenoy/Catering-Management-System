<?php
require_once '../config.php';
requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($id && $status) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $success = $stmt->execute([$status, $id]);
            
            if ($success) {
                echo json_encode(['success' => true, 'msg' => 'Booking ' . $status . ' successfully.']);
            } else {
                echo json_encode(['success' => false, 'msg' => 'Database update failed.']);
            }
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'msg' => 'DB Error: ' . $e->getMessage()]);
            exit;
        }
    }
}
echo json_encode(['success' => false, 'msg' => 'Invalid request: ID or Status missing. Received: ID=' . ($id ?? 'null') . ', Status=' . ($status ?? 'null')]);
?>
