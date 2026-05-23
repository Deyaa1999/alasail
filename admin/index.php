<?php
$pageTitle = 'Dashboard — Admin';
require_once __DIR__ . '/layout.php';

$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$totalOrders   = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalUsers    = $conn->query("SELECT COUNT(*) FROM users WHERE role='member'")->fetch_row()[0];
$totalRevenue  = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status!='cancelled'")->fetch_row()[0];
$recentOrders  = $conn->query("SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 10");
$lowStock      = $conn->query("SELECT * FROM products WHERE stock < 20 ORDER BY stock ASC LIMIT 5");
?>

<div class="admin-header">
    <div>
        <div class="admin-title">Dashboard</div>
        <div class="admin-subtitle">Welcome back, <?= htmlspecialchars($adminUser['first_name']) ?>!</div>
    </div>
    <a href="/GadgetZone/admin/products.php?action=new" class="btn-admin btn-admin-primary">+ Add Product</a>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-card-label">📦 Total Products</div>
        <div class="stat-card-num"><?= $totalProducts ?></div>
        <div class="stat-card-sub">In catalog</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">🛍️ Total Orders</div>
        <div class="stat-card-num"><?= $totalOrders ?></div>
        <div class="stat-card-sub">All time</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">👥 Customers</div>
        <div class="stat-card-num"><?= $totalUsers ?></div>
        <div class="stat-card-sub">Registered</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label">💰 Revenue</div>
        <div class="stat-card-num"><?= formatPrice($totalRevenue) ?></div>
        <div class="stat-card-sub">Excl. cancelled</div>
    </div>
</div>

<div class="admin-grid-2-1">
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-card-title">Recent Orders</div>
            <a href="/GadgetZone/admin/orders.php" class="btn-admin btn-admin-sm btn-admin-outline">View All →</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php while ($o = $recentOrders->fetch_assoc()): ?>
                <tr>
                    <td><span style="color:var(--accent);font-weight:600"><?= htmlspecialchars($o['order_number']) ?></span></td>
                    <td><?= $o['first_name'] ? htmlspecialchars($o['first_name'].' '.$o['last_name']) : 'Guest' ?></td>
                    <td><?= formatPrice($o['total_amount']) ?></td>
                    <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td><?= date('M j, g:ia', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-card-title">⚠️ Low Stock</div>
            <a href="/GadgetZone/admin/products.php" class="btn-admin btn-admin-sm btn-admin-outline">Manage →</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                <tbody>
                <?php while ($p = $lowStock->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars(substr($p['name'],0,30)) ?>...</td>
                    <td><span style="color:<?= $p['stock']<5?'#ef4444':'#f59e0b' ?>;font-weight:700"><?= $p['stock'] ?></span></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
