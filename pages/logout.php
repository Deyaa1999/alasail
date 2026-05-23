<?php
require_once __DIR__ . '/../includes/db.php';
session_destroy();
header('Location: /GadgetZone/index.php');
exit;
