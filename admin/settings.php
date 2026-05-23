<?php
$pageTitle = 'Settings — Admin';
require_once __DIR__ . '/layout.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currency = $conn->real_escape_string($_POST['active_currency'] ?? 'BDT');
    $pk       = $conn->real_escape_string(trim($_POST['stripe_publishable_key'] ?? ''));
    $sk       = $conn->real_escape_string(trim($_POST['stripe_secret_key'] ?? ''));
    $wh       = $conn->real_escape_string(trim($_POST['stripe_webhook_secret'] ?? ''));
    $google_client_id = $conn->real_escape_string(trim($_POST['google_client_id'] ?? ''));
    $notification_email = $conn->real_escape_string(trim($_POST['notification_email'] ?? ''));
    $sender_email = $conn->real_escape_string(trim($_POST['sender_email'] ?? ''));
    $smtp_host = $conn->real_escape_string(trim($_POST['smtp_host'] ?? ''));
    $smtp_port = $conn->real_escape_string(trim($_POST['smtp_port'] ?? ''));
    $smtp_user = $conn->real_escape_string(trim($_POST['smtp_user'] ?? ''));
    $smtp_pass = $conn->real_escape_string(trim($_POST['smtp_pass'] ?? ''));
    $smtp_secure = $conn->real_escape_string(trim($_POST['smtp_secure'] ?? ''));
    $shipping_amman = $conn->real_escape_string(trim($_POST['shipping_amman'] ?? '3.00'));
    $shipping_aqaba = $conn->real_escape_string(trim($_POST['shipping_aqaba'] ?? '10.00'));
    $shipping_others = $conn->real_escape_string(trim($_POST['shipping_others'] ?? '5.00'));

    $settings = [
        'active_currency'        => $currency,
        'stripe_publishable_key' => $pk,
        'stripe_secret_key'      => $sk,
        'stripe_webhook_secret'  => $wh,
        'google_client_id'       => $google_client_id,
        'notification_email'     => $notification_email,
        'sender_email'           => $sender_email,
        'smtp_host'              => $smtp_host,
        'smtp_port'              => $smtp_port,
        'smtp_user'              => $smtp_user,
        'smtp_pass'              => $smtp_pass,
        'smtp_secure'            => $smtp_secure,
        'shipping_amman'         => $shipping_amman,
        'shipping_aqaba'         => $shipping_aqaba,
        'shipping_others'        => $shipping_others,
    ];
    foreach ($settings as $k => $v) {
        $conn->query("INSERT INTO settings (setting_key,setting_value) VALUES ('$k','$v') ON DUPLICATE KEY UPDATE setting_value='$v'");
    }
    // Invalidate session cache
    unset($_SESSION['active_currency_data']);
    $msg = 'Settings saved successfully!';
}

// Load settings
$settingsData = [];
$res = $conn->query("SELECT * FROM settings");
while ($r = $res->fetch_assoc()) $settingsData[$r['setting_key']] = $r['setting_value'];

$activeCur = $settingsData['active_currency'] ?? 'BDT';
require_once __DIR__ . '/../includes/currency.php';

// Format validation check
$google_warning = '';
$loaded_google_id = trim($settingsData['google_client_id'] ?? '');
if ($loaded_google_id && strpos($loaded_google_id, '.apps.googleusercontent.com') === false) {
    $google_warning = 'Warning: The saved Google Web Client ID is not in a valid format. A valid Client ID must contain ".apps.googleusercontent.com". Storefront login buttons may not render correctly until this is fixed.';
}
?>

<div class="admin-header">
    <div>
        <div class="admin-title">Settings</div>
        <div class="admin-subtitle">Configure currency and payment options</div>
    </div>
</div>

<?php if ($msg): ?><div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($google_warning): ?><div class="alert alert-error" style="background: rgba(239, 68, 68, 0.05); border-left: 4px solid #ef4444; color: #f87171; padding: 14px; margin-bottom: 24px; border-radius: var(--r); font-size: 14px;">⚠️ <?= htmlspecialchars($google_warning) ?></div><?php endif; ?>

<form method="POST">
    <input type="hidden" name="active_currency" id="activeCurrencyInput" value="<?= htmlspecialchars($activeCur) ?>">

    <!-- Currency -->
    <div class="admin-card" style="margin-bottom:24px">
        <div class="admin-card-header">
            <div class="admin-card-title">🌐 Active Currency</div>
            <div style="font-size:13px;color:var(--text2)">Current: <strong style="color:var(--accent)"><?= $activeCur ?></strong></div>
        </div>
        <div class="admin-card-body">
            <div class="currency-grid">
                <?php foreach (CURRENCIES as $code => $cur): ?>
                <div class="currency-card <?= $code===$activeCur?'selected':'' ?>" data-code="<?= $code ?>">
                    <div class="currency-code"><?= $code ?></div>
                    <div class="currency-symbol"><?= $cur['symbol'] ?></div>
                    <div class="currency-name"><?= $cur['name'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Stripe -->
    <div class="admin-card" style="margin-bottom:24px">
        <div class="admin-card-header">
            <div class="admin-card-title">💳 Stripe Payment</div>
        </div>
        <div class="admin-card-body">
            <div class="alert alert-success" style="margin-bottom:16px">
                Get your keys from <a href="https://dashboard.stripe.com/test/apikeys" target="_blank" style="color:var(--accent)">dashboard.stripe.com</a> → Developers → API Keys (enable Test Mode first).
            </div>
            <div class="form-grid-2" style="gap:16px">
                <div class="form-group">
                    <label class="form-label">Publishable Key (pk_test_...)</label>
                    <input type="text" name="stripe_publishable_key" class="form-input" value="<?= htmlspecialchars($settingsData['stripe_publishable_key'] ?? '') ?>" placeholder="pk_test_...">
                </div>
                <div class="form-group">
                    <label class="form-label">Secret Key (sk_test_...)</label>
                    <input type="password" name="stripe_secret_key" class="form-input" value="<?= htmlspecialchars($settingsData['stripe_secret_key'] ?? '') ?>" placeholder="sk_test_...">
                </div>
                <div class="form-group">
                    <label class="form-label">Webhook Secret (optional)</label>
                    <input type="text" name="stripe_webhook_secret" class="form-input" value="<?= htmlspecialchars($settingsData['stripe_webhook_secret'] ?? '') ?>" placeholder="whsec_...">
                </div>
            </div>
            <div style="margin-top:12px;padding:14px;background:var(--surface2);border-radius:var(--r);font-size:13px;color:var(--text2)">
                <strong style="color:var(--text)">Test Cards:</strong>
                Visa: 4242 4242 4242 4242 &nbsp;|&nbsp; MC: 5555 5555 5555 4444 &nbsp;|&nbsp; Declined: 4000 0000 0000 0002 (Exp: 12/25, CVC: 123)
            </div>
        </div>
    </div>

    <!-- Google OAuth Settings -->
    <div class="admin-card" style="margin-bottom:24px">
        <div class="admin-card-header">
            <div class="admin-card-title">🌐 Google Social Sign-In (Production)</div>
        </div>
        <div class="admin-card-body">
            <div class="alert alert-success" style="margin-bottom:16px; border-left: 4px solid var(--accent); background: rgba(245,158,11,0.05); color: var(--text);">
                🔑 <strong>Configure Google Sign-In:</strong> Get your Web Client ID from the 
                <a href="https://console.cloud.google.com/" target="_blank" style="color:var(--accent); text-decoration: underline; font-weight: 600;">Google Cloud Console</a> 
                → APIs & Services → Credentials. 
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label" style="font-weight: 600; color: var(--text);">Google Web Client ID</label>
                <input type="text" name="google_client_id" class="form-input" value="<?= htmlspecialchars($settingsData['google_client_id'] ?? '') ?>" placeholder="your-client-id.apps.googleusercontent.com" style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                <p style="font-size:12px; color:var(--text3); margin-top:6px;">Leave this field empty to disable the Google Sign-In button on the storefront login and registration pages.</p>
            </div>
            <div style="margin-top:12px;padding:14px;background:var(--surface2);border-radius:var(--r);font-size:13px;color:var(--text2); border: 1px solid var(--border);">
                <strong style="color:var(--text)">Authorized JavaScript Origins & Redirect URI to configure in Google Console:</strong>
                <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 8px;">
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: var(--text3); display: block; margin-bottom: 2px;">Authorized JavaScript Origin:</span>
                        <code style="color:var(--accent); word-break: break-all; background: rgba(0,0,0,0.2); padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 13px;"><?= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?></code>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: var(--text3); display: block; margin-bottom: 2px;">Authorized Redirect URI:</span>
                        <code style="color:var(--accent); word-break: break-all; background: rgba(0,0,0,0.2); padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 13px;"><?= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>/GadgetZone/pages/google_callback.php</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Order Notifications Settings -->
    <div class="admin-card" style="margin-bottom:24px">
        <div class="admin-card-header">
            <div class="admin-card-title">📧 Email Order Notifications (Gmail & SMTP Settings)</div>
        </div>
        <div class="admin-card-body">
            <div class="alert alert-success" style="margin-bottom:16px; border-left: 4px solid var(--accent); background: rgba(245,158,11,0.05); color: var(--text);">
                ✉️ <strong>Dynamic Email Configuration:</strong> Set up your custom SMTP credentials below to automatically route new storefront order notifications to the store owner. All fields are completely dynamic.
            </div>
            <div class="form-grid-2" style="gap:16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Recipient Email (To Address)</label>
                    <input type="email" name="notification_email" class="form-input" value="<?= htmlspecialchars($settingsData['notification_email'] ?? 'daldebsi@gmail.com') ?>" placeholder="daldebsi@gmail.com" style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Sender Email (From Address)</label>
                    <input type="email" name="sender_email" class="form-input" value="<?= htmlspecialchars($settingsData['sender_email'] ?? 'daldebsi@gmail.com') ?>" placeholder="daldebsi@gmail.com" style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
            </div>
            
            <h4 style="margin: 20px 0 10px 0; color: var(--accent); font-size: 14px; border-bottom: 1px solid var(--border); padding-bottom: 6px;">🔐 SMTP Server Configurations</h4>
            <div class="form-grid-2" style="gap:16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">SMTP Host (e.g. smtp.gmail.com)</label>
                    <input type="text" name="smtp_host" class="form-input" value="<?= htmlspecialchars($settingsData['smtp_host'] ?? 'smtp.gmail.com') ?>" placeholder="smtp.gmail.com" style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Port (e.g. 587 or 465)</label>
                    <input type="text" name="smtp_port" class="form-input" value="<?= htmlspecialchars($settingsData['smtp_port'] ?? '587') ?>" placeholder="587" style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
            </div>
            
            <div class="form-grid-2" style="gap:16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">SMTP Username (Gmail Email)</label>
                    <input type="text" name="smtp_user" class="form-input" value="<?= htmlspecialchars($settingsData['smtp_user'] ?? 'daldebsi@gmail.com') ?>" placeholder="daldebsi@gmail.com" style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Password (Gmail App Password)</label>
                    <input type="password" name="smtp_pass" class="form-input" value="<?= htmlspecialchars($settingsData['smtp_pass'] ?? '') ?>" placeholder="Your Gmail App Password" style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="form-label">SMTP Encryption Security</label>
                <select name="smtp_secure" class="form-input" style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                    <?php $sec = $settingsData['smtp_secure'] ?? 'tls'; ?>
                    <option value="tls" <?= $sec === 'tls' ? 'selected' : '' ?>>TLS (Recommended for port 587)</option>
                    <option value="ssl" <?= $sec === 'ssl' ? 'selected' : '' ?>>SSL (Recommended for port 465)</option>
                    <option value="none" <?= $sec === 'none' ? 'selected' : '' ?>>None (Plaintext)</option>
                </select>
                <p style="font-size:12px; color:var(--text3); margin-top:6px;">If using Gmail, please create and use a **Gmail App Password** rather than your regular account password to guarantee connection approval.</p>
            </div>
        </div>
    </div>

    <!-- Shipping Rates Settings -->
    <div class="admin-card" style="margin-bottom:24px">
        <div class="admin-card-header">
            <div class="admin-card-title">🚚 Shipping & Delivery Rates</div>
        </div>
        <div class="admin-card-body">
            <div class="alert alert-success" style="margin-bottom:16px; border-left: 4px solid var(--accent); background: rgba(245,158,11,0.05); color: var(--text);">
                📦 <strong>Configure Shipping Tariffs:</strong> Update the shipping rates in JOD for the supported regions. These values dynamically calculate storefront cart subtotals in real-time.
            </div>
            <div class="form-grid" style="grid-template-columns: repeat(3, 1fr); gap:16px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Inside Amman (JOD)</label>
                    <input type="number" step="0.01" min="0" name="shipping_amman" class="form-input" value="<?= htmlspecialchars($settingsData['shipping_amman'] ?? '3.00') ?>" required style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Aqaba Governorate (JOD)</label>
                    <input type="number" step="0.01" min="0" name="shipping_aqaba" class="form-input" value="<?= htmlspecialchars($settingsData['shipping_aqaba'] ?? '10.00') ?>" required style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Other Governorates (JOD)</label>
                    <input type="number" step="0.01" min="0" name="shipping_others" class="form-input" value="<?= htmlspecialchars($settingsData['shipping_others'] ?? '5.00') ?>" required style="width: 100%; border: 1px solid var(--border); background: var(--surface2); color: var(--text); padding: 12px; border-radius: var(--r);">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-admin btn-admin-primary" style="padding:12px 32px;font-size:15px">Save All Settings</button>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>
