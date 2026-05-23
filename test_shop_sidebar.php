<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$allCats = $conn->query("SELECT c.*, COUNT(p.id) AS cnt FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.order_num ASC");

echo "Testing Shop Sidebar Subcategories:\n";
while ($c2 = $allCats->fetch_assoc()) {
    echo "Category: " . $c2['name'] . " (ID: " . $c2['id'] . ")\n";
    $subs = getSubcategories($c2['id']);
    echo "  - Count: " . count($subs) . "\n";
    foreach ($subs as $s) {
        echo "    * Subcat: " . $s['name'] . "\n";
    }
}
