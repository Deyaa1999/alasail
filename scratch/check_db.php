<?php
require_once __DIR__ . '/../includes/db.php';
$res = $conn->query("SELECT * FROM settings");
while ($r = $res->fetch_assoc()) {
    echo $r['setting_key'] . " = " . $r['setting_value'] . "\n";
}
