<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/functions.php';

echo "=== STARTING EMAIL NOTIFICATION SIMULATION ===\n";

// 1. Fetch some active products to link in our mock order items
$prodRes = $conn->query("SELECT id, name, price FROM products LIMIT 2");
if (!$prodRes || $prodRes->num_rows === 0) {
    die("Error: No products found in the database. Please import the database first using import.php.\n");
}

$mockProducts = [];
while ($row = $prodRes->fetch_assoc()) {
    $mockProducts[] = $row;
}
echo "Found " . count($mockProducts) . " products for mock order items.\n";

// 2. Generate a random mock order number
$orderNum = 'EVS-SIM-' . strtoupper(substr(md5(uniqid()), 0, 8));
echo "Generated mock order number: $orderNum\n";

// 3. Create the mock order in the database
$userId = 'NULL'; // guest checkout
$shippingAddress = "Test Client, 123 Al-Razi St, Amman — Phone: +962791234567";
$shippingAddressQ = $conn->real_escape_string($shippingAddress);
$notes = "Please ring the bell twice. Handle with extra care!";
$notesQ = $conn->real_escape_string($notes);

// Calculate total amount from our mock products
$totalAmount = 0.0;
foreach ($mockProducts as $p) {
    $totalAmount += $p['price'] * 2; // quantity = 2
}

$insertOrderQuery = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, shipping_address, notes, created_at)
                     VALUES ($userId, '$orderNum', $totalAmount, 'pending', 'cod', '$shippingAddressQ', '$notesQ', NOW())";

if (!$conn->query($insertOrderQuery)) {
    die("Error inserting mock order: " . $conn->error . "\n");
}
$orderId = $conn->insert_id;
echo "Inserted mock order with ID: $orderId\n";

// 4. Create mock order items in the database
foreach ($mockProducts as $p) {
    $prodId = $p['id'];
    $price = $p['price'];
    $qty = 2;
    $insertItemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price)
                        VALUES ($orderId, $prodId, $qty, $price)";
    if (!$conn->query($insertItemQuery)) {
        // Clean up order first
        $conn->query("DELETE FROM orders WHERE id = $orderId");
        die("Error inserting mock order item: " . $conn->error . "\n");
    }
    echo "Inserted mock order item: {$p['name']} x $qty @ $price BDT\n";
}

// 5. Trigger the email notification
echo "Triggering sendOrderNotification($orderNum)...\n";
$notified = sendOrderNotification($orderNum);
echo "Notification result: " . ($notified ? "SUCCESS" : "FAILED") . "\n";

// 6. Verify HTML email copy was generated in scratch/emails/
$expectedFile = __DIR__ . "/emails/order_notification_" . $orderNum . ".html";
if (file_exists($expectedFile)) {
    echo "HTML file exists! Path: $expectedFile\n";
    $contentSize = filesize($expectedFile);
    echo "HTML file size: $contentSize bytes\n";
} else {
    echo "Warning: Expected HTML copy was NOT found at: $expectedFile\n";
}

// 7. Clean up mock database entries to keep database clean
echo "Cleaning up mock database entries...\n";
$conn->query("DELETE FROM order_items WHERE order_id = $orderId");
$conn->query("DELETE FROM orders WHERE id = $orderId");
echo "Database successfully cleaned!\n";

echo "=== SIMULATION COMPLETED ===\n";
?>
