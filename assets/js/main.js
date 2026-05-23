/* Al Asail Equine — Main JS */
const _BASE = window.location.pathname.toLowerCase().startsWith('/gadgetzone') ? '/GadgetZone' : (window.location.pathname.startsWith('/gadget') ? '/gadget' : '');

/* ── Hamburger / Mobile Nav Side-Drawer ──────── */
const hamburger = document.getElementById('hamburger');
const mobileNav = document.getElementById('mobileNav');
const closeMobileNav = document.getElementById('closeMobileNav');
const dimMobileNav = document.getElementById('dimMobileNav');

function openMobileNav() {
    if (mobileNav) mobileNav.classList.add('open');
    document.body.classList.add('mobile-menu-open');
    const mobileSearchDropdown = document.getElementById('mobileSearchDropdown');
    if (mobileSearchDropdown) mobileSearchDropdown.classList.remove('open');
}
function closeMobileNavMenu() {
    if (mobileNav) mobileNav.classList.remove('open');
    document.body.classList.remove('mobile-menu-open');
}

if (hamburger) {
    hamburger.addEventListener('click', openMobileNav);
}
if (closeMobileNav) {
    closeMobileNav.addEventListener('click', closeMobileNavMenu);
}
if (dimMobileNav) {
    dimMobileNav.addEventListener('click', closeMobileNavMenu);
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMobileNavMenu();
});

/* ── Cart Popup ──────────────────────────────── */
function showCartPopup() {
    const overlay = document.getElementById('cartPopupOverlay');
    if (!overlay) return;
    overlay.classList.add('active');
    // Trap focus inside popup
    const popup = document.getElementById('cartPopup');
    if (popup) popup.focus();
}

function hideCartPopup() {
    const overlay = document.getElementById('cartPopupOverlay');
    if (overlay) overlay.classList.remove('active');
}

// Close button
document.addEventListener('DOMContentLoaded', () => {
    const closeBtn = document.getElementById('cartPopupClose');
    if (closeBtn) closeBtn.addEventListener('click', hideCartPopup);

    const overlay = document.getElementById('cartPopupOverlay');
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) hideCartPopup();
        });
    }

    // ESC key closes popup
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') hideCartPopup();
    });

    // Mobile menu subcategories accordion is handled purely via CSS transitions!

    document.querySelectorAll('.menu-item-parent').forEach(parent => {
        parent.addEventListener('click', () => {
            const catId = parent.dataset.catId;
            const target = document.getElementById('subcats-' + catId);
            if (!target) return;

            const isOpen = target.classList.contains('open');

            // Close other subcategory accordions
            document.querySelectorAll('.menu-subcats').forEach(subcat => {
                if (subcat !== target && subcat.classList.contains('open')) {
                    subcat.style.maxHeight = subcat.scrollHeight + 'px';
                    // Force reflow before collapsing
                    subcat.offsetHeight;
                    subcat.style.maxHeight = '0px';
                    subcat.classList.remove('open');
                }
            });
            document.querySelectorAll('.menu-item-parent').forEach(p => {
                if (p !== parent) {
                    p.classList.remove('open');
                }
            });

            if (isOpen) {
                // Collapse: set explicit height first, then animate to 0
                target.style.maxHeight = target.scrollHeight + 'px';
                target.offsetHeight; // force reflow
                target.style.maxHeight = '0px';
                target.classList.remove('open');
                parent.classList.remove('open');
            } else {
                // Expand: animate from 0 to scrollHeight, then switch to none
                target.style.maxHeight = '0px';
                target.classList.add('open');
                parent.classList.add('open');
                target.style.maxHeight = target.scrollHeight + 'px';

                // After transition completes, switch to max-height:none
                // so all items are guaranteed visible
                const onEnd = () => {
                    if (target.classList.contains('open')) {
                        target.style.maxHeight = 'none';
                    }
                    target.removeEventListener('transitionend', onEnd);
                };
                target.addEventListener('transitionend', onEnd);
            }
        });
    });

    // Mobile Top Header Search Toggle
    const mobileSearchToggle = document.getElementById('mobileSearchToggle');
    const mobileSearchDropdown = document.getElementById('mobileSearchDropdown');
    const mobileSearchDropdownInput = mobileSearchDropdown ? mobileSearchDropdown.querySelector('.mobile-search-dropdown-input') : null;

    if (mobileSearchToggle && mobileSearchDropdown) {
        mobileSearchToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = mobileSearchDropdown.classList.contains('open');
            if (isOpen) {
                mobileSearchDropdown.classList.remove('open');
            } else {
                mobileSearchDropdown.classList.add('open');
                setTimeout(() => {
                    if (mobileSearchDropdownInput) mobileSearchDropdownInput.focus();
                }, 100);
                closeMobileNavMenu();
            }
        });

        document.addEventListener('click', (e) => {
            if (!mobileSearchDropdown.contains(e.target) && e.target !== mobileSearchToggle) {
                mobileSearchDropdown.classList.remove('open');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                mobileSearchDropdown.classList.remove('open');
            }
        });
    }
});

/* ── Add to Cart ─────────────────────────────── */
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-add-cart');
    if (!btn) return;
    e.preventDefault();
    const productId = btn.dataset.id;
    if (!productId) return;
    const qty = parseInt(document.getElementById('detail-qty')?.value || 1);
    btn.disabled = true;
    btn.textContent = 'Adding...';
    try {
        const res = await fetch(_BASE + '/pages/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&product_id=${productId}&qty=${qty}`
        });
        const data = await res.json();
        if (data.success) {
            updateCartBadge(data.cart_count);
            btn.textContent = '✓ Added!';
            btn.style.background = '#10b981';
            // Show popup
            showCartPopup();
            setTimeout(() => {
                btn.textContent = 'Add to Cart 🛒';
                btn.style.background = '';
                btn.disabled = false;
            }, 2000);
        } else {
            btn.textContent = 'Error';
            btn.disabled = false;
        }
    } catch {
        btn.textContent = 'Add to Cart 🛒';
        btn.disabled = false;
    }
});

/* ── Cart Badge ──────────────────────────────── */
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge');
    if (!badge) return;
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
}

/* ── Cart Quantity ───────────────────────────── */
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.qty-btn');
    if (!btn) return;
    const row = btn.closest('tr');
    if (!row) return;
    const input = row.querySelector('.qty-input');
    if (!input) return;
    let qty = parseInt(input.value);
    if (btn.dataset.dir === 'up') qty++;
    if (btn.dataset.dir === 'down') qty--;
    if (qty < 1) qty = 1;
    if (qty > 99) qty = 99;
    input.value = qty;
    await updateCartItem(input.dataset.id, qty, row);
});

document.addEventListener('change', async (e) => {
    if (!e.target.classList.contains('qty-input')) return;
    const row = e.target.closest('tr');
    let qty = parseInt(e.target.value);
    if (isNaN(qty) || qty < 1) qty = 1;
    if (qty > 99) qty = 99;
    e.target.value = qty;
    await updateCartItem(e.target.dataset.id, qty, row);
});

async function updateCartItem(id, qty, row) {
    try {
        const res = await fetch(_BASE + '/pages/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&product_id=${id}&qty=${qty}`
        });
        const data = await res.json();
        if (data.success) {
            updateCartBadge(data.cart_count);
            const subtotalEl = row.querySelector('.cart-subtotal');
            if (subtotalEl && data.item_subtotal) subtotalEl.textContent = data.item_subtotal;
            const totalEl = document.querySelector('.cart-total-display');
            if (totalEl && data.formatted_total) totalEl.textContent = data.formatted_total;
            const shippingEl = document.querySelector('.shipping-display');
            if (shippingEl && data.shipping !== undefined) shippingEl.textContent = data.shipping;
            const shippingNote = document.querySelector('.shipping-note');
            if (shippingNote) shippingNote.textContent = data.shipping_note || '';
        }
    } catch {}
}

/* ── Cart Remove (AJAX intercept) ── */
document.addEventListener('submit', async (e) => {
    if (!e.target.classList.contains('remove-form')) return;
    e.preventDefault();
    const form = e.target;
    const row = form.closest('tr');
    const id = form.querySelector('[name=remove_id]').value;
    try {
        const res = await fetch(_BASE + '/pages/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=remove&product_id=${id}`
        });
        const data = await res.json();
        if (data.success) {
            row.style.transition = 'opacity 0.3s, transform 0.3s';
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            setTimeout(() => {
                row.remove();
                updateCartBadge(data.cart_count);
                const totalEl = document.querySelector('.cart-total-display');
                if (totalEl && data.formatted_total) totalEl.textContent = data.formatted_total;
                if (data.cart_count === 0) location.reload();
            }, 320);
        }
    } catch {
        form.submit();
    }
});

/* ── Payment method selection ────────────────── */
document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', () => {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        opt.classList.add('selected');
        opt.querySelector('input[type=radio]').checked = true;
    });
});

/* ── Countdown Timer ─────────────────────────── */
const timer = document.getElementById('countdown');
if (timer) {
    const end = new Date().getTime() + (23 * 3600 + 45 * 60 + 30) * 1000;
    setInterval(() => {
        const now = new Date().getTime();
        const diff = end - now;
        if (diff <= 0) return;
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        ['hours','mins','secs'].forEach((k, i) => {
            const el = document.getElementById('timer-' + k);
            if (el) el.textContent = [h,m,s][i].toString().padStart(2,'0');
        });
    }, 1000);
}

/* ── Scroll animations ───────────────────────── */
if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.product-card, .category-card, .testimonial-card').forEach(el => {
        el.style.animation = 'fadeIn 0.5s ease both';
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
}

/* ── Price range slider ──────────────────────── */
const priceRange = document.getElementById('priceRange');
const priceLabel = document.getElementById('priceLabel');
if (priceRange && priceLabel) {
    priceRange.addEventListener('input', () => {
        priceLabel.textContent = parseInt(priceRange.value).toLocaleString();
    });
}

/* ── Account tabs ────────────────────────────── */
document.querySelectorAll('[data-tab]').forEach(link => {
    link.addEventListener('click', (e) => {
        const tab = link.dataset.tab;
        if (!tab) return;
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.location = url.toString();
    });
});

/* ── Admin subcategory dropdown ──────────────── */
const adminCatSelect = document.getElementById('modal_cat');
const adminSubcatSelect = document.getElementById('modal_subcat');
if (adminCatSelect && adminSubcatSelect) {
    adminCatSelect.addEventListener('change', async function() {
        const catId = this.value;
        adminSubcatSelect.innerHTML = '<option value="">— None —</option>';
        if (!catId) return;
        try {
            const res = await fetch(_BASE + '/admin/get_subcategories.php?cat_id=' + catId);
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

/* ── Premium Category Slider Carousel ── */
document.addEventListener('DOMContentLoaded', () => {
    const track = document.getElementById('sliderTrack');
    const slider = document.getElementById('categorySlider');
    if (!track || !slider) return;

    const originalItems = Array.from(track.children);
    const N = originalItems.length;
    if (N === 0) return;

    // 1. Setup indices and positions (Finite Slider)
    let currentIndex = 0; // Start at the first item
    let isTransitioning = false;

    function getItemsVisible() {
        return window.innerWidth > 768 ? 7 : 4;
    }

    function updatePosition(animate = true) {
        if (animate) {
            track.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        } else {
            track.style.transition = 'none';
        }
        const visibleCount = getItemsVisible();
        const percent = -1 * (currentIndex * (100 / visibleCount));
        track.style.transform = `translateX(${percent}%)`;
        updateDots();
        updateProgressBar();
    }

    function updateProgressBar() {
        const bar = document.getElementById('progressBarFill');
        if (!bar) return;
        const visibleCount = getItemsVisible();
        const maxIndex = Math.max(1, N - visibleCount);
        const progress = (currentIndex / maxIndex) * 100;
        bar.style.width = progress + '%';
    }

    function rebuildDots() {
        const dotsContainer = document.getElementById('sliderDots');
        if (!dotsContainer) return;
        
        dotsContainer.innerHTML = '';
        const visibleCount = getItemsVisible();
        const maxIndex = N - visibleCount;
        const totalDots = Math.max(1, maxIndex + 1);
        
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement('button');
            dot.className = 'slider-dot';
            if (i === currentIndex) dot.classList.add('active');
            dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
            dot.addEventListener('click', () => {
                if (N <= getItemsVisible()) return;
                if (isTransitioning) return;
                isTransitioning = true;
                currentIndex = i;
                updatePosition();
                handleUserInteraction();
            });
            dotsContainer.appendChild(dot);
        }
    }

    function updateDots() {
        const dotsContainer = document.getElementById('sliderDots');
        if (!dotsContainer) return;
        const dots = dotsContainer.querySelectorAll('.slider-dot');
        dots.forEach((dot, idx) => {
            if (idx === currentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    function checkSliderState() {
        const visibleCount = getItemsVisible();
        const controls = document.querySelector('.slider-controls-bottom');
        
        if (N <= visibleCount) {
            // Disable slider, layout items statically and center them
            slider.classList.add('slider-disabled');
            track.style.transform = 'none';
            track.style.transition = 'none';
            track.style.justifyContent = 'center';
            
            if (controls) controls.style.display = 'none';
            stopTimer();
        } else {
            // Enable finite slider
            slider.classList.remove('slider-disabled');
            track.style.justifyContent = 'flex-start';
            
            const maxIndex = N - visibleCount;
            if (currentIndex > maxIndex) currentIndex = maxIndex;
            if (currentIndex < 0) currentIndex = 0;
            
            if (controls) {
                controls.style.display = 'flex';
                rebuildDots();
            }
            updatePosition(false);
            startTimer();
        }
    }

    // 2. Set initial position or static grid state
    setTimeout(() => {
        checkSliderState();
    }, 50);

    // 3. Manual Arrow Controls
    const prevBtn = document.getElementById('sliderPrevBtn');
    const nextBtn = document.getElementById('sliderNextBtn');

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (N <= getItemsVisible()) return;
            const maxIndex = N - getItemsVisible();
            if (currentIndex >= maxIndex) return;
            if (isTransitioning) return;
            isTransitioning = true;
            currentIndex++;
            updatePosition();
            handleUserInteraction();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (N <= getItemsVisible()) return;
            if (currentIndex <= 0) return;
            if (isTransitioning) return;
            isTransitioning = true;
            currentIndex--;
            updatePosition();
            handleUserInteraction();
        });
    }

    // 4. Handle transition end
    track.addEventListener('transitionend', () => {
        isTransitioning = false;
    });

    // 5. Click Active highlights (Gold ring border & tinted background)
    track.addEventListener('click', (e) => {
        const item = e.target.closest('.category-slider-item');
        if (!item) return;
        
        track.querySelectorAll('.category-slider-item').forEach(el => {
            el.classList.remove('active');
        });
        item.classList.add('active');
    });

    // 6. Autoplay — advances one step every 3s, pauses on user interaction
    let autoplayTimer = null;
    const AUTOPLAY_DELAY = 3000;
    const RESUME_DELAY  = 5000;
    let resumeTimer = null;

    function startTimer() {
        stopTimer();
        autoplayTimer = setInterval(() => {
            const visibleCount = getItemsVisible();
            const maxIndex = N - visibleCount;
            if (maxIndex <= 0) return;
            currentIndex = currentIndex >= maxIndex ? 0 : currentIndex + 1;
            isTransitioning = true;
            updatePosition();
        }, AUTOPLAY_DELAY);
    }

    function stopTimer() {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
    }

    function handleUserInteraction() {
        stopTimer();
        clearTimeout(resumeTimer);
        resumeTimer = setTimeout(startTimer, RESUME_DELAY);
    }

    // 7. Drag and Swipe Gesture Support (Touch on Mobile, Mouse Drag on Desktop)
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let currentX = 0;
    let diffX = 0;
    let isHorizontalDrag = null; // null=undecided, true=horizontal, false=vertical
    
    // Disable default image/link dragging to avoid conflicts
    track.querySelectorAll('a, img').forEach(el => {
        el.addEventListener('dragstart', (e) => e.preventDefault());
    });

    function getEventX(e) {
        return e.touches ? e.touches[0].clientX : e.clientX;
    }

    function dragStart(e) {
        if (N <= getItemsVisible()) return; // Disable drag/swipe when slider is disabled
        if (isTransitioning) return;
        isDragging = true;
        startX = getEventX(e);
        startY = e.touches ? e.touches[0].clientY : (e.clientY || 0);
        diffX = 0;
        isHorizontalDrag = null;
        handleUserInteraction(); // Pause autoplay timer

        // Remove transitions during drag for real-time responsiveness
        track.style.transition = 'none';
    }

    function dragMove(e) {
        if (!isDragging) return;
        currentX = getEventX(e);
        diffX = currentX - startX;

        if (e.touches) {
            const dy = Math.abs(e.touches[0].clientY - startY);
            const dx = Math.abs(diffX);

            // Decide axis quickly at 3px to outrun the browser's scroll decision
            if (isHorizontalDrag === null) {
                if (dx > 3 || dy > 3) {
                    isHorizontalDrag = dx >= dy;
                }
            }

            // Confirmed vertical — hand back to native scroll
            if (isHorizontalDrag === false) {
                isDragging = false;
                updatePosition();
                return;
            }

            // Prevent scroll for confirmed horizontal OR undecided-with-x-movement
            if (isHorizontalDrag === true || dx > 0) {
                if (e.cancelable) e.preventDefault();
            }
        }

        // Give real-time visual drag feedback
        const visibleCount = getItemsVisible();
        const currentPercent = -1 * (currentIndex * (100 / visibleCount));

        // Use container width (not track width) for accurate pixel→percent mapping
        const containerWidth = slider.offsetWidth;
        const dragPercent = containerWidth > 0 ? (diffX / containerWidth) * 100 : 0;

        let targetPercent = currentPercent + dragPercent;

        // Clamp bounds with rubber-band effect
        const maxIndex = N - visibleCount;
        const maxPercent = -1 * (maxIndex * (100 / visibleCount));

        if (targetPercent > 0) {
            targetPercent = targetPercent * 0.3; // rubber-band at start
        } else if (targetPercent < maxPercent) {
            const overflow = targetPercent - maxPercent;
            targetPercent = maxPercent + overflow * 0.3; // rubber-band at end
        }

        track.style.transform = `translateX(${targetPercent}%)`;
    }

    function dragEnd() {
        if (!isDragging) return;
        isDragging = false;
        
        const visibleCount = getItemsVisible();
        const maxIndex = N - visibleCount;
        
        // Snap if dragged > 10% of container width (min 30px)
        const snapThreshold = Math.max(30, slider.offsetWidth * 0.10);
        if (Math.abs(diffX) > snapThreshold) {
            if (diffX > 0) {
                // Dragged right (swiping right -> show previous item)
                currentIndex--;
            } else {
                // Dragged left (swiping left -> show next item)
                currentIndex++;
            }
        }
        
        // Clamp index to valid bounds
        if (currentIndex < 0) currentIndex = 0;
        if (currentIndex > maxIndex) currentIndex = maxIndex;
        
        // Animate track to its final snapped position
        isTransitioning = true;
        updatePosition();
        diffX = 0;
    }

    // Touch Event Listeners for Mobile
    slider.addEventListener('touchstart', dragStart, { passive: true });
    slider.addEventListener('touchmove', dragMove, { passive: false });
    slider.addEventListener('touchend', dragEnd);
    slider.addEventListener('touchcancel', dragEnd);

    // Mouse Event Listeners for Desktop Dragging
    slider.addEventListener('mousedown', dragStart);
    window.addEventListener('mousemove', dragMove);
    window.addEventListener('mouseup', dragEnd);

    // 8. Resize support
    window.addEventListener('resize', () => {
        checkSliderState();
    });

    // 9. Interactive 3D Parallax Tilt Effect for Hero Horse Card
    const tiltCard = document.getElementById('heroTiltCard');
    if (tiltCard) {
        tiltCard.addEventListener('mousemove', (e) => {
            const rect = tiltCard.getBoundingClientRect();
            const x = e.clientX - rect.left; // x coordinate inside element
            const y = e.clientY - rect.top;  // y coordinate inside element
            
            const w = rect.width;
            const h = rect.height;
            
            // Calculate tilt degrees (max 12deg tilt in 3D perspective space)
            const tiltX = -((y - h / 2) / (h / 2)) * 12;
            const tiltY = ((x - w / 2) / (w / 2)) * 12;
            
            // Apply 3D transform and update reflection glow coordinates
            tiltCard.style.animation = 'none'; // pause idle float animation on hover
            tiltCard.style.transform = `rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale3d(1.02, 1.02, 1.02)`;
            
            // Dynamic lighting glow reflection calculation
            const px = (x / w) * 100;
            const py = (y / h) * 100;
            tiltCard.style.setProperty('--glow-x', `${px}%`);
            tiltCard.style.setProperty('--glow-y', `${py}%`);
        });
        
        tiltCard.addEventListener('mouseleave', () => {
            tiltCard.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.5s ease';
            tiltCard.style.transform = 'rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
            
            // Resume gentle 3D idle floating animation after dynamic reset completes
            setTimeout(() => {
                tiltCard.style.animation = 'float3D 6s ease-in-out infinite';
                tiltCard.style.transition = 'transform 0.1s ease, box-shadow 0.3s ease';
            }, 500);
        });
    }
});
