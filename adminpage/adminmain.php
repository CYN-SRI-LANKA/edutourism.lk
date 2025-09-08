<?php
// adminmain.php (Testing Mode)
// Single-file admin with predefined users (plain-text), secure session handling, CSRF, and role-based UI.
// For local XAMPP (HTTP), secure cookie is disabled to allow sessions to work.

// ------------------ Configure Session BEFORE starting ------------------
declare(strict_types=1);

// Set session cookie flags BEFORE session_start()
ini_set('session.cookie_httponly', '1');
// For local testing without HTTPS, keep this 0. If you enable HTTPS, this will auto-become 1.
$using_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
ini_set('session.cookie_secure', $using_https ? '1' : '0');
ini_set('session.cookie_samesite', 'Strict');

// Optional: tighten session storage and behavior for testing
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_lifetime', '0');      // session cookie
ini_set('session.gc_maxlifetime', '3600');    // 1 hour

session_start();

// ------------------ Security Headers (sent before output) ------------------
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");

// ------------------ Testing Users (Plain-Text Passwords) ------------------
// For testing only. Do NOT use plain-text in production.
$USERS = [
  'gayanraj'     => ['pass' => 'admin123', 'roles' => ['SUPER_ADMIN'], 'active' => true],
  'tharusha'  => ['pass' => 'admin123',   'roles' => ['VMS_ADMIN'],   'active' => true],
  'tharushika'  => ['pass' => 'admin123',   'roles' => ['TMS_ADMIN'],   'active' => true],
];

// ------------------ Helpers ------------------
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}
function verify_csrf(?string $t): bool {
  return $t !== null && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}
function logged_in(): bool { return isset($_SESSION['user']); }
function roles(): array { return $_SESSION['user']['roles'] ?? []; }
function has_role(string ...$required): bool {
  $mine = roles();
  foreach ($required as $r) if (in_array($r, $mine, true)) return true;
  return false;
}
function can_vms(): bool { return has_role('SUPER_ADMIN', 'VMS_ADMIN'); }
function can_ems(): bool { return has_role('SUPER_ADMIN', 'EMS_ADMIN'); }
function can_tms(): bool { return has_role('SUPER_ADMIN', 'TMS_ADMIN'); }

// ------------------ Login Attempt Tracking (simple throttle) ------------------
// Prevent brute force during testing
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['last_attempt'])) $_SESSION['last_attempt'] = 0;

function throttle_delay_ms(): int {
  // Exponential backoff up to ~8s after many failures
  $attempts = (int)($_SESSION['login_attempts'] ?? 0);
  if ($attempts <= 2) return 200;          // 0-2 fails: 200ms
  if ($attempts <= 5) return 800;          // 3-5 fails: 800ms
  if ($attempts <= 8) return 2000;         // 6-8 fails: 2s
  return 8000;                              // 9+ fails: 8s
}

// ------------------ Handle POST actions ------------------
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $token  = $_POST['_csrf'] ?? null;

  if (!verify_csrf($token)) {
    http_response_code(403);
    $err = 'Security check failed.';
  } else {
    if ($action === 'login') {
      // Throttle
      $delay = throttle_delay_ms();
      usleep($delay * 1000);

      $u = trim((string)($_POST['username'] ?? ''));
      $p = (string)($_POST['password'] ?? '');

      global $USERS;
      if (!isset($USERS[$u])) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        $err = 'Invalid credentials.';
      } else {
        $rec = $USERS[$u];
        if (!$rec['active']) {
          $_SESSION['login_attempts']++;
          $_SESSION['last_attempt'] = time();
          $err = 'Account disabled.';
        } elseif ($p !== $rec['pass']) { // plain-text compare for testing
          $_SESSION['login_attempts']++;
          $_SESSION['last_attempt'] = time();
          $err = 'Invalid credentials.';
        } else {
          // Success: reset attempts and establish session
          $_SESSION['login_attempts'] = 0;
          $_SESSION['last_attempt'] = time();

          session_regenerate_id(true); // session fixation defense
          $_SESSION['user'] = [
            'username'   => $u,
            'roles'      => $rec['roles'],
            'login_time' => time(),
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
            'ua'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
          ];
          // Rotate CSRF after login
          $_SESSION['csrf'] = bin2hex(random_bytes(32));
          header('Location: ' . $_SERVER['PHP_SELF']);
          exit;
        }
      }
    } elseif ($action === 'logout') {
      // Log out cleanly
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

// ------------------ Views ------------------
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
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#f5f7f9; margin:0 }
      .wrap { max-width:420px; margin:9vh auto; background:#fff; padding:22px; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,.08) }
      h1 { margin:0 0 10px; font-size:20px }
      .muted { color:#6b7280; font-size:13px; margin-bottom:10px }
      label { display:block; font-size:14px; margin:10px 0 6px }
      input[type=text], input[type=password] { width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px }
      button { margin-top:14px; width:100%; padding:10px 14px; background:#2b8e62; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600 }
      .err { color:#b91c1c; background:#fee2e2; border:1px solid #fecaca; padding:8px 10px; border-radius:8px; margin:10px 0; font-size:14px }
      .hint { font-size:12px; color:#6b7280; margin-top:10px }
    </style>
  </head>
  <body>
    <div class="wrap">
      <h1>Admin Login</h1>
      <div class="muted">Use predefined testing credentials (no database).</div>
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
        Examples: super/admin123, vmsadmin/vms123, emsadmin/ems123, tmsadmin/tms123
        <?php if ($attempts > 0): ?>
          <br>Failed attempts: <?= (int)$attempts ?> (throttling applies)
        <?php endif; ?>
      </div>
    </div>
  </body>
  </html>
  <?php
}

function render_dashboard(): void {
  $csrf = csrf_token();
  $u = $_SESSION['user'];
  $roles = implode(', ', roles());
  $loginAt = isset($u['login_time']) ? date('Y-m-d H:i:s', (int)$u['login_time']) : '';
  $ip = h($u['ip'] ?? '');
  ?>
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Admin Main</title>
    <style>
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#0b1220; margin:0 }
      .nav { display:flex; justify-content:space-between; align-items:center; padding:14px 16px; background:#0f172a; color:#e5e7eb; border-bottom:1px solid #1f2937 }
      .btn { display:inline-block; background:#2b8e62; color:#fff; padding:10px 14px; border-radius:8px; text-decoration:none; border:none; cursor:pointer; font-weight:600 }
      .container { max-width:960px; margin:22px auto; padding:0 16px; color:#e5e7eb }
      .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-top:16px }
      .card { background:#111827; border:1px solid #1f2937; border-radius:12px; padding:18px }
      .title { font-weight:600; margin-bottom:6px }
      .desc { color:#9ca3af; font-size:13px; margin-bottom:12px }
      .disabled { background:#334155; opacity:.6; cursor:not-allowed }
      .meta { color:#9ca3af; font-size:12px; margin:8px 0 }
      form.inline { display:inline }
      .pill { background:#0ea5e9; color:#001018; border-radius:999px; padding:2px 8px; font-size:11px; margin-left:8px; font-weight:700 }
    </style>
  </head>
  <body>
    <div class="nav">
      <div>Admin Main <span class="pill"><?= h($u['username']) ?></span></div>
      <form class="inline" method="post" action="<?= h($_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
        <input type="hidden" name="action" value="logout">
        <button class="btn" type="submit">Logout</button>
      </form>
    </div>

    <div class="container">
      <div class="meta">Signed in at: <?= h($loginAt) ?> • IP: <?= $ip ?></div>

      <div class="grid">
        <div class="card">
          <div class="title">VMS</div>
          <div class="desc">Visa Management System</div>
          <?php if (can_vms()): ?>
            <a class="btn" href="adminvisa.php">Open VMS</a>
          <?php else: ?>
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="title">EMS</div>
          <div class="desc">Employee Management System</div>
          <?php if (can_ems()): ?>
            <a class="btn" href="adminemployee.php">Open EMS</a>
          <?php else: ?>
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="title">TMS</div>
          <div class="desc">Task Management System</div>
          <?php if (can_tms()): ?>
            <a class="btn" href="admintask.php">Open TMS</a>
          <?php else: ?>
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
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
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="title">FMS</div>
          <div class="desc">File Management System</div>
          <?php if (can_vms()): ?>
            <a class="btn" href="file_explorer.php">Open FMS</a>
          <?php else: ?>
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="title">RMS</div>
          <div class="desc">Review Management System</div>
          <?php if (can_vms()): ?>
            <a class="btn" href="adminreview.php">Open RMS</a>
          <?php else: ?>
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
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
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="title">HMS</div>
          <div class="desc">Highlight Management System</div>
          <?php if (can_vms()): ?>
            <a class="btn" href="adminhighlight.php">Open HMS</a>
          <?php else: ?>
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="title">Report Center</div>
          <div class="desc">All Report Genarations</div>
          <?php if (can_tms()): ?>
            <a class="btn" href="reportcenter.php">Open Report Center</a>
          <?php else: ?>
            <a class="btn disabled" href="#" onclick="return false;">No access</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="meta">Your roles: <?= h($roles) ?></div>
      <div class="meta">Note: Each module must also check the session and roles server-side.</div>
    </div>
  </body>
  </html>
  <?php
}

// ------------------ Router ------------------
if (!logged_in()) {
  render_login($err);
} else {
  render_dashboard();
}
