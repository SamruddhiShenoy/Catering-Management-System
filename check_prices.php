<?php
require 'config.php';
$stmt = $pdo->query("SELECT name, price FROM menu_items LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
