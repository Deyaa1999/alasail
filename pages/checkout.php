<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = __('checkout') . ' — Al Asail Equine';

$cartItems = getCartItems();
if (empty($cartItems)) { header('Location: /GadgetZone/pages/cart.php'); exit; }

$shippingCity = $_SESSION['shipping_city'] ?? '';
if (empty($shippingCity)) {
    header('Location: /GadgetZone/pages/cart.php');
    exit;
}

$cartTotal = getCartTotal();
$shipping = 0.0;
if ($shippingCity === 'amman') {
    $shipping = (float)getSetting('shipping_amman', '3.00');
} elseif ($shippingCity === 'aqaba') {
    $shipping = (float)getSetting('shipping_aqaba', '10.00');
} elseif ($shippingCity === 'others') {
    $shipping = (float)getSetting('shipping_others', '5.00');
}
$grand = $cartTotal + $shipping;
$user      = getCurrentUser();
$errors    = [];
$success   = false;

// Check Stripe keys
$stripeRes = $conn->query("SELECT setting_value FROM settings WHERE setting_key='stripe_publishable_key'");
$stripePk  = '';
if ($stripeRes) {
    $row = $stripeRes->fetch_assoc();
    $stripePk = $row['setting_value'] ?? '';
    $stripeRes->free();
}
$stripeEnabled = str_starts_with((string)$stripePk, 'pk_');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['stripe_redirect'])) {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName  = sanitize($_POST['last_name'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $phone     = sanitize($_POST['phone'] ?? '');
    $address   = sanitize($_POST['address'] ?? '');
    $city      = sanitize($_POST['city'] ?? '');
    $notes     = sanitize($_POST['notes'] ?? '');
    $payment   = sanitize($_POST['payment_method'] ?? 'cod');

    if (!$firstName) $errors[] = __('err_first_name');
    if (!$lastName)  $errors[] = __('err_last_name');
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = __('err_email');
    if (!$phone) $errors[] = __('err_phone');
    if (!$address) $errors[] = __('err_address');
    if (!$city)    $errors[] = __('err_city');

    if (empty($errors)) {
        $orderNum    = generateOrderNumber();
        $userId      = $user ? $user['id'] : 'NULL';
        $shippingAddr = "$firstName $lastName, $address, $city — Phone: $phone";
        $notesQ      = $conn->real_escape_string($notes);
        $addrQ       = $conn->real_escape_string($shippingAddr);

        $conn->query("INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, shipping_address, notes)
            VALUES ($userId, '$orderNum', $grand, 'pending', '$payment', '$addrQ', '$notesQ')");
        $orderId = $conn->insert_id;

        foreach ($cartItems as $item) {
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($orderId, {$item['id']}, {$item['qty']}, {$item['price']})");
        }

        // Send dynamic order notification automatically
        sendOrderNotification($orderNum);

        $_SESSION['cart'] = [];
        header("Location: /GadgetZone/pages/order_success.php?order=$orderNum");
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:24px">
    <div class="breadcrumb">
        <a href="/GadgetZone/index.php"><?= __('home') ?></a> <span>›</span>
        <a href="/GadgetZone/pages/cart.php"><?= __('cart') ?></a> <span>›</span> <?= __('checkout') ?>
    </div>
    <div class="page-hero"><h1 class="page-hero-title"><?= __('checkout') ?></h1></div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="checkout-layout">
            <div>
                <!-- CONTACT -->
                <div class="checkout-section">
                    <div class="section-step"><div class="step-num">1</div><div class="step-title"><?= __('contact_info') ?></div></div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label"><?= __('first_name') ?> <span class="required">*</span></label>
                            <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('last_name') ?> <span class="required">*</span></label>
                            <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('email_address') ?> <span class="required">*</span></label>
                            <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('phone') ?> <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- SHIPPING -->
                <div class="checkout-section">
                    <div class="section-step"><div class="step-num">2</div><div class="step-title"><?= __('shipping_address') ?></div></div>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label class="form-label"><?= __('address') ?> <span class="required">*</span></label>
                            <input type="text" name="address" class="form-input" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('city') ?> <span class="required">*</span></label>
                            <?php
                            $cityVal = '';
                            if (!empty($shippingCity)) {
                                if ($shippingCity === 'amman') {
                                    $cityVal = getCurrentLang() === 'ar' ? 'عمان' : 'Amman';
                                } elseif ($shippingCity === 'aqaba') {
                                    $cityVal = getCurrentLang() === 'ar' ? 'العقبة' : 'Aqaba';
                                } else {
                                    $cityVal = getCurrentLang() === 'ar' ? 'المحافظات الأخرى' : 'Other Governorates';
                                }
                            }
                            if (empty($cityVal)) {
                                $cityVal = $user['city'] ?? '';
                            }
                            ?>
                            <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($cityVal) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('country') ?></label>
                            <input type="text" class="form-input" value="Jordan" readonly>
                        </div>
                        <div class="form-group full">
                            <label class="form-label"><?= __('order_notes') ?></label>
                            <textarea name="notes" class="form-textarea" rows="3" placeholder="<?= __('delivery_instructions') ?>"></textarea>
                        </div>
                    </div>
                </div>

                <!-- PAYMENT -->
                <div class="checkout-section">
                    <div class="section-step"><div class="step-num">3</div><div class="step-title"><?= __('payment_method') ?></div></div>
                    <div class="payment-methods">
                        <label class="payment-option selected">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <div>
                                <div class="payment-label">💵 <?= __('cash_on_delivery') ?></div>
                                <div style="font-size:12px;color:var(--text2)"><?= __('cod_desc') ?></div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="visa">
                            <div>
                                <div class="payment-label">💳 <?= __('visa_card') ?></div>
                                <div style="font-size:12px;color:var(--text2)"><?= __('visa_desc') ?></div>
                            </div>
                            <span class="payment-badge">VISA</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- ORDER REVIEW -->
            <div class="order-review-sidebar">
                <div class="section-step" style="margin-bottom:16px"><div class="step-num" style="background:var(--surface2);color:var(--accent)">✓</div><div class="step-title"><?= __('order_review') ?></div></div>
                <div class="review-items">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="review-item">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="" class="review-thumb">
                        <div>
                            <div class="review-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="review-meta"><?= __('qty_label') ?>: <?= $item['qty'] ?></div>
                        </div>
                        <div class="review-price"><?= formatPrice($item['price'] * $item['qty']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="divider"></div>
                <div class="summary-row"><span><?= __('subtotal') ?></span><span><?= formatPrice($cartTotal) ?></span></div>
                <div class="summary-row"><span><?= __('shipping') ?></span><span><?= $shipping > 0 ? formatPrice($shipping) : '<span style="color:#34d399">' . __('free') . '</span>' ?></span></div>
                <div class="summary-row total"><span><?= __('total') ?></span><span><?= formatPrice($grand) ?></span></div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:16px;font-size:16px;margin-top:16px">
                    <?= sprintf(__('place_order_btn'), formatPrice($grand)) ?>
                </button>
                <div class="security-msg"><?= __('info_secure') ?></div>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', () => {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        opt.classList.add('selected');
    });
});

// Stripe redirect
const stripeOpt = document.getElementById('stripe-option');
if (stripeOpt) {
    document.querySelector('form').addEventListener('submit', function(e) {
        if (stripeOpt.querySelector('input').checked) {
            e.preventDefault();
            window.location = '/GadgetZone/pages/stripe_checkout.php';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
