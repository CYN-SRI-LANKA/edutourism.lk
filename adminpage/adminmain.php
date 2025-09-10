<?php


// adminmain.php - Complete role-based admin system with employees table authentication
// Role mapping: super -> SUPER_ADMIN (all access), admin -> TMS_ADMIN (TMS/PMS), staff -> VMS_ADMIN (VMS only)

declare(strict_types=1);


// Session configuration
ini_set('session.cookie_httponly', '1');
$using_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
ini_set('session.cookie_secure', $using_https ? '1' : '0');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_lifetime', '0');
ini_set('session.gc_maxlifetime', '3600');
session_start();

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");

// Database connection - CHANGE THESE VALUES FOR YOUR DATABASE
include('../homepage/db.php');

if ($con->connect_error) {
    die('Database connection failed: ' . $con->connect_error);
}

// Helper functions
function h(string $s): string { 
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); 
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(?string $t): bool {
    return $t !== null && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

function logged_in(): bool { 
    return isset($_SESSION['user']); 
}

function roles(): array { 
    if (!isset($_SESSION['user']['role']) || empty($_SESSION['user']['role'])) {
        return [];
    }
    
    // Map database roles to admin panel roles
    $role = $_SESSION['user']['role'];
    switch($role) {
        case 'super': return ['SUPER_ADMIN'];
        case 'admin': return ['TMS_ADMIN'];
        case 'staff': return ['VMS_ADMIN'];
        default: return [];
    }
}

function has_role(string ...$required): bool {
    $mine = roles();
    foreach ($required as $r) {
        if (in_array($r, $mine, true)) return true;
    }
    return false;
}

function can_vms(): bool { return has_role('SUPER_ADMIN', 'VMS_ADMIN'); }
function can_ems(): bool { return has_role('SUPER_ADMIN', 'EMS_ADMIN'); }
function can_tms(): bool { return has_role('SUPER_ADMIN', 'TMS_ADMIN'); }

// Login attempt tracking
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['last_attempt'])) $_SESSION['last_attempt'] = 0;

function throttle_delay_ms(): int {
    $attempts = (int)($_SESSION['login_attempts'] ?? 0);
    if ($attempts <= 2) return 200;
    if ($attempts <= 5) return 800;
    if ($attempts <= 8) return 2000;
    return 8000;
}

// Handle POST actions
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['_csrf'] ?? null;

    if (!verify_csrf($token)) {
        http_response_code(403);
        $err = 'Security check failed.';
    } else {
        if ($action === 'login') {
            // Throttle login attempts
            $delay = throttle_delay_ms();
            usleep($delay * 1000);

            $u = trim((string)($_POST['username'] ?? ''));
            $p = (string)($_POST['password'] ?? '');

            if (empty($u) || empty($p)) {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();
                $err = 'Username and password are required.';
            } else {
                // Query database for user
                $stmt = mysqli_prepare($con, "SELECT id, username, password, role, active FROM employees WHERE username = ? LIMIT 1");
                if (!$stmt) {
                    $err = 'Database error occurred.';
                } else {
                    mysqli_stmt_bind_param($stmt, "s", $u);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($user = mysqli_fetch_assoc($result)) {
                        if (!$user['active']) {
                            $_SESSION['login_attempts']++;
                            $_SESSION['last_attempt'] = time();
                            $err = 'Account disabled.';
                        } elseif (!password_verify($p, $user['password'])) {
                            $_SESSION['login_attempts']++;
                            $_SESSION['last_attempt'] = time();
                            $err = 'Invalid credentials.';
                        } else {
                            // Success: reset attempts and establish session
                            $_SESSION['login_attempts'] = 0;
                            $_SESSION['last_attempt'] = time();

                            session_regenerate_id(true);
                            $_SESSION['user'] = [
                                'id'         => $user['id'],
                                'username'   => $user['username'],
                                'role'       => $user['role'],
                                'login_time' => time(),
                                'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
                                'ua'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
                            ];
                            $_SESSION['csrf'] = bin2hex(random_bytes(32));
                            header('Location: ' . $_SERVER['PHP_SELF']);
                            exit;
                        }
                    } else {
                        $_SESSION['login_attempts']++;
                        $_SESSION['last_attempt'] = time();
                        $err = 'Invalid credentials.';
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        } elseif ($action === 'logout') {
            // Clean logout
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// Render login form
function render_login(string $err = ''): void {
    $csrf = csrf_token();
    $attempts = (int)($_SESSION['login_attempts'] ?? 0);
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>Admin Login</title>
        <style>
            body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f5f7f9; margin: 0; }
            .wrap { max-width: 420px; margin: 9vh auto; background: #fff; padding: 22px; border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
            h1 { margin: 0 0 10px; font-size: 20px; }
            .muted { color: #6b7280; font-size: 13px; margin-bottom: 10px; }
            label { display: block; font-size: 14px; margin: 10px 0 6px; }
            input[type=text], input[type=password] { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
            button { margin-top: 14px; width: 100%; padding: 10px 14px; background: #2b8e62; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
            button:hover { background: #218838; }
            .err { color: #b91c1c; background: #fee2e2; border: 1px solid #fecaca; padding: 8px 10px; border-radius: 8px; margin: 10px 0; font-size: 14px; }
            .hint { font-size: 12px; color: #6b7280; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="wrap">
            <img src="../homepage/img/logo.png" style="width: 200px; height: auto;">
            
            <h1>Admin Login</h1>
            <!-- <div class="muted">Database Authentication System</div> -->
            <?php if ($err): ?><div class="err"><?= h($err) ?></div><?php endif; ?>
            <form method="post" action="<?= h($_SERVER['PHP_SELF']) ?>">
                <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
                <input type="hidden" name="action" value="login">
                <label>Username</label>
                <input name="username" autocomplete="username" required>
                <label>Password</label>
                <input type="password" name="password" autocomplete="current-password" required>
                <button type="submit">Login</button>
            </form>
            <div class="hint">
                <!-- Role Access: super (All Systems), admin (TMS/PMS), staff (VMS) -->
                <?php if ($attempts > 0): ?>
                    <br>Failed attempts: <?= (int)$attempts ?>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Render dashboard
function render_dashboard(): void {
    $csrf = csrf_token();
    $u = $_SESSION['user'];
    $roles_array = roles();
    $roles = implode(', ', $roles_array);
    $loginAt = isset($u['login_time']) ? date('Y-m-d H:i:s', (int)$u['login_time']) : '';
    $ip = h($u['ip'] ?? '');
    $role = $u['role'] ?? 'Unknown'; // Safe access to role
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>Admin Main</title>
        <style>
            body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #0b1220; margin: 0; }
            .nav { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: #0f172a; color: #e5e7eb; border-bottom: 1px solid #1f2937; }
            .btn { display: inline-block; background: #2b8e62; color: #fff; padding: 10px 14px; border-radius: 8px; text-decoration: none; border: none; cursor: pointer; font-weight: 600; }
            .btn:hover { background: #218838; }
            .container { max-width: 960px; margin: 22px auto; padding: 0 16px; color: #e5e7eb; }
            .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 16px; }
            .card { background: #111827; border: 1px solid #1f2937; border-radius: 12px; padding: 18px; }
            .title { font-weight: 600; margin-bottom: 6px; }
            .desc { color: #9ca3af; font-size: 13px; margin-bottom: 12px; }
            .disabled { background: #334155; opacity: .6; cursor: not-allowed; }
            .meta { color: #9ca3af; font-size: 12px; margin: 8px 0; }
            form.inline { display: inline; }
            .pill { background: #0ea5e9; color: #001018; border-radius: 999px; padding: 2px 8px; font-size: 11px; margin-left: 8px; font-weight: 700; }
            .role-pill { background: #10b981; color: #001018; border-radius: 999px; padding: 2px 8px; font-size: 11px; margin-left: 8px; font-weight: 700; }
        </style>
    </head>
    <body>
        <div class="nav">
            <div>Admin Main 
                <span class="pill"><?= h($u['username']) ?></span>
                <span class="role-pill"><?= h($role) ?></span>
            </div>
            <form class="inline" method="post" action="<?= h($_SERVER['PHP_SELF']) ?>">
                <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
                <input type="hidden" name="action" value="logout">
                <button class="btn" type="submit">Logout</button>
            </form>
        </div>

        <div class="container">
            <div class="meta">Signed in at: <?= h($loginAt) ?> • IP: <?= $ip ?> • DB Role: <?= h($role) ?></div>

            <div class="grid">
                <div class="card">
                    <div class="title">VMS</div>
                    <div class="desc">Visa Management System</div>
                    <?php if (can_vms()): ?>
                        <a class="btn" href="adminvisa.php">Open VMS</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="title">EMS</div>
                    <div class="desc">Employee Management System</div>
                    <?php if (can_ems()): ?>
                        <a class="btn" href="adminemployee.php">Open EMS</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="title">TMS</div>
                    <div class="desc">Task Management System</div>
                    <?php if (can_tms()): ?>
                        <a class="btn" href="admintask.php">Open TMS</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid">
                <div class="card">
                    <div class="title">PMS</div>
                    <div class="desc">Post Management System</div>
                    <?php if (can_tms()): ?>
                        <a class="btn" href="adminpost.php">Open PMS</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="title">FMS</div>
                    <div class="desc">File Management System</div>
                    <?php if (can_vms()): ?>
                        <a class="btn" href="file_explorer.php">Open FMS</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="title">RMS</div>
                    <div class="desc">Review Management System</div>
                    <?php if (can_vms()): ?>
                        <a class="btn" href="adminreview.php">Open RMS</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid">
                <div class="card">
                    <div class="title">FAQMS</div>
                    <div class="desc">FAQ Management System</div>
                    <?php if (can_vms()): ?>
                        <a class="btn" href="adminfaq.php">Open FAQMS</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="title">HMS</div>
                    <div class="desc">Highlight Management System</div>
                    <?php if (can_vms()): ?>
                        <a class="btn" href="adminhighlight.php">Open HMS</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="title">Report Center</div>
                    <div class="desc">All Report Generations</div>
                    <?php if (can_tms()): ?>
                        <a class="btn" href="reportcenter.php">Open Report Center</a>
                    <?php else: ?>
                        <span class="btn disabled">No access</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Router
if (!logged_in()) {
    render_login($err);
} else {
    render_dashboard();
}
?>
