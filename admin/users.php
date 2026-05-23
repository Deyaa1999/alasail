<?php
$pageTitle = 'Users — Admin';
require_once __DIR__ . '/layout.php';

// Update role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $uid  = (int)$_POST['user_id'];
    $role = $conn->real_escape_string($_POST['role']);
    if (in_array($role, ['member','admin','super_admin'])) {
        $conn->query("UPDATE users SET role='$role' WHERE id=$uid");
    }
    header('Location: /GadgetZone/admin/users.php?msg=updated'); exit;
}

$users = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) AS order_count FROM users u ORDER BY u.created_at DESC");
?>

<div class="admin-header">
    <div>
        <div class="admin-title">Users</div>
        <div class="admin-subtitle">Manage user accounts and roles</div>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ User role updated.</div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title"><?= $users->num_rows ?> users</div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Role</th><th>Joined</th><th>Update Role</th></tr>
            </thead>
            <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div class="admin-avatar" style="width:32px;height:32px;font-size:12px"><?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?></div>
                        <span><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></span>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td><?= $u['order_count'] ?></td>
                <td>
                    <span class="badge <?= in_array($u['role'],['admin','super_admin']) ? 'badge-admin' : 'badge-member' ?>">
                        <?= htmlspecialchars($u['role']) ?>
                    </span>
                </td>
                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <?php if ($u['id'] !== $adminUser['id']): ?>
                    <form method="POST" style="display:flex;gap:6px">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <select name="role" class="form-select" style="padding:5px 8px;font-size:12px;width:130px">
                            <option value="member" <?= $u['role']==='member'?'selected':'' ?>>member</option>
                            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
                            <option value="super_admin" <?= $u['role']==='super_admin'?'selected':'' ?>>super_admin</option>
                        </select>
                        <button type="submit" class="btn-admin btn-admin-sm btn-admin-primary">✓</button>
                    </form>
                    <?php else: ?>
                    <span style="font-size:12px;color:var(--text3)">You</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
