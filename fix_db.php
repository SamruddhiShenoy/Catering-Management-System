<?php
require 'config.php';
try {
    $pdo->exec("ALTER TABLE bookings ADD COLUMN selected_menu TEXT AFTER notes");
    echo "Added selected_menu.\n";
} catch (Exception $e) {
    echo "Error adding selected_menu: " . $e->getMessage() . "\n";
}
try {
    $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_status VARCHAR(20) DEFAULT 'unpaid' AFTER selected_menu");
    echo "Added payment_status.\n";
} catch (Exception $e) {
    echo "Error adding payment_status: " . $e->getMessage() . "\n";
}
?>
