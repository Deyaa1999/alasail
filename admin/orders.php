<?php
$pageTitle = 'Orders — Admin';
require_once __DIR__ . '/layout.php';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $id     = (int)$_POST['order_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE id=$id");
    header('Location: /GadgetZone/admin/orders.php?msg=updated'); exit;
}

$statusFilter = $_GET['status'] ?? '';
$where = $statusFilter ? "WHERE o.status='".($conn->real_escape_string($statusFilter))."'" : '';
$orders = $conn->query("SELECT o.*, u.first_name, u.last_name, u.email,
    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id=o.id) AS item_count
    FROM orders o LEFT JOIN users u ON u.id=o.user_id $where ORDER BY o.created_at DESC");

$statuses = ['pending','processing','shipped','delivered','cancelled'];
?>

<div class="admin-header">
    <div>
        <div class="admin-title">Orders</div>
        <div class="admin-subtitle">Manage customer orders</div>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Order status updated.</div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="filter-bar">
            <a href="/GadgetZone/admin/orders.php" class="btn-admin btn-admin-sm <?= !$statusFilter?'btn-admin-primary':'btn-admin-outline' ?>">All</a>
            <?php foreach ($statuses as $s): ?>
            <a href="?status=<?= $s ?>" class="btn-admin btn-admin-sm <?= $statusFilter===$s?'btn-admin-primary':'btn-admin-outline' ?>"><?= ucfirst($s) ?></a>
            <?php endforeach; ?>
        </div>
        <div class="admin-card-title"><?= $orders->num_rows ?> orders</div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Update</th></tr>
            </thead>
            <tbody>
            <?php while ($o = $orders->fetch_assoc()): ?>
            <tr>
                <td><span style="color:var(--accent);font-weight:700"><?= htmlspecialchars($o['order_number']) ?></span></td>
                <td>
                    <?php if ($o['first_name']): ?>
                    <div><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></div>
                    <div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($o['email']) ?></div>
                    <?php else: ?><span style="color:var(--text3)">Guest</span><?php endif; ?>
                </td>
                <td><?= $o['item_count'] ?></td>
                <td><strong><?= formatPrice($o['total_amount']) ?></strong></td>
                <td><?= htmlspecialchars(ucfirst($o['payment_method'])) ?></td>
                <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                <td>
                    <form method="POST" style="display:flex;gap:6px">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="status" class="form-select" style="padding:5px 8px;font-size:12px;width:130px">
                            <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-admin btn-admin-sm btn-admin-primary">✓</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
