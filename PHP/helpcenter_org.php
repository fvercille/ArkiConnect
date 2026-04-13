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

if (empty($user_id) || empty($role) || !in_array($role, $allowed_roles)) {
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path);
    exit();
}

$role = (string)$role;
$userAvatarText = strtoupper(substr($userName, 0, 1));
$pageTitle = 'Help Center';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary:   #a43825;
            --primary-dk:#7a2618;
            --bg:        #f4f5f7;
            --text:      #333333;
            --muted:     #9ca3af;
            --border:    #e6e9ef;
            --shadow:    0 2px 12px rgba(0,0,0,0.06);
            --sidebar-w: 280px;
            --sidebar-collapsed: 70px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ══════════════════════════════
           SIDEBAR
        ══════════════════════════════ */
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
            box-shadow: 0 8px 32px rgba(0,0,0,0.10);
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
            letter-spacing: 0.01em;
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
            width: calc(100% - 8px);
            overflow: hidden;
            flex-shrink: 0;
            transition: all 0.14s ease;
        }

        .user-profile:hover { background: rgba(164,56,37,0.08); transform: translateY(-1px); }
        .user-profile img { width: 36px; height: 36px; min-width: 36px; border-radius: 8px; object-fit: cover; display: block; flex-shrink: 0; }
        .user-info .name { font-size: 13.5px; font-weight: 600; color: #1a1a2e; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; }
        .user-info .role { font-size: 11.5px; color: #888; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; }

        /* Collapsed sidebar */
        .sidebar.collapsed { width: var(--sidebar-collapsed); overflow: hidden; padding: 16px 8px; }
        .sidebar.collapsed .link-text,
        .sidebar.collapsed .user-info,
        .sidebar.collapsed .header-container { display: none; }
        .sidebar.collapsed .sidebar-top-icons { justify-content: center; flex-direction: column-reverse; align-items: center; gap: 8px; }
        .sidebar.collapsed .user-profile { justify-content: center; padding: 10px 0; margin: 0 2px; }
        .sidebar.collapsed .user-profile img { margin: 0; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 10px 0; margin: 2px 0; }
        .sidebar.collapsed .nav-link i,
        .sidebar.collapsed .nav-link svg { margin: 0; width: auto; }
        .sidebar.collapsed .separator { margin: 8px 2px; }
        .sidebar.collapsed .nav-section { padding: 0 2px; }

        /* ══════════════════════════════
           MAIN CONTENT
        ══════════════════════════════ */
        .content-area {
            flex: 1;
            padding: 24px 24px 24px 20px;
            min-height: 100vh;
            overflow-y: auto;
            background: var(--bg);
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        /* Header banner */
        .content-header {
            background: linear-gradient(120deg, var(--primary) 0%, var(--primary-dk) 100%);
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
        .header-left h1 { font-size: 1.9rem; font-weight: 700; color: #fff; letter-spacing: -0.03em; line-height: 1.1; margin: 0; }
        .header-left p { font-size: 0.82rem; color: rgba(255,255,255,0.65); font-weight: 500; margin: 0; }

        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 1;
        }

        .time-display {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 18px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            backdrop-filter: blur(8px);
        }

        .time-display i { color: rgba(255,255,255,0.8); font-size: 1rem; }
        .time-text { font-size: 1rem; font-weight: 700; color: #fff; letter-spacing: 0.02em; }

        .contact-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            font-family: 'Montserrat', sans-serif;
            transition: background 0.2s;
        }

        .contact-btn:hover { background: rgba(255,255,255,0.25); }

        /* ══════════════════════════════
           HELP CENTER — NEW DESIGN
        ══════════════════════════════ */
        .hc-wrap {
            font-family: 'Montserrat', sans-serif;
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 28px;
            padding: 0 4px 40px;
            animation: hcFade .4s ease both;
        }
        @keyframes hcFade { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

        .hc-sidebar { display: flex; flex-direction: column; gap: 16px; }

        .hc-search-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 20px;
        }
        .hc-search-card label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .hc-search-wrap {
            display: flex;
            align-items: center;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            overflow: hidden;
            transition: border-color .2s;
        }
        .hc-search-wrap:focus-within { border-color: var(--primary); }
        .hc-search-wrap i { padding: 0 12px; color: var(--muted); font-size: .85rem; }
        .hc-search-input {
            flex: 1; border: none; outline: none;
            padding: 10px 0; font-family: 'Montserrat', sans-serif;
            font-size: .88rem; color: var(--text); background: transparent;
        }
        .hc-search-input::placeholder { color: var(--muted); }

        .hc-cat-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .hc-cat-header {
            padding: 16px 20px 12px;
            border-bottom: 1px solid var(--border);
            font-size: .72rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--primary);
        }
        .hc-cat-list { padding: 8px 0; }
        .hc-cat-btn {
            width: 100%; display: flex; align-items: center; gap: 12px;
            padding: 11px 20px; border: none; background: transparent;
            cursor: pointer; font-family: 'Montserrat', sans-serif; font-size: .88rem;
            font-weight: 500; color: #4a2e27; text-align: left;
            transition: background .15s, color .15s; position: relative;
        }
        .hc-cat-btn:hover { background: #fdf7f5; color: var(--primary); }
        .hc-cat-btn.active { background: #fdf0ec; color: var(--primary); font-weight: 600; }
        .hc-cat-btn.active::before {
            content: ''; position: absolute;
            left: 0; top: 0; bottom: 0; width: 3px;
            background: var(--primary); border-radius: 0 2px 2px 0;
        }
        .hc-cat-btn i { width: 16px; text-align: center; font-size: .85rem; }
        .hc-cat-count {
            margin-left: auto; background: var(--border); color: var(--muted);
            font-size: .68rem; font-weight: 700; padding: 2px 8px;
            border-radius: 20px; transition: background .15s, color .15s;
        }
        .hc-cat-btn.active .hc-cat-count,
        .hc-cat-btn:hover .hc-cat-count { background: var(--primary); color: #fff; }

        .hc-contact-card {
            background: linear-gradient(135deg, var(--primary-dk) 0%, var(--primary) 100%);
            border-radius: 14px; padding: 22px 20px;
            color: #fff; box-shadow: 0 4px 16px rgba(164,56,37,0.22);
        }
        .hc-contact-card h4 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
        .hc-contact-card p { font-size: .78rem; color: rgba(255,255,255,.8); margin-bottom: 16px; line-height: 1.5; }
        .hc-contact-link {
            display: block; text-align: center;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.35);
            color: #fff; text-decoration: none;
            padding: 10px 16px; border-radius: 8px;
            font-size: .82rem; font-weight: 600;
            transition: background .2s;
        }
        .hc-contact-link:hover { background: rgba(255,255,255,.28); }

        /* FAQ Panel */
        .hc-faq-panel { display: flex; flex-direction: column; gap: 0; }

        .hc-faq-header {
            background: linear-gradient(135deg, var(--primary-dk) 0%, var(--primary) 55%, #c9503a 100%);
            border-radius: 14px 14px 0 0;
            padding: 24px 28px; display: flex; align-items: center;
            gap: 14px; position: relative; overflow: hidden;
        }
        .hc-faq-header::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hc-faq-header-icon {
            width: 42px; height: 42px; background: rgba(255,255,255,.18);
            border-radius: 10px; display: grid; place-items: center;
            color: #fff; font-size: 1.1rem; flex-shrink: 0; position: relative;
        }
        .hc-faq-header-text { position: relative; }
        .hc-faq-header-text h3 { font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 2px; }
        .hc-faq-header-text p { font-size: .78rem; color: rgba(255,255,255,.75); }
        .hc-faq-result-count {
            margin-left: auto;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.28);
            color: #fff; font-size: .72rem; font-weight: 700;
            padding: 5px 12px; border-radius: 20px; position: relative;
        }

        .hc-faq-list {
            background: #fff; border: 1px solid var(--border);
            border-top: none; border-radius: 0 0 14px 14px;
            box-shadow: 0 4px 16px rgba(164,56,37,0.11); overflow: hidden;
        }

        .hc-empty { display: none; padding: 48px 30px; text-align: center; color: var(--muted); }
        .hc-empty i { font-size: 2rem; margin-bottom: 12px; color: var(--border); display: block; }
        .hc-empty p { font-size: .9rem; }

        .hc-faq-item { border-bottom: 1px solid var(--border); }
        .hc-faq-item:last-child { border-bottom: none; }

        .hc-faq-q {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 24px; cursor: pointer;
            transition: background .15s; user-select: none;
        }
        .hc-faq-q:hover { background: #fdf7f5; }

        .hc-faq-q-icon {
            width: 30px; height: 30px; background: #fdf0ec;
            border-radius: 7px; display: grid; place-items: center;
            color: var(--primary); font-size: .75rem;
            flex-shrink: 0; transition: background .15s;
        }
        .hc-faq-item.active .hc-faq-q-icon { background: var(--primary); color: #fff; }

        .hc-faq-q-text {
            flex: 1; font-size: .92rem; font-weight: 600;
            color: #1e1210; line-height: 1.4;
        }
        .hc-faq-q-chevron {
            color: var(--muted); font-size: .8rem;
            transition: transform .25s; flex-shrink: 0;
        }
        .hc-faq-item.active .hc-faq-q-chevron { transform: rotate(180deg); }

        .hc-faq-a {
            display: none; padding: 0 24px 18px 68px;
            font-size: .88rem; color: #4a2e27; line-height: 1.8;
        }
        .hc-faq-item.active .hc-faq-a { display: block; }
        .hc-faq-a a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .hc-faq-a a:hover { text-decoration: underline; }

        .hc-faq-tag {
            font-size: .65rem; font-weight: 700; letter-spacing: .06em;
            text-transform: uppercase; padding: 2px 8px;
            border-radius: 20px; flex-shrink: 0;
        }
        .hc-faq-tag.events  { background: #fdecea; color: var(--primary); }
        .hc-faq-tag.profile { background: #fef9e7; color: #b7860b; }
        .hc-faq-tag.support { background: #e8f4fd; color: #1a6fa8; }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed; left: 0; top: 0;
                height: 100%;
                border-radius: 0;
                margin: 0;
            }
            .sidebar.active { transform: translateX(0); }
            .content-area { padding: 16px; }
            .hc-wrap { grid-template-columns: 1fr; }
            .hc-sidebar { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            .hc-contact-card { grid-column: 1/-1; }
            .hc-faq-header { border-radius: 14px; }
            .hc-faq-list { border-radius: 14px; border-top: 1px solid var(--border); margin-top: 8px; }
        }

        @media (max-width: 600px) {
            .hc-sidebar { grid-template-columns: 1fr; }
            .hc-faq-q { padding: 14px 16px; }
            .hc-faq-a { padding: 0 16px 14px 54px; }
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="left-sidebar">

        <div class="sidebar-top-icons">
            <button id="collapse-toggle" title="Collapse sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-container">
                <div class="logo">
                    <a href="index.php">
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
            <a href="helpcenter_org.php" class="nav-link active" title="Help Center">
                <i data-lucide="life-buoy"></i>
                <span class="link-text">Help Center</span>
            </a>
            <hr class="separator">
            <a href="settings_org.php" class="nav-link" title="Settings">
                <i data-lucide="settings"></i>
                <span class="link-text">Settings</span>
            </a>
            <a href="login.php?logout=true" class="nav-link" title="Logout">
                <i data-lucide="log-out"></i>
                <span class="link-text">Logout</span>
            </a>
        </div>

        <div class="user-profile">
            <img src="https://placehold.co/40x40/A43825/white?text=<?= htmlspecialchars($userAvatarText) ?>" alt="Avatar" loading="lazy">
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($userName) ?></div>
                <div class="role">ASAPHIL - TIP Manila</div>
            </div>
        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="content-area">

        <!-- Header Banner -->
        <header class="content-header">
            <div class="header-left">
                <h1>Help Center</h1>
                <p>Find answers to frequently asked questions</p>
            </div>
            <div class="header-right">
 
                <div class="time-display">
                    <i class="fas fa-clock"></i>
                    <span class="time-text"></span>
                </div>
            </div>
        </header>
        

        <!-- ══════════════════════════════
             NEW TWO-COLUMN HELP CENTER
        ══════════════════════════════ -->
        <div class="hc-wrap">

            <!-- LEFT SIDEBAR -->
            <aside class="hc-sidebar">

                <div class="hc-search-card">
                    <label>Search Articles</label>
                    <div class="hc-search-wrap">
                        <i class="fas fa-search"></i>
                        <input class="hc-search-input" id="hc-search" type="text" placeholder="Search questions...">
                    </div>
                </div>

                <div class="hc-cat-card">
                    <div class="hc-cat-header">Categories</div>
                    <div class="hc-cat-list">
                        <button class="hc-cat-btn active" data-cat="all" onclick="hcFilter('all', this)">
                            <i class="fas fa-th-large"></i> All Topics
                            <span class="hc-cat-count">10</span>
                        </button>
                        <button class="hc-cat-btn" data-cat="events" onclick="hcFilter('events', this)">
                            <i class="fas fa-calendar-alt"></i> Events &amp; Registration
                            <span class="hc-cat-count">5</span>
                        </button>
                        <button class="hc-cat-btn" data-cat="profile" onclick="hcFilter('profile', this)">
                            <i class="fas fa-user-circle"></i> Profile &amp; Account
                            <span class="hc-cat-count">3</span>
                        </button>
                        <button class="hc-cat-btn" data-cat="support" onclick="hcFilter('support', this)">
                            <i class="fas fa-cog"></i> Technical Support
                            <span class="hc-cat-count">2</span>
                        </button>
                    </div>
                </div>

                <div class="hc-contact-card">
                    <h4>Still need help?</h4>
                    <p>Can't find what you're looking for? Our support team is here for you.</p>
                    <a href="mailto:arkiconnect.support@tip.edu.ph" class="hc-contact-link">
                        <i class="fas fa-envelope" style="margin-right:6px;"></i> Get in Touch
                    </a>
                </div>

            </aside>

            <!-- FAQ PANEL -->
            <div class="hc-faq-panel">

                <div class="hc-faq-header">
                    <div class="hc-faq-header-icon"><i class="fas fa-book-open"></i></div>
                    <div class="hc-faq-header-text">
                        <h3 id="hc-panel-title">Frequently Asked Questions</h3>
                        <p id="hc-panel-sub">Browse all topics or filter by category</p>
                    </div>
                    <div class="hc-faq-result-count" id="hc-result-count">10 articles</div>
                </div>

                <div class="hc-faq-list">
                    <div class="hc-empty" id="hc-empty">
                        <i class="fas fa-search"></i>
                        <p>No articles matched your search. Try different keywords.</p>
                    </div>

                    <!-- EVENTS -->
                    <div class="hc-faq-item" data-cat="events">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">How can I create an event from the website?</span>
                            <span class="hc-faq-tag events">Events</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            On the <strong>Dashboard page</strong>, click the <strong>'Create Event'</strong> button. You'll need to provide the event title, date, time, and upload necessary files including the event poster and authorization paper signed by upper management.
                        </div>
                    </div>

                    <div class="hc-faq-item" data-cat="events">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">What are the requirements needed when creating an event?</span>
                            <span class="hc-faq-tag events">Events</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            You must submit: <strong>Event title, description, date and time, authorization paper signed by upper management, and optionally an event poster.</strong>
                        </div>
                    </div>

                    <div class="hc-faq-item" data-cat="events">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">Can I see the number of participants in my organization's events?</span>
                            <span class="hc-faq-tag events">Events</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            Yes, go to the <strong>'My Events'</strong> section to view participant counts.
                        </div>
                    </div>

                    <div class="hc-faq-item" data-cat="events">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">Are submitted events verified before posting?</span>
                            <span class="hc-faq-tag events">Events</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            Yes, all events go through moderation by the admin team to ensure accuracy and compliance.
                        </div>
                    </div>

                    <div class="hc-faq-item" data-cat="events">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">Where can I see the pending events that I created?</span>
                            <span class="hc-faq-tag events">Events</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            Pending events are listed in the <strong>'My Events'</strong> section of your dashboard.
                        </div>
                    </div>

                    <!-- PROFILE -->
                    <div class="hc-faq-item" data-cat="profile">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">How can I retrieve my account if I forgot my email or password?</span>
                            <span class="hc-faq-tag profile">Profile</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            On the login page, click <strong>'Forgot Password?'</strong> and follow the recovery steps.
                        </div>
                    </div>

                    <div class="hc-faq-item" data-cat="profile">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">Can I edit my profile information?</span>
                            <span class="hc-faq-tag profile">Profile</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            Yes! Go to <strong>Settings</strong> &gt; <strong>Account</strong> to update your name, email, or password.
                        </div>
                    </div>

                    <div class="hc-faq-item" data-cat="profile">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">How do I delete my profile?</span>
                            <span class="hc-faq-tag profile">Profile</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            Email <a href="mailto:arkiconnect.support@tip.edu.ph">arkiconnect.support@tip.edu.ph</a> with your full name and student ID. This action is permanent.
                        </div>
                    </div>

                    <!-- SUPPORT -->
                    <div class="hc-faq-item" data-cat="support">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">Who can I contact for technical support?</span>
                            <span class="hc-faq-tag support">Support</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            Contact the <strong>ArkiConnect Support Team</strong> at <a href="mailto:arkiconnect.support@tip.edu.ph">arkiconnect.support@tip.edu.ph</a> or use the "Get in touch" button above.
                        </div>
                    </div>

                    <div class="hc-faq-item" data-cat="support">
                        <div class="hc-faq-q">
                            <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                            <span class="hc-faq-q-text">What browsers are supported?</span>
                            <span class="hc-faq-tag support">Support</span>
                            <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                        </div>
                        <div class="hc-faq-a">
                            ArkiConnect works best on the latest versions of <strong>Chrome, Firefox, Safari, and Edge</strong>. Please keep your browser updated for the best experience.
                        </div>
                    </div>

                </div><!-- /.hc-faq-list -->
            </div><!-- /.hc-faq-panel -->
        </div><!-- /.hc-wrap -->

    </main>
</div>

<script>
    lucide.createIcons();

    // Sidebar collapse
    document.getElementById('collapse-toggle').addEventListener('click', function () {
        document.getElementById('left-sidebar').classList.toggle('collapsed');
    });

    // Live clock
    function updateTime() {
        const now  = new Date();
        let h      = now.getHours();
        const m    = String(now.getMinutes()).padStart(2, '0');
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        document.querySelector('.time-text').textContent = `${String(h).padStart(2, '0')}:${m} ${ampm}`;
    }
    updateTime();
    setInterval(updateTime, 60000);

    // FAQ logic
    (function () {
        const items   = document.querySelectorAll('.hc-faq-item');
        const search  = document.getElementById('hc-search');
        const empty   = document.getElementById('hc-empty');
        const countEl = document.getElementById('hc-result-count');
        const titleEl = document.getElementById('hc-panel-title');
        const subEl   = document.getElementById('hc-panel-sub');

        let activeCat = 'all';

        items.forEach(item => {
            item.querySelector('.hc-faq-q').addEventListener('click', () => {
                const wasActive = item.classList.contains('active');
                items.forEach(i => i.classList.remove('active'));
                if (!wasActive) item.classList.add('active');
            });
        });

        window.hcFilter = function(cat, btn) {
            activeCat = cat;
            document.querySelectorAll('.hc-cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            search.value = '';
            const labels = {
                all:     ['Frequently Asked Questions',     'Browse all topics or filter by category'],
                events:  ['Events & Registration',           'Creating, submitting, and managing events'],
                profile: ['Profile & Account',               'Manage your personal information'],
                support: ['Technical Support',               'Troubleshooting and browser compatibility'],
            };
            titleEl.textContent = labels[cat][0];
            subEl.textContent   = labels[cat][1];
            applyFilters();
        };

        search.addEventListener('input', applyFilters);

        function applyFilters() {
            const q = search.value.toLowerCase().trim();
            let visible = 0;
            items.forEach(item => {
                const catMatch    = activeCat === 'all' || item.dataset.cat === activeCat;
                const qText       = item.querySelector('.hc-faq-q-text').textContent.toLowerCase();
                const aText       = item.querySelector('.hc-faq-a').textContent.toLowerCase();
                const searchMatch = q === '' || qText.includes(q) || aText.includes(q);
                const show        = catMatch && searchMatch;
                item.style.display = show ? '' : 'none';
                if (show) visible++;
                if (!show) item.classList.remove('active');
            });
            countEl.textContent = visible + (visible === 1 ? ' article' : ' articles');
            empty.style.display = visible === 0 ? 'block' : 'none';
        }
    })();
</script>

</body>
</html>