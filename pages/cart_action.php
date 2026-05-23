<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$action    = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$qty       = (int)($_POST['qty'] ?? 1);

switch ($action) {
    case 'set_shipping':
        $city = sanitize($_POST['city'] ?? '');
        if (in_array($city, ['amman', 'aqaba', 'others'])) {
            $_SESSION['shipping_city'] = $city;
            if ($city === 'amman') {
                $cost = (float)getSetting('shipping_amman', '3.00');
            } elseif ($city === 'aqaba') {
                $cost = (float)getSetting('shipping_aqaba', '10.00');
            } else {
                $cost = (float)getSetting('shipping_others', '5.00');
            }
            $_SESSION['shipping_cost'] = $cost;
            
            $cartTotal = getCartTotal();
            $grand = $cartTotal + $cost;
            
            echo json_encode([
                'success' => true,
                'shipping_formatted' => formatPrice($cost),
                'grand_total_formatted' => formatPrice($grand)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid city']);
        }
        exit;

    case 'add':
        if (!$productId) { echo json_encode(['success' => false, 'message' => 'Invalid product']); exit; }
        addToCart($productId, max(1, $qty));
        break;
    case 'update':
        if (!$productId) { echo json_encode(['success' => false, 'message' => 'Invalid product']); exit; }
        updateCartQty($productId, $qty);
        break;
    case 'remove':
        if (!$productId) { echo json_encode(['success' => false, 'message' => 'Invalid product']); exit; }
        removeFromCart($productId);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit;
}

$cartItems   = getCartItems();
$cartTotal   = getCartTotal();
$cartCount   = getCartCount();

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
$grandTotal = $cartTotal + ($shippingSelected ? $shipping : 0.0);

// Item subtotal
$itemSubtotal = '';
$cart = getCart();
if (isset($cart[$productId])) {
    $res = $conn->query("SELECT price FROM products WHERE id=$productId");
    if ($res && $r = $res->fetch_assoc()) {
        $itemSubtotal = formatPrice($r['price'] * $cart[$productId]);
    }
}

echo json_encode([
    'success'        => true,
    'cart_count'     => $cartCount,
    'formatted_total'=> formatPrice($grandTotal),
    'item_subtotal'  => $itemSubtotal,
    'shipping'       => $shippingSelected ? formatPrice($shipping) : (getCurrentLang() === 'ar' ? 'اختر المحافظة' : 'Select Governorate'),
    'shipping_note'  => '',
]);
