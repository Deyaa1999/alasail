<?php
$pageTitle = 'Products — Admin | Al Asail Equine';
require_once __DIR__ . '/layout.php';

$msg = '';
$action = $_GET['action'] ?? '';

// Delete
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header('Location: /GadgetZone/admin/products.php?msg=deleted'); exit;
}

// Save (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid      = (int)($_POST['id'] ?? 0);
    $catId    = (int)$_POST['category_id'];
    $subcatId = $_POST['subcategory_id'] ? (int)$_POST['subcategory_id'] : 'NULL';
    $name     = $conn->real_escape_string(trim($_POST['name']));
    $slug     = $conn->real_escape_string(strtolower(preg_replace('/[^a-z0-9]+/i','-',trim($_POST['slug'] ?: $_POST['name']))));
    $desc     = $conn->real_escape_string(trim($_POST['description']));
    $price    = (float)$_POST['price'];
    $old      = $_POST['old_price'] ? (float)$_POST['old_price'] : 'NULL';
    $img      = $conn->real_escape_string(trim($_POST['image_url']));
    $badge    = $conn->real_escape_string($_POST['badge']);
    $stock    = (int)$_POST['stock'];
    $feat     = isset($_POST['featured']) ? 1 : 0;
    $lto      = isset($_POST['limited_time_offer']) ? 1 : 0;
    $subcatSql = is_numeric($subcatId) ? $subcatId : 'NULL';

    if ($pid) {
        $conn->query("UPDATE products SET category_id=$catId,subcategory_id=$subcatSql,name='$name',slug='$slug',description='$desc',price=$price,old_price=".($old==='NULL'?'NULL':"'$old'").",image_url='$img',badge='$badge',stock=$stock,featured=$feat,limited_time_offer=$lto WHERE id=$pid");
    } else {
        $conn->query("INSERT INTO products (category_id,subcategory_id,name,slug,description,price,old_price,image_url,badge,stock,featured,limited_time_offer) VALUES ($catId,$subcatSql,'$name','$slug','$desc',$price,".($old==='NULL'?'NULL':"'$old'").",'$img','$badge',$stock,$feat,$lto)");
    }
    header('Location: /GadgetZone/admin/products.php?msg=saved'); exit;
}

$categories = $conn->query("SELECT * FROM categories");

$search = $conn->real_escape_string($_GET['search'] ?? '');
$where  = $search ? "WHERE LOWER(p.name) LIKE LOWER('%$search%')" : '';
$products = $conn->query("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON c.id=p.category_id $where ORDER BY p.created_at DESC");

// Edit mode
$editProduct = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $editProduct = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
}
if ($action === 'new') $editProduct = [];
?>

<div class="admin-header">
    <div>
        <div class="admin-title">Products</div>
        <div class="admin-subtitle">Manage your product catalog</div>
    </div>
    <button class="btn-admin btn-admin-primary" onclick="openModal('productModal')">+ Add Product</button>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success"><?= $_GET['msg']==='deleted' ? '🗑️ Product deleted.' : '✅ Product saved successfully.' ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <form method="GET" class="filter-bar">
            <input type="text" name="search" class="search-admin" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-admin btn-admin-primary btn-admin-sm">Search</button>
            <?php if ($search): ?><a href="/GadgetZone/admin/products.php" class="btn-admin btn-admin-outline btn-admin-sm">Clear</a><?php endif; ?>
        </form>
        <div class="admin-card-title"><?= $products->num_rows ?> products</div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Img</th><th>Name</th><th>Category</th><th>Price</th><th>Badge</th><th>Stock</th><th>Featured</th><th>🕐 LTO</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php while ($p = $products->fetch_assoc()): ?>
            <tr>
                <td><img src="<?= htmlspecialchars($p['image_url']) ?>" alt="" class="product-thumb" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2244%22 height=%2244%22><rect width=%2244%22 height=%2244%22 fill=%22%231e1e2a%22/></svg>'"></td>
                <td><span style="font-weight:600"><?= htmlspecialchars($p['name']) ?></span></td>
                <td><?= htmlspecialchars($p['cat_name']) ?></td>
                <td><?= formatPrice($p['price']) ?></td>
                <td><?php if ($p['badge']): ?><span class="badge badge-<?= strtolower($p['badge']) ?>"><?= $p['badge'] ?></span><?php else: ?>—<?php endif; ?></td>
                <td><?= $p['stock'] ?></td>
                <td><?= $p['featured'] ? '⭐' : '—' ?></td>
                <td><?= $p['limited_time_offer'] ? '<span style="color:#f59e0b;font-weight:700">🔥 Yes</span>' : '<span style="color:var(--text2)">—</span>' ?></td>
                <td class="actions-bar">
                    <button class="btn-admin btn-admin-sm btn-admin-edit"
                        onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>
                    <a href="?action=delete&id=<?= $p['id'] ?>" class="btn-admin btn-admin-sm btn-admin-delete" data-confirm="Delete '<?= htmlspecialchars($p['name']) ?>'?">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Product Modal -->
<div class="modal-overlay" id="productModal">
    <div class="modal">
        <button class="modal-close">×</button>
        <div class="modal-title" id="modalTitle">Add Product</div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="modal_id" value="0">
            <div class="form-grid-2">
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" id="modal_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug (auto-generated)</label>
                    <input type="text" name="slug" id="modal_slug" class="form-input" placeholder="leave blank to auto-generate">
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="modal_cat" class="form-select">
                        <?php $categories->data_seek(0); while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Subcategory (optional)</label>
                    <select name="subcategory_id" id="modal_subcat" class="form-select">
                        <option value="">— None —</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Price (JOD)</label>
                    <input type="number" name="price" id="modal_price" class="form-input" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Old Price (JOD, optional)</label>
                    <input type="number" name="old_price" id="modal_old" class="form-input" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Badge</label>
                    <select name="badge" id="modal_badge" class="form-select">
                        <option value="">None</option>
                        <option value="NEW">NEW</option>
                        <option value="HOT">HOT</option>
                        <option value="SALE">SALE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" id="modal_stock" class="form-input" value="100" required>
                </div>
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Product Image</label>
                    <div class="img-upload-tabs">
                        <button type="button" class="img-tab active" id="tabUpload" onclick="switchImgTab('upload')">📁 Upload Photo</button>
                        <button type="button" class="img-tab" id="tabUrl" onclick="switchImgTab('url')">🔗 Use URL</button>
                    </div>
                    <div id="imgUploadPanel">
                        <div class="img-drop-zone" id="imgDropZone">
                            <div class="img-drop-inner">
                                <span style="font-size:32px">📸</span>
                                <p>Drag & drop or <label for="imgFileInput" style="color:var(--accent);cursor:pointer;text-decoration:underline">browse</label></p>
                                <small style="color:var(--text3)">JPG, PNG, WEBP up to 5 MB</small>
                            </div>
                            <input type="file" id="imgFileInput" accept="image/*" style="display:none">
                        </div>
                        <div id="imgUploadStatus" style="display:none;margin-top:8px;font-size:13px;color:var(--text2)"></div>
                    </div>
                    <div id="imgUrlPanel" style="display:none">
                        <input type="text" id="modal_img_url" class="form-input" placeholder="https://..." oninput="document.getElementById('modal_img').value=this.value;updateImgPreview(this.value)">
                    </div>
                    <input type="hidden" name="image_url" id="modal_img">
                    <div style="margin-top:10px">
                        <img id="imgPreview" src="" alt="" style="width:120px;height:90px;object-fit:cover;border-radius:8px;display:none;border:1px solid var(--border)">
                    </div>
                </div>
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="modal_desc" class="form-textarea"></textarea>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="featured" id="modal_feat" style="accent-color:var(--accent);width:15px;height:15px">
                        <span class="form-label" style="margin:0">⭐ Featured product</span>
                    </label>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="limited_time_offer" id="modal_lto" style="accent-color:#f59e0b;width:15px;height:15px">
                        <span class="form-label" style="margin:0">🔥 Limited Time Offer <small style="color:var(--text2);font-weight:400">(shows in Deal of the Day)</small></span>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;justify-content:center;padding:12px;margin-top:8px">Save Product</button>
        </form>
    </div>
</div>

<script>
/* ── Image tab switcher ── */
function switchImgTab(tab) {
    document.getElementById('imgUploadPanel').style.display = tab === 'upload' ? '' : 'none';
    document.getElementById('imgUrlPanel').style.display    = tab === 'url'    ? '' : 'none';
    document.getElementById('tabUpload').classList.toggle('active', tab === 'upload');
    document.getElementById('tabUrl').classList.toggle('active',    tab === 'url');
}
function updateImgPreview(url) {
    const p = document.getElementById('imgPreview');
    if (url) { p.src = url; p.style.display = 'block'; }
    else      { p.src = ''; p.style.display = 'none'; }
}

/* ── Drag & drop / file input ── */
document.addEventListener('DOMContentLoaded', () => {
    const dropZone  = document.getElementById('imgDropZone');
    const fileInput = document.getElementById('imgFileInput');
    const status    = document.getElementById('imgUploadStatus');
    const urlInput  = document.getElementById('modal_img_url');

    function uploadFile(file) {
        status.style.display = 'block';
        status.textContent = '⏳ Uploading...';
        status.style.color = 'var(--text2)';
        const fd = new FormData();
        fd.append('image', file);
        // Show preview immediately
        const reader = new FileReader();
        reader.onload = e => updateImgPreview(e.target.result);
        reader.readAsDataURL(file);
        fetch('/GadgetZone/admin/upload_image.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('modal_img').value = data.url;
                    updateImgPreview(data.url);
                    status.textContent = '✅ Saved as: ' + data.url;
                    status.style.color = '#34d399';
                } else {
                    status.textContent = '❌ ' + (data.error || 'Upload failed');
                    status.style.color = '#f87171';
                }
            })
            .catch(() => { status.textContent = '❌ Upload error'; status.style.color = '#f87171'; });
    }

    if (dropZone) {
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        dropZone.addEventListener('dragleave', ()=> dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault(); dropZone.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) uploadFile(file);
        });
    }
    if (fileInput) {
        fileInput.addEventListener('change', () => {
            if (fileInput.files[0]) uploadFile(fileInput.files[0]);
        });
    }
    if (urlInput) {
        urlInput.addEventListener('input', () => {
            document.getElementById('modal_img').value = urlInput.value;
            updateImgPreview(urlInput.value);
        });
    }
});

/* ── Edit modal ── */
function openEditModal(product) {
    document.getElementById('modalTitle').textContent = product.id ? 'Edit Product' : 'Add Product';
    document.getElementById('modal_id').value    = product.id || 0;
    document.getElementById('modal_name').value  = product.name || '';
    document.getElementById('modal_slug').value  = product.slug || '';
    document.getElementById('modal_price').value = product.price || '';
    document.getElementById('modal_old').value   = product.old_price || '';
    document.getElementById('modal_stock').value = product.stock || 100;
    document.getElementById('modal_desc').value  = product.description || '';
    document.getElementById('modal_badge').value = product.badge || '';
    
    if (product.category_id) {
        document.getElementById('modal_cat').value = product.category_id;
    } else {
        const firstOption = document.getElementById('modal_cat').options[0];
        if (firstOption) {
            document.getElementById('modal_cat').value = firstOption.value;
        }
    }
    
    document.getElementById('modal_feat').checked = product.featured == 1;
    document.getElementById('modal_lto').checked  = product.limited_time_offer == 1;

    const imgVal = product.image_url || '';
    document.getElementById('modal_img').value = imgVal;
    updateImgPreview(imgVal);
    const statusEl = document.getElementById('imgUploadStatus');
    if (statusEl) { statusEl.style.display = 'none'; statusEl.textContent = ''; }

    if (imgVal && imgVal.startsWith('http')) {
        switchImgTab('url');
        document.getElementById('modal_img_url').value = imgVal;
    } else {
        switchImgTab('upload');
        const urlIn = document.getElementById('modal_img_url');
        if (urlIn) urlIn.value = '';
    }

    const catId    = product.category_id || document.getElementById('modal_cat').value;
    const subcatId = product.subcategory_id || '';
    const subcatSel = document.getElementById('modal_subcat');
    subcatSel.innerHTML = '<option value="">— None —</option>';
    if (catId) {
        fetch('/GadgetZone/admin/get_subcategories.php?cat_id=' + catId)
            .then(r => r.json())
            .then(data => {
                data.forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.id; o.textContent = s.name;
                    if (s.id == subcatId) o.selected = true;
                    subcatSel.appendChild(o);
                });
            });
    }
    openModal('productModal');
}
document.querySelector('[onclick="openModal(\'productModal\')"]').addEventListener('click', () => openEditModal({}));
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

