<?php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /GadgetZone/pages/myaccount.php');
    exit;
}

$token = sanitize(trim($_GET['token'] ?? $_POST['token'] ?? ''));
$error = '';
$success = false;
$user = null;
$lang = getCurrentLang();

if (!$token) {
    $error = $lang === 'ar' ? 'رابط إعادة تعيين كلمة المرور غير صالح أو مفقود.' : 'Invalid or missing password reset link.';
} else {
    // Check if token matches and is not expired
    $now = date('Y-m-d H:i:s');
    $res = $conn->query("SELECT * FROM users WHERE reset_token = '$token' AND token_expires > '$now'");
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
    } else {
        $error = $lang === 'ar' ? 'رابط إعادة تعيين كلمة المرور غير صالح أو انتهت صلاحيته.' : 'The password reset link is invalid or has expired.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 6) {
        $error = $lang === 'ar' ? 'يجب أن تتكون كلمة المرور من 6 أحرف على الأقل.' : 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = $lang === 'ar' ? 'كلمتا المرور غير متطابقتين.' : 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userId = intval($user['id']);
        
        // Update password and clear reset tokens
        $upd = $conn->query("UPDATE users SET password='$hash', reset_token=NULL, token_expires=NULL WHERE id=$userId");
        if ($upd) {
            $success = true;
            header('Location: /GadgetZone/pages/login.php?reset=success');
            exit;
        } else {
            $error = $lang === 'ar' ? 'حدث خطأ أثناء تحديث كلمة المرور. يرجى المحاولة مجدداً.' : 'Error updating password. Please try again.';
        }
    }
}

$pageTitle = __('reset_password_title') . ' — Al Asail';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card fade-in">
        <h1 class="auth-title"><?= __('reset_password_title') ?></h1>
        <p class="auth-sub"><?= __('reset_password_sub') ?></p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($user && !$success): ?>
            <form method="POST" class="auth-form">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="form-group">
                    <label class="form-label"><?= __('new_password') ?></label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?= __('confirm_password') ?></label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:16px"><?= __('reset_btn') ?></button>
            </form>
        <?php endif; ?>
        
        <div class="auth-footer" style="margin-top: 20px;">
            <a href="/GadgetZone/pages/login.php"><?= __('back_to_login') ?></a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
