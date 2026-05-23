<?php
session_start();
$_SESSION['user_id'] = 1;
$_SERVER['REQUEST_URI'] = '/GadgetZone/pages/myaccount.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
require_once 'D:/xampp/htdocs/GadgetZone/pages/myaccount.php';
$html = ob_get_clean();

$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$subcatsDivs = $xpath->query("//div[contains(@class, 'menu-subcats')]");
echo "Found " . $subcatsDivs->length . " menu-subcats divs in myaccount.php:\n";
foreach ($subcatsDivs as $div) {
    $id = $div->getAttribute('id');
    $links = $xpath->query(".//a", $div);
    echo "  Div ID: $id, Links count: " . $links->length . "\n";
}
