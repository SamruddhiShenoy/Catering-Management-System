<?php 
require 'config.php'; 
print_r($pdo->query('SELECT id, name, image_path, emoji FROM menu_items LIMIT 5')->fetchAll(PDO::FETCH_ASSOC)); 
?>
