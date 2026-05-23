<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';

echo "=== SMTP DIAGNOSTIC TOOL ===\n";

// Load configuration
$config = [];
$s_res = $conn->query("SELECT * FROM settings WHERE setting_key IN ('notification_email', 'sender_email', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure')");
while ($row = $s_res->fetch_assoc()) {
    $config[$row['setting_key']] = $row['setting_value'];
}

$to = trim($config['notification_email'] ?? 'daldebsi@gmail.com');
$from = trim($config['sender_email'] ?? 'daldebsi@gmail.com');
$host = trim($config['smtp_host'] ?? 'smtp.gmail.com');
$port = intval($config['smtp_port'] ?? 587);
$user = trim($config['smtp_user'] ?? '');
$pass = trim($config['smtp_pass'] ?? '');
$secure = strtolower(trim($config['smtp_secure'] ?? 'tls'));

echo "Saved Configuration:\n";
echo " - To Email: $to\n";
echo " - From Email: $from\n";
echo " - SMTP Host: $host\n";
echo " - SMTP Port: $port\n";
echo " - SMTP User: $user\n";
echo " - SMTP Pass: " . (empty($pass) ? "[EMPTY / NOT SET]" : "[SET (Length: " . strlen($pass) . ")]") . "\n";
echo " - Encryption: $secure\n\n";

if (empty($user) || empty($pass)) {
    echo "⚠️ Warning: SMTP Username or Password is not configured in the settings.\n";
    echo "Without these credentials, PHP falls back to the native mail() function, which usually fails on local localhost XAMPP environments.\n";
    echo "Please visit the admin panel (http://localhost:8080/GadgetZone/admin/settings.php) and configure SMTP credentials.\n";
    exit;
}

echo "Attempting to connect to $host on port $port...\n";

$socketHost = $host;
if ($secure === 'ssl') {
    $socketHost = 'ssl://' . $host;
}

$socket = @fsockopen($socketHost, $port, $errno, $errstr, 15);
if (!$socket) {
    echo "❌ Connection failed: $errstr ($errno)\n";
    exit;
}
echo "✅ Connected to socket successfully!\n";

function readResponse($socket) {
    $res = '';
    while (($line = fgets($socket, 515)) !== false) {
        $res .= $line;
        echo "S: " . trim($line) . "\n";
        if (substr($line, 3, 1) === ' ') {
            break;
        }
    }
    return $res;
}

function sendCommand($socket, $cmd) {
    echo "C: $cmd\n";
    fwrite($socket, $cmd . "\r\n");
}

// Initial reading
readResponse($socket);

// EHLO
sendCommand($socket, "EHLO localhost");
readResponse($socket);

// STARTTLS
if ($secure === 'tls') {
    sendCommand($socket, "STARTTLS");
    readResponse($socket);
    
    echo "Enabling TLS encryption on stream...\n";
    if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        echo "✅ TLS encryption successfully enabled!\n";
    } else {
        echo "❌ Failed to enable TLS encryption.\n";
        fclose($socket);
        exit;
    }
    
    sendCommand($socket, "EHLO localhost");
    readResponse($socket);
}

// Auth Login
sendCommand($socket, "AUTH LOGIN");
$res = readResponse($socket);
if (substr($res, 0, 3) === '334') {
    sendCommand($socket, base64_encode($user));
    $res = readResponse($socket);
    
    if (substr($res, 0, 3) === '334') {
        sendCommand($socket, base64_encode($pass));
        $res = readResponse($socket);
        
        if (substr($res, 0, 3) === '235') {
            echo "✅ Authentication SUCCESSFUL!\n";
        } else {
            echo "❌ Authentication FAILED! Check your username/password (If using Gmail, make sure to use an App Password).\n";
            fclose($socket);
            exit;
        }
    }
}

// QUIT
sendCommand($socket, "QUIT");
readResponse($socket);
fclose($socket);
echo "=== SMTP DIAGNOSTIC COMPLETED ===\n";
?>
