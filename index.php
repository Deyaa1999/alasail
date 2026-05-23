<?php
/**
 * Al Asail Equine Veterinary Supplies — Home Page
 */

$pageTitle = 'Al Asail — Professional Equine Veterinary Supplies Jordan';
require_once __DIR__ . '/includes/header.php';

// Fetch Featured Products (up to 6)
$featured_res = $conn->query("
    SELECT p.*, c.name AS cat_name 
    FROM products p 
    JOIN categories c ON c.id = p.category_id 
    WHERE p.featured = 1 
    ORDER BY p.id ASC 
    LIMIT 6
");
$featured_products = [];
if ($featured_res) {
    while ($row = $featured_res->fetch_assoc()) {
        $featured_products[] = $row;
    }
    $featured_res->free();
}

// Fetch New Arrivals (up to 4)
$arrivals_res = $conn->query("
    SELECT p.*, c.name AS cat_name 
    FROM products p 
    JOIN categories c ON c.id = p.category_id 
    ORDER BY p.created_at DESC, p.id DESC 
    LIMIT 4
");
$new_arrivals = [];
if ($arrivals_res) {
    while ($row = $arrivals_res->fetch_assoc()) {
        $new_arrivals[] = $row;
    }
    $arrivals_res->free();
}

// Fetch Deal of the Day — first product marked as Limited Time Offer
$deal_res = $conn->query("
    SELECT p.*, c.name AS cat_name 
    FROM products p 
    JOIN categories c ON c.id = p.category_id 
    WHERE p.limited_time_offer = 1 
    ORDER BY p.id DESC
    LIMIT 1
");
$deal_product = null;
if ($deal_res) {
    if ($deal_res->num_rows) {
        $deal_product = $deal_res->fetch_assoc();
    }
    $deal_res->free();
}

// Fallback: use first featured product
if (!$deal_product && !empty($featured_products)) {
    $deal_product = $featured_products[0];
}
?>

<!-- ── 1. HERO SECTION ── -->
<section class="hero">
    <div class="hero-inner container">
        <div class="hero-content">
            <h1 class="hero-title"><?= __('hero_title') ?></h1>
            <p class="hero-desc"><?= __('hero_desc') ?></p>
            <div class="hero-ctas">
                <a href="/GadgetZone/pages/shop.php" class="btn-primary"><?= __('shop_products') ?></a>
                <a href="/GadgetZone/pages/shop.php?badge=SALE" class="btn-outline"><?= __('view_offers') ?></a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <div class="stat-number">500+</div>
                    <div class="stat-label"><?= __('products_stat') ?></div>
                </div>
                <div class="stat">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label"><?= __('horses_treated_stat') ?></div>
                </div>
                <div class="stat">
                    <div class="stat-number">4.9★</div>
                    <div class="stat-label"><?= __('vet_rating_stat') ?></div>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-perspective-container">
                <div class="hero-tilt-card" id="heroTiltCard">
                    <img src="/GadgetZone/assets/images/hero-horse.jpg" alt="Professional Equine Veterinary Care — Al Asail" class="hero-horse-img">
                    <div class="hero-card-glow"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── 2. CATEGORY SLIDER ── -->
<section class="category-slider-section">
    <div class="container">
        <!-- Section Header -->
        <div class="slider-header-wrapper">
            <h2 class="slider-header-title">تصفح التصنيفات</h2>
            <a href="/GadgetZone/pages/shop.php" class="slider-header-link">عرض الكل ←</a>
        </div>
        
        <!-- Slider Outer Wrapper -->
        <div class="slider-wrapper-outer">
            <button class="slider-arrow prev-arrow" id="sliderPrevBtn" aria-label="Previous">
                <span class="arrow-icon">&#8250;</span>
            </button>
            <div class="slider-container" id="categorySlider">
                <div class="slider-track" id="sliderTrack">
                    <?php 
                    // 11 Main Categories override mapping with high-fidelity inline SVGs (matching user screenshot vector line-art)
                    $slider_overrides = [
                        'veterinary-treatments' => [
                            'name_ar' => 'علاجات بيطرية',
                            'name_en' => 'Veterinary Treatments',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.15);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="9" width="20" height="6" rx="3" transform="rotate(45 12 12)" /><line x1="8.5" y1="8.5" x2="15.5" y2="15.5" /></svg>'
                        ],
                        'supplements-nutrition' => [
                            'name_ar' => 'المكملات والتغذية',
                            'name_en' => 'Supplements & Nutrition',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.05);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22C2 12 10 4 22 2c0 0-4 12-14 14L2 22z"/><path d="M9 14C9 8 13 4 19 3"/></svg>'
                        ],
                        'horseshoes' => [
                            'name_ar' => 'حداوي الخيل',
                            'name_en' => 'Horseshoes',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.15);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4c0 10 4 16 8 16s8-6 8-16M7 4a5 5 0 0 0 10 0"/><circle cx="6" cy="9" r="0.8" fill="#0c4a60"/><circle cx="18" cy="9" r="0.8" fill="#0c4a60"/><circle cx="8" cy="14" r="0.8" fill="#0c4a60"/><circle cx="16" cy="14" r="0.8" fill="#0c4a60"/></svg>'
                        ],
                        'horse-rider-equipment' => [
                            'name_ar' => 'معدات خيل وفارس',
                            'name_en' => 'Horse & Rider Equipment',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.2);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 13s-4-6-9-6c-2.5 0-4.5 2-4.5 4.5S10.5 16 13 16s4.5-1.5 4.5-4v2c0 2 1.5 3.5 3.5 3.5h1"/><path d="M12 7c-2 0-3.5 1-4 3C7.5 11.5 7 13.5 7 15.5l-3 4.5"/><circle cx="14.5" cy="10.5" r="0.8" fill="#0c4a60"/></svg>'
                        ],
                        'veterinary-consumables' => [
                            'name_ar' => 'المستهلكات الطبية',
                            'name_en' => 'Veterinary Consumables',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.05);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v10a6 6 0 0 0 12 0V3M12 19a4 4 0 1 0 8 0 4 4 0 1 0-8 0z"/><path d="M9 21h6"/></svg>'
                        ],
                        'horse-feed-fodder' => [
                            'name_ar' => 'أعلاف وأغذية',
                            'name_en' => 'Horse Feed & Fodder',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.15);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M12 5c-2-1.5-4-.5-4 1.5s2 3 4 3.5M12 5c2-1.5 4-.5 4 1.5s-2 3-4 3.5M12 10c-2-1.5-4-.5-4 1.5s2 3 4 3.5M12 10c2-1.5 4-.5 4 1.5s-2 3-4 3.5M12 15c-2-1.5-4-.5-4 1.5s2 3 4 3.5M12 15c2-1.5 4-.5 4 1.5s-2 3-4 3.5"/></svg>'
                        ],
                        'feeding-watering' => [
                            'name_ar' => 'معالف ومشارب',
                            'name_en' => 'Feeding & Watering',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(0.95);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 19h12M12 3v3"/><path d="M8 8a4 4 0 0 1 8 0v7a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2V8z"/><path d="M4 19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2H4v2z"/></svg>'
                        ],
                        'milking-equipment' => [
                            'name_ar' => "حلابات أبقار\nوأغنام ومحالب\nمركزية",
                            'name_en' => 'Milking Equipment',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(0.95);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 10h12v9a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3v-9z"/><path d="M9 10V6a2 2 0 0 1 4 0v4M12 3h1v3h-1z"/><path d="M18 13c1.5 1 3.5 1 3.5 3v3M6 13c-1.5 1-3.5 1-3.5 3v3"/></svg>'
                        ],
                        'egg-incubators' => [
                            'name_ar' => "فقاسات\nالبيض",
                            'name_en' => 'Egg Incubators',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.15);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 19a4 4 0 0 0 4-4c0-3.5-2.5-8-4-8S4 11.5 4 15a4 4 0 0 0 4 4z"/><path d="M15.5 19a3.5 3.5 0 0 0 3.5-3.5c0-3-2-7-3.5-7s-3.5 4-3.5 7a3.5 3.5 0 0 0 3.5 3.5z"/></svg>'
                        ],
                        'injection-equipment' => [
                            'name_ar' => 'معدات حقن',
                            'name_en' => 'Injection Equipment',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.1);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m18 2 4 4M13 7l4 4M11 9l-4 4M16 4l-9 9H5v-2l9-9h2zM3 21l5-5M3 21v-3M3 21h3"/><path d="M7 17v-1h-1v1h1z"/></svg>'
                        ],
                        'pet-treats' => [
                            'name_ar' => "إكسسوارات\nالحيوانات\nالأليفة",
                            'name_en' => 'Pet Accessories',
                            'icon' => '<svg viewBox="0 0 24 24" class="category-svg-icon" style="transform: scale(1.2);" fill="none" stroke="#0c4a60" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12z"/><path d="M8 7h8M12 10v6M10 16h4M12 16l-1 3.5a1.5 1.5 0 0 0 3 0L13 16"/></svg>'
                        ]
                    ];

                    $lang = getCurrentLang();
                    foreach ($categories as $cat): 
                        if ($cat['product_count'] <= 0) continue; // Only preview active categories with products
                        if (!isset($slider_overrides[$cat['slug']])) continue;
                        $override = $slider_overrides[$cat['slug']];
                        // If Arabic and has explicit wraps (newlines), convert them to HTML breaks
                        $catName = ($lang === 'ar') ? nl2br(htmlspecialchars($override['name_ar'])) : htmlspecialchars($override['name_en']);
                        // Render the exact dynamic icon saved in the database category record
                        $catIcon = $cat['icon'];
                    ?>
                        <a href="/GadgetZone/pages/shop.php?cat=<?= $cat['slug'] ?>" class="category-slider-item" data-slug="<?= $cat['slug'] ?>">
                            <div class="category-circle">
                                <span class="category-emoji"><?= $catIcon ?></span>
                            </div>
                            <span class="category-name"><?= $catName ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="slider-arrow next-arrow" id="sliderNextBtn" aria-label="Next">
                <span class="arrow-icon">&#8249;</span>
            </button>
        </div>

        <!-- Bottom Controls -->
        <div class="slider-controls-bottom">
            <!-- Gold Animated Progress Bar -->
            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="progressBarFill"></div>
            </div>
            <!-- Dot Indicators -->
            <div class="slider-dots" id="sliderDots"></div>
        </div>
    </div>
</section>

<!-- ── 3. FEATURE STRIP ── -->
<section class="feature-strip">
    <div class="feature-strip-inner container">
        <div class="feature-item">
            <div class="feature-icon">🔄</div>
            <div class="feature-text">
                <strong><?= __('easy_returns') ?></strong>
                <span><?= __('easy_returns_desc') ?></span>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">🩺</div>
            <div class="feature-text">
                <strong><?= __('vet_approved') ?></strong>
                <span><?= __('vet_approved_desc') ?></span>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">📞</div>
            <div class="feature-text">
                <strong><?= __('expert_support') ?></strong>
                <span><?= __('expert_support_desc') ?></span>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">🔒</div>
            <div class="feature-text">
                <strong><?= __('secure_payment') ?></strong>
                <span><?= __('secure_payment_desc') ?></span>
            </div>
        </div>
    </div>
</section>

<!-- ── 4. FEATURED PRODUCTS ── -->
<section class="section" style="background: rgba(255,255,255,0.01); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div class="section-header">
            <div class="section-label"><?= __('recommended') ?></div>
            <h2 class="section-title"><?= __('featured_products') ?></h2>
            <p class="section-sub"><?= __('featured_sub') ?></p>
        </div>
        
        <?php if (!empty($featured_products)): ?>
            <div class="products-grid">
                <?php foreach ($featured_products as $p): ?>
                    <div class="product-card">
                        <?php if ($p['badge']): ?>
                            <span class="badge <?= badgeClass($p['badge']) ?>"><?= htmlspecialchars($p['badge']) ?></span>
                        <?php endif; ?>
                        <div class="product-card-img">
                            <a href="/GadgetZone/pages/product.php?slug=<?= $p['slug'] ?>">
                                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            </a>
                        </div>
                        <div class="product-card-body">
                            <div class="product-cat"><?= htmlspecialchars(__($p['cat_name'])) ?></div>
                            <a href="/GadgetZone/pages/product.php?slug=<?= $p['slug'] ?>">
                                <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                            </a>
                            <?= starRating(4.8) ?>
                            <div class="product-price">
                                <span class="price-current"><?= formatPrice($p['price']) ?></span>
                                <?php if ($p['old_price']): ?>
                                    <span class="price-old"><?= formatPrice($p['old_price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn-add-cart" data-id="<?= $p['id'] ?>"><?= __('add_to_cart') ?></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">🐴</div>
                <h3><?= __('no_products_found_featured') ?></h3>
                <p><?= __('run_migration_admin') ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── 5. DEAL OF THE DAY ── -->
<?php if ($deal_product): ?>
<section class="section">
    <div class="container">
        <div class="deal-banner">
            <div class="deal-inner">
                <div class="deal-left">
                    <div class="deal-label"><?= __('limited_offer') ?></div>
                    <h2 class="deal-title"><?= htmlspecialchars($deal_product['name']) ?></h2>
                    <p class="deal-desc"><?= htmlspecialchars($deal_product['description']) ?></p>
                    
                    <div class="deal-price">
                        <?= formatPrice($deal_product['price']) ?>
                        <?php if ($deal_product['old_price']): ?>
                            <span><?= formatPrice($deal_product['old_price']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="deal-timer" id="countdown">
                        <div class="timer-block">
                            <div class="timer-num" id="timer-hours">23</div>
                            <div class="timer-label"><?= __('hours') ?></div>
                        </div>
                        <div class="timer-block">
                            <div class="timer-num" id="timer-mins">45</div>
                            <div class="timer-label"><?= __('mins') ?></div>
                        </div>
                        <div class="timer-block">
                            <div class="timer-num" id="timer-secs">30</div>
                            <div class="timer-label"><?= __('secs') ?></div>
                        </div>
                    </div>
                    
                    <div class="deal-actions">
                        <button class="btn-primary btn-add-cart" data-id="<?= $deal_product['id'] ?>"><?= __('add_to_cart') ?></button>
                        <a href="/GadgetZone/pages/product.php?slug=<?= $deal_product['slug'] ?>" class="btn-outline"><?= __('quick_view') ?> 🔍</a>
                    </div>
                </div>
                <div class="deal-center">
                    <img src="<?= htmlspecialchars($deal_product['image_url']) ?>" alt="Deal of the Day" class="deal-img">
                </div>
                <div class="deal-right">
                    <div class="deal-rating">🏆</div>
                    <div class="deal-meta">
                        <p style="font-weight:700;color:var(--accent);margin-bottom:4px;"><?= __('vet_recommended') ?></p>
                        <span><?= __('verified_review') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── 6. NEW ARRIVALS ── -->
<section class="section" style="background: rgba(255,255,255,0.01); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div class="section-header">
            <div class="section-label"><?= __('new_products') ?></div>
            <h2 class="section-title"><?= __('new_products') ?></h2>
            <p class="section-sub"><?= __('new_products_sub') ?></p>
        </div>
        
        <?php if (!empty($new_arrivals)): ?>
            <div class="products-grid-4">
                <?php foreach ($new_arrivals as $p): ?>
                    <div class="product-card">
                        <?php if ($p['badge']): ?>
                            <span class="badge <?= badgeClass($p['badge']) ?>"><?= htmlspecialchars($p['badge']) ?></span>
                        <?php endif; ?>
                        <div class="product-card-img">
                            <a href="/GadgetZone/pages/product.php?slug=<?= $p['slug'] ?>">
                                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            </a>
                        </div>
                        <div class="product-card-body">
                            <div class="product-cat"><?= htmlspecialchars(__($p['cat_name'])) ?></div>
                            <a href="/GadgetZone/pages/product.php?slug=<?= $p['slug'] ?>">
                                <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                            </a>
                            <?= starRating(4.6) ?>
                            <div class="product-price">
                                <span class="price-current"><?= formatPrice($p['price']) ?></span>
                                <?php if ($p['old_price']): ?>
                                    <span class="price-old"><?= formatPrice($p['old_price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn-add-cart" data-id="<?= $p['id'] ?>"><?= __('add_to_cart') ?></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">🐴</div>
                <h3><?= __('no_products_found') ?></h3>
                <p><?= __('run_migration_admin') ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── 7. TESTIMONIALS ── -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div class="section-label"><?= __('our_community') ?></div>
            <h2 class="section-title"><?= __('what_owners_say') ?></h2>
            <p class="section-sub"><?= __('what_owners_say_sub') ?></p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text"><?= __('testi1_text') ?></p>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background: #8B4513;">DK</div>
                    <div>
                        <div class="author-name"><?= __('testi1_author') ?></div>
                        <div class="author-location"><?= __('testi1_loc') ?></div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text"><?= __('testi2_text') ?></p>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background: #B8860B;">SM</div>
                    <div>
                        <div class="author-name"><?= __('testi2_author') ?></div>
                        <div class="author-location"><?= __('testi2_loc') ?></div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text"><?= __('testi3_text') ?></p>
                <div class="testimonial-author">
                    <div class="author-avatar" style="background: #34d399;">FN</div>
                    <div>
                        <div class="author-name"><?= __('testi3_author') ?></div>
                        <div class="author-location"><?= __('testi3_loc') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── 8. NEWSLETTER ── -->
<section class="newsletter-section">
    <div class="container">
        <h2><?= __('stay_updated') ?></h2>
        <p><?= __('stay_updated_sub') ?></p>
        <form class="newsletter-form" onsubmit="event.preventDefault(); alert(<?= htmlspecialchars(json_encode(__('subscribe_alert'))) ?>);">
            <input type="email" class="newsletter-input" placeholder="<?= __('enter_email') ?>" required>
            <button type="submit" class="newsletter-btn"><?= __('subscribe') ?></button>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
