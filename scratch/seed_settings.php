<?php
require_once __DIR__ . '/../includes/db.php';

// Seed new settings
$settings = [
    'notification_email' => 'daldebsi@gmail.com',
    'sender_email'       => 'daldebsi@gmail.com',
    'smtp_host'          => 'smtp.gmail.com',
    'smtp_port'          => '587',
    'smtp_user'          => 'daldebsi@gmail.com',
    'smtp_secure'        => 'tls'
];

foreach ($settings as $k => $v) {
    $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$k', '$v') ON DUPLICATE KEY UPDATE setting_value='$v'");
}

// Delete old WhatsApp configuration key if it exists
$conn->query("DELETE FROM settings WHERE setting_key = 'whatsapp_notification_num'");

echo "Database successfully seeded and cleaned!\n";
?>
