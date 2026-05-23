<?php
$url = 'http://localhost:8080/GadgetZone/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
if ($html === false) {
    echo "CURL Error: " . curl_error($ch) . "\n";
    $url = 'http://localhost/GadgetZone/index.php';
    $ch2 = curl_init($url);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($ch2);
    if ($html === false) {
        echo "CURL Error 2: " . curl_error($ch2) . "\n";
        exit;
    }
}

$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$subcatsDivs = $xpath->query("//div[contains(@class, 'menu-subcats')]");
echo "Found " . $subcatsDivs->length . " menu-subcats divs in index.php:\n";
foreach ($subcatsDivs as $div) {
    $id = $div->getAttribute('id');
    $links = $xpath->query(".//a", $div);
    echo "  Div ID: $id, Links count: " . $links->length . "\n";
    foreach ($links as $link) {
         echo "    - " . trim($link->textContent) . "\n";
    }
}
