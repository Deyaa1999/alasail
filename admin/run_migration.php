<?php
/**
 * Al Asail Equine — Safe Database Migration
 * Run once via: http://localhost:8080/GadgetZone/admin/run_migration.php
 * This script is non-destructive for users, orders, and order_items.
 */
require_once __DIR__ . '/../includes/db.php';

$log = [];
$errors = [];

function run(mysqli $conn, string $sql, string $label, array &$log, array &$errors): void {
    if ($conn->query($sql)) {
        $log[] = "✅ $label";
    } else {
        $errors[] = "❌ $label — " . $conn->error;
    }
}

// ── 1. Add order_num to categories (if not exists) ──────────────────
$col = $conn->query("SHOW COLUMNS FROM categories LIKE 'order_num'");
if ($col->num_rows === 0) {
    run($conn, "ALTER TABLE categories ADD COLUMN order_num INT DEFAULT 0 AFTER icon", "Added order_num to categories", $log, $errors);
} else { $log[] = "⏭️ order_num already exists on categories"; }

// ── 2. Create subcategories table ───────────────────────────────────
run($conn, "
CREATE TABLE IF NOT EXISTS subcategories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    icon        VARCHAR(20)  DEFAULT '🐴',
    order_num   INT DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)
", "Created subcategories table", $log, $errors);

// ── 3. Add subcategory_id to products (if not exists) ───────────────
$col2 = $conn->query("SHOW COLUMNS FROM products LIKE 'subcategory_id'");
if ($col2->num_rows === 0) {
    run($conn, "ALTER TABLE products ADD COLUMN subcategory_id INT NULL AFTER category_id", "Added subcategory_id to products", $log, $errors);
    run($conn, "ALTER TABLE products ADD CONSTRAINT fk_subcat FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE SET NULL", "Added FK subcategory_id → subcategories", $log, $errors);
} else { $log[] = "⏭️ subcategory_id already exists on products"; }

// ── 4. Add limited_time_offer to products (if not exists) ───────────
$col3 = $conn->query("SHOW COLUMNS FROM products LIKE 'limited_time_offer'");
if ($col3->num_rows === 0) {
    run($conn, "ALTER TABLE products ADD COLUMN limited_time_offer TINYINT(1) DEFAULT 0 AFTER featured", "Added limited_time_offer to products", $log, $errors);
} else { $log[] = "⏭️ limited_time_offer already exists on products"; }

// ── 5. Change default currency to JOD ───────────────────────────────
run($conn, "UPDATE settings SET setting_value='JOD' WHERE setting_key='active_currency'", "Set default currency to JOD", $log, $errors);
run($conn, "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('active_currency', 'JOD')", "Ensured JOD currency setting exists", $log, $errors);

// ── 6. Clear old electronics categories & products ──────────────────
run($conn, "SET FOREIGN_KEY_CHECKS=0", "Disabled FK checks", $log, $errors);
run($conn, "DELETE FROM subcategories", "Cleared subcategories", $log, $errors);
run($conn, "DELETE FROM products", "Cleared old products", $log, $errors);
run($conn, "DELETE FROM categories", "Cleared old categories", $log, $errors);
run($conn, "ALTER TABLE categories AUTO_INCREMENT = 1", "Reset categories auto-increment", $log, $errors);
run($conn, "ALTER TABLE subcategories AUTO_INCREMENT = 1", "Reset subcategories auto-increment", $log, $errors);
run($conn, "ALTER TABLE products AUTO_INCREMENT = 1", "Reset products auto-increment", $log, $errors);
run($conn, "SET FOREIGN_KEY_CHECKS=1", "Re-enabled FK checks", $log, $errors);

// ── 7. Seed Equine Categories ────────────────────────────────────────
$categories = [
    [1, 'Veterinary Treatments for Horses', 'veterinary-treatments', '💊', 1],
    [2, 'Supplements & Nutrition',           'supplements-nutrition', '🌿', 2],
    [3, 'Horseshoes',                         'horseshoes',           '🧲', 3],
    [4, 'Horse & Rider Equipment',            'horse-rider-equipment','🐎', 4],
    [5, 'Veterinary Consumables',             'veterinary-consumables','🩺', 5],
    [6, 'Horse Feed & Fodder',                'horse-feed-fodder',    '🌾', 6],
];
foreach ($categories as [$id, $name, $slug, $icon, $order]) {
    run($conn, "INSERT INTO categories (id, name, slug, icon, order_num) VALUES ($id, '$name', '$slug', '$icon', $order)", "Seeded category: $name", $log, $errors);
}

// ── 8. Seed Equine Subcategories ─────────────────────────────────────
$subcategories = [
    // Veterinary Treatments (cat 1)
    [1, 'Antibiotics',                  'antibiotics',                 '💊', 1],
    [1, 'Painkillers',                  'painkillers',                 '💉', 2],
    [1, 'Antiparasitics',               'antiparasitics',              '🔬', 3],
    [1, 'Deworming Syrups',             'deworming-syrups',            '🧪', 4],
    [1, 'Supplements & Vitamins',       'supplements-vitamins',        '🌡️', 5],
    [1, 'Insecticides',                 'insecticides',                '🛡️', 6],
    // Supplements & Nutrition (cat 2)
    [2, 'Hoof Care',                    'hoof-care',                   '🐴', 1],
    [2, 'Digestive System Care',        'digestive-system-care',       '🌿', 2],
    [2, 'Muscle Care',                  'muscle-care',                 '💪', 3],
    [2, 'Postpartum & Foaling Care',    'postpartum-foaling-care',     '🐣', 4],
    [2, 'Respiratory System Care',      'respiratory-system-care',     '🫁', 5],
    [2, 'Joint & Mobility Care',        'joint-mobility-care',         '🦴', 6],
    [2, 'Bone & Joint Care',            'bone-joint-care',             '🦷', 7],
    // Horseshoes (cat 3)
    [3, 'Standard Shoes',               'standard-shoes',              '🧲', 1],
    [3, 'Nails',                        'nails',                       '📌', 2],
    [3, 'Therapeutic Shoes',            'therapeutic-shoes',           '⚕️', 3],
    [3, 'Horseshoeing Tools',           'horseshoeing-tools',          '🔨', 4],
    // Horse & Rider Equipment (cat 4)
    [4, 'Rider Accessories',            'rider-accessories',           '🏇', 1],
    [4, 'Training Equipment',           'training-equipment',          '🎯', 2],
    [4, 'Stable Supplies',              'stable-supplies',             '🏠', 3],
];
foreach ($subcategories as [$catId, $name, $slug, $icon, $order]) {
    $nameSafe = $conn->real_escape_string($name);
    run($conn, "INSERT INTO subcategories (category_id, name, slug, icon, order_num) VALUES ($catId, '$nameSafe', '$slug', '$icon', $order)", "Seeded subcategory: $name", $log, $errors);
}

// ── 9. Seed Sample Equine Products ──────────────────────────────────
// Get subcategory IDs
$subIds = [];
$res = $conn->query("SELECT id, slug FROM subcategories");
while ($r = $res->fetch_assoc()) $subIds[$r['slug']] = $r['id'];

$products = [
    // Veterinary Treatments
    [1, $subIds['antibiotics']??null,          'Penicillin G Injectable',          'penicillin-g-injectable',         'Broad-spectrum antibiotic for bacterial infections in horses. 100mL vial.',         45.00, 52.00, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&q=80', 'HOT',  50, 1, 1],
    [1, $subIds['painkillers']??null,           'Phenylbutazone Paste',             'phenylbutazone-paste',             'Anti-inflammatory and analgesic paste for musculoskeletal pain in horses.',          28.50, null,  'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=600&q=80', 'NEW',  80, 1, 0],
    [1, $subIds['deworming-syrups']??null,      'Ivermectin Dewormer Syringe',      'ivermectin-dewormer-syringe',      'Effective dewormer for strongyles, pinworms, and bots. Easy-dose syringe.',          18.75, 22.00, 'https://images.unsplash.com/photo-1631549916768-4119b2e5f926?w=600&q=80', 'SALE', 120, 1, 0],
    [1, $subIds['antiparasitics']??null,        'Praziquantel Tapeworm Tabs',       'praziquantel-tapeworm-tabs',       'Highly effective against equine tapeworms. Palatable tablet form.',                  22.00, null,  'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?w=600&q=80', '',     60, 0, 0],
    // Supplements & Nutrition
    [2, $subIds['hoof-care']??null,             'BioTin Hoof Supplement',           'biotin-hoof-supplement',           'High-potency biotin formula for stronger, healthier hooves. 3kg bucket.',            55.00, 65.00, 'https://images.unsplash.com/photo-1535914254981-b5012eebbd15?w=600&q=80', 'HOT',  40, 1, 0],
    [2, $subIds['joint-mobility-care']??null,   'Glucosamine Joint Support',        'glucosamine-joint-support',        'Premium joint supplement with glucosamine, chondroitin & MSM for horses.',           72.00, null,  'https://images.unsplash.com/photo-1550831107-1553da8c8464?w=600&q=80', 'NEW',  35, 1, 0],
    [2, $subIds['muscle-care']??null,           'Electrolyte & Muscle Paste',       'electrolyte-muscle-paste',         'Replenishes electrolytes and supports muscle recovery after intense exercise.',       24.50, 29.00, 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&q=80', 'SALE', 90, 0, 0],
    // Horseshoes
    [3, $subIds['standard-shoes']??null,        'Steel Horseshoes Set (4 pcs)',     'steel-horseshoes-set',             'Standard forged steel horseshoes. Available in sizes 0-6. Set of 4.',                38.00, null,  'https://images.unsplash.com/photo-1553799775-b81cabe299c9?w=600&q=80', '',     200, 0, 0],
    [3, $subIds['therapeutic-shoes']??null,     'Aluminum Therapeutic Shoe',        'aluminum-therapeutic-shoe',        'Lightweight aluminum shoe designed for horses with laminitis or navicular disease.',  65.00, 75.00, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80', 'NEW',  30, 1, 0],
    [3, $subIds['horseshoeing-tools']??null,    'Professional Farrier Hammer',      'professional-farrier-hammer',      'Balanced steel farrier hammer with hickory handle. Essential for horseshoeing.',     42.00, null,  'https://images.unsplash.com/photo-1504222490345-c075b626c559?w=600&q=80', '',     25, 0, 0],
    // Horse & Rider Equipment
    [4, $subIds['rider-accessories']??null,     'Professional Riding Helmet',       'professional-riding-helmet',       'CE certified equestrian helmet with ventilation system. Adjustable fit.',            85.00, 99.00, 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&q=80', 'SALE', 45, 1, 0],
    [4, $subIds['stable-supplies']??null,       'Premium Horse Halter & Leadrope',  'premium-horse-halter-leadrope',    'Durable nylon halter with matching 3m lead rope. Brass fittings.',                   32.00, null,  'https://images.unsplash.com/photo-1553799775-b81cabe299c9?w=600&q=80', '',     70, 0, 0],
    // Veterinary Consumables
    [5, null,                                   'Sterile Disposable Syringes 10mL', 'sterile-syringes-10ml',            'Box of 100 sterile single-use syringes with Luer-lock tip.',                         15.00, null,  'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&q=80', '',     500, 0, 0],
    [5, null,                                   'Veterinary Exam Gloves (Box/100)', 'vet-exam-gloves-box',              'Powder-free nitrile gloves, ideal for equine examinations. Box of 100.',             12.00, 15.00, 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&q=80', 'SALE', 300, 0, 0],
    // Horse Feed & Fodder
    [6, null,                                   'Premium Alfalfa Hay (25kg)',       'premium-alfalfa-hay-25kg',         'High-quality sun-dried alfalfa hay, rich in protein and calcium.',                  18.00, null,  'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=600&q=80', '',     150, 0, 0],
    [6, null,                                   'Racehorse Performance Pellets',    'racehorse-performance-pellets',    'High-energy performance feed for racehorses and sport horses. 30kg bag.',             48.00, 55.00, 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=600&q=80', 'HOT',  60, 1, 1],
];

foreach ($products as [$catId, $subcatId, $name, $slug, $desc, $price, $oldPrice, $img, $badge, $stock, $featured, $lto]) {
    $nameSafe = $conn->real_escape_string($name);
    $slugSafe = $conn->real_escape_string($slug);
    $descSafe = $conn->real_escape_string($desc);
    $imgSafe  = $conn->real_escape_string($img);
    $badgeSafe = $conn->real_escape_string($badge);
    $subcatVal = $subcatId ? $subcatId : 'NULL';
    $oldPriceVal = $oldPrice ? $oldPrice : 'NULL';
    run($conn, "INSERT INTO products (category_id, subcategory_id, name, slug, description, price, old_price, image_url, badge, stock, featured, limited_time_offer) VALUES ($catId, $subcatVal, '$nameSafe', '$slugSafe', '$descSafe', $price, $oldPriceVal, '$imgSafe', '$badgeSafe', $stock, $featured, $lto)", "Seeded product: $name", $log, $errors);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DB Migration — Al Asail Equine</title>
<style>
  body { font-family: monospace; background: #0a0a0f; color: #e2e8f0; padding: 40px; }
  h1 { color: #8B4513; font-size: 22px; margin-bottom: 24px; }
  .ok  { color: #34d399; }
  .err { color: #f87171; }
  .skip{ color: #9090a8; }
  .summary { margin-top: 24px; padding: 16px; background: #1a1a2e; border-radius: 8px; border-left: 4px solid #8B4513; }
  a { color: #8B4513; }
</style>
</head>
<body>
<h1>🐎 Al Asail Equine — Database Migration</h1>
<?php foreach ($log as $l): ?>
<div class="<?= str_starts_with($l,'✅') ? 'ok' : (str_starts_with($l,'❌') ? 'err' : 'skip') ?>"><?= htmlspecialchars($l) ?></div>
<?php endforeach; ?>
<?php foreach ($errors as $e): ?>
<div class="err"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>
<div class="summary">
  <strong>Migration complete:</strong> <?= count($log) ?> steps, <?= count($errors) ?> errors.<br>
  <?php if (empty($errors)): ?>
  <span class="ok">✅ All done! <a href="/GadgetZone/index.php">→ Go to homepage</a> | <a href="/GadgetZone/admin/">→ Admin Dashboard</a></span>
  <?php else: ?>
  <span class="err">⚠️ Some steps failed. Check errors above.</span>
  <?php endif; ?>
</div>
</body>
</html>
