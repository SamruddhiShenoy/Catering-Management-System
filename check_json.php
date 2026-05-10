<?php
require 'config.php';
$stmt = $pdo->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY category, name");
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$menu_by_cat = [];
foreach($menu_items as $item) {
    $menu_by_cat[$item['category']][] = $item;
}
$json = json_encode($menu_by_cat);
if ($json === false) {
    echo "JSON encoding failed: " . json_last_error_msg() . "\n";
} else {
    echo "JSON encoding success. Length: " . strlen($json) . "\n";
}
?>
