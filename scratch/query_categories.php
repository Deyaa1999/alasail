<?php
require_once __DIR__ . '/../includes/db.php';
$res = $conn->query("SELECT * FROM categories");
$cats = [];
while ($row = $res->fetch_assoc()) {
    $cats[] = $row;
}
echo json_encode($cats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
