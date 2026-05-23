<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';

echo "Connected successfully to MySQL database!\n";

$res = $conn->query("SELECT * FROM settings");
if ($res) {
    echo "Found " . $res->num_rows . " settings in table:\n";
    while ($row = $res->fetch_assoc()) {
        echo " - " . $row['setting_key'] . ": " . $row['setting_value'] . "\n";
    }
} else {
    echo "Error querying settings: " . $conn->error . "\n";
}
?>
