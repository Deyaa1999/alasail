<?php
/**
 * GadgetZone — Automated Database Importer
 * Visually setups and seeds the MySQL database using XAMPP defaults.
 */

$host = '127.0.0.1';
$port = 3306;
$username = 'root';
$password = '';
$dbname = 'gadgetzone';
$sqlFile = __DIR__ . '/database_setup.sql';

$status = '';
$error = '';
$success = false;

if (isset($_POST['run_import'])) {
    // 1. Connect to MySQL without selecting database first
    $conn = @new mysqli($host, $username, $password, '', $port);

    if ($conn->connect_error) {
        $error = "Failed to connect to MySQL server. Please ensure XAMPP MySQL is active.<br>Error: " . htmlspecialchars($conn->connect_error);
    } else {
        $conn->set_charset('utf8mb4');

        // 2. Read database_setup.sql
        if (!file_exists($sqlFile)) {
            $error = "Database setup SQL file not found at " . htmlspecialchars($sqlFile);
        } else {
            $sqlContent = file_get_contents($sqlFile);

            // 3. Prepend DROP DATABASE to force a clean rebuild.
            //    This fixes InnoDB "doesn't exist in engine" corruption from partial imports.
            $dropSql = "DROP DATABASE IF EXISTS `gadgetzone`;\n";
            $fullSql  = $dropSql . $sqlContent;

            // 4. Execute everything as a multi-query
            $errors = [];
            if ($conn->multi_query($fullSql)) {
                do {
                    if ($res = $conn->store_result()) {
                        $res->free();
                    }
                    if ($conn->error) {
                        $errors[] = $conn->error;
                    }
                } while ($conn->more_results() && $conn->next_result());
            } else {
                $errors[] = $conn->error;
            }

            if (!empty($errors)) {
                $error = "Import failed with the following error(s):<ul style='margin:8px 0 0 16px'>"
                    . implode('', array_map(fn($e) => "<li>" . htmlspecialchars($e) . "</li>", $errors))
                    . "</ul>";
            } else {
                $success = true;
                $status  = "Database <code>$dbname</code> successfully created, tables structured, and default data seeded!";
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup — GadgetZone</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #f59e0b;
            --accent-l: #fcd34d;
            --bg: #0a0a0f;
            --surface: #16161f;
            --surface2: #1e1e2a;
            --border: rgba(255,255,255,0.08);
            --border-success: rgba(16,185,129,0.3);
            --border-error: rgba(239,68,68,0.3);
            --text: #f0f0f5;
            --text2: #9090a8;
            --font-h: 'IBM Plex Sans', sans-serif;
            --font-b: 'DM Sans', sans-serif;
            --r: 12px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-b);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .setup-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            text-align: center;
        }
        .logo {
            font-family: var(--font-h);
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .logo span { color: var(--accent); }
        .logo-icon { font-size: 32px; }
        h2 {
            font-family: var(--font-h);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        p {
            color: var(--text2);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 14px 28px;
            border-radius: var(--r);
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
            font-family: var(--font-b);
        }
        .btn:hover {
            background: var(--accent-l);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(245,158,11,0.2);
        }
        .btn-secondary {
            background: transparent;
            color: var(--text);
            border: 1px solid var(--border);
            margin-top: 12px;
        }
        .btn-secondary:hover {
            background: var(--surface2);
        }
        .alert {
            padding: 18px;
            border-radius: var(--r);
            font-size: 14px;
            margin-bottom: 24px;
            text-align: left;
            line-height: 1.5;
        }
        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid var(--border-error);
            color: #f87171;
        }
        .alert-success {
            background: rgba(16,185,129,0.1);
            border: 1px solid var(--border-success);
            color: #34d399;
        }
        .alert code {
            background: rgba(255,255,255,0.06);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        .warning {
            font-size: 12px;
            color: #f87171;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
    </style>
</head>
<body>

<div class="setup-card">
    <div class="logo" style="gap:12px">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;font-weight:900;font-size:20px">Z</span>
        <span><span style="color:#d1d5db">ZAYN</span><span style="color:#a855f7"> AI</span></span>
    </div>
    
    <?php if ($success): ?>
        <h2>🎉 Database Successfully Installed!</h2>
        <div class="alert alert-success">
            <?= $status ?>
        </div>
        <p>The database has been fully seeded with categories, standard products, and a Super Admin account.</p>
        
        <a href="/GadgetZone/index.php"><button class="btn">Enter Storefront 🛍️</button></a>
        <a href="/GadgetZone/admin/index.php"><button class="btn btn-secondary">Enter Admin Dashboard 📊</button></a>
        
        <div class="warning">
            ⚠️ <span>For security, please delete <strong>import.php</strong> from your project directory.</span>
        </div>
        
    <?php else: ?>
        <h2>Database Setup Wizard</h2>
        <p>This utility will <strong>drop and fully recreate</strong> the <code>gadgetzone</code> database, import all tables, and seed sample data. Uses XAMPP defaults: <code>127.0.0.1</code>, user <code>root</code>, no password, port <code>3306</code>.</p>
        <p style="color:#f87171;font-size:13px;margin-top:-16px;">⚠️ Any existing <code>gadgetzone</code> data will be wiped and replaced.</p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" name="run_import" class="btn">🔄 Fresh Install Database</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
