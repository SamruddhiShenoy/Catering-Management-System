<?php
require 'config.php';
$stmt = $pdo->query("SELECT DISTINCT category FROM menu_items WHERE available = 1 ORDER BY category");
foreach($stmt as $row) {
    $cat = $row['category'];
    echo $cat . ' -> ' . preg_replace('/[^a-zA-Z0-9]/', '_', $cat) . "\n";
}
?>
