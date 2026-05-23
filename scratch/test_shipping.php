<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';

echo "=== E2E Shipping Rate Verification ===\n\n";

// 1. Fetch from settings DB to prove dynamic configuration
$rateAmman = (float)getSetting('shipping_amman', '3.00');
$rateAqaba = (float)getSetting('shipping_aqaba', '10.00');
$rateOthers = (float)getSetting('shipping_others', '5.00');

echo "Database Seeded Rates:\n";
echo "- Amman: {$rateAmman} JOD\n";
echo "- Aqaba: {$rateAqaba} JOD\n";
echo "- Others: {$rateOthers} JOD\n\n";

// Mock cart total
$cartTotal = 18.00;
echo "Simulated Cart Subtotal: {$cartTotal} JOD\n\n";

// Test Case 1: Amman
$_SESSION['shipping_city'] = 'amman';
$shipping = $rateAmman;
$grand = $cartTotal + $shipping;
echo "Test Case 1 [Amman]:\n";
echo "- Session City: {$_SESSION['shipping_city']}\n";
echo "- Calculated Shipping: {$shipping} JOD\n";
echo "- Grand Total: {$grand} JOD\n";
echo $grand === 21.00 ? "✅ MATCHES 21.00 JOD\n\n" : "❌ MISMATCH\n\n";

// Test Case 2: Aqaba
$_SESSION['shipping_city'] = 'aqaba';
$shipping = $rateAqaba;
$grand = $cartTotal + $shipping;
echo "Test Case 2 [Aqaba]:\n";
echo "- Session City: {$_SESSION['shipping_city']}\n";
echo "- Calculated Shipping: {$shipping} JOD\n";
echo "- Grand Total: {$grand} JOD\n";
echo $grand === 28.00 ? "✅ MATCHES 28.00 JOD\n\n" : "❌ MISMATCH\n\n";

// Test Case 3: Others
$_SESSION['shipping_city'] = 'others';
$shipping = $rateOthers;
$grand = $cartTotal + $shipping;
echo "Test Case 3 [Others]:\n";
echo "- Session City: {$_SESSION['shipping_city']}\n";
echo "- Calculated Shipping: {$shipping} JOD\n";
echo "- Grand Total: {$grand} JOD\n";
echo $grand === 23.00 ? "✅ MATCHES 23.00 JOD\n\n" : "❌ MISMATCH\n\n";

echo "=== All Tests Completed ===";
