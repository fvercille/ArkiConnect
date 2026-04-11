<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; 

$login_page_path = 'http://localhost/FinalProject1/PHP/login.php'; 
$pageTitle = 'Admin Dashboard';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if ($role !== 'admin' || empty($user_id)) {
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path); 
    exit();
}

$total_events = 0;
$total_orgs = 0;
$pending_events = 0;

$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'approved'");
if ($result) $total_events = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'org_rep'");
if ($result) $total_orgs = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'pending'");
if ($result) $pending_events = $result->fetch_assoc()['count'];

$recent_events = [];
$sql = "SELECT e.id, e.title, e.event_date, e.status, u.fullname as org_name, e.created_at, e.description
        FROM events e 
        LEFT JOIN users u ON e.created_by = u.id 
        WHERE e.status = 'pending'
        ORDER BY e.created_at DESC 
        LIMIT 10";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) $recent_events[] = $row;
}

$userName = $_SESSION['fullname'] ?? 'Admin User';
$words = explode(' ', trim($userName));
$userInitials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
    :root {
        --brand:        #A43825;
        --brand-hover:  #8a2d1f;
        --brand-dim:    rgba(164,56,37,0.15);

        /* ── LIGHTENED BACKGROUNDS ── */
        --bg:           #1e1e24;   /* was #0f0f11 */
        --surface:      #2a2a32;   /* was #18181b */
        --surface-2:    #32323c;   /* was #202024 */
        --surface-3:    #3c3c48;   /* was #2a2a2f */

        --border:       rgba(255,255,255,0.10);
        --border-soft:  rgba(255,255,255,0.06);

        /* ── BRIGHTER TEXT ── */
        --text:         #f5f5f3;
        --muted:        #b0b0bb;   /* was #88888f — much more readable */

        --success:      #22c55e;
        --success-dim:  rgba(34,197,94,0.15);
        --warn:         #f59e0b;
        --warn-dim:     rgba(245,158,11,0.15);
        --danger:       #ef4444;
        --danger-dim:   rgba(239,68,68,0.15);

        --sidebar-w:    270px;
        --radius:       14px;
        --ease:         0.22s cubic-bezier(0.4,0,0.2,1);
        --font:         'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    body {
        font-family: var(--font);
        background: var(--bg);
        color: var(--text);
        display: flex;
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
    }

    .dashboard-container { display: flex; width: 100%; min-height: 100vh; }

    /* ══ SIDEBAR ══ */
.sidebar {
    width: var(--sidebar-w);
    background: var(--surface);
    display: flex;
    flex-direction: column;
    padding: 16px 10px 16px;
    position: sticky;
    top: 16px;
    height: calc(100vh - 32px);
    overflow: hidden; /* ← changed from overflow-y: auto */
    flex-shrink: 0;
    z-index: 100;
    box-shadow: 0 8px 32px rgba(0,0,0,0.35);
    border-radius: 20px;
    margin: 16px 0 16px 16px;
    border: 1px solid var(--border);
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                padding 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                clip-path: inset(0 round 20px);
}
    .sidebar-top-icons {
        display: flex;
        justify-content: space-between;
        flex-direction: row-reverse;
        align-items: center;
        padding: 4px 6px 12px;
        margin-bottom: 4px;
        border-bottom: 1px solid var(--border);
    }

    .menu-toggle-desktop {
        background: var(--surface-2);
        border: none;
        color: var(--muted);
        cursor: pointer;
        padding: 8px;
        border-radius: 10px;
        width: 34px; height: 34px;
        display: flex; align-items: center; justify-content: center;
        transition: var(--ease);
        flex-shrink: 0;
    }
    .menu-toggle-desktop:hover { background: var(--surface-3); color: var(--text); }
    .menu-toggle-desktop svg { width: 17px; height: 17px; }

    .logo a { display: flex; align-items: center; text-decoration: none; }
    .logo img {
    height: 32px;
    object-fit: contain;
    filter: none;           /* remove the invert — it's causing the dark box to show */
    background: transparent;
    border-radius: 0;
}
    .logo-fallback { font-size: 0.95rem; font-weight: 800; color: var(--text); }
    .logo-fallback span { color: var(--brand); }

.nav-section {
    flex: 1;
    padding: 10px 4px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow-y: auto; /* ← scroll only the nav links */
    overflow-x: hidden;
}
    .nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 14px;
        border-radius: 12px;
        color: var(--muted);
        text-decoration: none;
        font-size: 0.855rem;
        font-weight: 600;
        transition: var(--ease);
        white-space: nowrap;
        letter-spacing: 0.01em;
    }
    .nav-link svg { width: 17px; height: 17px; flex-shrink: 0; }
    .nav-link .link-text { flex: 1; }
    .nav-link:hover:not(.active) { background: var(--surface-3); color: var(--text); }
    .nav-link.active {
        background: var(--brand);
        color: #fff;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(164,56,37,0.35);
    }
    .nav-link.logout-link:hover { background: var(--danger-dim); color: var(--danger); }

    .nav-badge {
        margin-left: auto;
        background: #fff;
        color: var(--brand);
        font-size: 0.66rem;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 20px;
    }
    .nav-link.active .nav-badge { background: rgba(255,255,255,0.25); color: #fff; }

    .separator { border: none; border-top: 1px solid var(--border); margin: 8px 4px; }

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    margin: 8px 4px 8px; /* ← bottom margin */
    border-radius: 12px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    transition: var(--ease);
    flex-shrink: 0; /* ← never shrink */
    overflow: hidden;
    min-width: 0;
    margin-bottom: -17px;
}
    .user-profile:hover { background: var(--surface-3); }
    .user-profile img { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
    .user-profile .name { font-size: 0.8rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-profile .role { font-size: 0.7rem; color: var(--muted); margin-top: 2px; }

    /* Collapsed sidebar */
.sidebar.collapsed {
    width: 68px;
    padding: 16px 8px;
    overflow: hidden;
}

.sidebar.collapsed .user-profile {
    justify-content: center;
    align-items: center;
    padding: 10px 0;
    margin: 0;
    overflow: visible;
    width: 100%;
    margin-bottom: -17px;
}

.sidebar.collapsed .user-profile img { 
    margin: 0 auto; 
    display: block;
    flex-shrink: 0;
    margin-left: 11px;
}

.user-profile-wrap {
    padding: 0 4px 16px;
    flex-shrink: 0;
}

.sidebar.collapsed .user-profile-wrap {
    padding: 0 0 16px;
}
.sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 10px 0;
    margin: 2px 0;
}

.sidebar.collapsed .nav-link i,
.sidebar.collapsed .nav-link svg { margin: 0; }

.sidebar.collapsed .separator { margin: 8px 2px; }

.sidebar.collapsed .nav-section { padding: 0 2px; }

.sidebar.collapsed .link-text,
.sidebar.collapsed .user-profile .name,
.sidebar.collapsed .user-profile .role,
.sidebar.collapsed .logo img,
.sidebar.collapsed .logo-fallback { display: none; }

    /* ══ MAIN CONTENT ══ */
    .content-area {
        flex: 1 1 0%;
        padding: 24px;
        min-height: 100vh;
        overflow-y: auto;
        background: var(--bg);
        display: flex;
        flex-direction: column;
    }

    .content-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--border);
    }
    .content-header h2 { font-size: 1.75rem; font-weight: 800; color: var(--text); letter-spacing: -0.025em; }
    .header-right { display: flex; align-items: center; gap: 14px; font-size: 0.78rem; color: var(--muted); font-weight: 500; }
    .online-pill { display: flex; align-items: center; gap: 6px; }
    .pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--success); box-shadow: 0 0 6px var(--success); }

    .main-content-layout { display: flex; flex-direction: column; gap: 20px; flex: 1; }

    /* ══ STATS ══ */
    .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

    .stat-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 18px;
        background: var(--surface);
        padding: 24px 22px;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: 0 2px 12px rgba(0,0,0,0.20);
        overflow: hidden;
        transition: box-shadow var(--ease), transform var(--ease), border-color var(--ease);
    }
    .stat-card::before {
        content: '';
        position: absolute; top: 0; left: 0;
        width: 4px; height: 100%;
        background: var(--brand);
        opacity: 0;
        transition: opacity var(--ease);
    }
    .stat-card:hover { box-shadow: 0 8px 28px rgba(164,56,37,0.18); transform: translateY(-3px); border-color: rgba(164,56,37,0.35); }
    .stat-card:hover::before { opacity: 1; }

    .stat-icon-wrapper {
        width: 54px; height: 54px;
        background: var(--brand);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(164,56,37,0.40);
        transition: transform var(--ease);
    }
    .stat-card:hover .stat-icon-wrapper { transform: scale(1.08); }
    .stat-icon-wrapper i { font-size: 1.5rem; color: #fff; }

    .stat-content { display: flex; flex-direction: column; gap: 5px; }
    .stat-value { font-size: 2.1rem; font-weight: 800; color: var(--text); line-height: 1; letter-spacing: -0.03em; }
    .stat-label { font-size: 0.8rem; font-weight: 600; color: var(--muted); }

    .stat-tag {
        position: absolute; top: 13px; right: 13px;
        font-size: 0.63rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
        padding: 3px 9px; border-radius: 20px;
        background: var(--warn-dim); color: var(--warn);
    }

    /* ══ TABLE CARD ══ */
    .table-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.20);
    }

    .table-card-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        background: var(--surface-2);
    }
    .table-card-head h3 { font-size: 1rem; font-weight: 700; color: var(--text); margin: 0; }
    .table-card-head p  { font-size: 0.76rem; color: var(--muted); margin-top: 4px; }

    .count-pill {
        font-size: 0.72rem; font-weight: 700;
        padding: 5px 14px; border-radius: 20px;
        background: var(--surface-3); border: 1px solid var(--border);
        color: var(--muted); white-space: nowrap;
    }

    table { width: 100%; border-collapse: collapse; }
    thead tr { background: var(--surface-2); }
    thead th {
        padding: 12px 24px; text-align: left;
        font-size: 0.68rem; font-weight: 700;
        color: var(--muted);
        text-transform: uppercase; letter-spacing: 0.08em;
        border-bottom: 1px solid var(--border);
    }
    tbody tr { border-bottom: 1px solid var(--border-soft); transition: background var(--ease); }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--surface-2); }
    tbody td { padding: 15px 24px; font-size: 0.855rem; color: var(--text); vertical-align: middle; }

    .evt-cell { display: flex; align-items: center; gap: 12px; }
    .evt-initial {
        width: 38px; height: 38px; border-radius: 10px;
        background: var(--brand-dim); color: var(--brand);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; font-weight: 800; flex-shrink: 0;
    }
    .evt-title { font-weight: 600; color: var(--text); }
    .evt-desc  { font-size: 0.72rem; color: var(--muted); margin-top: 3px; }

    .org-chip { display: flex; align-items: center; gap: 7px; font-size: 0.82rem; color: var(--muted); font-weight: 500; }
    .org-dot  { width: 6px; height: 6px; border-radius: 50%; background: var(--brand); flex-shrink: 0; }

    .d-main { font-size: 0.82rem; font-weight: 600; color: var(--text); }
    .d-sub  { font-size: 0.72rem; color: var(--muted); margin-top: 3px; }

    .act-row { display: flex; gap: 7px; }
    .act-btn {
        padding: 7px 14px; border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface-2);
        cursor: pointer;
        display: flex; align-items: center; gap: 5px;
        color: var(--muted);
        font-size: 0.77rem; font-weight: 700;
        font-family: var(--font);
        transition: var(--ease);
    }
    .act-btn svg { width: 13px; height: 13px; }
    .act-btn:hover { background: var(--surface-3); color: var(--text); }
    .act-btn.approve { color: var(--success); border-color: rgba(34,197,94,0.25); }
    .act-btn.approve:hover { background: var(--success); color: #fff; border-color: transparent; }
    .act-btn.decline { color: var(--danger); border-color: rgba(239,68,68,0.25); }
    .act-btn.decline:hover { background: var(--danger); color: #fff; border-color: transparent; }
    .act-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    .empty-state { text-align: center; padding: 64px 20px; }
    .empty-icon {
        width: 56px; height: 56px; border-radius: 15px;
        background: var(--success-dim); color: var(--success);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
    }
    .empty-icon svg { width: 24px; height: 24px; }
    .empty-state h3 { font-size: 1rem; font-weight: 700; color: var(--text); margin-bottom: 6px; }
    .empty-state p  { font-size: 0.82rem; color: var(--muted); }

    /* ══ MODAL ══ */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.70); backdrop-filter: blur(6px);
        z-index: 200; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 18px; width: 90%; max-width: 450px; padding: 30px;
        animation: popIn 0.22s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes popIn { from { transform:scale(0.93); opacity:0; } to { transform:scale(1); opacity:1; } }
    .modal-title { font-size: 1.05rem; font-weight: 800; margin-bottom: 4px; color: var(--text); }
    .modal-sub   { font-size: 0.8rem; color: var(--muted); margin-bottom: 22px; }
    .field label { display: block; font-size: 0.72rem; font-weight: 700; color: var(--muted); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px; }
    .field textarea {
        width: 100%; background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 10px; color: var(--text);
        font-family: var(--font); font-size: 0.855rem;
        padding: 12px 14px; resize: vertical; min-height: 100px; outline: none;
        transition: var(--ease);
    }
    .field textarea:focus { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-dim); }
    .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }
    .btn-ghost {
        padding: 10px 20px; background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 9px; color: var(--muted); font-family: var(--font);
        font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: var(--ease);
    }
    .btn-ghost:hover { color: var(--text); background: var(--surface-3); }
    .btn-danger-solid {
        padding: 10px 20px; background: var(--danger); border: none;
        border-radius: 9px; color: #fff; font-family: var(--font);
        font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: var(--ease);
        display: flex; align-items: center; gap: 6px;
    }
    .btn-danger-solid svg { width: 13px; height: 13px; }
    .btn-danger-solid:hover { background: #dc2626; }

    /* ══ TOAST ══ */
    .toast {
        position: fixed; bottom: 22px; right: 22px;
        background: var(--surface); border: 1px solid var(--border);
        color: var(--text); padding: 12px 18px; border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        z-index: 999; display: flex; align-items: center; gap: 10px;
        font-size: 0.82rem; font-weight: 600;
        opacity: 0; transform: translateY(14px);
        pointer-events: none; transition: var(--ease);
    }
    .toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }

    @media (max-width: 1024px) { .stats-row { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) {
        .sidebar { position: fixed; transform: translateX(-100%); margin: 0; border-radius: 0; height: 100vh; top: 0; }
        .content-area { padding: 16px; }
        .stats-row { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body>

<div class="dashboard-container">

<aside class="sidebar" id="left-sidebar" style="height: calc(100vh - 32px);">
    <div class="sidebar-top-icons">
        <button class="menu-toggle-desktop" id="collapse-toggle" title="Toggle sidebar">
            <i data-lucide="menu"></i>
        </button>
        <div class="logo">
    <a href="Admin_db.php">
        <img src="../Images/newlogo.png" alt="Arki Connect"
             style="height:32px; object-fit:contain; border-radius:0; background:transparent; filter:none;"
             onerror="this.style.display='none';document.getElementById('logo-fb').style.display='block'">
        <span class="logo-fallback" id="logo-fb" style="display:none;">Arki<span>Admin</span></span>
    </a>
</div>
    </div>

    <div class="nav-section">
        <a href="Admin_db.php" class="nav-link active" title="Dashboard">
            <i data-lucide="layout-dashboard"></i>
            <span class="link-text">Dashboard</span>
            <?php if ($pending_events > 0): ?>
                <span class="nav-badge"><?= $pending_events ?></span>
            <?php endif; ?>
        </a>
        <a href="manage_events.php" class="nav-link" title="Manage Events">
            <i data-lucide="calendar-days"></i>
            <span class="link-text">Manage Events</span>
        </a>
        <a href="event_reports.php" class="nav-link" title="Event Reports">
            <i data-lucide="file-bar-chart"></i>
            <span class="link-text">Event Reports</span>
        </a>
        <hr class="separator">
        <a href="settings_admin.php" class="nav-link" title="Settings">
            <i data-lucide="settings"></i>
            <span class="link-text">Settings</span>
        </a>
        <a href="login.php?logout=true" class="nav-link logout-link" title="Sign out">
            <i data-lucide="log-out"></i>
            <span class="link-text">Logout</span>
        </a>
    </div>

   <div class="user-profile-wrap">
    <div class="user-profile">
        <img src="https://placehold.co/36x36/A43825/ffffff?text=<?= htmlspecialchars($userInitials) ?>" alt="Admin">
        <div class="user-info">
            <div class="name"><?= htmlspecialchars($userName) ?></div>
            <div class="role">System Administrator</div>
        </div>
    </div>
</div>
</aside>

<main class="content-area" id="content-area">
    <header class="content-header">
        <h2>Dashboard</h2>
        <div class="header-right">
            <span id="headerDate"></span>
            <div class="online-pill">
                <span class="pulse-dot"></span> Online
            </div>
        </div>
    </header>

    <div class="main-content-layout">

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon-wrapper"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?= $total_events ?></div>
                    <div class="stat-label">Approved Events</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper"><i class="fas fa-building"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?= $total_orgs ?></div>
                    <div class="stat-label">Organizations</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-content">
                    <div class="stat-value"><?= $pending_events ?></div>
                    <div class="stat-label">Awaiting Review</div>
                </div>
                <?php if ($pending_events > 0): ?>
                    <span class="stat-tag">Needs action</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-head">
                <div>
                    <h3>Events Pending Approval</h3>
                    <p>Review and approve or reject submissions from organizations</p>
                </div>
                <span class="count-pill">
                    <?= count($recent_events) ?> event<?= count($recent_events) !== 1 ? 's' : '' ?>
                </span>
            </div>

            <?php if (empty($recent_events)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i data-lucide="check"></i></div>
                    <h3>All clear!</h3>
                    <p>No events are currently awaiting review.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Organization</th>
                            <th>Event Date</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_events as $event): ?>
                        <tr>
                            <td>
                                <div class="evt-cell">
                                    <div class="evt-initial"><?= strtoupper(substr($event['title'], 0, 1)) ?></div>
                                    <div>
                                        <div class="evt-title"><?= htmlspecialchars($event['title']) ?></div>
                                        <div class="evt-desc"><?= htmlspecialchars(substr($event['description'], 0, 52)) . (strlen($event['description']) > 52 ? '…' : '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="org-chip">
                                    <span class="org-dot"></span>
                                    <?= htmlspecialchars($event['org_name']) ?>
                                </div>
                            </td>
                            <td><div class="d-main"><?= date('M d, Y', strtotime($event['event_date'])) ?></div></td>
                            <td>
                                <div class="d-main"><?= date('M d, Y', strtotime($event['created_at'])) ?></div>
                                <div class="d-sub"><?= date('h:i A', strtotime($event['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="act-row">
                                    <button class="act-btn approve" onclick="approveEvent(<?= $event['id'] ?>)" data-event-id="<?= $event['id'] ?>">
                                        <i data-lucide="check"></i> Approve
                                    </button>
                                    <button class="act-btn decline" onclick="openRejectModal(<?= $event['id'] ?>)">
                                        <i data-lucide="x"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</main>
</div>

<div id="rejectModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Reject Event</div>
        <div class="modal-sub">Give the organizer a clear reason so they can revise and resubmit.</div>
        <div class="field">
            <label>Reason for rejection</label>
            <textarea id="rejectReason" placeholder="e.g. Missing venue details, conflicting schedule…"></textarea>
        </div>
        <div class="modal-actions">
            <button class="btn-ghost" onclick="closeRejectModal()">Cancel</button>
            <button class="btn-danger-solid" onclick="submitReject()">
                <i data-lucide="ban"></i> Reject Event
            </button>
        </div>
    </div>
</div>

<div id="toast" class="toast">
    <span id="toastIcon"></span>
    <span id="toastMsg"></span>
</div>

<script>
    document.getElementById('headerDate').textContent =
        new Date().toLocaleDateString('en-US', { weekday:'long', month:'long', day:'numeric', year:'numeric' });

    let currentEventId = null;

    function approveEvent(id) {
        const btn = document.querySelector(`[data-event-id="${id}"]`);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Approving…';
        fetch('approve_event.php?action=approve', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ event_id: id })
})
        .then(r => r.json())
        .then(d => {
            if (d.success) { toast('Event approved!', 'success'); setTimeout(() => location.reload(), 1500); }
            else { toast('Error: ' + d.error, 'error'); btn.disabled = false; btn.innerHTML = '✓ Approve'; }
        })
        .catch(() => { toast('Connection error.', 'error'); btn.disabled = false; btn.innerHTML = '✓ Approve'; });
    }

    function openRejectModal(id) {
        currentEventId = id;
        document.getElementById('rejectModal').classList.add('open');
        document.getElementById('rejectReason').value = '';
        setTimeout(() => document.getElementById('rejectReason').focus(), 60);
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('open');
        currentEventId = null;
    }

    function submitReject() {
        const reason = document.getElementById('rejectReason').value.trim();
        if (!reason) { document.getElementById('rejectReason').style.borderColor = '#ef4444'; return; }
        fetch('approve_event.php?action=reject', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: currentEventId, reason })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) { toast('Event rejected.', 'warn'); closeRejectModal(); setTimeout(() => location.reload(), 1500); }
            else toast('Error: ' + d.error, 'error');
        })
        .catch(() => toast('Connection error.', 'error'));
    }

    function toast(msg, type = 'success') {
        const map = { success:['✓','#22c55e'], error:['✕','#ef4444'], warn:['→','#f59e0b'] };
        const [ic, col] = map[type] || map.success;
        const el = document.getElementById('toast');
        document.getElementById('toastIcon').textContent = ic;
        document.getElementById('toastIcon').style.color = col;
        document.getElementById('toastMsg').textContent = msg;
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3200);
    }

    document.getElementById('rejectModal').addEventListener('click', e => {
        if (e.target === document.getElementById('rejectModal')) closeRejectModal();
    });
    document.getElementById('rejectReason').addEventListener('input', function() {
        this.style.borderColor = '';
    });

    lucide.createIcons();

    // Hamburger / sidebar toggle
const sidebar = document.querySelector('.sidebar');
const menuBtn = document.querySelector('.menu-toggle-desktop');

menuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});
</script>
</body>
</html> 