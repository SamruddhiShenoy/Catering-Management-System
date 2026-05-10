<?php
require 'config.php';

try {
    // Add columns for split payments
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS advance_amount DECIMAL(10,2) DEFAULT 0 AFTER amount");
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS paid_amount DECIMAL(10,2) DEFAULT 0 AFTER advance_amount");
    
    echo "Database updated for split payments.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
