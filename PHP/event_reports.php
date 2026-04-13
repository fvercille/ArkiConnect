<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);



$user_id = $_SESSION['user_id'] ?? null;
$role    = $_SESSION['role']    ?? null;

if ($role !== 'admin' || empty($user_id)) {
    header("Location: login.php");
    exit();
}

$orgs_result   = $conn->query("SELECT id, fullname FROM users WHERE role = 'org_rep' ORDER BY fullname");
$organizations = $orgs_result->fetch_all(MYSQLI_ASSOC);

$filter_status    = $_GET['status']    ?? 'all';
$filter_org       = $_GET['org']       ?? 'all';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to   = $_GET['date_to']   ?? '';

$where_conditions = ["1=1"];
$params      = [];
$param_types = '';

if ($filter_status !== 'all')   { $where_conditions[] = "e.status = ?";       $params[] = $filter_status;    $param_types .= 's'; }
if ($filter_org    !== 'all')   { $where_conditions[] = "e.created_by = ?";   $params[] = $filter_org;       $param_types .= 'i'; }
if (!empty($filter_date_from))  { $where_conditions[] = "e.event_date >= ?";  $params[] = $filter_date_from; $param_types .= 's'; }
if (!empty($filter_date_to))    { $where_conditions[] = "e.event_date <= ?";  $params[] = $filter_date_to;   $param_types .= 's'; }

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

$stats_sql = "SELECT
    COUNT(*) as total_events,
    SUM(CASE WHEN status='approved'  THEN 1 ELSE 0 END) as approved_events,
    SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) as pending_events,
    SUM(CASE WHEN status='rejected'  THEN 1 ELSE 0 END) as rejected_events,
    SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled_events,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed_events
  FROM events e {$where_clause}";

$stats_stmt = $conn->prepare($stats_sql);
if (!empty($params)) $stats_stmt->bind_param($param_types, ...$params);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

$events_sql = "SELECT
    e.id, e.title, e.description, e.event_date, e.event_time,
    e.location, e.status, e.created_at, e.approved_at,
    u.fullname as org_name, u.email as org_email,
    (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed') as total_registrations
  FROM events e
  LEFT JOIN users u ON e.created_by = u.id
  {$where_clause}
  ORDER BY e.created_at DESC";

$events_stmt = $conn->prepare($events_sql);
if (!empty($params)) $events_stmt->bind_param($param_types, ...$params);
$events_stmt->execute();
$events = $events_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = max(1, (int)$stats['total_events']);

$userName    = $_SESSION['fullname'] ?? 'Admin User';
$words       = explode(' ', trim($userName));
$userInitials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Reports - Admin Dashboard</title>
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

        --bg:           #1e1e24;
        --surface:      #2a2a32;
        --surface-2:    #32323c;
        --surface-3:    #3c3c48;

        --border:       rgba(255,255,255,0.10);
        --border-soft:  rgba(255,255,255,0.06);

        --text:         #f5f5f3;
        --muted:        #b0b0bb;

        --success:      #22c55e;
        --success-dim:  rgba(34,197,94,0.15);
        --warn:         #f59e0b;
        --warn-dim:     rgba(245,158,11,0.15);
        --danger:       #ef4444;
        --danger-dim:   rgba(239,68,68,0.15);
        --blue:         #3b82f6;
        --blue-dim:     rgba(59,130,246,0.15);

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
     margin-bottom: -3px;
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

    /* ─── MAIN ─── */
    .content-area {
        flex:1 1 0%; padding:24px;
        min-height:100vh; overflow-y:auto;
        background:var(--bg); display:flex; flex-direction:column;
    }

    .content-header {
        display:flex; align-items:center; justify-content:space-between;
        gap:12px; margin-bottom:24px; padding-bottom:18px;
        border-bottom:1px solid var(--border);
    }
    .content-header h2 { font-size:1.75rem; font-weight:800; color:var(--text); letter-spacing:-.025em; }
    .header-right { display:flex; align-items:center; gap:14px; font-size:.78rem; color:var(--muted); font-weight:500; }
    .online-pill  { display:flex; align-items:center; gap:6px; }
    .pulse-dot { width:7px; height:7px; border-radius:50%; background:var(--success); box-shadow:0 0 6px var(--success); }

    .main-content-layout { display:flex; flex-direction:column; gap:18px; flex:1; }

    /* ─── FILTER ─── */
    .filter-card {
        background:var(--surface);
        border:1px solid var(--border);
        border-radius:var(--radius);
        padding:20px 22px;
        box-shadow:0 2px 12px rgba(0,0,0,.20);
    }
    .section-label {
        font-size:.68rem; font-weight:700; color:var(--muted);
        text-transform:uppercase; letter-spacing:.08em;
        display:flex; align-items:center; gap:7px; margin-bottom:16px;
    }
    .section-label i { color:var(--brand); font-size:.75rem; }

    .filter-grid {
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:12px;
        margin-bottom:14px;
    }
    .filter-group label {
        display:block; font-size:.67rem; font-weight:700;
        color:var(--muted); text-transform:uppercase; letter-spacing:.07em; margin-bottom:5px;
    }
    .filter-group select,
    .filter-group input[type="date"] {
        width:100%; padding:9px 12px;
        background:var(--surface-2);
        border:1px solid var(--border);
        border-radius:9px; font-size:.78rem; color:var(--text);
        font-family:var(--font); font-weight:600;
        outline:none; appearance:none; -webkit-appearance:none;
        color-scheme:dark; cursor:pointer; transition:var(--ease);
    }
    .filter-group select {
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23b0b0bb' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat:no-repeat; background-position:right 10px center; padding-right:32px;
    }
    .filter-group select:focus,
    .filter-group input:focus { border-color:var(--brand); box-shadow:0 0 0 3px var(--brand-dim); }

    .filter-actions { display:flex; gap:9px; justify-content:flex-end; flex-wrap:wrap; }

    .btn {
        padding:9px 16px; border-radius:9px; border:none;
        cursor:pointer; font-weight:700; font-size:.77rem;
        font-family:var(--font); transition:var(--ease);
        display:inline-flex; align-items:center; gap:7px;
    }
    .btn-ghost    { background:var(--surface-2); color:var(--muted); border:1px solid var(--border); }
    .btn-ghost:hover  { background:var(--surface-3); color:var(--text); }
    .btn-primary  { background:var(--brand); color:#fff; }
    .btn-primary:hover { background:var(--brand-hover); }
    .btn-export   { background:var(--success-dim); color:var(--success); border:1px solid rgba(34,197,94,.2); }
    .btn-export:hover { background:rgba(34,197,94,.25); }

    /* ─── STATS ROW (all 6 on one line) ─── */
    .stats-row {
        display:grid;
        grid-template-columns:repeat(6,1fr);
        gap:12px;
    }
    .stat-card {
        background:var(--surface);
        border:1px solid var(--border);
        border-radius:var(--radius);
        padding:18px 14px; text-align:center;
        box-shadow:0 2px 12px rgba(0,0,0,.20);
        transition:transform var(--ease), box-shadow var(--ease);
        cursor:default;
    }
    .stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.3); }
    .stat-value { font-size:2rem; font-weight:800; line-height:1; margin-bottom:6px; }
    .stat-label { font-size:.62rem; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px; }
    .stat-bar   { height:3px; border-radius:2px; }

    .stat-card.total    .stat-value { color:var(--brand); }
    .stat-card.approved .stat-value { color:var(--success); }
    .stat-card.pending  .stat-value { color:var(--warn); }
    .stat-card.rejected .stat-value { color:var(--danger); }
    .stat-card.cancelled .stat-value{ color:var(--muted); }
    .stat-card.completed .stat-value{ color:var(--blue); }

    .stat-card.total    .stat-bar { background:linear-gradient(90deg,var(--brand),var(--brand-dim)); }
    .stat-card.approved .stat-bar { background:linear-gradient(90deg,var(--success),var(--success-dim)); }
    .stat-card.pending  .stat-bar { background:linear-gradient(90deg,var(--warn),var(--warn-dim)); }
    .stat-card.rejected .stat-bar { background:linear-gradient(90deg,var(--danger),var(--danger-dim)); }
    .stat-card.cancelled .stat-bar{ background:rgba(255,255,255,.08); }
    .stat-card.completed .stat-bar{ background:linear-gradient(90deg,var(--blue),var(--blue-dim)); }

    /* ─── TABLE ─── */
    .table-card {
        background:var(--surface);
        border:1px solid var(--border);
        border-radius:var(--radius);
        overflow:hidden;
        box-shadow:0 2px 12px rgba(0,0,0,.20);
    }
    .table-card-head {
        padding:16px 22px;
        border-bottom:1px solid var(--border);
        background:var(--surface-2);
        display:flex; align-items:center; justify-content:space-between;
    }
    .table-card-head h3 { font-size:.9rem; font-weight:700; color:var(--text); display:flex; align-items:center; gap:8px; }
    .table-card-head h3 i { color:var(--brand); }
    .table-meta { font-size:.72rem; color:var(--muted); font-weight:600; }

    .table-wrap { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; }
    thead tr { background:var(--surface-2); }
    thead th {
        padding:11px 18px; text-align:left;
        font-size:.64rem; font-weight:700; color:var(--muted);
        text-transform:uppercase; letter-spacing:.08em;
        border-bottom:1px solid var(--border); white-space:nowrap;
    }
    thead th.center { text-align:center; }
    tbody tr { border-bottom:1px solid var(--border-soft); transition:background var(--ease); }
    tbody tr:last-child { border-bottom:none; }
    tbody tr:hover { background:var(--surface-2); }
    tbody td { padding:13px 18px; font-size:.82rem; color:var(--text); vertical-align:middle; }

    .td-id     { color:var(--muted); font-size:.74rem; font-weight:600; }
    .td-title  { font-weight:700; color:var(--text); font-size:.84rem; }
    .td-sub    { font-size:.70rem; color:var(--muted); margin-top:3px; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .td-org    { font-weight:600; font-size:.80rem; }
    .td-email  { font-size:.70rem; color:var(--muted); margin-top:2px; }
    .td-date   { white-space:nowrap; font-size:.80rem; }
    .td-regs   { font-weight:800; color:var(--brand); font-size:.9rem; text-align:center; }
    .td-muted  { color:var(--muted); font-size:.74rem; white-space:nowrap; }

    .badge {
        padding:3px 10px; border-radius:20px; font-size:.6rem; font-weight:800;
        text-transform:uppercase; letter-spacing:.06em; white-space:nowrap;
        display:inline-flex; align-items:center; gap:5px;
    }
    .badge::before { content:''; width:5px; height:5px; border-radius:50%; flex-shrink:0; }
    .badge.approved  { background:var(--success-dim); color:var(--success); }
    .badge.approved::before  { background:var(--success); }
    .badge.pending   { background:var(--warn-dim);    color:var(--warn); }
    .badge.pending::before   { background:var(--warn); }
    .badge.rejected  { background:var(--danger-dim);  color:var(--danger); }
    .badge.rejected::before  { background:var(--danger); }
    .badge.cancelled { background:rgba(255,255,255,.07); color:var(--muted); }
    .badge.cancelled::before { background:var(--muted); }
    .badge.completed { background:var(--blue-dim);    color:var(--blue); }
    .badge.completed::before { background:var(--blue); }

    .no-data { text-align:center; padding:64px 20px; color:var(--muted); }
    .no-data i { font-size:3rem; color:var(--surface-3); margin-bottom:16px; display:block; }
    .no-data h3 { font-size:1rem; font-weight:700; color:var(--text); margin-bottom:6px; }
    .no-data p  { font-size:.82rem; }

    @media (max-width:1100px) {
        .stats-row { grid-template-columns:repeat(3,1fr); }
    }
    @media (max-width:768px) {
        .sidebar { position:fixed; transform:translateX(-100%); margin:0; border-radius:0; height:100vh; top:0; }
        .content-area { padding:16px; }
        .filter-grid { grid-template-columns:1fr 1fr; }
        .stats-row { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width:480px) {
        .filter-grid { grid-template-columns:1fr; }
        .stats-row { grid-template-columns:repeat(2,1fr); }
    }
    </style>
</head>
<body>
<div class="dashboard-container">

<!-- ─── SIDEBAR ─── -->
<aside class="sidebar">
    <div class="sidebar-top-icons">
        <button class="menu-toggle-desktop" title="Toggle sidebar">
            <i data-lucide="menu"></i>
        </button>
        <div class="logo">
            <a href="Admin_db.php">
                <img src="../Images/newlogo.png" alt="Arki Connect"
                     onerror="this.style.display='none';document.getElementById('logo-fb').style.display='block'">
                <span class="logo-fallback" id="logo-fb" style="display:none;">Arki<span>Admin</span></span>
            </a>
        </div>
    </div>

    <div class="nav-section">
        <a href="Admin_db.php" class="nav-link">
            <i data-lucide="layout-dashboard"></i>
            <span class="link-text">Dashboard</span>
        </a>
        <a href="manage_events.php" class="nav-link">
            <i data-lucide="calendar-days"></i>
            <span class="link-text">Manage Events</span>
        </a>
        <a href="event_reports.php" class="nav-link active">
            <i data-lucide="file-bar-chart"></i>
            <span class="link-text">Event Reports</span>
        </a>
        <hr class="separator">
        <a href="settings_admin.php" class="nav-link">
            <i data-lucide="settings"></i>
            <span class="link-text">Settings</span>
        </a>
        <a href="login.php?logout=true" class="nav-link logout-link">
            <i data-lucide="log-out"></i>
            <span class="link-text">Logout</span>
        </a>
    </div>

    <div class="user-profile">
        <img src="https://placehold.co/36x36/A43825/ffffff?text=<?= htmlspecialchars($userInitials) ?>" alt="Admin">
        <div class="user-info">
            <div class="name"><?= htmlspecialchars($userName) ?></div>
            <div class="role">System Administrator</div>
        </div>
    </div>
</aside>

<!-- ─── MAIN ─── -->
<main class="content-area">
    <header class="content-header">
        <h2>Event Reports</h2>
        <div class="header-right">
            <span id="headerDate"></span>
            <div class="online-pill">
                <span class="pulse-dot"></span> Online
            </div>
        </div>
    </header>

    <div class="main-content-layout">

        <!-- FILTER -->
        <section class="filter-card">
            <div class="section-label"></i> Filter Reports</div>
            <form method="GET" action="event_reports.php">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="status">Event Status</label>
                        <select name="status" id="status">
                            <option value="all"       <?= $filter_status === 'all'       ? 'selected' : '' ?>>All Statuses</option>
                            <option value="approved"  <?= $filter_status === 'approved'  ? 'selected' : '' ?>>Approved</option>
                            <option value="pending"   <?= $filter_status === 'pending'   ? 'selected' : '' ?>>Pending</option>
                            <option value="rejected"  <?= $filter_status === 'rejected'  ? 'selected' : '' ?>>Rejected</option>
                            <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="completed" <?= $filter_status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="org">Organization</label>
                        <select name="org" id="org">
                            <option value="all">All Organizations</option>
                            <?php foreach ($organizations as $org): ?>
                                <option value="<?= $org['id'] ?>" <?= $filter_org == $org['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($org['fullname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date_from">Date From</label>
                        <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($filter_date_from) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="date_to">Date To</label>
                        <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($filter_date_to) ?>">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn btn-ghost" onclick="window.location.href='event_reports.php'">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-export" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-export" onclick="exportReport('csv')">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                </div>
            </form>
        </section>

        <!-- STATS — all 6 in one row -->
        <div class="stats-row">
            <?php
            $statDefs = [
                ['key' => 'total_events',     'label' => 'Total Events', 'class' => 'total',    'pct' => 100],
                ['key' => 'approved_events',  'label' => 'Approved',     'class' => 'approved', 'pct' => $total > 0 ? round($stats['approved_events']  / $total * 100) : 0],
                ['key' => 'pending_events',   'label' => 'Pending',      'class' => 'pending',  'pct' => $total > 0 ? round($stats['pending_events']   / $total * 100) : 0],
                ['key' => 'rejected_events',  'label' => 'Rejected',     'class' => 'rejected', 'pct' => $total > 0 ? round($stats['rejected_events']  / $total * 100) : 0],
                ['key' => 'cancelled_events', 'label' => 'Cancelled',    'class' => 'cancelled','pct' => $total > 0 ? round($stats['cancelled_events'] / $total * 100) : 0],
                ['key' => 'completed_events', 'label' => 'Completed',    'class' => 'completed','pct' => $total > 0 ? round($stats['completed_events'] / $total * 100) : 0],
            ];
            foreach ($statDefs as $s): ?>
            <div class="stat-card <?= $s['class'] ?>">
                <div class="stat-value"><?= (int)$stats[$s['key']] ?></div>
                <div class="stat-label"><?= $s['label'] ?></div>
                <div class="stat-bar" style="width:<?= max(6, $s['pct']) ?>%"></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- TABLE -->
        <div class="table-card">
            <div class="table-card-head">
                <h3><i class="fas fa-list"></i> Event Details</h3>
                <span class="table-meta"><?= count($events) ?> event<?= count($events) !== 1 ? 's' : '' ?> found</span>
            </div>

            <?php if (empty($events)): ?>
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <h3>No Events Found</h3>
                    <p>No events match your filter criteria.</p>
                </div>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event Title</th>
                            <th>Organization</th>
                            <th>Event Date</th>
                            <th>Location</th>
                            <th class="center">Reg.</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td class="td-id">#<?= $event['id'] ?></td>
                            <td>
                                <div class="td-title"><?= htmlspecialchars($event['title']) ?></div>
                                <div class="td-sub">
                                    <?= htmlspecialchars(substr($event['description'] ?? '', 0, 55)) ?><?= strlen($event['description'] ?? '') > 55 ? '…' : '' ?>
                                </div>
                            </td>
                            <td>
                                <div class="td-org"><?= htmlspecialchars($event['org_name']) ?></div>
                                <div class="td-email"><?= htmlspecialchars($event['org_email']) ?></div>
                            </td>
                            <td class="td-date"><?= date('M d, Y', strtotime($event['event_date'])) ?></td>
                            <td><?= htmlspecialchars($event['location']) ?></td>
                            <td class="td-regs"><?= (int)$event['total_registrations'] ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($event['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($event['status'])) ?>
                                </span>
                            </td>
                            <td class="td-muted"><?= date('M d, Y', strtotime($event['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /.main-content-layout -->
</main>
</div><!-- /.dashboard-container -->

<script>
    document.getElementById('headerDate').textContent =
        new Date().toLocaleDateString('en-US', { weekday:'long', month:'long', day:'numeric', year:'numeric' });

    function exportReport(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('export', format);
        window.location.href = 'generate_report.php?' + params.toString();
    }

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