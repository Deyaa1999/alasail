<?php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /GadgetZone/pages/myaccount.php');
    exit;
}

$lang = getCurrentLang();
$pageTitle = __('create_account') . ' — Al Asail';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fn    = trim($_POST['first_name'] ?? '');
    $ln    = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['confirm_password'] ?? '';

    if (!$fn) $errors[] = $lang === 'ar' ? 'الاسم الأول مطلوب.' : 'First name is required.';
    if (!$ln) $errors[] = $lang === 'ar' ? 'الاسم الأخير مطلوب.' : 'Last name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang === 'ar' ? 'البريد الإلكتروني المدخل غير صالح.' : 'Valid email is required.';
    }
    if (strlen($pass) < 6) {
        $errors[] = $lang === 'ar' ? 'يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.' : 'Password must be at least 6 characters.';
    }
    if ($pass !== $conf) {
        $errors[] = $lang === 'ar' ? 'كلمتا المرور غير متطابقتين.' : 'Passwords do not match.';
    }

    if (empty($errors)) {
        $e = $conn->real_escape_string($email);
        $exists = $conn->query("SELECT id FROM users WHERE email='$e'")->num_rows;
        if ($exists) {
            $errors[] = $lang === 'ar' 
                ? 'البريد الإلكتروني مسجل بالفعل. <a href="/GadgetZone/pages/login.php">تسجيل الدخول بدلاً من ذلك ←</a>' 
                : 'Email is already registered. <a href="/GadgetZone/pages/login.php">Log in instead →</a>';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $fnQ  = $conn->real_escape_string($fn);
            $lnQ  = $conn->real_escape_string($ln);
            $conn->query("INSERT INTO users (first_name, last_name, email, password) VALUES ('$fnQ','$lnQ','$e','$hash')");
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['user_role'] = 'customer';
            header('Location: /GadgetZone/pages/myaccount.php');
            exit;
        }
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



<!-- Load Google Authentication Script -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<div class="auth-page">
    <div class="auth-card fade-in">
        <h1 class="auth-title"><?= __('create_account') ?></h1>
        <p class="auth-sub"><?= __('join_us') ?></p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-grid" style="display: flex; gap: 12px;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label"><?= __('first_name') ?></label>
                    <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label"><?= __('last_name') ?></label>
                    <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('email_address') ?></label>
                <input type="email" name="email" class="form-input" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('password') ?> (<?= $lang === 'ar' ? '6 أحرف على الأقل' : 'min 6 characters' ?>)</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('confirm_password') ?></label>
                <input type="password" name="confirm_password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:16px"><?= __('register_btn') ?></button>
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
                     data-context="signup"
                     data-ux_mode="popup"
                     data-login_uri="<?= htmlspecialchars($google_callback_url) ?>"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="rectangular"
                     data-theme="filled_black"
                     data-text="signup_with"
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
            <?= __('already_have_account') ?> <a href="/GadgetZone/pages/login.php"><?= __('login') ?></a>
        </div>
    </div>
</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>
