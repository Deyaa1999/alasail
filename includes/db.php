<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '127.0.0.1';
$port = 3306;
$dbname = 'gadgetzone';
$username = 'root';
$password = '';

$conn = @new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;background:#0a0a0f;color:#f59e0b;min-height:100vh;display:flex;align-items:center;justify-content:center;flex-direction:column;">
        <h2>⚠️ Database Connection Failed</h2>
        <p style="color:#9090a8;">Error: ' . htmlspecialchars($conn->connect_error) . '</p>
        <p style="color:#9090a8;">Please ensure MySQL is running on <code>127.0.0.1:3306</code> and the <code>gadgetzone</code> database exists.<br>
        Visit <a href="/GadgetZone/import.php" style="color:#f59e0b;">import.php</a> to create and seed the database.</p>
    </div>');
}

$conn->set_charset('utf8mb4');
