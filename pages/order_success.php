<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Order Confirmed — GadgetZone';
$orderNum  = htmlspecialchars($_GET['order'] ?? '');
if (!$orderNum) { header('Location: /GadgetZone/index.php'); exit; }
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="order-success fade-in">
        <div class="success-icon">✅</div>
        <h1>Order Confirmed!</h1>
        <p style="color:var(--text2);font-size:17px;margin-bottom:16px">Thank you for your purchase. Your order has been placed successfully.</p>
        <div class="order-num"><?= $orderNum ?></div>
        <p style="color:var(--text2);font-size:14px">You'll receive a confirmation shortly. Estimated delivery: 3–5 business days.</p>
        <div class="order-steps">
            <div class="order-step"><div class="order-step-icon">📋</div><div class="order-step-label">Order Placed</div></div>
            <div style="font-size:20px;color:var(--text3);margin-top:14px">→</div>
            <div class="order-step"><div class="order-step-icon">⚙️</div><div class="order-step-label">Processing</div></div>
            <div style="font-size:20px;color:var(--text3);margin-top:14px">→</div>
            <div class="order-step"><div class="order-step-icon">🚚</div><div class="order-step-label">Shipped</div></div>
            <div style="font-size:20px;color:var(--text3);margin-top:14px">→</div>
            <div class="order-step"><div class="order-step-icon">📦</div><div class="order-step-label">Delivered</div></div>
        </div>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-top:16px">
            <?php if (isLoggedIn()): ?>
            <a href="/GadgetZone/pages/myaccount.php?tab=orders" class="btn-primary">View My Orders</a>
            <?php endif; ?>
            <a href="/GadgetZone/pages/shop.php" class="btn-outline">Continue Shopping</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
