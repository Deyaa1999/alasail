<?php
require_once 'D:/xampp/htdocs/GadgetZone/includes/db.php';
require_once 'D:/xampp/htdocs/GadgetZone/includes/functions.php';

$categories = getCategories();
foreach ($categories as $cat) {
    echo "ID: " . $cat['id'] . " | Name: " . $cat['name'] . " | Icon: " . $cat['icon'] . " | Arabic: " . __($cat['name']) . "\n";
}
