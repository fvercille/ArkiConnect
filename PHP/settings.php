<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db_connect.php';


$login_page_path = '/PHP/login.php';

if (isset($_GET['logout'])) {
    session_regenerate_id(true);
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path);
    exit();
}

$allowed_roles = ['student', 'org_rep', 'admin'];
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
$userName = $_SESSION['fullname'] ?? 'Architecture Student';
$userAffiliation = 'TIP Manila';

if (empty($user_id) || empty($role) || !in_array($role, $allowed_roles)) {
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path);
    exit();
}

$role = (string)$role;

switch ($role) {
    case 'student':  $homeLink = 'Student_db.php';  $pageTitle = 'Settings'; break;
    case 'org_rep':  $homeLink = 'OrgRep_db.php';   $pageTitle = 'Settings'; break;
    case 'admin':    $homeLink = 'Admin_db.php';     $pageTitle = 'Settings'; break;
    default:         $homeLink = '#';                $pageTitle = 'Settings';
}

$userAvatarText      = strtoupper(substr($userName, 0, 1));
$userEmail           = $_SESSION['email'] ?? 'student@tip.edu.ph';
$userRole            = $_SESSION['role'] ?? 'Student';
$userAffiliationFull = 'Technological Institute of the Philippines - Manila';
$userYear            = '2nd Year';
$userCourse          = 'BS Architecture';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/Student_db.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

        :root {
            --primary:       #a43825;
            --primary-dark:  #7c2a1b;
            --primary-light: #c9503a;
            --card-bg:       #ffffff;
            --text-dark:     #1e1210;
            --text-mid:      #4a2e27;
            --text-muted:    #9e7f78;
            --border:        #ecddd8;
            --shadow-sm:     0 1px 4px rgba(164,56,37,0.07);
            --shadow-md:     0 4px 16px rgba(164,56,37,0.11);
            --radius:        14px;
        }

        /* ── Settings wrapper ── */
        .st-wrap {
            font-family: 'DM Sans', sans-serif;
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 28px;
            padding: 0 4px 40px;
            animation: stFade .4s ease both;
        }
        @keyframes stFade { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

        /* ── Left nav ── */
        .st-nav {
            display: flex;
            flex-direction: column;
            gap: 0;
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
            font-family: inherit;
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
        .st-nav-btn.active {
            background: #fdf0ec;
            color: var(--primary);
            font-weight: 600;
        }
        .st-nav-btn.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: var(--primary);
            border-radius: 0 2px 2px 0;
        }
        .st-nav-btn i { width: 16px; text-align: center; font-size: .85rem; }

        /* ── Right panel ── */
        .st-panel { display: flex; flex-direction: column; gap: 0; }

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
        .st-panel-text h3 {
            font-size: 1.2rem; font-weight: 700;
            color: #fff; margin-bottom: 2px;
        }
        .st-panel-text p { font-size: .78rem; color: rgba(255,255,255,.75); }

        .st-panel-body {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 var(--radius) var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        /* Tab content */
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
        }
        .avatar-info { flex: 1; }
        .avatar-info h3 {
            font-size: 1.15rem; font-weight: 700;
            color: var(--primary-dark); margin-bottom: 2px;
        }
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
        .info-box-label {
            font-size: .7rem; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase;
            color: var(--text-muted); margin-bottom: 4px;
        }
        .info-box-val {
            font-size: .92rem; font-weight: 700;
            color: var(--primary);
        }

        /* Form section */
        .form-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 4px 0 20px;
        }
        .form-section-title {
            font-size: .72rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--primary); margin-bottom: 16px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .form-field { display: flex; flex-direction: column; gap: 6px; }
        .field-label {
            font-size: .82rem; font-weight: 600;
            color: var(--text-mid);
            display: flex; align-items: center; gap: 6px;
        }
        .field-label i { color: var(--primary); font-size: .78rem; }
        .field-row { display: flex; gap: 8px; }
        .form-input {
            flex: 1;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-family: inherit;
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
            border-radius: 8px; font-family: inherit;
            font-size: .82rem; font-weight: 700;
            cursor: pointer; white-space: nowrap;
            transition: background .15s, transform .12s;
        }
        .btn-save:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .btn-upload {
            padding: 8px 14px;
            background: #1f318e;
            color: #fff; border: none;
            border-radius: 8px; font-family: inherit;
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
            border-radius: 8px; font-family: inherit;
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
            border-radius: 8px; font-family: inherit;
            font-size: .82rem; font-weight: 700;
            cursor: pointer; white-space: nowrap;
            transition: background .15s, color .15s;
        }
        .btn-change-pw:hover { background: var(--primary); color: #fff; }

        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .btn-primary-lg {
            padding: 11px 24px;
            background: var(--primary);
            color: #fff; border: none;
            border-radius: 9px; font-family: inherit;
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
        .toggle-info h4 {
            font-size: .9rem; font-weight: 600;
            color: var(--text-dark); margin-bottom: 3px;
        }
        .toggle-info p { font-size: .8rem; color: var(--text-muted); }

        .switch {
            position: relative;
            display: inline-block;
            width: 46px; height: 24px;
            flex-shrink: 0;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #d1d5db;
            border-radius: 24px;
            transition: background .2s;
        }
        .switch-slider::before {
            content: '';
            position: absolute;
            height: 18px; width: 18px;
            left: 3px; bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: transform .2s;
        }
        .switch input:checked + .switch-slider { background: var(--primary); }
        .switch input:checked + .switch-slider::before { transform: translateX(22px); }

        /* Modal */
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
        .modal-head h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem; font-weight: 700; color: #fff; margin: 0;
        }
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
        .modal-field label {
            display: block;
            font-size: .82rem; font-weight: 600;
            color: var(--text-mid); margin-bottom: 6px;
        }
        .pw-wrap { display: flex; align-items: center; position: relative; }
        .pw-wrap .form-input { padding-right: 40px; }
        .pw-toggle {
            position: absolute; right: 10px;
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            font-size: .85rem;
        }
        .strength-bar {
            height: 5px; background: var(--border);
            border-radius: 4px; overflow: hidden; margin-top: 8px;
        }
        .strength-fill {
            height: 100%; width: 0;
            background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981);
            transition: width .2s;
        }
        .pw-checklist { margin-top: 10px; padding: 0; list-style: none; }
        .pw-checklist li {
            font-size: .78rem; color: var(--text-muted);
            display: flex; align-items: center; gap: 7px; margin-bottom: 4px;
        }
        .pw-checklist li.valid { color: #10b981; }
        .pw-checklist li.invalid { color: #ef4444; }
        .modal-foot {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex; justify-content: flex-end; gap: 10px;
        }
        .btn-modal-cancel {
            padding: 9px 18px;
            background: #fff; color: var(--text-mid);
            border: 1px solid var(--border);
            border-radius: 8px; font-family: inherit;
            font-size: .85rem; font-weight: 600; cursor: pointer;
        }
        .btn-modal-apply {
            padding: 9px 18px;
            background: var(--primary); color: #fff;
            border: none; border-radius: 8px;
            font-family: inherit; font-size: .85rem;
            font-weight: 700; cursor: pointer;
            transition: background .15s;
        }
        .btn-modal-apply:hover { background: var(--primary-dark); }
        .btn-modal-apply:disabled { opacity: .5; cursor: not-allowed; }

        /* Responsive */
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
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar" id="left-sidebar">
        <div class="sidebar-top-icons">
            <button class="menu-toggle-desktop" id="collapse-toggle" title="Collapse sidebar">
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
            <a href="<?php echo htmlspecialchars($homeLink); ?>" class="nav-link" title="Go to Dashboard">
                <i data-lucide="layout-dashboard"></i>
                <span class="link-text">Dashboard</span>
            </a>
            <hr class="separator">
            <a href="organizations.php" class="nav-link sub-link-item" title="Organization Directory">
                <i data-lucide="users"></i>
                <span class="link-text">Organizations</span>
            </a>
            <a href="registered_events.php" class="nav-link sub-link-item" title="My Saved Events">
                <i data-lucide="bookmark"></i>
                <span class="link-text">Registered Events</span>
            </a>
            <a href="event_calendar.php" class="nav-link sub-link-item" title="Event Calendar">
                <i data-lucide="calendar-days"></i>
                <span class="link-text">Event Calendar</span>
            </a>
            <hr class="separator">
            <a href="helpcenter.php" class="nav-link sub-link-item" title="Help Center">
                <i data-lucide="life-buoy"></i>
                <span class="link-text">Help Center</span>
            </a>
            <a href="settings.php" class="nav-link sub-link-item active" title="Settings">
                <i data-lucide="settings"></i>
                <span class="link-text">Settings</span>
            </a>
            <a href="login.php?logout=true" class="nav-link" title="Logout">
                <i data-lucide="log-out"></i>
                <span class="link-text">Logout</span>
            </a>
        </div>

        <div class="user-profile">
            <img src="https://placehold.co/40x40/A43825/white?text=<?php echo htmlspecialchars($userAvatarText); ?>" alt="User Avatar" loading="lazy">
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                <div class="role"><?php echo htmlspecialchars($userAffiliation); ?></div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content-area" id="content-area">

        <header class="content-header">
            <button id="menu-toggle-left" class="menu-toggle" title="Open Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
        </header>

        <section class="main-content-layout">
            <div class="st-wrap">

                <!-- ════════ LEFT NAV ════════ -->
                <nav class="st-nav" aria-label="Settings navigation">
                    <div class="st-nav-header">Settings</div>
                    <button class="st-nav-btn active" data-tab="account">
                        <i class="fas fa-user-circle"></i> Account
                    </button>
                    <button class="st-nav-btn" data-tab="notifications">
                        <i class="fas fa-bell"></i> Notifications
                    </button>
                    <button class="st-nav-btn" data-tab="privacy">
                        <i class="fas fa-shield-alt"></i> Privacy & Security
                    </button>
                </nav>

                <!-- ════════ RIGHT PANEL ════════ -->
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

                            <!-- Avatar row -->
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

                            <!-- Info grid -->
                            <div class="info-grid">
                                <div class="info-box">
                                    <div class="info-box-label">Account Role</div>
                                    <div class="info-box-val"><?php echo htmlspecialchars($userRole); ?></div>
                                </div>
                                <div class="info-box">
                                    <div class="info-box-label">Program</div>
                                    <div class="info-box-val"><?php echo htmlspecialchars($userCourse); ?></div>
                                </div>
                                <div class="info-box">
                                    <div class="info-box-label">Year Level</div>
                                    <div class="info-box-val"><?php echo htmlspecialchars($userYear); ?></div>
                                </div>
                                <div class="info-box">
                                    <div class="info-box-label">Institution</div>
                                    <div class="info-box-val">TIP Manila</div>
                                </div>
                            </div>

                            <hr class="form-divider">
                            <div class="form-section-title">Edit Profile Details</div>

                            <div class="form-grid">
                                <!-- Full name -->
                                <div class="form-field">
                                    <label class="field-label"><i class="fas fa-user"></i> Full Name</label>
                                    <div class="field-row">
                                        <input type="text" class="form-input" id="editableName" value="<?php echo htmlspecialchars($userName); ?>">
                                        <button class="btn-save" onclick="updateField('name')">Save</button>
                                    </div>
                                </div>
                                <!-- Email -->
                                <div class="form-field">
                                    <label class="field-label"><i class="fas fa-envelope"></i> Email Address</label>
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
                                    <p>Receive email updates about your activity.</p>
                                </div>
                                <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                            </div>
                            <div class="toggle-item">
                                <div class="toggle-info">
                                    <h4>Event Reminders</h4>
                                    <p>Get reminded about upcoming events you registered for.</p>
                                </div>
                                <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                            </div>
                            <div class="toggle-item">
                                <div class="toggle-info">
                                    <h4>Organization Announcements</h4>
                                    <p>Receive updates from organizations you follow.</p>
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
                                    <h4>Show Profile on Event Registration</h4>
                                    <p>Allow event organizers to view your name, course, and year level.</p>
                                </div>
                                <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                            </div>
                            <div class="toggle-item">
                                <div class="toggle-info">
                                    <h4>Receive Event Invitations</h4>
                                    <p>Allow Organization Representatives to send you event invitations.</p>
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
        </section>

    </main>

    <!-- PASSWORD MODAL -->
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

    <div class="overlay" id="mobile-overlay"></div>

</div><!-- /.dashboard-container -->

<!-- FIX: dummy variables so Student_ds.js doesn't crash -->
<script>
    const currentMonth = new Date().getMonth() + 1;
    const currentYear  = new Date().getFullYear();
    const eventDates   = {};

    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('right-sidebar')) {
            const dummy = document.createElement('div');
            dummy.id = 'right-sidebar';
            dummy.style.display = 'none';
            document.body.appendChild(dummy);
        }
    });
</script>

<script src="../JavaScript/Student_ds.js"></script>

<script>
lucide.createIcons();

// ── Tab switching ──
document.querySelectorAll('.st-nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;

        document.querySelectorAll('.st-nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.st-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(tab + '-tab').classList.add('active');

        const headers = {
            account:       ['fa-user-circle',  'Account Settings',        'Manage your profile, email and password'],
            notifications: ['fa-bell',          'Notification Preferences', 'Control how and when you receive alerts'],
            privacy:       ['fa-shield-alt',    'Privacy & Security',       'Control your visibility and security settings'],
        };
        const [icon, title, sub] = headers[tab];
        document.getElementById('st-header-icon').className = 'fas ' + icon;
        document.getElementById('st-header-title').textContent = title;
        document.getElementById('st-header-sub').textContent = sub;
    });
});

// ── Toast ──
window.showToast = function(msg, type) {
    let el = document.getElementById('st-toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'st-toast';
        el.style.cssText = `position:fixed;bottom:28px;right:28px;background:#a43825;color:#fff;padding:12px 22px;border-radius:10px;font-weight:600;font-size:.88rem;z-index:99999;box-shadow:0 4px 16px rgba(164,56,37,.25);transition:opacity .3s;opacity:0;`;
        document.body.appendChild(el);
    }
    if (type === 'success') el.style.background = '#10b981';
    else if (type === 'error') el.style.background = '#ef4444';
    else el.style.background = '#a43825';
    el.textContent = msg;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.opacity = '0'; }, 3000);
};

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

// Strength logic
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

// Close modal on overlay click
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) closeChangePasswordModal();
});
</script>
</body>
</html>