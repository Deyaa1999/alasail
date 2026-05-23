<?php
$c = new mysqli('127.0.0.1', 'root', '', 'gadgetzone', 3306);
$c->query("UPDATE settings SET setting_value = '' WHERE setting_key = 'google_client_id'");
echo "Cleared invalid google_client_id from database settings.\n";
