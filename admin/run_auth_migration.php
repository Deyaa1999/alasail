<?php
/**
 * Safe Database Migration for Auth Enhancements (Google Sign-In & Password Reset)
 * Run this via browser or command line.
 */
require_once __DIR__ . '/../includes/db.php';

echo "<h2>Al Asail Equine — Auth Migration</h2>";

$log = [];
$errors = [];

function checkAndAddColumn(mysqli $conn, string $table, string $column, string $definition, array &$log, array &$errors): void {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($res && $res->num_rows === 0) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if ($conn->query($sql)) {
            $log[] = "✅ Added column `$column` to `$table` table.";
        } else {
            $errors[] = "❌ Failed adding `$column` to `$table` table — " . $conn->error;
        }
    } else {
        $log[] = "⏭️ Column `$column` already exists in `$table` table.";
    }
}

// 1. Add google_id to users
checkAndAddColumn($conn, 'users', 'google_id', "VARCHAR(255) NULL UNIQUE DEFAULT NULL AFTER avatar", $log, $errors);

// 2. Add reset_token to users
checkAndAddColumn($conn, 'users', 'reset_token', "VARCHAR(255) NULL UNIQUE DEFAULT NULL AFTER google_id", $log, $errors);

// 3. Add token_expires to users
checkAndAddColumn($conn, 'users', 'token_expires', "DATETIME NULL DEFAULT NULL AFTER reset_token", $log, $errors);

// 4. Ensure google_client_id exists in settings table
$res = $conn->query("SELECT id FROM settings WHERE setting_key = 'google_client_id'");
if ($res && $res->num_rows === 0) {
    if ($conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('google_client_id', '')")) {
        $log[] = "✅ Added google_client_id setting key.";
    } else {
        $errors[] = "❌ Failed to add google_client_id setting key — " . $conn->error;
    }
} else {
    $log[] = "⏭️ google_client_id setting key already exists.";
}

echo "<h3>Migration Results:</h3><ul>";
foreach ($log as $l) {
    echo "<li>$l</li>";
}
echo "</ul>";

if (!empty($errors)) {
    echo "<h3 style='color:red;'>Errors:</h3><ul>";
    foreach ($errors as $e) {
        echo "<li style='color:red;'>$e</li>";
    }
    echo "</ul>";
} else {
    echo "<h3 style='color:green;'>Migration completed successfully!</h3>";
}
?>
