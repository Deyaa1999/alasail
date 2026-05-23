<?php
// Mock $_SERVER
$_SERVER['REQUEST_URI'] = '/GadgetZone/pages/shop.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once 'D:/xampp/htdocs/GadgetZone/includes/db.php';
require_once 'D:/xampp/htdocs/GadgetZone/includes/functions.php';

// Simulate shop.php active query
$whereStr = '1=1';
$joinSubcat = "LEFT JOIN subcategories s ON s.id=p.subcategory_id";
$orderBy = 'p.created_at DESC';
$perPage = 9;
$offset = 0;

$products = $conn->query("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
    s.name AS subcat_name
    FROM products p
    JOIN categories c ON c.id=p.category_id
    $joinSubcat
    WHERE $whereStr ORDER BY $orderBy LIMIT $perPage OFFSET $offset");

// Now get categories and their subcategories
$categories = getCategories();
echo "Simulating shop.php environment:\n";
foreach ($categories as $cat) {
    $subcategories = getSubcategories(intval($cat['id']));
    echo "Category: " . $cat['name'] . " (Count of subcategories: " . count($subcategories) . ")\n";
    if (count($subcategories) > 0) {
        echo "  First subcategory: " . $subcategories[0]['name'] . "\n";
    }
}
