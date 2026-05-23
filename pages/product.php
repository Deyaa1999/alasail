<?php
require_once __DIR__ . '/../includes/functions.php';
$slug = $conn->real_escape_string($_GET['slug'] ?? '');
$product = $conn->query("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug FROM products p JOIN categories c ON c.id=p.category_id WHERE p.slug='$slug'")->fetch_assoc();
if (!$product) { header('Location: /GadgetZone/pages/shop.php'); exit; }

$pageTitle = htmlspecialchars($product['name']) . ' — GadgetZone';

// Related products
$catId = (int)$product['category_id'];
$pid   = (int)$product['id'];
$related_res = $conn->query("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.category_id=$catId AND p.id!=$pid LIMIT 4");
$relatedList = [];
if ($related_res) {
    while ($row = $related_res->fetch_assoc()) {
        $relatedList[] = $row;
    }
    $related_res->free();
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="/GadgetZone/index.php"><?= __('home') ?></a> <span>›</span>
        <a href="/GadgetZone/pages/shop.php"><?= __('shop') ?></a> <span>›</span>
        <a href="/GadgetZone/pages/shop.php?cat=<?= $product['cat_slug'] ?>"><?= htmlspecialchars(__($product['cat_name'])) ?></a>
        <span>›</span> <?= htmlspecialchars($product['name']) ?>
    </div>

    <div class="product-detail">
        <div class="product-detail-img">
            <?php if ($product['badge']): ?><span class="badge <?= badgeClass($product['badge']) ?>" style="position:static;display:inline-block;margin-bottom:12px"><?= $product['badge'] ?></span><?php endif; ?>
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="product-detail-info">
            <div class="product-cat" style="font-size:13px;margin-bottom:8px"><?= htmlspecialchars(__($product['cat_name'])) ?></div>
            <h1 class="product-detail-title"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="stars" style="font-size:18px;margin-bottom:12px">★★★★★ <span style="font-size:13px;color:var(--text2);">(4.9 · 128 <?= __('reviews') ?>)</span></div>
            <div class="product-detail-price"><?= formatPrice($product['price']) ?></div>
            <?php if ($product['old_price']): ?>
            <div class="product-detail-old"><?= formatPrice($product['old_price']) ?>
                <span style="font-size:13px;background:var(--accent);color:#000;padding:2px 8px;border-radius:5px;margin-left:8px;font-style:normal">
                    <?= round((1 - $product['price']/$product['old_price'])*100) ?>% <?= __('off') ?>
                </span>
            </div>
            <?php endif; ?>
            <p class="product-detail-desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <div class="stock-info">✅ <?= __('in_stock_msg') ?> — <?= $product['stock'] ?> <?= __('units_available') ?></div>
            <div class="qty-row">
                <label style="font-size:13px;color:var(--text2)"><?= __('quantity') ?></label>
                <div class="qty-control">
                    <button type="button" class="qty-btn" onclick="let i=document.getElementById('detail-qty');i.value=Math.max(1,+i.value-1)">−</button>
                    <input type="number" id="detail-qty" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button type="button" class="qty-btn" onclick="let i=document.getElementById('detail-qty');i.value=Math.min(<?= $product['stock'] ?>,+i.value+1)">+</button>
                </div>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
                <button class="btn-primary btn-add-cart" data-id="<?= $product['id'] ?>" style="flex:1;min-width:180px">🛒 <?= __('add_to_cart') ?></button>
                <a href="/GadgetZone/pages/cart.php" class="btn-outline" style="flex:1;min-width:180px;text-align:center"><?= __('go_to_cart') ?> →</a>
            </div>
            <div style="margin-top:24px;display:flex;flex-direction:column;gap:8px">
                <div style="display:flex;gap:10px;font-size:13px;color:var(--text2)"><span>🚚</span> <?= __('free_delivery_over') ?> <?= formatPrice(5000) ?></div>
                <div style="display:flex;gap:10px;font-size:13px;color:var(--text2)"><span>↩️</span> <?= __('returns_policy') ?></div>
                <div style="display:flex;gap:10px;font-size:13px;color:var(--text2)"><span>🛡️</span> <?= __('warranty_policy') ?></div>
                <div style="display:flex;gap:10px;font-size:13px;color:var(--text2)"><span>🔒</span> <?= __('secure_encrypted') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- RELATED PRODUCTS -->
<?php if (!empty($relatedList)): ?>
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="section-header">
            <div class="section-label"><?= __('similar_products') ?></div>
            <h2 class="section-title"><?= __('similar_products') ?></h2>
        </div>
        <div class="products-grid-4">
            <?php foreach ($relatedList as $p): ?>
            <div class="product-card">
                <?php if ($p['badge']): ?><span class="badge <?= badgeClass($p['badge']) ?>"><?= __($p['badge']) ?></span><?php endif; ?>
                <a href="/GadgetZone/pages/product.php?slug=<?= $p['slug'] ?>">
                    <div class="product-card-img">
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    </div>
                </a>
                <div class="product-card-body">
                    <div class="product-cat"><?= htmlspecialchars(__($p['cat_name'])) ?></div>
                    <a href="/GadgetZone/pages/product.php?slug=<?= $p['slug'] ?>">
                        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                    </a>
                    <div class="stars">★★★★★</div>
                    <div class="product-price">
                        <span class="price-current"><?= formatPrice($p['price']) ?></span>
                        <?php if ($p['old_price']): ?><span class="price-old"><?= formatPrice($p['old_price']) ?></span><?php endif; ?>
                    </div>
                    <button class="btn-add-cart" data-id="<?= $p['id'] ?>"><?= __('add_to_cart') ?></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
