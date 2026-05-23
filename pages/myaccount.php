<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$pageTitle = __('my_account') . ' — Al Asail Equine';
$user = getCurrentUser();
$tab  = $_GET['tab'] ?? 'dashboard';
$success = $error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'profile') {
    $fn   = $conn->real_escape_string(trim($_POST['first_name'] ?? ''));
    $ln   = $conn->real_escape_string(trim($_POST['last_name'] ?? ''));
    $ph   = $conn->real_escape_string(trim($_POST['phone'] ?? ''));
    $addr = $conn->real_escape_string(trim($_POST['address'] ?? ''));
    $city = $conn->real_escape_string(trim($_POST['city'] ?? ''));
    $conn->query("UPDATE users SET first_name='$fn',last_name='$ln',phone='$ph',address='$addr',city='$city' WHERE id={$user['id']}");
    $success = __('profile_updated');
    $user = getCurrentUser();
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!password_verify($current, $user['password'])) {
        $error = __('err_current_password');
    } elseif (strlen($new) < 6) {
        $error = __('err_password_length');
    } elseif ($new !== $confirm) {
        $error = __('err_password_match');
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hash' WHERE id={$user['id']}");
        $success = __('password_updated');
    }
}

$uid = $user['id'];

// Stats
$totalOrders    = $conn->query("SELECT COUNT(*) FROM orders WHERE user_id=$uid")->fetch_row()[0];
$deliveredOrders= $conn->query("SELECT COUNT(*) FROM orders WHERE user_id=$uid AND status='delivered'")->fetch_row()[0];
$totalSpentRow  = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id=$uid AND status!='cancelled'")->fetch_row()[0];

// Orders
$orders_res = $conn->query("SELECT o.*, (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id=o.id) AS item_count FROM orders o WHERE o.user_id=$uid ORDER BY o.created_at DESC ".($tab==='dashboard' ? 'LIMIT 5' : ''));
$ordersList = [];
if ($orders_res) {
    while ($row = $orders_res->fetch_assoc()) {
        $ordersList[] = $row;
    }
    $orders_res->free();
}

$tabs = [
    'dashboard' => ['icon'=>'📊','label'=> __('dashboard')],
    'orders'    => ['icon'=>'🛍️','label'=> __('my_orders')],
    'profile'   => ['icon'=>'👤','label'=> __('profile')],
    'password'  => ['icon'=>'🔒','label'=> __('change_password')],
];

$initials = strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1));
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:24px">
    <div class="breadcrumb"><a href="/GadgetZone/index.php"><?= __('home') ?></a> <span>›</span> <?= __('my_account') ?></div>
    <div class="page-hero"><h1 class="page-hero-title"><?= __('my_account') ?></h1></div>

    <div class="account-layout">
        <!-- SIDEBAR -->
        <aside class="account-sidebar">
            <div class="account-profile">
                <div class="account-avatar">
                    <?php if ($user['avatar']): ?>
                    <img src="/GadgetZone/assets/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="">
                    <?php else: ?>
                    <?= $initials ?>
                    <?php endif; ?>
                </div>
                <div class="account-name"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div>
                <div class="account-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <nav class="account-nav">
                <?php foreach ($tabs as $key => $t): ?>
                <a href="?tab=<?= $key ?>" class="<?= $tab===$key ? 'active' : '' ?>"><?= $t['icon'] ?> <?= $t['label'] ?></a>
                <?php endforeach; ?>
                <a href="/GadgetZone/pages/logout.php" class="danger">🚪 <?= __('logout') ?></a>
            </nav>
        </aside>

        <!-- CONTENT -->
        <div class="account-content">
            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <?php if ($tab === 'dashboard'): ?>
            <div class="account-panel">
                <h2 style="margin-bottom:4px"><?= sprintf(__('welcome_back_user'), htmlspecialchars($user['first_name'])) ?></h2>
                <p style="color:var(--text2);margin-bottom:24px"><?= __('account_summary') ?></p>
                <div class="stat-cards">
                    <div class="stat-card"><div class="stat-card-num"><?= $totalOrders ?></div><div class="stat-card-label"><?= __('total_orders') ?></div></div>
                    <div class="stat-card"><div class="stat-card-num"><?= $deliveredOrders ?></div><div class="stat-card-label"><?= __('delivered') ?></div></div>
                    <div class="stat-card"><div class="stat-card-num"><?= formatPrice($totalSpentRow) ?></div><div class="stat-card-label"><?= __('total_spent') ?></div></div>
                </div>
                <div class="panel-title"><?= __('recent_orders') ?></div>
                <?php if (empty($ordersList)): ?>
                <div class="empty-state" style="padding:40px 0"><div class="empty-icon">📦</div><h3><?= __('no_orders_yet') ?></h3><p><?= __('start_shopping_desc') ?></p><a href="/GadgetZone/pages/shop.php" class="btn-primary" style="margin-top:16px"><?= __('shop_now') ?></a></div>
                <?php else: ?>
                <table class="orders-table">
                    <thead><tr><th><?= __('order_num') ?></th><th><?= __('date') ?></th><th><?= __('total') ?></th><th><?= __('status') ?></th><th><?= __('payment') ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($ordersList as $o): ?>
                    <tr>
                        <td><span style="color:var(--accent);font-weight:600"><?= htmlspecialchars($o['order_number']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                        <td><?= formatPrice($o['total_amount']) ?></td>
                        <td><?= statusBadge($o['status']) ?></td>
                        <td><?= htmlspecialchars(__($o['payment_method'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <?php elseif ($tab === 'orders'): ?>
            <div class="account-panel">
                <div class="panel-title"><?= __('my_orders') ?></div>
                <?php if (empty($ordersList)): ?>
                <div class="empty-state" style="padding:40px 0"><div class="empty-icon">📦</div><h3><?= __('no_orders_yet') ?></h3><a href="/GadgetZone/pages/shop.php" class="btn-primary" style="margin-top:16px"><?= __('shop_now') ?></a></div>
                <?php else: ?>
                <div style="overflow-x:auto">
                <table class="orders-table">
                    <thead><tr><th><?= __('order_num') ?></th><th><?= __('date') ?></th><th><?= __('items_count') ?></th><th><?= __('total') ?></th><th><?= __('status') ?></th><th><?= __('payment') ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($ordersList as $o): ?>
                    <tr>
                        <td><span style="color:var(--accent);font-weight:600"><?= htmlspecialchars($o['order_number']) ?></span></td>
                        <td><?= date('M j, Y g:ia', strtotime($o['created_at'])) ?></td>
                        <td><?= $o['item_count'] ?> <?= __('items_count_suffix') ?></td>
                        <td><?= formatPrice($o['total_amount']) ?></td>
                        <td><?= statusBadge($o['status']) ?></td>
                        <td><?= htmlspecialchars(__($o['payment_method'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($tab === 'profile'): ?>
            <div class="account-panel">
                <div class="panel-title"><?= __('account_details') ?></div>
                <form method="POST">
                    <input type="hidden" name="tab" value="profile">
                    <div class="form-grid" style="gap:16px">
                        <div class="form-group">
                            <label class="form-label"><?= __('first_name') ?></label>
                            <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('last_name') ?></label>
                            <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                        <div class="form-group full">
                            <label class="form-label"><?= __('email_address') ?></label>
                            <input type="email" class="form-input" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            <small style="color:var(--text3);font-size:12px"><?= __('email_cannot_change') ?></small>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('phone') ?></label>
                            <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('city') ?></label>
                            <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                        </div>
                        <div class="form-group full">
                            <label class="form-label"><?= __('address') ?></label>
                            <textarea name="address" class="form-textarea"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top:16px"><?= __('update_btn') ?></button>
                </form>
            </div>

            <?php elseif ($tab === 'password'): ?>
            <div class="account-panel">
                <div class="panel-title"><?= __('change_password') ?></div>
                <form method="POST" style="max-width:400px">
                    <input type="hidden" name="tab" value="password">
                    <div class="auth-form">
                        <div class="form-group">
                            <label class="form-label"><?= __('current_password') ?> <span class="required">*</span></label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('new_password') ?> <span class="required">*</span></label>
                            <input type="password" name="new_password" class="form-input" placeholder="<?= __('min_6_chars') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= __('confirm_password') ?> <span class="required">*</span></label>
                            <input type="password" name="confirm_password" class="form-input" required>
                        </div>
                        <button type="submit" class="btn-primary"><?= __('change_password') ?></button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
