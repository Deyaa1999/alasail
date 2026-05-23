<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/currency.php';

/* ── Localization ─────────────────────────────────────── */
function initLanguage(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check url parameter first
    if (isset($_GET['lang'])) {
        $lang = strtolower($_GET['lang']);
        if (in_array($lang, ['ar', 'en'])) {
            $_SESSION['lang'] = $lang;
            setcookie('lang', $lang, time() + (3600 * 24 * 30), '/'); // 30 days
        }
    }
    
    // Fallback to cookie, then default (ar)
    if (empty($_SESSION['lang'])) {
        if (!empty($_COOKIE['lang']) && in_array(strtolower($_COOKIE['lang']), ['ar', 'en'])) {
            $_SESSION['lang'] = strtolower($_COOKIE['lang']);
        } else {
            $_SESSION['lang'] = 'ar';
        }
    }
}

// Automatically trigger language initialization
initLanguage();

function getCurrentLang(): string {
    return $_SESSION['lang'] ?? 'ar';
}

function __ (string $key): string {
    global $translations;
    if (!isset($translations)) {
        $lang = getCurrentLang();
        $file = __DIR__ . "/lang_{$lang}.php";
        if (file_exists($file)) {
            $translations = require $file;
        } else {
            $translations = [];
        }
    }
    return $translations[$key] ?? $key;
}

function getLangToggleUrl(string $targetLang): string {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $params = $_GET;
    $params['lang'] = $targetLang;
    return $uri . '?' . http_build_query($params);
}


/* ── Auth ─────────────────────────────────────────────── */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header("Location: /GadgetZone/pages/login.php?redirect=$redirect");
        exit;
    }
}

function getCurrentUser(): ?array {
    global $conn;
    if (!isLoggedIn()) return null;
    $id = (int)$_SESSION['user_id'];
    $r = $conn->query("SELECT * FROM users WHERE id=$id");
    return $r && $r->num_rows ? $r->fetch_assoc() : null;
}

function isAdmin(): bool {
    $u = getCurrentUser();
    return $u && in_array($u['role'], ['admin','super_admin']);
}

/* ── Cart ─────────────────────────────────────────────── */
function getCart(): array {
    return $_SESSION['cart'] ?? [];
}

function addToCart(int $id, int $qty = 1): void {
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = 0;
    }
    $_SESSION['cart'][$id] += $qty;
}

function updateCartQty(int $id, int $qty): void {
    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
}

function removeFromCart(int $id): void {
    unset($_SESSION['cart'][$id]);
}

function getCartCount(): int {
    return array_sum($_SESSION['cart'] ?? []);
}

function getCartTotal(): float {
    global $conn;
    $cart = getCart();
    if (empty($cart)) return 0.0;
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $res = $conn->query("SELECT id, price FROM products WHERE id IN ($ids)");
    $total = 0.0;
    while ($row = $res->fetch_assoc()) {
        $total += $row['price'] * ($cart[$row['id']] ?? 0);
    }
    return $total;
}

function getCartItems(): array {
    global $conn;
    $cart = getCart();
    if (empty($cart)) return [];
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $res = $conn->query("SELECT p.*, c.name AS cat_name FROM products p
        JOIN categories c ON c.id=p.category_id WHERE p.id IN ($ids)");
    $items = [];
    while ($row = $res->fetch_assoc()) {
        $row['qty'] = $cart[$row['id']];
        $items[] = $row;
    }
    return $items;
}

/* ── Utilities ────────────────────────────────────────── */
function sanitize(string $data): string {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

function generateOrderNumber(): string {
    return 'EVS-' . strtoupper(uniqid());
}

function badgeClass(string $badge): string {
    return match($badge) {
        'HOT'  => 'badge-hot',
        'NEW'  => 'badge-new',
        'SALE' => 'badge-sale',
        default => '',
    };
}

function starRating(float $rating = 4.5): string {
    $html = '<div class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating ? '★' : ($i - 0.5 <= $rating ? '½' : '☆');
    }
    $html .= '</div>';
    return $html;
}

function statusBadge(string $status): string {
    $map = [
        'pending'    => 'badge-pending',
        'processing' => 'badge-processing',
        'shipped'    => 'badge-shipped',
        'delivered'  => 'badge-delivered',
        'cancelled'  => 'badge-cancelled',
    ];
    $cls = $map[$status] ?? '';
    return '<span class="status-badge '.$cls.'">'.__($status).'</span>';
}

function getCategories(): array {
    global $conn;
    $res = $conn->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS product_count 
        FROM categories c 
        ORDER BY c.order_num ASC, c.id ASC
    ");
    $cats = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $cats[] = $r;
        $res->free();
    }
    return $cats;
}

function getSubcategories(int $categoryId): array {
    global $conn;
    $res = $conn->query("
        SELECT s.*, 
               (SELECT COUNT(*) FROM products p WHERE p.subcategory_id=s.id) AS product_count 
        FROM subcategories s 
        WHERE s.category_id=$categoryId 
        ORDER BY s.order_num ASC, s.id ASC
    ");
    $subs = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $subs[] = $r;
        $res->free();
    }
    return $subs;
}

function getAllSubcategories(): array {
    global $conn;
    $res = $conn->query("
        SELECT s.*, c.name AS cat_name,
               (SELECT COUNT(*) FROM products p WHERE p.subcategory_id=s.id) AS product_count
        FROM subcategories s
        JOIN categories c ON c.id = s.category_id
        ORDER BY c.order_num ASC, s.order_num ASC
    ");
    $subs = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $subs[] = $r;
        $res->free();
    }
    return $subs;
}

function sendEmailViaSMTP(string $to, string $subject, string $body, array $config): bool {
    $host = trim($config['smtp_host'] ?? 'smtp.gmail.com');
    $port = intval($config['smtp_port'] ?? 587);
    $user = trim($config['smtp_user'] ?? '');
    $pass = trim($config['smtp_pass'] ?? '');
    $from = trim($config['sender_email'] ?? $user);
    $secure = strtolower(trim($config['smtp_secure'] ?? 'tls')); // 'tls', 'ssl', 'none'

    // Resolve socket connection prefix based on SSL/TLS
    $socketHost = $host;
    if ($secure === 'ssl') {
        $socketHost = 'ssl://' . $host;
    }

    $socket = @fsockopen($socketHost, $port, $errno, $errstr, 15);
    if (!$socket) {
        error_log("SMTP Connection Error: $errstr ($errno)");
        return false;
    }

    $read = function() use ($socket) {
        $res = '';
        while (($line = fgets($socket, 515)) !== false) {
            $res .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $res;
    };

    $cmd = function($c, $expectedCode) use ($socket, $read) {
        fwrite($socket, $c . "\r\n");
        $res = $read();
        $code = substr($res, 0, 3);
        if ($code !== $expectedCode) {
            error_log("SMTP Error: CMD '$c' returned response '$res' (expected $expectedCode)");
            return false;
        }
        return $res;
    };

    // Read initial server greeting
    $res = $read();
    if (substr($res, 0, 3) !== '220') {
        error_log("SMTP Greeting Error: $res");
        fclose($socket);
        return false;
    }

    // EHLO
    if ($cmd("EHLO localhost", "250") === false) { fclose($socket); return false; }

    // STARTTLS if TLS
    if ($secure === 'tls') {
        if ($cmd("STARTTLS", "220") === false) { fclose($socket); return false; }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log("SMTP Error: Failed to enable TLS encryption on stream");
            fclose($socket);
            return false;
        }
        // EHLO again after secure tunnel is established
        if ($cmd("EHLO localhost", "250") === false) { fclose($socket); return false; }
    }

    // AUTH LOGIN if credentials exist
    if (!empty($user) && !empty($pass)) {
        if ($cmd("AUTH LOGIN", "334") === false) { fclose($socket); return false; }
        if ($cmd(base64_encode($user), "334") === false) { fclose($socket); return false; }
        if ($cmd(base64_encode($pass), "235") === false) { fclose($socket); return false; }
    }

    // MAIL FROM
    if ($cmd("MAIL FROM:<$from>", "250") === false) { fclose($socket); return false; }

    // RCPT TO
    if ($cmd("RCPT TO:<$to>", "250") === false) { fclose($socket); return false; }

    // DATA
    if ($cmd("DATA", "354") === false) { fclose($socket); return false; }

    // Assemble dynamic headers & HTML body
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Al Asail Equine <$from>\r\n";
    $headers .= "To: <$to>\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "Message-ID: <" . time() . "-" . uniqid() . "@localhost>\r\n";

    $emailData = $headers . "\r\n" . $body . "\r\n.";
    
    if ($cmd($emailData, "250") === false) { fclose($socket); return false; }

    // QUIT
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
    return true;
}

function sendOrderNotification(string $orderNum): bool {
    global $conn;
    
    // 1. Fetch all email and SMTP settings dynamically from DB
    $config = [];
    $s_res = $conn->query("SELECT * FROM settings WHERE setting_key IN ('notification_email', 'sender_email', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure')");
    while ($row = $s_res->fetch_assoc()) {
        $config[$row['setting_key']] = $row['setting_value'];
    }
    
    // Fallbacks
    $to = trim($config['notification_email'] ?? 'daldebsi@gmail.com');
    if (empty($to)) $to = 'daldebsi@gmail.com';
    
    $from = trim($config['sender_email'] ?? 'daldebsi@gmail.com');
    if (empty($from)) $from = 'daldebsi@gmail.com';
    
    // 2. Fetch the order details
    $orderNumQ = $conn->real_escape_string($orderNum);
    $orderRes = $conn->query("SELECT * FROM orders WHERE order_number = '$orderNumQ'");
    if (!$orderRes || $orderRes->num_rows === 0) {
        return false;
    }
    $order = $orderRes->fetch_assoc();
    $orderId = intval($order['id']);
    
    // 3. Fetch order items
    $itemsRes = $conn->query("
        SELECT oi.*, p.name 
        FROM order_items oi 
        JOIN products p ON p.id = oi.product_id 
        WHERE oi.order_id = $orderId
    ");
    
    $items = [];
    if ($itemsRes) {
        while ($row = $itemsRes->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    // 4. Format a highly premium, inline-styled HTML order receipt
    // Fetch active currency from settings table dynamically
    $currRes = $conn->query("SELECT setting_value FROM settings WHERE setting_key='active_currency'");
    $currency = ($currRes && $currRes->num_rows) ? $currRes->fetch_assoc()['setting_value'] : 'JOD';
    
    $itemsSubtotal = 0.0;
    $tableRows = '';
    foreach ($items as $index => $item) {
        $bgColor = $index % 2 === 0 ? '#1b221b' : '#131713';
        $subtotal = $item['quantity'] * $item['price'];
        $itemsSubtotal += $subtotal;
        $tableRows .= "
        <tr style='background: $bgColor;'>
            <td style='padding: 12px; border-bottom: 1px solid #1f2d1f; color: #ffffff;'>{$item['name']}</td>
            <td style='padding: 12px; border-bottom: 1px solid #1f2d1f; color: #a0aec0; text-align: center;'>{$item['quantity']}</td>
            <td style='padding: 12px; border-bottom: 1px solid #1f2d1f; color: #a0aec0; text-align: right;'>" . number_format($item['price'], 2) . " $currency</td>
            <td style='padding: 12px; border-bottom: 1px solid #1f2d1f; color: #f59e0b; text-align: right; font-weight: bold;'>" . number_format($subtotal, 2) . " $currency</td>
        </tr>";
    }
    
    $shippingCost = floatval($order['total_amount']) - $itemsSubtotal;
    $shippingText = $shippingCost > 0 ? number_format($shippingCost, 2) . " $currency" : "Free / مجاني";
    
    $paymentRaw = strtolower(trim($order['payment_method']));
    $paymentFormatted = '';
    if ($paymentRaw === 'cod') {
        $paymentFormatted = 'Cash on Delivery / الدفع عند الاستلام';
    } elseif ($paymentRaw === 'visa') {
        $paymentFormatted = 'Visa Card / بطاقة فيزا';
    } else {
        $paymentFormatted = ucfirst($paymentRaw);
    }
    
    $htmlContent = "
    <div style='background: #0d0f0d; color: #ffffff; font-family: \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; padding: 24px 12px; min-height: 100%;'>
        <div style='max-width: 600px; margin: 0 auto; background: #131713; border: 1px solid #1f2d1f; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);'>
            <!-- Brand Header -->
            <div style='text-align: center; border-bottom: 2px solid #f59e0b; padding-bottom: 16px; margin-bottom: 24px;'>
                <span style='font-size: 26px; font-weight: bold; color: #f59e0b; letter-spacing: 1px;'>🐎 Al Asail Equine</span>
                <div style='font-size: 14px; color: #a0aec0; margin-top: 4px;'>New Order Notification / إشعار طلب جديد</div>
            </div>
            
            <!-- Welcome Alert -->
            <div style='background: rgba(245, 158, 11, 0.08); border-left: 4px solid #f59e0b; padding: 14px; border-radius: 6px; margin-bottom: 24px;'>
                <strong style='color: #ffffff; font-size: 15px;'>🔔 A new order has been placed on the storefront!</strong>
                <p style='margin: 4px 0 0 0; font-size: 13px; color: #a0aec0;'>Below are the details of the transaction and customer delivery requirements.</p>
            </div>
            
            <!-- Order Details Cards -->
            <div style='display: table; width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 16px;'>
                <div style='display: table-row;'>
                    <div style='display: table-cell; width: 50%; background: #1b221b; border: 1px solid #1f2d1f; border-radius: 8px; padding: 12px; vertical-align: top;'>
                        <span style='font-size: 11px; text-transform: uppercase; color: #718096; display: block;'>Receipt ID</span>
                        <strong style='font-size: 14px; color: #ffffff; display: block; margin-top: 2px;'>{$order['order_number']}</strong>
                    </div>
                    <div style='display: table-cell; width: 50%; background: #1b221b; border: 1px solid #1f2d1f; border-radius: 8px; padding: 12px; vertical-align: top;'>
                        <span style='font-size: 11px; text-transform: uppercase; color: #718096; display: block;'>Total Amount</span>
                        <strong style='font-size: 16px; color: #f59e0b; display: block; margin-top: 2px;'>" . number_format($order['total_amount'], 2) . " $currency</strong>
                    </div>
                </div>
            </div>
            
            <div style='display: table; width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 24px;'>
                <div style='display: table-row;'>
                    <div style='display: table-cell; width: 50%; background: #1b221b; border: 1px solid #1f2d1f; border-radius: 8px; padding: 12px; vertical-align: top;'>
                        <span style='font-size: 11px; text-transform: uppercase; color: #718096; display: block;'>Payment Method</span>
                        <strong style='font-size: 14px; color: #ffffff; display: block; margin-top: 2px;'>$paymentFormatted</strong>
                    </div>
                    <div style='display: table-cell; width: 50%; background: #1b221b; border: 1px solid #1f2d1f; border-radius: 8px; padding: 12px; vertical-align: top;'>
                        <span style='font-size: 11px; text-transform: uppercase; color: #718096; display: block;'>Placement Date</span>
                        <strong style='font-size: 14px; color: #ffffff; display: block; margin-top: 2px;'>{$order['created_at']}</strong>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Details -->
            <div style='background: #1b221b; border: 1px solid #1f2d1f; border-radius: 8px; padding: 16px; margin-bottom: 24px;'>
                <h3 style='margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; color: #f59e0b; border-bottom: 1px solid #1f2d1f; padding-bottom: 6px;'>📍 Shipping & Customer Contact</h3>
                <p style='margin: 0; font-size: 14px; line-height: 1.6; color: #e2e8f0; font-weight: 500;'>
                    " . nl2br(htmlspecialchars($order['shipping_address'])) . "
                </p>
            </div>";
            
    if (!empty($order['notes'])) {
        $htmlContent .= "
            <!-- Customer Notes -->
            <div style='background: #1b221b; border: 1px solid #1f2d1f; border-radius: 8px; padding: 16px; margin-bottom: 24px;'>
                <h3 style='margin: 0 0 8px 0; font-size: 14px; text-transform: uppercase; color: #a0aec0; border-bottom: 1px solid #1f2d1f; padding-bottom: 6px;'>📝 Customer Instructions</h3>
                <p style='margin: 0; font-size: 13px; line-height: 1.6; color: #cbd5e0; font-style: italic;'>
                    \"" . htmlspecialchars($order['notes']) . "\"
                </p>
            </div>";
    }
    
    $htmlContent .= "
            <!-- Ordered Items Table -->
            <h3 style='margin: 0 0 12px 0; font-size: 15px; text-transform: uppercase; color: #f59e0b;'>📦 Ordered Items</h3>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 14px;'>
                <thead>
                    <tr style='background: #1f2d1f;'>
                        <th style='padding: 10px; text-align: left; color: #f59e0b; border-bottom: 2px solid #f59e0b;'>Item / الصنف</th>
                        <th style='padding: 10px; text-align: center; color: #f59e0b; border-bottom: 2px solid #f59e0b;'>Qty</th>
                        <th style='padding: 10px; text-align: right; color: #f59e0b; border-bottom: 2px solid #f59e0b;'>Unit Price</th>
                        <th style='padding: 10px; text-align: right; color: #f59e0b; border-bottom: 2px solid #f59e0b;'>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    $tableRows
                </tbody>
            </table>
            
            <!-- Financial Breakdown Table -->
            <div style='background: #1b221b; border: 1px solid #1f2d1f; border-radius: 8px; padding: 16px; margin-bottom: 24px;'>
                <table style='width: 100%; font-size: 14px; color: #cbd5e0;'>
                    <tr>
                        <td style='padding: 4px 0; color: #a0aec0;'>Items Subtotal / مجموع المنتجات:</td>
                        <td style='padding: 4px 0; text-align: right; font-weight: bold; color: #ffffff;'>" . number_format($itemsSubtotal, 2) . " $currency</td>
                    </tr>
                    <tr>
                        <td style='padding: 4px 0; color: #a0aec0;'>Shipping Fee / رسوم التوصيل:</td>
                        <td style='padding: 4px 0; text-align: right; font-weight: bold; color: #ffffff;'>$shippingText</td>
                    </tr>
                    <tr style='border-top: 1px solid #1f2d1f;'>
                        <td style='padding: 8px 0 0 0; color: #f59e0b; font-size: 16px; font-weight: bold;'>Total Amount / المجموع الكلي:</td>
                        <td style='padding: 8px 0 0 0; text-align: right; font-size: 18px; font-weight: bold; color: #f59e0b;'>" . number_format($order['total_amount'], 2) . " $currency</td>
                    </tr>
                </table>
            </div>
            
            <!-- Footer -->
            <div style='margin-top: 32px; border-top: 1px solid #1f2d1f; padding-top: 16px; font-size: 12px; color: #4a5568; text-align: center;'>
                🐎 Al Asail Equine Veterinary Store. All rights reserved &copy; 2026.
            </div>
        </div>
    </div>";
    
    // 5. Save to local scratch files for developer verification (sandbox simulation)
    if (!file_exists(__DIR__ . '/../scratch/emails')) {
        mkdir(__DIR__ . '/../scratch/emails', 0777, true);
    }
    $logFile = __DIR__ . "/../scratch/emails/order_notification_" . $orderNum . ".html";
    file_put_contents($logFile, $htmlContent);
    
    // 6. Send live email
    $subject = "🐎 New Order Placed — " . $orderNum;
    
    // If SMTP username and password exist, use authenticated socket SMTP
    if (!empty($config['smtp_user']) && !empty($config['smtp_pass'])) {
        return sendEmailViaSMTP($to, $subject, $htmlContent, $config);
    }
    
    // Fallback to PHP native mail() if credentials are empty (standard local/server setup)
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isLocal = in_array($host, ['localhost', '127.0.0.1']) || strpos($host, '192.168.') !== false;
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Al Asail Equine Notifications <$from>\r\n";
    
    if (!$isLocal) {
        @mail($to, $subject, $htmlContent, $headers);
    } else {
        @mail($to, "[SIMULATED] " . $subject, $htmlContent, $headers);
    }
    
    return true;
}

function getSetting(string $key, string $default = ''): string {
    global $conn;
    $key = $conn->real_escape_string($key);
    $res = $conn->query("SELECT setting_value FROM settings WHERE setting_key='$key'");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        return $row['setting_value'];
    }
    return $default;
}
