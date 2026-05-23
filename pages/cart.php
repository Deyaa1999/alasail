<?php
require_once __DIR__ . '/../includes/functions.php';

// Server-side remove fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    removeFromCart((int)$_POST['remove_id']);
    header('Location: /GadgetZone/pages/cart.php');
    exit;
}

$pageTitle = __('shopping_cart') . ' — Al Asail Equine';
$cartItems = getCartItems();
$cartTotal = getCartTotal();

$shippingCity = $_SESSION['shipping_city'] ?? '';
$shipping = 0.0;
$shippingSelected = false;

if ($cartTotal > 0) {
    if ($shippingCity === 'amman') {
        $shipping = (float)getSetting('shipping_amman', '3.00');
        $shippingSelected = true;
    } elseif ($shippingCity === 'aqaba') {
        $shipping = (float)getSetting('shipping_aqaba', '10.00');
        $shippingSelected = true;
    } elseif ($shippingCity === 'others') {
        $shipping = (float)getSetting('shipping_others', '5.00');
        $shippingSelected = true;
    }
}

$grand = $cartTotal + ($shippingSelected ? $shipping : 0);
$itemCount = getCartCount();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:24px">
    <div class="breadcrumb">
        <a href="/GadgetZone/index.php"><?= __('home') ?></a> <span>›</span> <?= __('shopping_cart') ?>
    </div>
    <div class="page-hero">
        <h1 class="page-hero-title"><?= __('shopping_cart') ?> <span class="accent">(<?= $itemCount ?>)</span></h1>
    </div>

    <?php if (empty($cartItems)): ?>
    <div class="cart-empty">
        <div class="cart-empty-icon">🛍️</div>
        <h2 style="font-size:26px;margin-bottom:12px"><?= __('empty_cart_title') ?></h2>
        <p style="color:var(--text2);margin-bottom:24px"><?= __('empty_cart_desc') ?></p>
        <a href="/GadgetZone/pages/shop.php" class="btn-primary"><?= __('start_shopping') ?></a>
    </div>
    <?php else: ?>
    <div class="cart-layout">
        <div>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th><?= __('product_col') ?></th>
                        <th><?= __('price_col') ?></th>
                        <th><?= __('quantity_col') ?></th>
                        <th><?= __('subtotal') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td class="product-cell">
                            <div class="cart-product">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="" class="cart-thumb">
                                <div>
                                    <div class="cart-product-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="cart-product-cat"><?= htmlspecialchars($item['cat_name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-label="<?= __('price_col') ?>"><span class="cart-price"><?= formatPrice($item['price']) ?></span></td>
                        <td data-label="<?= __('quantity_col') ?>">
                            <div class="qty-control">
                                <button type="button" class="qty-btn" data-dir="down">−</button>
                                <input type="number" class="qty-input" data-id="<?= $item['id'] ?>" value="<?= $item['qty'] ?>" min="1" max="99">
                                <button type="button" class="qty-btn" data-dir="up">+</button>
                            </div>
                        </td>
                        <td data-label="<?= __('subtotal') ?>"><span class="cart-subtotal cart-price"><?= formatPrice($item['price'] * $item['qty']) ?></span></td>
                        <td class="remove-cell">
                            <form method="POST" class="remove-form">
                                <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="remove-btn" title="Remove">×</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:16px">
                <a href="/GadgetZone/pages/shop.php" class="btn-outline"><?= getCurrentLang() === 'ar' ? '→' : '←' ?> <?= __('continue_shopping') ?></a>
            </div>
        </div>

        <!-- ORDER SUMMARY -->
        <div class="order-summary">
            <div class="summary-title"><?= __('order_summary') ?></div>
            <div class="summary-row">
                <span><?= __('subtotal') ?> (<?= $itemCount ?> <?= __('items_count_suffix') ?>)</span>
                <span><?= formatPrice($cartTotal) ?></span>
            </div>
            
            <!-- Governorate/City Dropdown Selector -->
            <div class="form-group" style="margin: 16px 0;">
                <label class="form-label" style="display:block;margin-bottom:8px;font-weight:700;font-size:13px;color:var(--text2)">
                    <?= getCurrentLang() === 'ar' ? 'المحافظة / المدينة' : 'Governorate / City' ?> <span class="required" style="color:#ef4444">*</span>
                </label>
                <select id="shipping_city_select" required>
                    <option value=""><?= getCurrentLang() === 'ar' ? '-- اختر المحافظة --' : '-- Select Governorate --' ?></option>
                    <option value="amman" <?= ($shippingCity === 'amman') ? 'selected' : '' ?>>
                        <?= getCurrentLang() === 'ar' ? 'عمان (داخل عمان)' : 'Amman (Inside Amman)' ?>
                    </option>
                    <option value="aqaba" <?= ($shippingCity === 'aqaba') ? 'selected' : '' ?>>
                        <?= getCurrentLang() === 'ar' ? 'العقبة' : 'Aqaba' ?>
                    </option>
                    <option value="others" <?= ($shippingCity === 'others') ? 'selected' : '' ?>>
                        <?= getCurrentLang() === 'ar' ? 'المحافظات الأخرى' : 'Other Governorates' ?>
                    </option>
                </select>
            </div>

            <div class="summary-row">
                <span><?= __('shipping') ?></span>
                <span class="shipping-display">
                    <?php if ($shippingSelected): ?>
                        <?= formatPrice($shipping) ?>
                    <?php else: ?>
                        <span style="color:#f59e0b;font-weight:700;font-size:12px;"><?= getCurrentLang() === 'ar' ? 'اختر المحافظة' : 'Select Governorate' ?></span>
                    <?php endif; ?>
                </span>
            </div>
            
            <div class="divider"></div>
            <div class="summary-row total">
                <span><?= __('total') ?></span>
                <span class="cart-total-display"><?= formatPrice($grand) ?></span>
            </div>
            
            <div id="shipping-warning" class="alert-warning" style="margin: 12px 0; <?= $shippingSelected ? 'display:none;' : '' ?>">
                ⚠️ <?= getCurrentLang() === 'ar' ? 'يرجى اختيار المحافظة لتحديد الشحن والمتابعة' : 'Please select a governorate to determine shipping and proceed' ?>
            </div>

            <a href="/GadgetZone/pages/checkout.php" id="checkout-btn" class="btn-primary <?= $shippingSelected ? '' : 'disabled' ?>" style="width:100%;justify-content:center;padding:14px;font-size:16px;margin-top:8px; <?= $shippingSelected ? '' : 'pointer-events: none; opacity: 0.5;' ?>">
                <?= __('proceed_to_checkout') ?>
            </a>
            <div class="payment-icons-row">
                <span class="pay-icon">VISA</span>
                <span class="pay-icon">MC</span>
                <span class="pay-icon">PayPal</span>
                <span class="pay-icon">Stripe</span>
            </div>
            <div class="security-msg"><?= __('secure_checkout') ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('shipping_city_select');
    if (!select) return;

    select.addEventListener('change', async function() {
        const city = this.value;
        const checkoutBtn = document.getElementById('checkout-btn');
        const warning = document.getElementById('shipping-warning');
        const shippingDisplay = document.querySelector('.shipping-display');
        const totalDisplay = document.querySelector('.cart-total-display');

        if (!city) {
            checkoutBtn.classList.add('disabled');
            checkoutBtn.style.pointerEvents = 'none';
            checkoutBtn.style.opacity = '0.5';
            warning.style.display = 'flex';
            shippingDisplay.innerHTML = `<span style="color:#f59e0b;font-weight:700;font-size:12px;"><?= getCurrentLang() === 'ar' ? 'اختر المحافظة' : 'Select Governorate' ?></span>`;
            // Calculate total as just cart subtotal (shipping is 0 when none selected)
            const subtotalText = document.querySelector('.summary-row span:last-child').textContent;
            totalDisplay.textContent = subtotalText;
            return;
        }

        try {
            // Set shipping city via AJAX
            const res = await fetch('/GadgetZone/pages/cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=set_shipping&city=${city}`
            });
            const data = await res.json();
            if (data.success) {
                shippingDisplay.textContent = data.shipping_formatted;
                totalDisplay.textContent = data.grand_total_formatted;

                checkoutBtn.classList.remove('disabled');
                checkoutBtn.style.pointerEvents = 'auto';
                checkoutBtn.style.opacity = '1';
                warning.style.display = 'none';
            }
        } catch (err) {
            console.error(err);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
