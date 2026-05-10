<?php
require 'config.php';
$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM menu_items WHERE available = 1 GROUP BY category");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Category Counts:\n";
foreach($rows as $row) {
    echo "[" . $row['category'] . "]: " . $row['count'] . "\n";
}
?>
