<?php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$email = '';
$google_id = '';
$first_name = '';
$last_name = '';

// Real Google JWT flow
$credential = $_POST['credential'] ?? '';
if ($credential) {
    // Call Google Tokeninfo API to verify JWT securely
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($credential);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local XAMPP compatibility
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        $payload = json_decode($response, true);
        if (isset($payload['sub'])) {
            $google_id = sanitize($payload['sub']);
            $email = sanitize($payload['email'] ?? '');
            $first_name = sanitize($payload['given_name'] ?? 'Google');
            $last_name = sanitize($payload['family_name'] ?? 'User');
        } else {
            $error = 'Invalid Google JWT payload.';
        }
    } else {
        $error = 'Failed to contact Google Auth servers for token validation.';
    }
} else {
    $error = 'No Google credential payload provided.';
}

if (!$error && $email && $google_id) {
    // 1. Check if user already exists by google_id
    $res = $conn->query("SELECT * FROM users WHERE google_id = '$google_id'");
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: /GadgetZone/pages/myaccount.php');
        exit;
    }
    
    // 2. Check if user exists by email (bind Google ID)
    $res_email = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($res_email && $res_email->num_rows > 0) {
        $user = $res_email->fetch_assoc();
        // Update user record with google_id
        $conn->query("UPDATE users SET google_id = '$google_id' WHERE id = " . intval($user['id']));
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: /GadgetZone/pages/myaccount.php');
        exit;
    }
    
    // 3. Register as a new user auto-generated
    $fnQ = $conn->real_escape_string($first_name);
    $lnQ = $conn->real_escape_string($last_name);
    $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    
    $ins = $conn->query("INSERT INTO users (first_name, last_name, email, password, google_id, role) 
                         VALUES ('$fnQ', '$lnQ', '$email', '$dummy_pass', '$google_id', 'customer')");
    
    if ($ins) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_role'] = 'customer';
        header('Location: /GadgetZone/pages/myaccount.php');
        exit;
    } else {
        $error = 'Failed to register new account via Google: ' . $conn->error;
    }
}

// If we reached here, there was an error
$pageTitle = 'Google Auth Error — Al Asail';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: 100px 20px; text-align: center;">
    <div style="max-width: 500px; margin: 0 auto; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 40px;">
        <span style="font-size: 48px;">⚠️</span>
        <h1 style="color: #ef4444; margin: 20px 0 10px; font-weight: 700;">Google Authentication Error</h1>
        <p style="color: var(--text2); margin-bottom: 30px; font-size: 15px; line-height: 1.6;">
            <?= htmlspecialchars($error ?: 'An unexpected error occurred during Google Sign-in.') ?>
        </p>
        <a href="/GadgetZone/pages/login.php" class="btn-primary" style="display: inline-block;">Back to Login</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
