<?php
$pages = [
    'index.php' => 'http://localhost:8080/GadgetZone/index.php',
    'shop.php' => 'http://localhost:8080/GadgetZone/pages/shop.php',
    'product.php' => 'http://localhost:8080/GadgetZone/pages/product.php?slug=aluminum-therapeutic-shoe',
    'myaccount.php' => 'http://localhost:8080/GadgetZone/pages/myaccount.php',
    'checkout.php' => 'http://localhost:8080/GadgetZone/pages/checkout.php',
    'cart.php' => 'http://localhost:8080/GadgetZone/pages/cart.php',
    'login.php' => 'http://localhost:8080/GadgetZone/pages/login.php',
    'register.php' => 'http://localhost:8080/GadgetZone/pages/register.php',
    'order_success.php' => 'http://localhost:8080/GadgetZone/pages/order_success.php?order=EVS-1234'
];

foreach ($pages as $name => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=test_session_id'); // mock session if needed
    $html = curl_exec($ch);
    if ($html === false) {
        // Try fallback without port
        $urlFallback = str_replace(':8080', '', $url);
        $ch2 = curl_init($urlFallback);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($ch2);
    }
    
    if ($html === false) {
        echo "$name: Failed to fetch\n";
        continue;
    }
    
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $subcatsDivs = $xpath->query("//div[contains(@class, 'menu-subcats')]");
    echo "$name: Found " . $subcatsDivs->length . " menu-subcats divs\n";
    foreach ($subcatsDivs as $div) {
        $id = $div->getAttribute('id');
        $links = $xpath->query(".//a", $div);
        echo "  - $id: " . $links->length . " links\n";
    }
}
