/* Admin JS */
// Modal helpers
function openModal(id) {
    document.getElementById(id)?.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id)?.classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
        document.body.style.overflow = '';
    }
    if (e.target.classList.contains('modal-close')) {
        e.target.closest('.modal-overlay')?.classList.remove('open');
        document.body.style.overflow = '';
    }
});

// Image preview
document.querySelectorAll('.image-url-input').forEach(input => {
    input.addEventListener('input', () => {
        const preview = document.getElementById(input.dataset.preview);
        if (preview) preview.src = input.value || '';
    });
});

// Currency card selection
document.querySelectorAll('.currency-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.currency-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        const hiddenInput = document.getElementById('activeCurrencyInput');
        if (hiddenInput) hiddenInput.value = card.dataset.code;
    });
});

// Delete confirmation
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if (!confirm(btn.dataset.confirm)) e.preventDefault();
    });
});

// Sidebar toggle on mobile
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.admin-sidebar');
const overlay = document.getElementById('adminSidebarOverlay');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay?.classList.toggle('active');
    });
}
if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        overlay.classList.remove('active');
    });
}

// Admin subcategory dropdown dynamic loader
const adminCatSelect = document.getElementById('modal_cat');
const adminSubcatSelect = document.getElementById('modal_subcat');
if (adminCatSelect && adminSubcatSelect) {
    adminCatSelect.addEventListener('change', async function() {
        const catId = this.value;
        adminSubcatSelect.innerHTML = '<option value="">— None —</option>';
        if (!catId) return;
        try {
            const res = await fetch('/GadgetZone/admin/get_subcategories.php?cat_id=' + catId);
            const data = await res.json();
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                adminSubcatSelect.appendChild(opt);
            });
        } catch(e) { console.error('Subcategory fetch error', e); }
    });
}

