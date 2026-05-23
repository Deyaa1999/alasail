<?php
$pageTitle = 'Shop — Al Asail Equine Veterinary Supplies';
require_once __DIR__ . '/../includes/functions.php';

// Filters
$search   = trim($_GET['search']   ?? '');
$cat      = trim($_GET['cat']      ?? '');
$subcat   = trim($_GET['subcat']   ?? '');
$sort     = $_GET['sort']          ?? 'newest';
$badge    = $_GET['badge']         ?? '';
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 9999;
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 9;

$where = ['1=1'];
if ($search) {
    $s = $conn->real_escape_string($search);
    $where[] = "LOWER(p.name) LIKE LOWER('%$s%')";
}
if ($cat) {
    $c = $conn->real_escape_string($cat);
    $where[] = "c.slug='$c'";
}
if ($subcat) {
    // subcat can be slug or id
    $sc = $conn->real_escape_string($subcat);
    $where[] = "(s.slug='$sc' OR s.id='$sc')";
}
if ($badge) {
    $b = $conn->real_escape_string($badge);
    $where[] = "p.badge='$b'";
}
if ($maxPrice < 9999) {
    $where[] = "p.price <= $maxPrice";
}

$whereStr = implode(' AND ', $where);
$orderBy  = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'popular'    => 'p.featured DESC, p.id DESC',
    'rating'     => 'p.id DESC',
    default      => 'p.created_at DESC',
};

$joinSubcat = "LEFT JOIN subcategories s ON s.id=p.subcategory_id";

// Count total
$total      = (int)$conn->query("SELECT COUNT(*) FROM products p JOIN categories c ON c.id=p.category_id $joinSubcat WHERE $whereStr")->fetch_row()[0];
$totalPages = (int)ceil($total / $perPage);
$offset     = ($page - 1) * $perPage;

$products_res = $conn->query("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
    s.name AS subcat_name
    FROM products p
    JOIN categories c ON c.id=p.category_id
    $joinSubcat
    WHERE $whereStr ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$productsList = [];
if ($products_res) {
    while ($row = $products_res->fetch_assoc()) {
        $productsList[] = $row;
    }
    $products_res->free();
}

// Category list with counts for sidebar
$allCats_res = $conn->query("SELECT c.*, COUNT(p.id) AS cnt FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.order_num ASC");
$allCatsList = [];
if ($allCats_res) {
    while ($row = $allCats_res->fetch_assoc()) {
        $allCatsList[] = $row;
    }
    $allCats_res->free();
}

// If a category is selected, fetch its subcategories
$selectedCatObj = null;
$subcatList = [];
if ($cat) {
    $selectedCatObj = $conn->query("SELECT * FROM categories WHERE slug='".$conn->real_escape_string($cat)."'")->fetch_assoc();
    if ($selectedCatObj) {
        $subcatList = getSubcategories($selectedCatObj['id']);
    }
}
$currentCat = $selectedCatObj['name'] ?? '';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:20px">
    <div class="breadcrumb">
        <a href="/GadgetZone/index.php"><?= __('home') ?></a> <span>›</span>
        <a href="/GadgetZone/pages/shop.php"><?= __('shop') ?></a>
        <?php if ($currentCat): ?><span>›</span> <?= htmlspecialchars($currentCat) ?><?php endif; ?>
        <?php if ($subcat && !empty($subcatList)): ?>
            <?php foreach ($subcatList as $sc): if ($sc['slug'] == $subcat || $sc['id'] == $subcat): ?>
                <span>›</span> <?= htmlspecialchars($sc['name']) ?>
            <?php endif; endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Mobile filter toggle -->
    <button class="filter-toggle-btn" id="filterToggle" onclick="document.getElementById('shopSidebar').classList.toggle('open')">
        ⚡ <?= __('filters') ?>
    </button>

    <div class="shop-layout" style="margin-top:16px">
        <!-- ── SIDEBAR ── -->
        <aside class="shop-sidebar" id="shopSidebar">
            <button class="sidebar-close-btn" onclick="document.getElementById('shopSidebar').classList.remove('open')">✕ <?= __('close') ?></button>
            <form method="GET" id="filterForm">
                <input type="hidden" name="sort"   value="<?= htmlspecialchars($sort) ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php if ($badge): ?><input type="hidden" name="badge" value="<?= htmlspecialchars($badge) ?>"><?php endif; ?>

                <!-- ── CATEGORIES ── -->
                <div class="sidebar-title">📂 <?= __('categories') ?></div>
                <div class="filter-option">
                    <input type="radio" name="cat" id="cat_all" value="" <?= !$cat ? 'checked' : '' ?> onchange="clearSubcatAndSubmit(this)">
                    <label for="cat_all"><?= __('all_categories') ?></label>
                    <span class="filter-count"><?= $total ?></span>
                </div>
                <?php 
                foreach ($allCatsList as $c2): 
                    $subs = getSubcategories($c2['id']);
                    $isCurrentOrChildActive = ($cat === $c2['slug']);
                    if (!$isCurrentOrChildActive && $subcat && !empty($subs)) {
                        foreach ($subs as $sc) {
                            if ($sc['slug'] === $subcat) {
                                $isCurrentOrChildActive = true;
                                break;
                            }
                        }
                    }
                ?>
                <div class="cat-filter-group">
                    <div class="cat-filter-header">
                        <div class="filter-option">
                            <input type="radio" name="cat" id="cat_<?= $c2['slug'] ?>"
                                   value="<?= $c2['slug'] ?>"
                                   <?= $cat === $c2['slug'] ? 'checked' : '' ?>
                                   onchange="clearSubcatAndSubmit(this)">
                            <label for="cat_<?= $c2['slug'] ?>"><?= $c2['icon'] ?> <?= htmlspecialchars(__($c2['name'])) ?></label>
                            <span class="filter-count"><?= $c2['cnt'] ?></span>
                        </div>
                        <?php if (!empty($subs)): ?>
                        <button type="button" class="subcat-toggle-btn" id="toggle_btn_<?= $c2['id'] ?>" onclick="toggleSubcat(this, 'subcat_list_<?= $c2['id'] ?>')">
                            <?= $isCurrentOrChildActive ? '−' : '＋' ?>
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($subs)): ?>
                    <div class="subcat-list <?= $isCurrentOrChildActive ? 'open' : '' ?>" id="subcat_list_<?= $c2['id'] ?>">
                        <div class="filter-option subcat-option">
                            <input type="radio" name="subcat" id="subcat_all_<?= $c2['id'] ?>" value=""
                                   <?= ($cat === $c2['slug'] && !$subcat) ? 'checked' : '' ?>
                                   onchange="selectCatAndSubmit(this, '<?= $c2['slug'] ?>')">
                            <label for="subcat_all_<?= $c2['id'] ?>" style="font-style:italic"><?= __('all_in') ?> <?= htmlspecialchars(__($c2['name'])) ?></label>
                        </div>
                        <?php foreach ($subs as $sc): ?>
                        <div class="filter-option subcat-option">
                            <input type="radio" name="subcat" id="subcat_<?= $sc['slug'] ?>"
                                   value="<?= $sc['slug'] ?>"
                                   <?= $subcat === $sc['slug'] ? 'checked' : '' ?>
                                   onchange="selectCatAndSubmit(this, '<?= $c2['slug'] ?>')">
                            <label for="subcat_<?= $sc['slug'] ?>"><?= $sc['icon'] ?> <?= htmlspecialchars(__($sc['name'])) ?></label>
                            <span class="filter-count"><?= $sc['product_count'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="divider"></div>

                <!-- ── PRICE FILTER ── -->
                <div class="sidebar-title">💰 <?= __('max_price') ?></div>
                <input type="range" id="priceRange" name="max_price" class="price-range"
                       min="0" max="9999" step="1"
                       value="<?= $maxPrice >= 9999 ? 9999 : (int)$maxPrice ?>">
                <div class="price-labels">
                    <span>JOD 0</span>
                    <span id="priceDisplay">
                        <?= $maxPrice >= 9999 ? __('any') : __('jod') . ' ' . number_format($maxPrice, 0) ?>
                    </span>
                </div>

                <div style="display:flex;gap:8px;margin-top:16px">
                    <button type="submit" class="btn-sm" style="flex:1"><?= __('apply') ?></button>
                    <a href="/GadgetZone/pages/shop.php" class="btn-sm" style="background:var(--surface2);color:var(--text2);border:1px solid var(--border);flex:1;text-align:center"><?= __('clear') ?></a>
                </div>
            </form>
        </aside>

        <!-- ── MAIN PRODUCTS ── -->
        <div class="shop-main">
            <div class="shop-header">
                <div class="results-count">
                    <?php if ($total > 0): ?>
                    <?= __('showing') ?> <strong><?= $offset+1 ?>–<?= min($offset+$perPage, $total) ?></strong> <?= __('of') ?> <strong><?= $total ?></strong> <?= __('products') ?>
                    <?= $currentCat ? ' ' . __('in') . ' <strong>'.htmlspecialchars(__($currentCat)).'</strong>' : '' ?>
                    <?php else: ?>
                    <?= __('no_products_found') ?>
                    <?php endif; ?>
                </div>
                <form method="GET" style="display:flex;align-items:center;gap:8px">
                    <?php foreach ($_GET as $k => $v): if ($k === 'sort') continue; ?>
                    <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
                    <?php endforeach; ?>
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="newest"     <?= $sort==='newest'     ? 'selected' : '' ?>><?= __('newest') ?></option>
                        <option value="popular"    <?= $sort==='popular'    ? 'selected' : '' ?>><?= __('popular') ?></option>
                        <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected' : '' ?>><?= __('price_low_high') ?></option>
                        <option value="price_desc" <?= $sort==='price_desc' ? 'selected' : '' ?>><?= __('price_high_low') ?></option>
                    </select>
                </form>
            </div>

            <?php if ($total === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">🐴</div>
                <h3><?= __('no_products_found') ?></h3>
                <p><?= __('adjust_filters_desc') ?></p>
                <a href="/GadgetZone/pages/shop.php" class="btn-primary"><?= __('clear_filters') ?></a>
            </div>
            <?php else: ?>
            <div class="products-grid">
                <?php foreach ($productsList as $p): ?>
                <div class="product-card">
                    <?php if ($p['badge']): ?><span class="badge <?= badgeClass($p['badge']) ?>"><?= $p['badge'] ?></span><?php endif; ?>
                    <a href="/GadgetZone/pages/product.php?slug=<?= $p['slug'] ?>">
                        <div class="product-card-img">
                            <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                        </div>
                    </a>
                    <div class="product-card-body">
                        <div class="product-cat">
                            <?= htmlspecialchars(__($p['cat_name'])) ?>
                            <?php if ($p['subcat_name']): ?> · <?= htmlspecialchars(__($p['subcat_name'])) ?><?php endif; ?>
                        </div>
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

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php
                $baseParams = $_GET;
                $baseParams['sort'] = $sort;
                $makeLink = function($pg) use ($baseParams) {
                    $p2 = $baseParams; $p2['page'] = $pg;
                    return '/GadgetZone/pages/shop.php?' . http_build_query($p2);
                };
                ?>
                <a href="<?= $page > 1 ? $makeLink($page-1) : '#' ?>" class="page-btn <?= $page<=1 ? 'disabled' : '' ?>">‹</a>
                <?php for ($i=1; $i<=$totalPages; $i++): ?>
                <a href="<?= $makeLink($i) ?>" class="page-btn <?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="<?= $page < $totalPages ? $makeLink($page+1) : '#' ?>" class="page-btn <?= $page>=$totalPages ? 'disabled' : '' ?>">›</a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Price range label updater
const pr = document.getElementById('priceRange');
const pd = document.getElementById('priceDisplay');
if (pr && pd) {
    pr.addEventListener('input', () => {
        pd.textContent = parseInt(pr.value) >= 9999 ? <?= json_encode(__('any')) ?> : <?= json_encode(__('jod')) ?> + ' ' + parseInt(pr.value).toLocaleString();
    });
}

// Collapsible categories sidebar functions
function toggleSubcat(btn, listId) {
    const list = document.getElementById(listId);
    if (!list) return;
    const isOpen = list.classList.toggle('open');
    btn.textContent = isOpen ? '−' : '＋';
}

function selectCatAndSubmit(subcatRadio, catSlug) {
    const catRadio = document.querySelector(`input[name="cat"][value="${catSlug}"]`);
    if (catRadio) {
        catRadio.checked = true;
    }
    subcatRadio.form.submit();
}

function clearSubcatAndSubmit(catRadio) {
    const subcatRadios = catRadio.form.querySelectorAll('input[name="subcat"]');
    subcatRadios.forEach(r => r.checked = false);
    catRadio.form.submit();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
