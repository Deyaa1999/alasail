<?php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /GadgetZone/pages/myaccount.php');
    exit;
}

$lang = getCurrentLang();
$pageTitle = __('login') . ' — Al Asail';
$error = '';
$reset_success = isset($_GET['reset']) && $_GET['reset'] === 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    
    if ($email && $pass) {
        $res = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($res === false) {
            $error = 'Database error: ' . htmlspecialchars($conn->error) . '. Please run migrations first.';
        } else {
            $user = $res->fetch_assoc();
            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $redirect = $_GET['redirect'] ?? '/GadgetZone/pages/myaccount.php';
                header("Location: $redirect");
                exit;
            } else {
                $error = $lang === 'ar' ? 'البريد الإلكتروني أو كلمة المرور غير صحيحة.' : 'Invalid email or password.';
            }
        }
    } else {
        $error = $lang === 'ar' ? 'يرجى ملء جميع الحقول.' : 'Please fill in all fields.';
    }
}

// Fetch Google Client ID
$google_client_id = '';
$s_res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'google_client_id'");
if ($s_res && $s_res->num_rows > 0) {
    $google_client_id = trim($s_res->fetch_assoc()['setting_value'] ?? '');
}

// Calculate fully dynamic, production-safe Google OAuth Callback URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$curDir = dirname($_SERVER['SCRIPT_NAME']);
$curDir = str_replace('\\', '/', $curDir);
if ($curDir === '/') {
    $curDir = '';
}
$google_callback_url = $protocol . '://' . $host . $curDir . '/google_callback.php';

require_once __DIR__ . '/../includes/header.php';
?>

<style>

.forgot-link-container {
    text-align: right;
    margin-top: 6px;
}
[dir="rtl"] .forgot-link-container {
    text-align: left;
}
.forgot-password-link {
    font-size: 13px;
    color: var(--accent);
    text-decoration: none;
    transition: color 0.2s;
}
.forgot-password-link:hover {
    color: var(--accent-l);
}
</style>

<!-- Load Google Authentication Script -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<div class="auth-page">
    <div class="auth-card fade-in">
        <h1 class="auth-title"><?= __('welcome_back') ?></h1>
        <p class="auth-sub"><?= __('login_sub') ?></p>
        
        <?php if ($reset_success): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                🔑 <?= $lang === 'ar' ? 'تم إعادة تعيين كلمة المرور الخاصة بك بنجاح. يمكنك الآن تسجيل الدخول باستخدام كلمة المرور الجديدة.' : 'Your password has been successfully updated. You can now log in.' ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label class="form-label"><?= __('email_address') ?></label>
                <input type="email" name="email" class="form-input" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group" style="margin-bottom: 8px;">
                <label class="form-label"><?= __('password') ?></label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <div class="forgot-link-container">
                <a href="/GadgetZone/pages/forgot_password.php" class="forgot-password-link"><?= __('forgot_password') ?></a>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:16px;margin-top:15px;"><?= __('login_btn') ?></button>
        </form>
        
        <div style="margin: 20px 0; text-align: center; border-bottom: 1px solid var(--border); line-height: 0.1em;">
            <span style="background: #111511; padding: 0 15px; color: var(--text3); font-size: 13px;"><?= __('or_sign_in_with') ?></span>
        </div>
        
        <!-- Google Authentication Button Container -->
        <div class="google-auth-button-wrapper">
            <?php if ($google_client_id && strpos($google_client_id, '.apps.googleusercontent.com') !== false): ?>
                <!-- Official Google Identity Button -->
                <div id="g_id_onload"
                     data-client_id="<?= htmlspecialchars($google_client_id) ?>"
                     data-context="signin"
                     data-ux_mode="popup"
                     data-login_uri="<?= htmlspecialchars($google_callback_url) ?>"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="rectangular"
                     data-theme="filled_black"
                     data-text="signin_with"
                     data-size="large"
                     data-logo_alignment="left"
                     data-width="100%">
                </div>
            <?php elseif ($google_client_id): ?>
                <div style="padding: 12px; border: 1px dashed #ef4444; border-radius: 8px; background: rgba(239,68,68,0.05); text-align: center; font-size: 13px; color: #f87171;">
                    ⚠️ <?= $lang === 'ar' ? 'معرف العميل (Client ID) الموفر غير صالح. يجب أن يحتوي على .apps.googleusercontent.com' : 'The configured Google Client ID is invalid. It must contain .apps.googleusercontent.com' ?>
                </div>
            <?php else: ?>
                <div style="padding: 12px; border: 1px dashed #f59e0b; border-radius: 8px; background: rgba(245,158,11,0.05); text-align: center; font-size: 13px; color: var(--text);">
                    ⚠️ <?= $lang === 'ar' ? 'تسجيل الدخول عبر Google غير مهيأ بعد. يرجى إعداد معرف العميل في لوحة التحكم.' : 'Google Sign-In is not configured yet. Please set your Client ID in the admin settings.' ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="auth-footer" style="margin-top: 24px;">
            <?= __('dont_have_account') ?> <a href="/GadgetZone/pages/register.php"><?= __('create_one') ?></a>
        </div>
    </div>
</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>
