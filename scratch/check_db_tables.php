<?php
require_once __DIR__ . '/../includes/db.php';

echo "--- DATABASES ---\n";
$res = $conn->query("SHOW DATABASES");
while ($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}

echo "\n--- TABLES in gadgetzone ---\n";
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}

echo "\n--- CATEGORIES ---\n";
$res = $conn->query("SELECT * FROM categories");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Slug: {$row['slug']} | Icon: {$row['icon']}\n";
}

echo "\n--- SUBCATEGORIES ---\n";
$res = $conn->query("SELECT * FROM subcategories");
while ($row = $res->fetch_assoc()) {
    echo "ID: {$row['id']} | CatID: {$row['category_id']} | Name: {$row['name']} | Slug: {$row['slug']}\n";
}
