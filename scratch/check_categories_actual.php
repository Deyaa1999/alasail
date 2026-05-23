<?php
require_once 'D:/xampp/htdocs/GadgetZone/includes/db.php';
$res = $conn->query("SELECT * FROM categories");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
