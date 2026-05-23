<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/currency.php';

// Get session ID from URL
$session_id = isset($_GET['session_id']) ? sanitize($_GET['session_id']) : '';

if (empty($session_id)) {
    header('Location: /GadgetZone/pages/checkout.php?error=invalid_session');
    exit;
}

// Fetch Stripe secret key from DB
$sk_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key='stripe_secret_key' LIMIT 1");
$stripe_secret = $sk_result ? $sk_result->fetch_assoc()['setting_value'] : '';

if (empty($stripe_secret) || !str_starts_with($stripe_secret, 'sk_')) {
    header('Location: /GadgetZone/pages/checkout.php?error=stripe_not_configured');
    exit;
}

// Retrieve the Stripe session via API
$ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . urlencode($session_id));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD        => $stripe_secret . ':',
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    header('Location: /GadgetZone/pages/checkout.php?error=stripe_verify_failed');
    exit;
}

$session = json_decode($response, true);

// Verify payment was completed
if (!isset($session['payment_status']) || $session['payment_status'] !== 'paid') {
    header('Location: /GadgetZone/pages/checkout.php?error=payment_incomplete');
    exit;
}

// Retrieve order number from metadata
$order_number = $session['metadata']['order_number'] ?? '';

if (empty($order_number)) {
    // Try to find order by session ID
    $stmt = $conn->prepare("SELECT order_number FROM orders WHERE stripe_session_id = ? LIMIT 1");
    $stmt->bind_param('s', $session_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $order_number = $row['order_number'] ?? '';
}

if (!empty($order_number)) {
    // Update order payment status
    $stmt = $conn->prepare("UPDATE orders SET payment_status='paid', status='processing' WHERE order_number=?");
    $stmt->bind_param('s', $order_number);
    $stmt->execute();
    $stmt->close();

    // Clear cart
    $_SESSION['cart'] = [];

    header('Location: /GadgetZone/pages/order_success.php?order=' . urlencode($order_number));
    exit;
} else {
    // Fallback: check if there's a pending order for this user with stripe session
    if (isLoggedIn()) {
        $uid = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT order_number FROM orders WHERE user_id=? AND stripe_session_id=? LIMIT 1");
        $stmt->bind_param('is', $uid, $session_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $order_number = $row['order_number'];
            $stmt2 = $conn->prepare("UPDATE orders SET payment_status='paid', status='processing' WHERE order_number=?");
            $stmt2->bind_param('s', $order_number);
            $stmt2->execute();
            $stmt2->close();

            $_SESSION['cart'] = [];
            header('Location: /GadgetZone/pages/order_success.php?order=' . urlencode($order_number));
            exit;
        }
    }

    // Could not match order — clear cart anyway and show success
    $_SESSION['cart'] = [];
    header('Location: /GadgetZone/pages/order_success.php');
    exit;
}
