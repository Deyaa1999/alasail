<?php
$_SESSION = [];
$_SERVER['REQUEST_URI'] = '/GadgetZone/index.php';
require_once 'D:/xampp/htdocs/GadgetZone/includes/db.php';
require_once 'D:/xampp/htdocs/GadgetZone/includes/functions.php';

// Capture output ofincludes/header.php
ob_start();
require_once 'D:/xampp/htdocs/GadgetZone/includes/header.php';
$html = ob_get_clean();

// Extract the mobile navigation menu section
$start = strpos($html, '<!-- ── MOBILE MENU SIDE-DRAWER ── -->');
if ($start !== false) {
    $mobile_menu = substr($html, $start);
    echo $mobile_menu;
} else {
    echo "Mobile menu not found in HTML";
}
