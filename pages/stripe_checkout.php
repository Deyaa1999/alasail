<?php
require_once __DIR__ . '/../includes/functions.php';

$cartItems = getCartItems();
if (empty($cartItems)) { header('Location: /GadgetZone/pages/cart.php'); exit; }

// Get Stripe secret key
$res = $conn->query("SELECT setting_value FROM settings WHERE setting_key='stripe_secret_key'");
$sk  = $res ? $res->fetch_assoc()['setting_value'] ?? '' : '';

if (!str_starts_with((string)$sk, 'sk_')) {
    header('Location: /GadgetZone/pages/checkout.php?error=stripe_not_configured');
    exit;
}

$cartTotal  = getCartTotal();
$shipping   = $cartTotal > 5000 ? 0 : 150;
$grandTotal = $cartTotal + $shipping;

$lineItems = [];
foreach ($cartItems as $item) {
    $lineItems[] = [
        'price_data' => [
            'currency'     => getStripeCurrencyCode(),
            'product_data' => ['name' => $item['name']],
            'unit_amount'  => getStripeAmount($item['price']),
        ],
        'quantity' => $item['qty'],
    ];
}
if ($shipping > 0) {
    $lineItems[] = [
        'price_data' => [
            'currency'     => getStripeCurrencyCode(),
            'product_data' => ['name' => 'Shipping'],
            'unit_amount'  => getStripeAmount($shipping),
        ],
        'quantity' => 1,
    ];
}

$payload = json_encode([
    'payment_method_types' => ['card'],
    'line_items'           => $lineItems,
    'mode'                 => 'payment',
    'success_url'          => 'https://'.$_SERVER['HTTP_HOST'].'/GadgetZone/pages/stripe_return.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'           => 'https://'.$_SERVER['HTTP_HOST'].'/GadgetZone/pages/checkout.php?error=cancelled',
]);

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer '.$sk,
        'Content-Type: application/json',
    ],
]);
$response = curl_exec($ch);
$err      = curl_error($ch);
curl_close($ch);

if ($err) { header('Location: /GadgetZone/pages/checkout.php?error=curl_error'); exit; }

$session = json_decode($response, true);
if (isset($session['url'])) {
    header('Location: '.$session['url']); exit;
}

header('Location: /GadgetZone/pages/checkout.php?error=stripe_session_failed');
exit;
