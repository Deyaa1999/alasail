<?php
require_once __DIR__ . '/../includes/functions.php';
if (!isLoggedIn() || !isAdmin()) {
    header('Location: /GadgetZone/pages/login.php');
    exit;
}
$adminUser = getCurrentUser();
$adminPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin — Al Asail Equine' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/GadgetZone/admin/admin.css">
</head>
<body class="admin-body">

<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<div class="admin-layout">
    <!-- Mobile top bar -->
    <header class="admin-mobile-header">
        <button type="button" id="sidebarToggle" class="admin-mobile-toggle" aria-label="Toggle Navigation">
            <span class="toggle-bar"></span>
            <span class="toggle-bar"></span>
            <span class="toggle-bar"></span>
        </button>
        <div class="admin-mobile-logo">
            <a href="/GadgetZone/index.php">
                <img src="/GadgetZone/assets/images/logo.png" alt="Al Asail Equine">
            </a>
        </div>
        <div class="admin-mobile-avatar">
            <?= strtoupper(substr($adminUser['first_name'] ?? 'A',0,1).substr($adminUser['last_name'] ?? 'M',0,1)) ?>
        </div>
    </header>

    <aside class="admin-sidebar">
        <div class="admin-logo">
            <a href="/GadgetZone/index.php">
                <img src="/GadgetZone/assets/images/logo.png" alt="Al Asail Equine" style="height:44px;object-fit:contain;display:block;margin:0 auto;">
            </a>
            <div class="admin-badge">Admin</div>
        </div>
        <nav class="admin-nav">
            <a href="/GadgetZone/admin/index.php"      class="<?= $adminPage==='index'?'active':'' ?>">📊 Dashboard</a>
            <a href="/GadgetZone/admin/products.php"   class="<?= $adminPage==='products'?'active':'' ?>">📦 Products</a>
            <a href="/GadgetZone/admin/categories.php" class="<?= $adminPage==='categories'?'active':'' ?>">📂 Categories</a>
            <a href="/GadgetZone/admin/orders.php"     class="<?= $adminPage==='orders'?'active':'' ?>">🛍️ Orders</a>
            <a href="/GadgetZone/admin/users.php"      class="<?= $adminPage==='users'?'active':'' ?>">👥 Users</a>
            <a href="/GadgetZone/admin/settings.php"   class="<?= $adminPage==='settings'?'active':'' ?>">⚙️ Settings</a>
        </nav>
        <div class="admin-sidebar-footer">
            <div class="admin-user-info">
                <div class="admin-avatar"><?= strtoupper(substr($adminUser['first_name'],0,1).substr($adminUser['last_name'],0,1)) ?></div>
                <div>
                    <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($adminUser['first_name'].' '.$adminUser['last_name']) ?></div>
                    <div style="font-size:11px;color:#9090a8"><?= htmlspecialchars($adminUser['role']) ?></div>
                </div>
            </div>
            <a href="/GadgetZone/pages/logout.php" class="admin-logout">🚪 Logout</a>
        </div>
    </aside>
    <main class="admin-main">
