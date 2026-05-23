<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$categories = getCategories();
foreach ($categories as $cat) {
    echo "Category: " . $cat['name'] . " (ID: " . $cat['id'] . ", Slug: " . $cat['slug'] . ")\n";
    $subcategories = getSubcategories($cat['id']);
    foreach ($subcategories as $sub) {
        echo "  - Subcategory: " . $sub['name'] . " (ID: " . $sub['id'] . ", Slug: " . $sub['slug'] . ")\n";
    }
}
