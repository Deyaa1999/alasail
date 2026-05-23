<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

echo "Simulating active product query on shop.php:\n";
$products = $conn->query("SELECT * FROM products LIMIT 5");

$categories = getCategories();
echo "Categories count: " . count($categories) . "\n";
foreach ($categories as $cat) {
    $subs = getSubcategories($cat['id']);
    echo "Category " . $cat['name'] . " (ID: " . $cat['id'] . ") has " . count($subs) . " subcategories\n";
}
