<?php
// Mock session and environment
$_SESSION = [];
$_SERVER['REQUEST_URI'] = '/GadgetZone/index.php';

// Include the database and functions
require_once 'D:/xampp/htdocs/GadgetZone/includes/db.php';
require_once 'D:/xampp/htdocs/GadgetZone/includes/functions.php';

// Get categories
$categories = getCategories();

echo "HTML mobile menu categories:\n";
foreach ($categories as $cat) {
    $subcategories = getSubcategories(intval($cat['id']));
    $has_subs = !empty($subcategories);
    echo "Category: " . $cat['name'] . " (Has subs: " . ($has_subs ? 'YES' : 'NO') . ", Count: " . count($subcategories) . ")\n";
    if ($has_subs) {
        echo "  Subcats:\n";
        foreach ($subcategories as $sub) {
            echo "    - " . $sub['name'] . " (slug: " . $sub['slug'] . ")\n";
        }
    }
}
