</main>

<!-- ── Add to Cart Popup Modal ── -->
<div class="cart-popup-overlay" id="cartPopupOverlay">
    <div class="cart-popup" id="cartPopup" role="dialog" aria-modal="true" aria-labelledby="cartPopupTitle">
        <div class="cart-popup-icon">✅</div>
        <h3 class="cart-popup-title" id="cartPopupTitle"><?= __('product_added') ?></h3>
        <p class="cart-popup-sub"><?= __('next_step') ?></p>
        <div class="cart-popup-btns">
            <a href="/GadgetZone/pages/cart.php" class="cart-popup-btn-primary"><?= __('go_to_cart') ?></a>
            <button class="cart-popup-btn-outline" id="cartPopupClose"><?= __('continue_shopping') ?></button>
        </div>
    </div>
</div>

<!-- ── WhatsApp Floating Button ── -->
<a href="https://wa.me/962798139022" class="whatsapp-float" target="_blank" rel="noopener" title="Chat on WhatsApp">
    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
    </svg>
</a>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="/GadgetZone/index.php" class="logo">
                    <img src="/GadgetZone/assets/images/logo.png" alt="Al Asail Equine" class="site-logo-img site-logo-footer">
                </a>
                <p class="footer-desc"><?= __('footer_desc') ?></p>
                <div class="footer-contact">
                    <a href="https://wa.me/962798139022" class="footer-contact-item" target="_blank" rel="noopener">
                        <span class="footer-contact-icon">📱</span>
                        <div>
                            <div class="footer-contact-label"><?= __('contact_whatsapp') ?></div>
                            <div class="footer-contact-value">+962 7 9813 9022</div>
                        </div>
                    </a>
                    <a href="mailto:info@alasail-equine.com" class="footer-contact-item">
                        <span class="footer-contact-icon">📧</span>
                        <div>
                            <div class="footer-contact-label"><?= __('contact_email') ?></div>
                            <div class="footer-contact-value">info@alasail-equine.com</div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="footer-col">
                <h4><?= __('shop') ?></h4>
                <a href="/GadgetZone/pages/shop.php?cat=veterinary-treatments"><?= __('Veterinary Treatments for Horses') ?></a>
                <a href="/GadgetZone/pages/shop.php?cat=supplements-nutrition"><?= __('Supplements & Nutrition') ?></a>
                <a href="/GadgetZone/pages/shop.php?cat=horseshoes"><?= __('Horseshoes') ?></a>
                <a href="/GadgetZone/pages/shop.php?cat=horse-rider-equipment"><?= __('Horse & Rider Equipment') ?></a>
                <a href="/GadgetZone/pages/shop.php?cat=veterinary-consumables"><?= __('Veterinary Consumables') ?></a>
                <a href="/GadgetZone/pages/shop.php?cat=horse-feed-fodder"><?= __('Horse Feed & Fodder') ?></a>
            </div>
            <div class="footer-col">
                <h4><?= __('account') ?></h4>
                <a href="/GadgetZone/pages/myaccount.php"><?= __('my_account') ?></a>
                <a href="/GadgetZone/pages/myaccount.php?tab=orders"><?= __('orders') ?></a>
                <a href="/GadgetZone/pages/cart.php"><?= __('shopping_cart') ?></a>
                <a href="/GadgetZone/pages/checkout.php"><?= __('checkout') ?></a>
            </div>
            <div class="footer-col">
                <h4><?= __('support') ?></h4>
                <a href="#"><?= __('faq') ?></a>
                <a href="#"><?= __('shipping_policy') ?></a>
                <a href="#"><?= __('returns_refunds') ?></a>
                <a href="#"><?= __('privacy_policy') ?></a>
                <a href="https://wa.me/962798139022" target="_blank" rel="noopener">💬 <?= __('whatsapp_chat') ?></a>
                <a href="mailto:info@alasail-equine.com">📧 <?= __('email_us') ?></a>
            </div>
        </div>

        <!-- Payment methods -->
        <div class="footer-payment-row">
            <div class="footer-payment-label"><?= __('accepted_payments') ?>:</div>
            <div class="payment-method-badge">
                <span class="pay-method-icon">💳</span>
                <span><?= __('visa_card') ?></span>
            </div>
            <div class="payment-method-badge">
                <span class="pay-method-icon">💵</span>
                <span><?= __('cash_on_delivery') ?></span>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 Al Asail Equine Veterinary Services. <?= __('rights_reserved') ?></p>
            <a href="https://wa.me/962798139022" class="footer-whatsapp-link" target="_blank" rel="noopener">
                💬 +962 7 9813 9022
            </a>
        </div>
    </div>
</footer>

<script src="/GadgetZone/assets/js/main.js?v=<?= file_exists(__DIR__.'/../assets/js/main.js') ? filemtime(__DIR__.'/../assets/js/main.js') : time() ?>"></script>
</body>
</html>
