<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

$login_page_path = 'http://localhost/FinalProject1/PHP/login.php';

if (isset($_GET['logout'])) {
    session_regenerate_id(true);
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path);
    exit();
}

$allowed_roles = ['student', 'org_rep', 'admin'];
$user_id = $_SESSION['user_id'] ?? null;
$role    = $_SESSION['role']    ?? null;
$userName        = $_SESSION['fullname'] ?? 'Org Representative';
$userAffiliation = 'TIP Manila';

if (empty($user_id) || empty($role) || !in_array($role, $allowed_roles)) {
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path);
    exit();
}

$role = (string)$role;

switch ($role) {
    case 'student': $homeLink = 'Student_db.php';  $pageTitle = 'Settings'; break;
    case 'org_rep': $homeLink = 'OrgRep_db.php';   $pageTitle = 'Settings'; break;
    case 'admin':   $homeLink = 'Admin_db.php';     $pageTitle = 'Settings'; break;
    default:        $homeLink = '#';                $pageTitle = 'Settings';
}

$userAvatarText      = strtoupper(substr($userName, 0, 1));
$userEmail           = $_SESSION['email'] ?? 'orgrep@tip.edu.ph';
$userRole            = $_SESSION['role']  ?? 'org_rep';
$userAffiliationFull = 'Technological Institute of the Philippines - Manila';
$userOrg             = 'ASAPHIL - TIP Manila';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | ArkiConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        /* ══════════════════════════════════════════════
           GLOBAL RESET & VARIABLES
        ══════════════════════════════════════════════ */
        :root {
            --primary:       #a43825;
            --primary-dark:  #7c2a1b;
            --primary-light: #c9503a;
            --card-bg:       #ffffff;
            --bg:            #f4f1ef;
            --text-dark:     #1e1210;
            --text-mid:      #4a2e27;
            --text-muted:    #9e7f78;
            --border:        #ecddd8;
            --shadow-sm:     0 1px 4px rgba(164,56,37,0.07);
            --shadow-md:     0 4px 16px rgba(164,56,37,0.11);
            --radius:        14px;
            --sidebar-w:     280px;
            --sidebar-col:   70px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

         body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .dashboard-container { display: flex; width: 100%; min-height: 100vh; }

        /* ══════════════════════════════════════════════
           SIDEBAR
        ══════════════════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            background: #fff;
            display: flex;
            flex-direction: column;
            padding: 16px 10px;
            position: sticky;
            top: 16px;
            height: calc(100vh - 32px);
            overflow-y: auto;
            overflow-x: hidden;
            flex-shrink: 0;
            z-index: 100;
            box-shadow: 0 8px 32px rgba(164,56,37,0.10);
            transition: width 0.22s ease;
            border-radius: 20px;
            margin: 16px 0 16px 16px;
        }

        .sidebar-top-icons {
            display: flex;
            justify-content: space-between;
            flex-direction: row-reverse;
            align-items: center;
            padding: 4px 6px 10px;
            margin-bottom: 4px;
        }

        .sidebar-top-icons button {
            background: #f3f3f7;
            border: none;
            color: #888;
            font-size: 1rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 10px;
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.14s ease;
        }
        .sidebar-top-icons button:hover { background: #e8e8f0; color: var(--primary); }
        .sidebar-top-icons .logo img { height: 25px; width: auto; }

        .nav-section { flex-grow: 1; padding: 0 4px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: #555;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.14s ease;
            margin: 2px 0;
            border-radius: 12px;
            white-space: nowrap;
        }

        .nav-link i, .nav-link svg { width: 20px; text-align: center; font-size: 1rem; flex-shrink: 0; }
        .nav-link:hover:not(.active) { background: rgba(164,56,37,0.08); color: var(--primary); }
        .nav-link.active { background: var(--primary); color: white; font-weight: 700; box-shadow: 0 4px 14px rgba(164,56,37,0.25); }

        .separator { border: 0; height: 1px; background: #f0f0f5; margin: 8px 4px; }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            margin: 8px 4px 0;
            border-radius: 12px;
            background: #f3f3f7;
            cursor: pointer;
            overflow: hidden;
            flex-shrink: 0;
            transition: all 0.14s ease;
        }
        .user-profile:hover { background: rgba(164,56,37,0.08); transform: translateY(-1px); }
        .user-profile img { width: 36px; height: 36px; min-width: 36px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
        .user-info .name { font-size: 13px; font-weight: 600; color: #1a1a2e; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; }
        .user-info .role { font-size: 11px; color: #888; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; }

        /* Collapsed */
        .sidebar.collapsed { width: var(--sidebar-col); overflow: hidden; padding: 16px 8px; }
        .sidebar.collapsed .link-text,
        .sidebar.collapsed .user-info,
        .sidebar.collapsed .header-container { display: none; }
        .sidebar.collapsed .sidebar-top-icons { justify-content: center; flex-direction: column-reverse; align-items: center; gap: 8px; }
        .sidebar.collapsed .user-profile { justify-content: center; padding: 10px 0; margin: 0 2px; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 10px 0; }
        .sidebar.collapsed .separator { margin: 8px 2px; }
        .sidebar.collapsed .nav-section { padding: 0 2px; }

        /* ══════════════════════════════════════════════
           CONTENT AREA
        ══════════════════════════════════════════════ */
        .content-area {
            flex: 1;
            padding: 24px 24px 40px 20px;
            min-height: 100vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .content-header {
            background: linear-gradient(120deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 18px;
            padding: 26px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 28px rgba(164,56,37,0.28);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        .content-header::before {
            content: ''; position: absolute;
            top: -40px; right: -40px;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .content-header::after {
            content: ''; position: absolute;
            bottom: -60px; right: 120px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }

        .header-left { display: flex; flex-direction: column; gap: 4px; position: relative; z-index: 1; }
        .header-left h1 {  1.85rem; font-weight: 700; color: #fff; line-height: 1.1; }
        .header-left p { font-size: 0.82rem; color: rgba(255,255,255,0.65); font-weight: 500; }

        .header-right { display: flex; align-items: center; gap: 10px; position: relative; z-index: 1; }
        .time-display {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 18px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
        }
        .time-display i { color: rgba(255,255,255,0.8); font-size: 1rem; }
        .time-text { font-size: 1rem; font-weight: 700; color: #fff; letter-spacing: 0.02em; }

        /* ══════════════════════════════════════════════
           SETTINGS LAYOUT  (mirrors settings.php)
        ══════════════════════════════════════════════ */
        .st-wrap {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 28px;
            animation: stFade .4s ease both;
        }
        @keyframes stFade { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

        /* Left nav */
        .st-nav {
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            height: fit-content;
        }
        .st-nav-header {
            padding: 16px 20px 12px;
            border-bottom: 1px solid var(--border);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--primary);
        }
        .st-nav-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: 'DM Sans', inherit;
            font-size: .88rem;
            font-weight: 500;
            color: var(--text-mid);
            text-align: left;
            transition: background .15s, color .15s;
            position: relative;
            border-bottom: 1px solid var(--border);
        }
        .st-nav-btn:last-child { border-bottom: none; }
        .st-nav-btn:hover { background: #fdf7f5; color: var(--primary); }
        .st-nav-btn.active { background: #fdf0ec; color: var(--primary); font-weight: 600; }
        .st-nav-btn.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: var(--primary);
            border-radius: 0 2px 2px 0;
        }
        .st-nav-btn i { width: 16px; text-align: center; font-size: .85rem; }

        /* Right panel */
        .st-panel { display: flex; flex-direction: column; }

        .st-panel-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 55%, var(--primary-light) 100%);
            border-radius: var(--radius) var(--radius) 0 0;
            padding: 22px 28px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            overflow: hidden;
        }
        .st-panel-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .st-panel-icon {
            width: 42px; height: 42px;
            background: rgba(255,255,255,.18);
            border-radius: 10px;
            display: grid; place-items: center;
            color: #fff; font-size: 1.1rem;
            flex-shrink: 0; position: relative;
            backdrop-filter: blur(4px);
        }
        .st-panel-text { position: relative; }
        .st-panel-text h3 {  1.2rem; font-weight: 700; color: #fff; margin-bottom: 2px; }
        .st-panel-text p { font-size: .78rem; color: rgba(255,255,255,.75); }

        .st-panel-body {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 var(--radius) var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        /* Tabs */
        .st-tab { display: none; padding: 28px; animation: stFade .3s ease; }
        .st-tab.active { display: block; }

        /* Avatar row */
        .avatar-row {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: #fdf7f5;
            border-radius: 10px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .avatar-circle {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 2rem; font-weight: 700;
            box-shadow: 0 4px 14px rgba(164,56,37,.25);
            flex-shrink: 0;
            background-size: cover;
            background-position: center;
        }
        .avatar-info { flex: 1; }
        .avatar-info h3 { font-size: 1.15rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 2px; }
        .avatar-info p { font-size: .8rem; color: var(--text-muted); }
        .avatar-actions { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }

        /* Info grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .info-box {
            background: #fdf7f5;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 16px;
        }
        .info-box-label { font-size: .7rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px; }
        .info-box-val { font-size: .92rem; font-weight: 700; color: var(--primary); }

        /* Form */
        .form-divider { border: none; border-top: 1px solid var(--border); margin: 4px 0 20px; }
        .form-section-title { font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--primary); margin-bottom: 16px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .form-field { display: flex; flex-direction: column; gap: 6px; }
        .field-label { font-size: .82rem; font-weight: 600; color: var(--text-mid); display: flex; align-items: center; gap: 6px; }
        .field-label i { color: var(--primary); font-size: .78rem; }
        .field-row { display: flex; gap: 8px; }
        .form-input {
            flex: 1;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-family: 'DM Sans', sans-serif;
            font-size: .88rem;
            color: var(--text-dark);
            background: #fafafa;
            transition: border-color .2s;
        }
        .form-input:focus { outline: none; border-color: var(--primary); }
        .form-input:disabled { background: #f5f5f5; color: var(--text-muted); }

        /* Buttons */
        .btn-save {
            padding: 9px 16px;
            background: var(--primary);
            color: #fff; border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .82rem; font-weight: 700;
            cursor: pointer; white-space: nowrap;
            transition: background .15s, transform .12s;
        }
        .btn-save:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .btn-upload {
            padding: 8px 14px;
            background: #1f318e;
            color: #fff; border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .8rem; font-weight: 700;
            cursor: pointer; display: inline-flex;
            align-items: center; gap: 6px;
            transition: opacity .15s;
        }
        .btn-upload:hover { opacity: .88; }

        .btn-ghost {
            padding: 8px 14px;
            background: #fff;
            color: var(--text-mid);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .8rem; font-weight: 600;
            cursor: pointer; display: inline-flex;
            align-items: center; gap: 6px;
            transition: background .15s;
        }
        .btn-ghost:hover { background: #fdf7f5; }

        .btn-change-pw {
            padding: 9px 16px;
            background: #fff;
            color: var(--primary);
            border: 1.5px solid var(--primary);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .82rem; font-weight: 700;
            cursor: pointer; white-space: nowrap;
            transition: background .15s, color .15s;
        }
        .btn-change-pw:hover { background: var(--primary); color: #fff; }

        .form-actions { margin-top: 20px; display: flex; justify-content: flex-end; }
        .btn-primary-lg {
            padding: 11px 24px;
            background: var(--primary);
            color: #fff; border: none;
            border-radius: 9px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem; font-weight: 700;
            cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px;
            transition: background .15s, transform .12s;
        }
        .btn-primary-lg:hover { background: var(--primary-dark); transform: translateY(-1px); }

        /* Toggle items */
        .toggle-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
            gap: 16px;
        }
        .toggle-item:last-child { border-bottom: none; }
        .toggle-info h4 { font-size: .9rem; font-weight: 600; color: var(--text-dark); margin-bottom: 3px; }
        .toggle-info p { font-size: .8rem; color: var(--text-muted); }

        .switch { position: relative; display: inline-block; width: 46px; height: 24px; flex-shrink: 0; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .switch-slider { position: absolute; cursor: pointer; inset: 0; background: #d1d5db; border-radius: 24px; transition: background .2s; }
        .switch-slider::before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: transform .2s; }
        .switch input:checked + .switch-slider { background: var(--primary); }
        .switch input:checked + .switch-slider::before { transform: translateX(22px); }

        /* ══════════════════════════════════════════════
           PASSWORD MODAL
        ══════════════════════════════════════════════ */
        #passwordModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            backdrop-filter: blur(4px);
        }
        #passwordModal .modal-inner {
            width: 100%; max-width: 480px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.18);
            overflow: hidden;
            animation: stFade .25s ease;
        }
        .modal-head {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 20px 24px;
            display: flex; align-items: center; gap: 12px;
        }
        .modal-head-icon {
            width: 38px; height: 38px;
            background: rgba(255,255,255,.18);
            border-radius: 9px;
            display: grid; place-items: center;
            color: #fff; font-size: .9rem;
        }
        .modal-head h3 { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 700; color: #fff; margin: 0; }
        .modal-head p { font-size: .75rem; color: rgba(255,255,255,.75); margin: 2px 0 0; }
        .modal-close-btn {
            margin-left: auto;
            background: rgba(255,255,255,.18);
            border: none; color: #fff;
            width: 30px; height: 30px;
            border-radius: 50%;
            font-size: 1rem; cursor: pointer;
            display: grid; place-items: center;
            transition: background .15s;
        }
        .modal-close-btn:hover { background: rgba(255,255,255,.32); }
        .modal-body { padding: 22px 24px; }
        .modal-field { margin-bottom: 14px; }
        .modal-field label { display: block; font-size: .82rem; font-weight: 600; color: var(--text-mid); margin-bottom: 6px; }
        .pw-wrap { display: flex; align-items: center; position: relative; }
        .pw-wrap .form-input { padding-right: 40px; }
        .pw-toggle { position: absolute; right: 10px; background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: .85rem; }
        .strength-bar { height: 5px; background: var(--border); border-radius: 4px; overflow: hidden; margin-top: 8px; }
        .strength-fill { height: 100%; width: 0; background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981); transition: width .2s; }
        .pw-checklist { margin-top: 10px; padding: 0; list-style: none; }
        .pw-checklist li { font-size: .78rem; color: var(--text-muted); display: flex; align-items: center; gap: 7px; margin-bottom: 4px; }
        .pw-checklist li.valid { color: #10b981; }
        .pw-checklist li.invalid { color: #ef4444; }
        .modal-foot { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }
        .btn-modal-cancel { padding: 9px 18px; background: #fff; color: var(--text-mid); border: 1px solid var(--border); border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: .85rem; font-weight: 600; cursor: pointer; }
        .btn-modal-apply { padding: 9px 18px; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: .85rem; font-weight: 700; cursor: pointer; transition: background .15s; }
        .btn-modal-apply:hover { background: var(--primary-dark); }
        .btn-modal-apply:disabled { opacity: .5; cursor: not-allowed; }

        /* ══════════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════════ */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); position: fixed; left: 0; top: 0; height: 100%; border-radius: 0; margin: 0; }
            .sidebar.active { transform: translateX(0); }
            .content-area { padding: 16px; }
        }
        @media (max-width: 900px) {
            .st-wrap { grid-template-columns: 1fr; }
            .st-nav { display: grid; grid-template-columns: repeat(3,1fr); }
            .st-nav-header { display: none; }
            .st-nav-btn { border-bottom: none; border-right: 1px solid var(--border); justify-content: center; }
            .st-nav-btn:last-child { border-right: none; }
            .st-nav-btn.active::before { top: auto; bottom: 0; left: 0; right: 0; width: auto; height: 3px; }
            .st-panel-header { border-radius: var(--radius); }
            .st-panel-body { border-radius: var(--radius); border-top: 1px solid var(--border); margin-top: 8px; }
            .form-grid { grid-template-columns: 1fr; }
            .info-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 500px) {
            .info-grid { grid-template-columns: 1fr; }
            .modal-foot { flex-direction: column-reverse; }
            .btn-modal-cancel, .btn-modal-apply { width: 100%; text-align: center; justify-content: center; }
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">

    <!-- ════════════════ SIDEBAR ════════════════ -->
    <aside class="sidebar" id="left-sidebar">
        <div class="sidebar-top-icons">
            <button id="collapse-toggle" title="Collapse sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-container">
                <div class="logo">
                    <a href="<?php echo htmlspecialchars($homeLink); ?>">
                        <img src="../Images/newlogo.png" alt="Arki Connect Logo">
                    </a>
                </div>
            </div>
        </div>

        <div class="nav-section">
            <a href="OrgRep_db.php" class="nav-link" title="Dashboard">
                <i data-lucide="layout-dashboard"></i>
                <span class="link-text">Dashboard</span>
            </a>
            <a href="create_event.php" class="nav-link" title="Create Event">
                <i data-lucide="plus-circle"></i>
                <span class="link-text">Create Event</span>
            </a>
            <a href="my_events.php" class="nav-link" title="My Events">
                <i data-lucide="calendar-days"></i>
                <span class="link-text">My Events</span>
            </a>
            <a href="registration_reports.php" class="nav-link" title="Registration Reports">
                <i data-lucide="file-text"></i>
                <span class="link-text">Registration Reports</span>
            </a>
            <hr class="separator">
            <a href="my_organization.php" class="nav-link" title="My Organization">
                <i data-lucide="info"></i>
                <span class="link-text">My Organization</span>
            </a>
            <a href="helpcenter_org.php" class="nav-link" title="Help Center">
                <i data-lucide="life-buoy"></i>
                <span class="link-text">Help Center</span>
            </a>
            <hr class="separator">
            <a href="settings_org.php" class="nav-link active" title="Settings">
                <i data-lucide="settings"></i>
                <span class="link-text">Settings</span>
            </a>
            <a href="login.php?logout=true" class="nav-link" title="Logout">
                <i data-lucide="log-out"></i>
                <span class="link-text">Logout</span>
            </a>
        </div>

        <div class="user-profile">
            <img src="https://placehold.co/40x40/A43825/white?text=<?php echo htmlspecialchars($userAvatarText); ?>" alt="Avatar" loading="lazy">
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                <div class="role">ASAPHIL - TIP Manila</div>
            </div>
        </div>
    </aside>

    <!-- ════════════════ MAIN CONTENT ════════════════ -->
    <main class="content-area">

        <!-- Header Banner -->
        <header class="content-header">
            <div class="header-left">
                <h1>Settings</h1>
                <p>Manage your account and organization preferences</p>
            </div>
            <div class="header-right">
                <div class="time-display">
                    <i class="fas fa-clock"></i>
                    <span class="time-text" id="liveTime"></span>
                </div>
            </div>
        </header>

        <!-- ════════ SETTINGS WRAP ════════ -->
        <div class="st-wrap">

            <!-- Left nav -->
            <nav class="st-nav" aria-label="Settings navigation">
                <div class="st-nav-header">Settings</div>
                <button class="st-nav-btn active" data-tab="account">
                    <i class="fas fa-user-circle"></i> Account
                </button>
                <button class="st-nav-btn" data-tab="notifications">
                    <i class="fas fa-bell"></i> Notifications
                </button>
                <button class="st-nav-btn" data-tab="privacy">
                    <i class="fas fa-shield-alt"></i> Privacy &amp; Security
                </button>
            </nav>

            <!-- Right panel -->
            <div class="st-panel">

                <!-- Dynamic header -->
                <div class="st-panel-header">
                    <div class="st-panel-icon"><i class="fas fa-user-circle" id="st-header-icon"></i></div>
                    <div class="st-panel-text">
                        <h3 id="st-header-title">Account Settings</h3>
                        <p id="st-header-sub">Manage your profile, email and password</p>
                    </div>
                </div>

                <div class="st-panel-body">

                    <!-- ── ACCOUNT TAB ── -->
                    <div class="st-tab active" id="account-tab">

                        <div class="avatar-row">
                            <div class="avatar-circle" id="avatarCircle"><?php echo htmlspecialchars($userAvatarText); ?></div>
                            <div class="avatar-info">
                                <h3><?php echo htmlspecialchars($userName); ?></h3>
                                <p><?php echo htmlspecialchars($userAffiliationFull); ?></p>
                                <div class="avatar-actions">
                                    <label class="btn-upload">
                                        <i class="fas fa-upload"></i> Upload New
                                        <input type="file" id="avatarUpload" accept="image/*" style="display:none;">
                                    </label>
                                    <button type="button" class="btn-ghost" id="deleteAvatarBtn">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div class="info-box">
                                <div class="info-box-label">Account Role</div>
                                <div class="info-box-val"><?php echo htmlspecialchars($userRole); ?></div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-label">Organization</div>
                                <div class="info-box-val">ASAPHIL</div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-label">Chapter</div>
                                <div class="info-box-val">TIP Manila</div>
                            </div>
                            <div class="info-box">
                                <div class="info-box-label">Institution</div>
                                <div class="info-box-val">TIP Manila</div>
                            </div>
                        </div>

                        <hr class="form-divider">
                        <div class="form-section-title">Edit Profile Details</div>

                        <div class="form-grid">
                            <!-- Name -->
                            <div class="form-field">
                                <label class="field-label"><i class="fas fa-user"></i> Organization Representative Name</label>
                                <div class="field-row">
                                    <input type="text" class="form-input" id="editableName" value="<?php echo htmlspecialchars($userName); ?>">
                                    <button class="btn-save" onclick="updateField('name')">Save</button>
                                </div>
                            </div>
                            <!-- Email -->
                            <div class="form-field">
                                <label class="field-label"><i class="fas fa-envelope"></i> Organization Email Address</label>
                                <div class="field-row">
                                    <input type="email" class="form-input" id="editableEmail" value="<?php echo htmlspecialchars($userEmail); ?>">
                                    <button class="btn-save" onclick="updateField('email')">Save</button>
                                </div>
                            </div>
                            <!-- Password -->
                            <div class="form-field">
                                <label class="field-label"><i class="fas fa-lock"></i> Password</label>
                                <div class="field-row">
                                    <input type="password" class="form-input" value="••••••••" disabled>
                                    <button class="btn-change-pw" onclick="openChangePasswordModal()">Change Password</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button class="btn-primary-lg" onclick="showToast('All changes saved!','success')">
                                <i class="fas fa-check-circle"></i> Save All Changes
                            </button>
                        </div>
                    </div>

                    <!-- ── NOTIFICATIONS TAB ── -->
                    <div class="st-tab" id="notifications-tab">
                        <div class="toggle-item">
                            <div class="toggle-info">
                                <h4>Email Notifications</h4>
                                <p>Receive email updates about event submissions and approvals.</p>
                            </div>
                            <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                        </div>
                        <div class="toggle-item">
                            <div class="toggle-info">
                                <h4>Event Reminders</h4>
                                <p>Get reminders for upcoming events your organization created.</p>
                            </div>
                            <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                        </div>
                        <div class="toggle-item">
                            <div class="toggle-info">
                                <h4>Organization Announcements</h4>
                                <p>Receive system-wide updates from ArkiConnect Admins.</p>
                            </div>
                            <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                        </div>
                        <div class="form-actions">
                            <button class="btn-primary-lg" onclick="showToast('Notification preferences saved!','success')">
                                <i class="fas fa-check"></i> Save Preferences
                            </button>
                        </div>
                    </div>

                    <!-- ── PRIVACY TAB ── -->
                    <div class="st-tab" id="privacy-tab">
                        <div class="toggle-item">
                            <div class="toggle-info">
                                <h4>Organization Visibility</h4>
                                <p>Control whether your organization appears in public directories.</p>
                            </div>
                            <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                        </div>
                        <div class="toggle-item">
                            <div class="toggle-info">
                                <h4>Admin Approval Required</h4>
                                <p>Require admin review before publishing new events or updates.</p>
                            </div>
                            <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                        </div>
                        <div class="toggle-item">
                            <div class="toggle-info">
                                <h4>Two-Factor Authentication</h4>
                                <p>Add an extra layer of security to your account.</p>
                            </div>
                            <button class="btn-change-pw" onclick="showToast('2FA setup requires backend configuration.','info')">Enable 2FA</button>
                        </div>
                        <div class="form-actions">
                            <button class="btn-primary-lg" onclick="showToast('Privacy settings saved!','success')">
                                <i class="fas fa-check"></i> Save Privacy Settings
                            </button>
                        </div>
                    </div>

                </div><!-- /.st-panel-body -->
            </div><!-- /.st-panel -->
        </div><!-- /.st-wrap -->

    </main><!-- /.content-area -->
</div><!-- /.dashboard-container -->

<!-- ════════ PASSWORD MODAL ════════ -->
<div id="passwordModal" role="dialog" aria-modal="true">
    <div class="modal-inner">
        <div class="modal-head">
            <div class="modal-head-icon"><i class="fas fa-lock"></i></div>
            <div>
                <h3>Change Password</h3>
                <p>Update password for enhanced account security</p>
            </div>
            <button class="modal-close-btn" onclick="closeChangePasswordModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="changePasswordForm" novalidate>
                <div class="modal-field">
                    <label>Current Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="oldPassword" class="form-input" autocomplete="current-password">
                        <button type="button" class="pw-toggle" onclick="togglePassword('oldPassword',this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="modal-field">
                    <label>New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="newPassword" class="form-input" autocomplete="new-password">
                        <button type="button" class="pw-toggle" onclick="togglePassword('newPassword',this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="modal-field">
                    <label>Confirm New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirmNewPassword" class="form-input" autocomplete="new-password">
                        <button type="button" class="pw-toggle" onclick="togglePassword('confirmNewPassword',this)"><i class="fas fa-eye"></i></button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <ul class="pw-checklist">
                        <li id="checkUpper" class="invalid"><i class="fas fa-times-circle"></i> At least 1 uppercase</li>
                        <li id="checkNumber" class="invalid"><i class="fas fa-times-circle"></i> At least 1 number</li>
                        <li id="checkLength" class="invalid"><i class="fas fa-times-circle"></i> At least 8 characters</li>
                    </ul>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn-modal-cancel" onclick="closeChangePasswordModal()">Cancel</button>
            <button class="btn-modal-apply" id="applyPasswordBtn" disabled>Apply Changes</button>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

// ── Sidebar collapse ──
document.getElementById('collapse-toggle').addEventListener('click', () => {
    document.getElementById('left-sidebar').classList.toggle('collapsed');
});

// ── Live clock ──
function updateTime() {
    const now = new Date();
    let h = now.getHours();
    const m = String(now.getMinutes()).padStart(2,'0');
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    document.getElementById('liveTime').textContent = `${String(h).padStart(2,'0')}:${m} ${ampm}`;
}
updateTime();
setInterval(updateTime, 60000);

// ── Toast ──
window.showToast = function(msg, type) {
    let el = document.getElementById('st-toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'st-toast';
        el.style.cssText = `position:fixed;bottom:28px;right:28px;padding:12px 22px;border-radius:10px;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.88rem;z-index:99999;box-shadow:0 4px 16px rgba(0,0,0,.15);transition:opacity .3s;opacity:0;color:#fff;`;
        document.body.appendChild(el);
    }
    el.style.background = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#a43825';
    el.textContent = msg;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.opacity = '0'; }, 3000);
};

// ── Tab switching ──
const tabHeaders = {
    account:       ['fa-user-circle',  'Account Settings',          'Manage your profile, email and password'],
    notifications: ['fa-bell',          'Notification Preferences',  'Control how and when you receive alerts'],
    privacy:       ['fa-shield-alt',    'Privacy & Security',        'Control your visibility and security settings'],
};

document.querySelectorAll('.st-nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        document.querySelectorAll('.st-nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.st-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(tab + '-tab').classList.add('active');
        const [icon, title, sub] = tabHeaders[tab];
        document.getElementById('st-header-icon').className = 'fas ' + icon;
        document.getElementById('st-header-title').textContent = title;
        document.getElementById('st-header-sub').textContent = sub;
    });
});

// ── updateField stub ──
window.updateField = function(field) {
    showToast(field.charAt(0).toUpperCase() + field.slice(1) + ' updated!', 'success');
};

// ── Avatar upload ──
document.getElementById('avatarUpload').addEventListener('change', function() {
    const file = this.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => {
        const ac = document.getElementById('avatarCircle');
        ac.style.backgroundImage = `url('${e.target.result}')`;
        ac.style.backgroundSize = 'cover';
        ac.style.backgroundPosition = 'center';
        ac.textContent = '';
    };
    reader.readAsDataURL(file);
});

document.getElementById('deleteAvatarBtn').addEventListener('click', () => {
    const ac = document.getElementById('avatarCircle');
    ac.style.backgroundImage = 'none';
    ac.style.background = 'linear-gradient(135deg,#a43825 0%,#7c2a1b 100%)';
    ac.textContent = '<?php echo $userAvatarText; ?>';
});

// ── Password modal ──
window.openChangePasswordModal = function() {
    document.getElementById('passwordModal').style.display = 'flex';
};
window.closeChangePasswordModal = function() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('changePasswordForm').reset();
    document.getElementById('strengthFill').style.width = '0';
    document.getElementById('applyPasswordBtn').disabled = true;
    ['checkUpper','checkNumber','checkLength'].forEach(id => {
        const li = document.getElementById(id);
        li.className = 'invalid';
        li.querySelector('i').className = 'fas fa-times-circle';
    });
};

window.togglePassword = function(id, btn) {
    const inp = document.getElementById(id);
    const ico = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'fas fa-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'fas fa-eye'; }
};

// Strength checker
const newPwInput  = document.getElementById('newPassword');
const confPwInput = document.getElementById('confirmNewPassword');
const applyBtn    = document.getElementById('applyPasswordBtn');
const fillEl      = document.getElementById('strengthFill');

function evalPassword() {
    const pw = newPwInput.value;
    const hasUpper  = /[A-Z]/.test(pw);
    const hasNumber = /[0-9]/.test(pw);
    const hasLength = pw.length >= 8;
    let score = 0;
    if (hasUpper)  score += 33;
    if (hasNumber) score += 33;
    if (hasLength) score += 34;
    fillEl.style.width = score + '%';
    setCheck('checkUpper',  hasUpper);
    setCheck('checkNumber', hasNumber);
    setCheck('checkLength', hasLength);
    applyBtn.disabled = !(hasUpper && hasNumber && hasLength && pw === confPwInput.value);
}

function setCheck(id, valid) {
    const li = document.getElementById(id);
    li.className = valid ? 'valid' : 'invalid';
    li.querySelector('i').className = valid ? 'fas fa-check-circle' : 'fas fa-times-circle';
}

newPwInput.addEventListener('input', evalPassword);
confPwInput.addEventListener('input', evalPassword);

applyBtn.addEventListener('click', () => {
    closeChangePasswordModal();
    showToast('Password updated successfully!', 'success');
});

// Close modal on backdrop click
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) closeChangePasswordModal();
});

// Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.getElementById('passwordModal').style.display === 'flex')
        closeChangePasswordModal();
});
</script>

</body>
</html>