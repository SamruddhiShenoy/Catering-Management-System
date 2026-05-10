<?php
require_once '../config.php';
require_once '../includes/notifications.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $client_id     = $_SESSION['user_id'];
        $client_name   = $_SESSION['user_name'] ?? 'Client';
        $event_type    = $_POST['event_type'] ?? '';
        $date          = $_POST['event_date'] ?? '';
        $guests        = (int)($_POST['guests'] ?? 0);
        $venue         = $_POST['venue'] ?? '';
        $notes         = $_POST['notes'] ?? '';
        $pkg_name      = $_POST['package_name'] ?? '';
        $selected_items = $_POST['selected_items'] ?? '';

        // Calculate amount from package + selected items (JSON)
        $stmt = $pdo->prepare("SELECT price FROM packages WHERE name = ?");
        $stmt->execute([$pkg_name]);
        $pkg_price = (float)($stmt->fetchColumn() ?: 0);
        
        $item_total = 0;
        if (!empty($selected_items)) {
            $items_array = json_decode($selected_items, true);
            if (is_array($items_array)) {
                foreach ($items_array as $item) {
                    $stmt = $pdo->prepare("SELECT price FROM menu_items WHERE id = ?");
                    $stmt->execute([$item['id']]);
                    $price = (float)$stmt->fetchColumn();
                    $item_total += ($price * (int)$item['qty']);
                }
            }
        }
        
        $advance_amount = $guests * $pkg_price;
        $subtotal = $advance_amount + $item_total;
        $discount = $subtotal * 0.20;
        $amount = $subtotal - $discount;

        $id = 'BK' . date('ymd') . rand(10, 99);

        $sql = "INSERT INTO bookings (id, client_id, event, date, guests, package, amount, advance_amount, status, venue, notes, selected_menu, payment_status, created) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, 'unpaid', ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $client_id, $event_type, $date, $guests, $pkg_name, $amount, $advance_amount, $venue, $notes, $selected_items, date('Y-m-d')]);

        // Notify ALL admins about new booking
        $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($admins as $adminId) {
            createNotification(
                $pdo,
                $adminId,
                '🆕 New Booking Request',
                "{$client_name} has submitted a new booking ({$id}) for \"{$event_type}\" on {$date} for {$guests} guests. Approval required.",
                'info',
                '/Catering_Management_System/admin/bookings.php'
            );
        }

        // Notify client — booking received
        createNotification(
            $pdo,
            $client_id,
            '📨 Booking Received',
            "Your booking ({$id}) for \"{$event_type}\" on {$date} has been submitted and is awaiting admin approval.",
            'info',
            '/Catering_Management_System/client/my_bookings.php'
        );

        $_SESSION['toast'] = ['msg' => "Booking {$id} submitted! Awaiting admin approval.", 'type' => 'success'];
        header("Location: ../client/my_bookings.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => "Error: " . $e->getMessage(), 'type' => 'error'];
        header("Location: ../client/book.php");
        exit;
    }
}
header("Location: ../client/book.php");
?>
