<?php
require_once __DIR__ . '/../includes/db.php';
$p = $conn->query("SELECT slug FROM products LIMIT 1")->fetch_assoc();
if (!$p) {
    echo "No products found!\n";
    exit;
}
$slug = $p['slug'];
$url = 'http://localhost:8080/GadgetZone/pages/product.php?slug=' . $slug;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
if ($html === false) {
    echo "CURL Error: " . curl_error($ch) . "\n";
    exit;
} else {
    echo "Fetched from " . $url . "\n";
}

// Parse HTML to find mobile menu subcategories
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$subcatsDivs = $xpath->query("//div[contains(@class, 'menu-subcats')]");
echo "Found " . $subcatsDivs->length . " menu-subcats divs:\n";
foreach ($subcatsDivs as $div) {
    $id = $div->getAttribute('id');
    $class = $div->getAttribute('class');
    echo "Div ID: $id, Class: $class\n";
    $links = $xpath->query(".//a", $div);
    echo "  Links count: " . $links->length . "\n";
}
