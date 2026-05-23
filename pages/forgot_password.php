<?php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    header('Location: /GadgetZone/pages/myaccount.php');
    exit;
}

$isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) 
    || strpos($_SERVER['HTTP_HOST'], '127.0.0.1:') === 0 
    || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0;

$pageTitle = __('forgot_password_title') . ' — Al Asail';
$error = '';
$success = false;
$simulatedEmailContent = '';
$simulatedResetLink = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize(trim($_POST['email'] ?? ''));
    if ($email) {
        $res = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            
            // Generate a secure reset token
            $token = bin2hex(random_bytes(32));
            // Expire in 1 hour
            $expires = date('Y-m-d H:i:s', time() + 3600);
            
            // Save token in the database
            $conn->query("UPDATE users SET reset_token='$token', token_expires='$expires' WHERE id=" . intval($user['id']));
            
            $success = true;
            
            // Generate local simulated reset link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $simulatedResetLink = "$protocol://$host/GadgetZone/pages/reset_password.php?token=$token";
            
            // Generate Simulated HTML Email Content
            $userName = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
            $lang = getCurrentLang();
            $dir = $lang === 'ar' ? 'rtl' : 'ltr';
            $emailTitle = __('reset_password_title');
            
            $simulatedEmailContent = "
            <div dir='$dir' style=\"font-family: 'Cairo', sans-serif; background: #0b0f0b; color: #e2e8f0; padding: 30px; border-radius: 12px; border: 1px solid #1f2d1f; max-width: 600px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.5);\">
                <div style='text-align: center; margin-bottom: 24px;'>
                    <span style='font-size: 24px; font-weight: bold; color: #f59e0b;'>🐎 Al Asail Equine</span>
                </div>
                <h2 style='color: #ffffff; border-bottom: 1px solid #1f2d1f; padding-bottom: 12px; font-weight: 600;'>" . ($lang === 'ar' ? 'طلب إعادة تعيين كلمة المرور' : 'Password Reset Request') . "</h2>
                <p style='font-size: 15px; line-height: 1.6; color: #a0aec0;'>" . 
                    ($lang === 'ar' ? "مرحباً $userName، لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في الأصيل للخيول." : "Hello $userName, we received a request to reset your password for your Al Asail Equine account.") . 
                "</p>
                <p style='font-size: 15px; line-height: 1.6; color: #a0aec0;'>" . 
                    ($lang === 'ar' ? "يرجى النقر فوق الزر أدناه لإعادة تعيين كلمة مرور جديدة لحسابك. هذا الرابط صالح لمدة ساعة واحدة فقط." : "Please click the button below to reset your password. This link is valid for 1 hour only.") . 
                "</p>
                <div style='text-align: center; margin: 32px 0;'>
                    <a href='$simulatedResetLink' style='background: #f59e0b; color: #000000; padding: 14px 28px; font-weight: bold; text-decoration: none; border-radius: 8px; font-size: 15px; display: inline-block; transition: background 0.2s;'>
                        " . ($lang === 'ar' ? '🔒 إعادة تعيين كلمة المرور' : '🔒 Reset Password') . "
                    </a>
                </div>
                <p style='font-size: 13px; line-height: 1.6; color: #718096;'>" . 
                    ($lang === 'ar' ? "إذا لم تطلب هذا التغيير، يرجى تجاهل هذا البريد الإلكتروني وسيظل حسابك آمناً." : "If you didn't request this change, please ignore this email and your account will remain secure.") . 
                "</p>
                <div style='margin-top: 24px; border-top: 1px solid #1f2d1f; padding-top: 16px; font-size: 12px; color: #4a5568; text-align: center;'>
                    &copy; 2026 Al Asail Equine. All rights reserved.
                </div>
            </div>";
            
            // Create directories if missing
            if (!file_exists(__DIR__ . '/../scratch/emails')) {
                mkdir(__DIR__ . '/../scratch/emails', 0777, true);
            }
            // Save to local file for validation
            file_put_contents(__DIR__ . "/../scratch/emails/reset_email_" . str_replace(['@', '.'], '_', $email) . ".html", $simulatedEmailContent);

            // Send real email via PHP mail() in production
            if (!$isLocal) {
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: Al Asail Equine <noreply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
                @mail($email, "Al Asail Equine — Reset Password Link", $simulatedEmailContent, $headers);
            }

            // Set dynamic success message
            $lang = getCurrentLang();
            if ($isLocal) {
                $successMsg = $lang === 'ar' 
                    ? 'تم محاكاة إرسال رابط استعادة كلمة المرور لبريدك الإلكتروني أدناه بنجاح.' 
                    : 'A password reset link has been simulated for your email below.';
            } else {
                $successMsg = $lang === 'ar' 
                    ? 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني بنجاح (يرجى التحقق من صندوق الوارد أو مجلد البريد العشوائي).' 
                    : 'A password reset link has been successfully sent to your email address (please check your inbox or spam folder).';
            }
        } else {
            $error = $lang === 'ar' ? 'البريد الإلكتروني المدخل غير مسجل لدينا.' : 'The entered email is not registered with us.';
        }
    } else {
        $error = $lang === 'ar' ? 'يرجى إدخال البريد الإلكتروني.' : 'Please enter your email.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card fade-in">
        <h1 class="auth-title"><?= __('forgot_password_title') ?></h1>
        <p class="auth-sub"><?= __('forgot_password_sub') ?></p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                🎉 <?= htmlspecialchars($successMsg) ?>
            </div>
            
            <?php if ($isLocal): ?>
                <!-- Beautiful Local simulated email drawer / Modal overlay -->
                <div class="demo-email-overlay" style="margin-top: 20px; background: #0c0f0c; border: 2px dashed var(--accent); border-radius: var(--r); padding: 20px;">
                    <div style="display:flex; justify-content: space-between; align-items:center; border-bottom:1px solid var(--border); padding-bottom: 10px; margin-bottom: 15px;">
                        <span style="font-weight: 700; color: var(--accent); font-size: 14px;">🛠️ DEVELOPER SANDBOX: SIMULATED INBOX</span>
                        <span style="font-size: 11px; background: rgba(245,158,11,0.15); color: var(--accent); padding: 2px 6px; border-radius: 4px;">Local Dev Mode</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.02); border-radius: 6px; padding: 12px; margin-bottom: 15px; font-size: 13px; color: var(--text2);">
                        <strong>To:</strong> <?= htmlspecialchars($email) ?><br>
                        <strong>Subject:</strong> 🐎 Al Asail Equine — Reset Password Link<br>
                        <strong>Log File:</strong> <code>scratch/emails/reset_email_<?= htmlspecialchars(str_replace(['@', '.'], '_', $email)) ?>.html</code>
                    </div>
                    
                    <?= $simulatedEmailContent ?>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="/GadgetZone/pages/login.php" class="btn-outline-sm" style="display:inline-block;"><?= __('back_to_login') ?></a>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="/GadgetZone/pages/login.php" class="btn-primary" style="display:inline-block; padding:12px 32px; font-size:15px;"><?= __('back_to_login') ?></a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label class="form-label"><?= __('email_address') ?></label>
                    <input type="email" name="email" class="form-input" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:16px"><?= __('send_reset_link') ?></button>
            </form>
            <div class="auth-footer" style="margin-top: 20px;">
                <a href="/GadgetZone/pages/login.php"><?= __('back_to_login') ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
