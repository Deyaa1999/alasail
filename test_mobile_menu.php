<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$categories = getCategories();
foreach ($categories as $cat) {
    if ($cat['id'] == 3) {
        $subcategories = getSubcategories(intval($cat['id']));
        echo "Category: " . $cat['name'] . "\n";
        echo "Subcategories count: " . count($subcategories) . "\n";
        foreach ($subcategories as $sub) {
            echo " - Subcat: " . $sub['name'] . " (slug: " . $sub['slug'] . ")\n";
        }
    }
}
