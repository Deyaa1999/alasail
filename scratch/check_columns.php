<?php
require_once __DIR__ . '/../includes/db.php';
$res = $conn->query("DESCRIBE orders");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
