<?php
/**
 * Admin: Category & Subcategory Management
 * Full CRUD for categories and subcategories
 */
$pageTitle = 'Categories — Admin';
require_once __DIR__ . '/layout.php';

$msg = '';
$action = $_GET['action'] ?? '';
$type   = $_GET['type']   ?? 'category'; // 'category' or 'subcategory'

/* ── DELETE ── */
if ($action === 'delete') {
    if ($type === 'subcategory' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $conn->query("DELETE FROM subcategories WHERE id=$id");
        header('Location: /GadgetZone/admin/categories.php?msg=subcat_deleted'); exit;
    } elseif ($type === 'category' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $conn->query("DELETE FROM categories WHERE id=$id");
        header('Location: /GadgetZone/admin/categories.php?msg=cat_deleted'); exit;
    }
}

/* ── SAVE CATEGORY ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'category') {
    $cid   = (int)($_POST['id'] ?? 0);
    $name  = $conn->real_escape_string(trim($_POST['name']));
    $slug  = $conn->real_escape_string(strtolower(preg_replace('/[^a-z0-9]+/i','-',trim($_POST['slug'] ?: $_POST['name']))));
    $icon  = $conn->real_escape_string(trim($_POST['icon'] ?? '📦'));
    $order = (int)($_POST['order_num'] ?? 0);

    if ($cid) {
        $conn->query("UPDATE categories SET name='$name',slug='$slug',icon='$icon',order_num=$order WHERE id=$cid");
    } else {
        $conn->query("INSERT INTO categories (name,slug,icon,order_num) VALUES ('$name','$slug','$icon',$order)");
    }
    header('Location: /GadgetZone/admin/categories.php?msg=cat_saved'); exit;
}

/* ── SAVE SUBCATEGORY ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'subcategory') {
    $sid   = (int)($_POST['id'] ?? 0);
    $catId = (int)$_POST['category_id'];
    $name  = $conn->real_escape_string(trim($_POST['name']));
    $slug  = $conn->real_escape_string(strtolower(preg_replace('/[^a-z0-9]+/i','-',trim($_POST['slug'] ?: $_POST['name']))));
    $icon  = $conn->real_escape_string(trim($_POST['icon'] ?? '🐴'));
    $order = (int)($_POST['order_num'] ?? 0);

    if ($sid) {
        $conn->query("UPDATE subcategories SET category_id=$catId,name='$name',slug='$slug',icon='$icon',order_num=$order WHERE id=$sid");
    } else {
        $conn->query("INSERT INTO subcategories (category_id,name,slug,icon,order_num) VALUES ($catId,'$name','$slug','$icon',$order)");
    }
    header('Location: /GadgetZone/admin/categories.php?msg=subcat_saved'); exit;
}

/* ── Fetch data ── */
$categories = $conn->query("
    SELECT c.*, COUNT(DISTINCT p.id) AS product_count 
    FROM categories c 
    LEFT JOIN products p ON p.category_id=c.id 
    GROUP BY c.id 
    ORDER BY c.order_num ASC, c.id ASC
");

$subcategories = $conn->query("
    SELECT s.*, c.name AS cat_name, COUNT(p.id) AS product_count
    FROM subcategories s
    JOIN categories c ON c.id=s.category_id
    LEFT JOIN products p ON p.subcategory_id=s.id
    GROUP BY s.id
    ORDER BY c.order_num ASC, s.order_num ASC
");

$allCatsForSelect = $conn->query("SELECT id,name FROM categories ORDER BY order_num ASC");
?>

<div class="admin-header">
    <div>
        <div class="admin-title">Categories</div>
        <div class="admin-subtitle">Manage categories and subcategories</div>
    </div>
    <div style="display:flex;gap:10px;">
        <button class="btn-admin btn-admin-primary" onclick="openModal('catModal')">+ Add Category</button>
        <button class="btn-admin btn-admin-primary" style="background:#B8860B" onclick="openModal('subcatModal')">+ Add Subcategory</button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success">
    <?= match($_GET['msg']) {
        'cat_saved'     => '✅ Category saved successfully.',
        'cat_deleted'   => '🗑️ Category deleted.',
        'subcat_saved'  => '✅ Subcategory saved successfully.',
        'subcat_deleted'=> '🗑️ Subcategory deleted.',
        default         => '✅ Done.'
    } ?>
</div>
<?php endif; ?>

<!-- ── CATEGORIES TABLE ── -->
<div class="admin-card" style="margin-bottom:24px">
    <div class="admin-card-header">
        <div class="admin-card-title">📂 Main Categories</div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Icon</th><th>Name</th><th>Slug</th><th>Order</th><th>Products</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php while ($c = $categories->fetch_assoc()): ?>
            <tr>
                <td style="font-size:22px"><?= htmlspecialchars($c['icon']) ?></td>
                <td><span style="font-weight:600"><?= htmlspecialchars($c['name']) ?></span></td>
                <td><code style="color:var(--accent);font-size:12px"><?= htmlspecialchars($c['slug']) ?></code></td>
                <td><?= $c['order_num'] ?></td>
                <td><?= $c['product_count'] ?></td>
                <td><?= date('M j, Y', strtotime($c['created_at'] ?? 'now')) ?></td>
                <td class="actions-bar">
                    <button class="btn-admin btn-admin-sm btn-admin-edit"
                        onclick="openEditCat(<?= htmlspecialchars(json_encode($c)) ?>)">Edit</button>
                    <a href="?action=delete&type=category&id=<?= $c['id'] ?>"
                       class="btn-admin btn-admin-sm btn-admin-delete"
                       onclick="return confirm('Delete category \'<?= htmlspecialchars($c['name']) ?>\'? This will also delete all its subcategories.')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── SUBCATEGORIES TABLE ── -->
<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">📁 Subcategories</div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Icon</th><th>Name</th><th>Parent Category</th><th>Order</th><th>Products</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php while ($s = $subcategories->fetch_assoc()): ?>
            <tr>
                <td style="font-size:20px"><?= htmlspecialchars($s['icon']) ?></td>
                <td><span style="font-weight:600"><?= htmlspecialchars($s['name']) ?></span></td>
                <td><span style="color:var(--accent)"><?= htmlspecialchars($s['cat_name']) ?></span></td>
                <td><?= $s['order_num'] ?></td>
                <td><?= $s['product_count'] ?></td>
                <td class="actions-bar">
                    <button class="btn-admin btn-admin-sm btn-admin-edit"
                        onclick="openEditSubcat(<?= htmlspecialchars(json_encode($s)) ?>)">Edit</button>
                    <a href="?action=delete&type=subcategory&id=<?= $s['id'] ?>"
                       class="btn-admin btn-admin-sm btn-admin-delete"
                       onclick="return confirm('Delete subcategory \'<?= htmlspecialchars($s['name']) ?>\'?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── CATEGORY MODAL ── -->
<div class="modal-overlay" id="catModal">
    <div class="modal">
        <button class="modal-close">×</button>
        <div class="modal-title" id="catModalTitle">Add Category</div>
        <form method="POST">
            <input type="hidden" name="form_type" value="category">
            <input type="hidden" name="id" id="cat_id" value="0">
            <div class="form-grid-2">
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" id="cat_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug (auto-generated)</label>
                    <input type="text" name="slug" id="cat_slug" class="form-input" placeholder="leave blank to auto-generate">
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (emoji)</label>
                    <input type="text" name="icon" id="cat_icon" class="form-input" value="📦" maxlength="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Order Number</label>
                    <input type="number" name="order_num" id="cat_order" class="form-input" value="0">
                </div>
            </div>
            <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;justify-content:center;padding:12px;margin-top:8px">Save Category</button>
        </form>
    </div>
</div>

<!-- ── SUBCATEGORY MODAL ── -->
<div class="modal-overlay" id="subcatModal">
    <div class="modal">
        <button class="modal-close">×</button>
        <div class="modal-title" id="subcatModalTitle">Add Subcategory</div>
        <form method="POST">
            <input type="hidden" name="form_type" value="subcategory">
            <input type="hidden" name="id" id="subcat_id" value="0">
            <div class="form-grid-2">
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Subcategory Name *</label>
                    <input type="text" name="name" id="subcat_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Parent Category *</label>
                    <select name="category_id" id="subcat_catid" class="form-select" required>
                        <?php $allCatsForSelect->data_seek(0); while ($c = $allCatsForSelect->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug (auto-generated)</label>
                    <input type="text" name="slug" id="subcat_slug" class="form-input" placeholder="leave blank to auto-generate">
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (emoji)</label>
                    <input type="text" name="icon" id="subcat_icon" class="form-input" value="🐴" maxlength="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Order Number</label>
                    <input type="number" name="order_num" id="subcat_order" class="form-input" value="0">
                </div>
            </div>
            <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;justify-content:center;padding:12px;margin-top:8px;background:#B8860B">Save Subcategory</button>
        </form>
    </div>
</div>

<script>
function openEditCat(cat) {
    document.getElementById('catModalTitle').textContent = cat.id ? 'Edit Category' : 'Add Category';
    document.getElementById('cat_id').value    = cat.id || 0;
    document.getElementById('cat_name').value  = cat.name || '';
    document.getElementById('cat_slug').value  = cat.slug || '';
    document.getElementById('cat_icon').value  = cat.icon || '📦';
    document.getElementById('cat_order').value = cat.order_num || 0;
    openModal('catModal');
}
function openEditSubcat(s) {
    document.getElementById('subcatModalTitle').textContent = s.id ? 'Edit Subcategory' : 'Add Subcategory';
    document.getElementById('subcat_id').value     = s.id || 0;
    document.getElementById('subcat_name').value   = s.name || '';
    document.getElementById('subcat_slug').value   = s.slug || '';
    document.getElementById('subcat_icon').value   = s.icon || '🐴';
    document.getElementById('subcat_order').value  = s.order_num || 0;
    document.getElementById('subcat_catid').value  = s.category_id || '';
    openModal('subcatModal');
}
document.querySelector('[onclick="openModal(\'catModal\')"]')?.addEventListener('click', () => openEditCat({}));
document.querySelector('[onclick="openModal(\'subcatModal\')"]')?.addEventListener('click', () => openEditSubcat({}));
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
