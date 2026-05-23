<?php
$url = 'http://localhost:8080/GadgetZone/pages/shop.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
if ($html === false) {
    echo "CURL Error: " . curl_error($ch) . "\n";
    // Try without port 8080
    $url = 'http://localhost/GadgetZone/pages/shop.php';
    $ch2 = curl_init($url);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($ch2);
    if ($html === false) {
        echo "CURL Error 2: " . curl_error($ch2) . "\n";
        exit;
    } else {
        echo "Fetched from " . $url . "\n";
    }
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
    foreach ($links as $link) {
        echo "    - " . trim($link->textContent) . " (" . $link->getAttribute('href') . ")\n";
    }
}
