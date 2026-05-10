<?php
require 'config.php';

try {
    // Add edit_notes column
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS edit_notes TEXT AFTER notes");
    
    // Add feedback columns
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS feedback TEXT AFTER edit_notes");
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS rating INT DEFAULT 0 AFTER feedback");
    
    echo "Database updated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
