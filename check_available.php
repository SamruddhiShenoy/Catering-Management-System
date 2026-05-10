<?php
require 'config.php';
$stmt = $pdo->query('SELECT COUNT(*) FROM menu_items WHERE available = 1');
echo "Available items: " . $stmt->fetchColumn() . "\n";
$stmt = $pdo->query('SELECT DISTINCT category FROM menu_items WHERE available = 1');
echo "Categories: " . implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN)) . "\n";
?>
