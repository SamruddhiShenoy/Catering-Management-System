<?php
require 'config.php';
$stmt = $pdo->query('SELECT id, name, category, available FROM menu_items');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
