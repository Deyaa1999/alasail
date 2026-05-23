<?php
global $conn;
require_once __DIR__ . '/functions.php';
$cartCount = getCartCount();
$user = getCurrentUser();
$categories = getCategories();
$currentPath = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>" dir="<?= getCurrentLang() === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Al Asail — Equine Veterinary Supplies' ?></title>
    <meta name="description" content="Al Asail Equine Veterinary Supplies — Professional horse medicines, supplements, horseshoes, and veterinary products in Jordan.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/GadgetZone/assets/css/style.css?v=<?= filemtime(__DIR__.'/../assets/css/style.css') ?>">
    <?= $extraHead ?? '' ?>
</head>
<body>

<header class="site-header">
    <div class="header-inner container">
        <a href="/GadgetZone/index.php" class="logo">
            <img src="/GadgetZone/assets/images/logo.png" alt="Al Asail Equine Veterinary Supplies" class="site-logo-img">
        </a>

        <nav class="main-nav">
            <a href="/GadgetZone/index.php" class="nav-link"><?= __('home') ?></a>
            <a href="/GadgetZone/pages/shop.php" class="nav-link"><?= __('shop') ?></a>
            <div class="nav-dropdown">
                <span class="nav-link"><?= __('categories') ?> ▾</span>
                <div class="dropdown-menu mega-menu-grid">
                    <?php foreach ($categories as $cat): 
                        $subs = getSubcategories(intval($cat['id']));
                    ?>
                    <div class="mega-menu-column">
                        <a href="/GadgetZone/pages/shop.php?cat=<?= $cat['slug'] ?>" class="mega-menu-title">
                            <span><?= $cat['icon'] ?></span> <?= htmlspecialchars(__($cat['name'])) ?>
                        </a>
                        <div class="mega-menu-subcats">
                            <a href="/GadgetZone/pages/shop.php?cat=<?= $cat['slug'] ?>" class="mega-menu-subitem all-item">
                                <?= __('all_in') ?> <?= htmlspecialchars(__($cat['name'])) ?>
                                <span class="subcat-count"><?= $cat['product_count'] ?></span>
                            </a>
                            <?php foreach ($subs as $sub): ?>
                            <a href="/GadgetZone/pages/shop.php?cat=<?= $cat['slug'] ?>&subcat=<?= $sub['slug'] ?>" class="mega-menu-subitem">
                                <span><?= htmlspecialchars(__($sub['name'])) ?></span>
                                <span class="subcat-count"><?= $sub['product_count'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="/GadgetZone/pages/shop.php?badge=SALE" class="nav-link accent"><?= __('offers') ?></a>
        </nav>

        <div class="header-search">
            <form action="/GadgetZone/pages/shop.php" method="GET">
                <input type="text" name="search" placeholder="<?= __('search_placeholder') ?>" class="search-input" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="search-btn">🔍</button>
            </form>
        </div>

        <div class="header-actions">
            <!-- Language Toggle -->
            <div class="lang-toggle-container">
                <?php if (getCurrentLang() === 'ar'): ?>
                <a href="<?= getLangToggleUrl('en') ?>" class="lang-toggle-btn">EN</a>
                <?php else: ?>
                <a href="<?= getLangToggleUrl('ar') ?>" class="lang-toggle-btn">AR</a>
                <?php endif; ?>
            </div>

            <?php if (isLoggedIn()): ?>
            <a href="/GadgetZone/pages/myaccount.php" class="btn-icon" title="<?= __('my_account') ?>">
                <span class="avatar-tiny"><?= strtoupper(substr($user['first_name'] ?? 'A',0,1).substr($user['last_name'] ?? 'U',0,1)) ?></span>
            </a>
            <?php if (isAdmin()): ?>
            <a href="/GadgetZone/admin/index.php" class="btn-icon" title="<?= __('admin') ?>">⚙️</a>
            <?php endif; ?>
            <?php else: ?>
            <a href="/GadgetZone/pages/login.php" class="btn-outline-sm"><?= __('login') ?></a>
            <?php endif; ?>

            <button type="button" class="btn-icon mobile-search-toggle" id="mobileSearchToggle" title="<?= __('search') ?>">🔍</button>
            <a href="/GadgetZone/pages/cart.php" class="cart-btn">
                🛒
                <span class="cart-badge" style="<?= $cartCount === 0 ? 'display:none' : '' ?>"><?= $cartCount ?></span>
            </a>
        </div>

        <button class="hamburger" id="hamburger" aria-label="Toggle menu">☰</button>
    </div>
</header>

<!-- ── MOBILE SEARCH DROPDOWN ── -->
<div class="mobile-search-dropdown" id="mobileSearchDropdown">
    <div class="container">
        <form action="/GadgetZone/pages/shop.php" method="GET" class="mobile-search-form">
            <input type="text" name="search" placeholder="<?= __('search_placeholder') ?>" class="mobile-search-dropdown-input" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="mobile-search-dropdown-btn">🔍</button>
        </form>
    </div>
</div>

<!-- ── MOBILE MENU SIDE-DRAWER ── -->
<div class="mobile-menu-overlay" id="mobileNav">
    <div class="menu-panel">
        <div class="menu-header">
            <span class="menu-brand"><?= __('menu') ?></span>
            <div class="mobile-lang-toggle">
                <?php if (getCurrentLang() === 'ar'): ?>
                <a href="<?= getLangToggleUrl('en') ?>" class="lang-toggle-btn-mobile">EN</a>
                <?php else: ?>
                <a href="<?= getLangToggleUrl('ar') ?>" class="lang-toggle-btn-mobile">AR</a>
                <?php endif; ?>
            </div>
            <button type="button" class="close-btn" id="closeMobileNav" aria-label="Close menu">✕</button>
        </div>

        <?php if (isLoggedIn() && isset($user) && $user): ?>
        <div class="menu-user">
            <div class="user-avatar"><?= strtoupper(substr($user['first_name'] ?? '',0,1).substr($user['last_name'] ?? '',0,1)) ?></div>
            <div class="user-info">
                <p><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></p>
                <a href="/GadgetZone/pages/myaccount.php" style="text-decoration:none;"><span><?= __('my_account') ?></span></a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Mobile Search -->
        <div class="menu-search">
            <form action="/GadgetZone/pages/shop.php" method="GET">
                <input type="text" name="search" placeholder="<?= __('search_placeholder') ?>" class="menu-search-input" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="menu-search-btn">🔍</button>
            </form>
        </div>

        <div class="menu-section-label"><?= __('navigation') ?></div>

        <a href="/GadgetZone/index.php" class="menu-item <?= ($currentPath === '/GadgetZone/index.php' || $currentPath === '/GadgetZone/') ? 'active' : '' ?>">
            <div class="menu-icon">🏠</div>
            <div class="menu-item-text"><p><?= __('home') ?></p></div>
            <div class="menu-arrow">➔</div>
        </a>

        <a href="/GadgetZone/pages/shop.php" class="menu-item <?= ($currentPath === '/GadgetZone/pages/shop.php' && empty($_GET['cat']) && empty($_GET['badge'])) ? 'active' : '' ?>">
            <div class="menu-icon">🛍️</div>
            <div class="menu-item-text"><p><?= __('shop') ?></p></div>
            <div class="menu-arrow">➔</div>
        </a>

        <div class="menu-section-label"><?= __('categories') ?></div>

        <?php foreach ($categories as $cat): 
            $subcategories = getSubcategories(intval($cat['id']));
            $has_subs = !empty($subcategories);
        ?>
            <?php if ($has_subs): ?>
                <div class="menu-item-parent <?= (($_GET['cat'] ?? '') === $cat['slug']) ? 'active open' : '' ?>" data-cat-id="<?= $cat['id'] ?>">
                    <div class="menu-icon"><?= $cat['icon'] ?></div>
                    <div class="menu-item-text">
                        <p><?= htmlspecialchars(__($cat['name'])) ?></p>
                    </div>
                    <div class="menu-chevron">▾</div>
                </div>
                <div class="menu-subcats <?= (($_GET['cat'] ?? '') === $cat['slug']) ? 'open' : '' ?>" id="subcats-<?= $cat['id'] ?>">
                    <a href="/GadgetZone/pages/shop.php?cat=<?= $cat['slug'] ?>" class="menu-subcat-item <?= (($_GET['cat'] ?? '') === $cat['slug'] && empty($_GET['subcat'])) ? 'active' : '' ?>">
                        <?= __('all_in') ?> <?= htmlspecialchars(__($cat['name'])) ?>
                    </a>
                    <?php foreach ($subcategories as $sub): ?>
                    <a href="/GadgetZone/pages/shop.php?cat=<?= $cat['slug'] ?>&subcat=<?= $sub['slug'] ?>" class="menu-subcat-item <?= (($_GET['subcat'] ?? '') === $sub['slug']) ? 'active' : '' ?>">
                        <?= htmlspecialchars(__($sub['name'])) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <a href="/GadgetZone/pages/shop.php?cat=<?= $cat['slug'] ?>" class="menu-item <?= (($_GET['cat'] ?? '') === $cat['slug']) ? 'active' : '' ?>">
                    <div class="menu-icon"><?= $cat['icon'] ?></div>
                    <div class="menu-item-text"><p><?= htmlspecialchars(__($cat['name'])) ?></p></div>
                    <div class="menu-arrow">➔</div>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>

        <a href="/GadgetZone/pages/shop.php?badge=SALE" class="menu-item <?= (($_GET['badge'] ?? '') === 'SALE') ? 'active' : '' ?>">
            <div class="menu-icon">🏷️</div>
            <div class="menu-item-text"><p><?= __('offers') ?></p></div>
            <div class="menu-arrow">➔</div>
        </a>

        <div class="menu-footer">
            <?php if (isLoggedIn()): ?>
            <a href="/GadgetZone/pages/logout.php" class="logout-btn">
                <span>🚪 <?= __('logout') ?></span>
            </a>
            <?php else: ?>
            <div style="display:flex;gap:10px;">
                <a href="/GadgetZone/pages/login.php" class="menu-btn-outline" style="flex:1;text-align:center;"><?= __('login') ?></a>
                <a href="/GadgetZone/pages/register.php" class="menu-btn-primary" style="flex:1;text-align:center;"><?= __('create_account') ?></a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="dim-area" id="dimMobileNav">
        <div class="dim-hint">
            <p><?= __('tap_to_close') ?></p>
        </div>
    </div>
</div>

<main>
