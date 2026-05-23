<?php
/**
 * Admin: Get subcategories by category ID — AJAX endpoint
 */
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode([]);
    exit;
}

$catId = (int)($_GET['cat_id'] ?? 0);
if (!$catId) { echo json_encode([]); exit; }

$subs = getSubcategories($catId);
echo json_encode($subs);
