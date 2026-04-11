<?php
session_start();
$pageTitle = 'Manage Events';

$conn = new mysqli('localhost', 'root', '', 'user_db');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['fullname'] ?? 'Admin User';
$userRole = $_SESSION['role'] ?? 'student';

$words = explode(' ', trim($userName));
$userInitials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

$query = "SELECT 
    e.id, e.title, e.description, e.event_date, e.location, e.status,
    e.organizer as org_name, e.created_at,
    COUNT(er.id) as registered_count,
    e.registrants as max_participants
FROM events e
LEFT JOIN event_registrations er ON e.id = er.event_id
GROUP BY e.id
ORDER BY e.created_at DESC";

$result = $conn->query($query);
$all_events = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $all_events[] = $row;
}

$pending_count = 0;
foreach ($all_events as $ev) {
    if ($ev['status'] === 'pending') $pending_count++;
}

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
        --purple:       #8b5cf6;
        --purple-dim:   rgba(139,92,246,0.15);
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
     margin-bottom: -1px;
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

    .filter-bar {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 20px;
        display: flex;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 2px 12px rgba(0,0,0,0.20);
    }

    .filter-group { display: flex; align-items: center; gap: 8px; }
    .filter-group label { font-size: 0.7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; white-space: nowrap; }

    .filter-select {
        padding: 8px 36px 8px 12px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.8rem;
        color: var(--text);
        font-family: var(--font);
        font-weight: 600;
        cursor: pointer;
        outline: none;
        transition: var(--ease);
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23b0b0bb' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }
    .filter-select:focus { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-dim); }

    .search-box { flex: 1; min-width: 220px; position: relative; }
    .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.8rem; }
    .search-box input {
        width: 100%;
        padding: 9px 14px 9px 36px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: 0.8rem;
        font-family: var(--font);
        font-weight: 500;
        color: var(--text);
        outline: none;
        transition: var(--ease);
    }
    .search-box input::placeholder { color: var(--muted); }
    .search-box input:focus { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-dim); }

    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(370px, 1fr));
        gap: 16px;
    }

    .event-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 22px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.20);
        transition: box-shadow var(--ease), transform var(--ease), border-color var(--ease);
        display: flex;
        flex-direction: column;
        gap: 14px;
        position: relative;
        overflow: hidden;
    }
    .event-card::before {
        content: '';
        position: absolute; top: 0; left: 0;
        width: 4px; height: 100%;
        background: var(--brand);
        opacity: 0;
        transition: opacity var(--ease);
    }
    .event-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(164,56,37,0.18); border-color: rgba(164,56,37,0.35); }
    .event-card:hover::before { opacity: 1; }

    .event-card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }

    .event-title-block { display: flex; align-items: flex-start; gap: 11px; flex: 1; min-width: 0; }
    .event-initial {
        width: 40px; height: 40px; border-radius: 10px;
        background: var(--brand-dim); color: var(--brand);
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; font-weight: 800; flex-shrink: 0;
    }
    .event-title { font-size: 0.95rem; font-weight: 700; color: var(--text); line-height: 1.3; }
    .event-org { font-size: 0.72rem; color: var(--muted); margin-top: 3px; display: flex; align-items: center; gap: 5px; }
    .org-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--brand); flex-shrink: 0; }

    .status-badge {
        padding: 4px 11px; border-radius: 20px;
        font-size: 0.63rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.06em;
        white-space: nowrap; flex-shrink: 0;
    }
    .status-badge.pending   { background: var(--warn-dim);    color: var(--warn); }
    .status-badge.approved  { background: var(--success-dim); color: var(--success); }
    .status-badge.accepted  { background: var(--success-dim); color: var(--success); }
    .status-badge.upcoming  { background: var(--blue-dim);    color: var(--blue); }
    .status-badge.ongoing   { background: rgba(239,68,68,0.15); color: var(--danger); }
    .status-badge.completed { background: var(--purple-dim);  color: var(--purple); }
    .status-badge.cancelled { background: var(--danger-dim);  color: var(--danger); }
    .status-badge.rejected  { background: var(--danger-dim);  color: var(--danger); }

    .event-description {
        font-size: 0.78rem; color: var(--muted); line-height: 1.6;
        display: -webkit-box; -webkit-line-clamp: 2;
        -webkit-box-orient: vertical; overflow: hidden;
    }

    .event-meta { display: flex; flex-direction: column; gap: 7px; }
    .meta-row { display: flex; align-items: center; gap: 9px; font-size: 0.78rem; color: var(--muted); font-weight: 500; }
    .meta-row i { width: 14px; color: var(--brand); font-size: 0.72rem; flex-shrink: 0; }

    .participants-bar {
        background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 9px; padding: 10px 14px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .participants-bar span { font-size: 0.75rem; color: var(--muted); font-weight: 500; }
    .participants-count { font-weight: 800; color: var(--brand) !important; font-size: 0.85rem !important; }

    .event-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: auto; }
    .action-btn {
        flex: 1; min-width: 70px;
        padding: 9px 14px; border-radius: 9px;
        border: 1px solid var(--border);
        background: var(--surface-2);
        cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        font-size: 0.75rem; font-weight: 700;
        font-family: var(--font); color: var(--muted);
        transition: var(--ease); text-decoration: none;
    }
    .action-btn i { font-size: 0.7rem; }
    .action-btn:hover { background: var(--surface-3); color: var(--text); }
    .btn-view     { color: var(--blue);    border-color: rgba(59,130,246,0.2); }
    .btn-view:hover     { background: var(--blue);    color: #fff; border-color: transparent; }
    .btn-approve  { color: var(--success); border-color: rgba(34,197,94,0.2); }
    .btn-approve:hover  { background: var(--success); color: #fff; border-color: transparent; }
    .btn-decline  { color: var(--danger);  border-color: rgba(239,68,68,0.2); }
    .btn-decline:hover  { background: var(--danger);  color: #fff; border-color: transparent; }
    .btn-sign-pdf { color: var(--purple);  border-color: rgba(139,92,246,0.2); }
    .btn-sign-pdf:hover { background: var(--purple);  color: #fff; border-color: transparent; }

    .no-data {
        grid-column: 1 / -1; text-align: center; padding: 72px 20px;
        color: var(--muted); background: var(--surface);
        border: 1px solid var(--border); border-radius: var(--radius);
    }
    .no-data-icon {
        width: 60px; height: 60px; border-radius: 16px;
        background: var(--surface-2); color: var(--muted);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px; font-size: 1.6rem;
    }
    .no-data h3 { font-size: 1rem; font-weight: 700; color: var(--text); margin-bottom: 6px; }
    .no-data p  { font-size: 0.8rem; }

    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.70); backdrop-filter: blur(6px);
        z-index: 200; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 18px; width: 90%; max-width: 620px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        animation: popIn 0.22s cubic-bezier(0.34,1.56,0.64,1); overflow: hidden;
    }
    @keyframes popIn { from { transform:scale(0.93); opacity:0; } to { transform:scale(1); opacity:1; } }

    .modal-head {
        background: linear-gradient(135deg, var(--brand) 0%, #8a2d1f 100%);
        padding: 22px 26px; display: flex; justify-content: space-between; align-items: center;
    }
    .modal-head h3 { font-size: 1.05rem; font-weight: 800; color: #fff; margin: 0; }
    .modal-close {
        background: rgba(255,255,255,0.15); border: none; color: #fff;
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 1.1rem; font-weight: 700; transition: var(--ease);
    }
    .modal-close:hover { background: rgba(255,255,255,0.25); }

    .modal-body {
        padding: 24px 26px; max-height: 65vh; overflow-y: auto;
        display: flex; flex-direction: column; gap: 18px;
    }
    .modal-field label { display: block; font-size: 0.65rem; font-weight: 700; color: var(--muted); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px; }
    .modal-field p { font-size: 0.845rem; color: var(--text); line-height: 1.6; }
    .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .modal-footer { padding: 16px 26px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }

    .btn-ghost {
        padding: 9px 20px; background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 9px; color: var(--muted); font-family: var(--font);
        font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: var(--ease);
    }
    .btn-ghost:hover { color: var(--text); background: var(--surface-3); }

    .reject-textarea {
        width: 100%;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 9px;
        color: var(--text);
        font-family: var(--font);
        font-size: 0.855rem;
        padding: 12px 14px;
        resize: vertical;
        min-height: 100px;
        outline: none;
        transition: var(--ease);
    }
    .reject-textarea:focus { border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-dim); }

    .btn-danger-solid {
        padding: 9px 20px; background: var(--danger); border: none;
        border-radius: 9px; color: #fff; font-family: var(--font);
        font-size: 0.8rem; font-weight: 700; cursor: pointer; transition: var(--ease);
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-danger-solid:hover { background: #dc2626; }

    .toast {
        position: fixed; bottom: 22px; right: 22px;
        background: var(--surface); border: 1px solid var(--border);
        color: var(--text); padding: 12px 18px; border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        z-index: 999; display: flex; align-items: center; gap: 10px;
        font-size: 0.8rem; font-weight: 600;
        opacity: 0; transform: translateY(14px);
        pointer-events: none; transition: var(--ease);
    }
    .toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }

    @media (max-width: 768px) {
        .sidebar { position: fixed; transform: translateX(-100%); margin: 0; border-radius: 0; height: 100vh; top: 0; }
        .content-area { padding: 16px; }
        .events-grid { grid-template-columns: 1fr; }
        .modal-grid { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body>

<div class="dashboard-container">

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
        <a href="Admin_db.php" class="nav-link" title="Dashboard">
            <i data-lucide="layout-dashboard"></i>
            <span class="link-text">Dashboard</span>
        </a>
        <a href="manage_events.php" class="nav-link active" title="Manage Events">
            <i data-lucide="calendar-days"></i>
            <span class="link-text">Manage Events</span>
            <?php if ($pending_count > 0): ?>
                <span class="nav-badge"><?= $pending_count ?></span>
            <?php endif; ?>
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

    <div class="user-profile">
        <img src="https://placehold.co/36x36/A43825/ffffff?text=<?= htmlspecialchars($userInitials) ?>" alt="Admin">
        <div class="user-info">
            <div class="name"><?= htmlspecialchars($userName) ?></div>
            <div class="role">System Administrator</div>
        </div>
    </div>
</aside>

<main class="content-area">
    <header class="content-header">
        <h2>Manage Events</h2>
        <div class="header-right">
            <span id="headerDate"></span>
            <div class="online-pill">
                <span class="pulse-dot"></span> Online
            </div>
        </div>
    </header>

    <div class="main-content-layout">

        <div class="filter-bar">
            <div class="filter-group">
                <label>Status</label>
                <select class="filter-select" id="statusFilter">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Sort</label>
                <select class="filter-select" id="sortFilter">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="date">Event Date</option>
                </select>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search events by title or organization…">
            </div>
        </div>

        <div class="events-grid" id="eventsGrid">
            <?php if (count($all_events) > 0): ?>
                <?php foreach ($all_events as $event): ?>
                <div class="event-card"
                     data-status="<?= strtolower($event['status']) ?>"
                     data-date="<?= strtotime($event['event_date']) ?>"
                     data-created="<?= strtotime($event['created_at']) ?>">

                    <div class="event-card-header">
                        <div class="event-title-block">
                            <div class="event-initial"><?= strtoupper(substr($event['title'], 0, 1)) ?></div>
                            <div>
                                <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                                <div class="event-org">
                                    <span class="org-dot"></span>
                                    <?= htmlspecialchars($event['org_name'] ?? 'N/A') ?>
                                </div>
                            </div>
                        </div>
                        <span class="status-badge <?= strtolower($event['status']) ?>">
                            <?= ucfirst($event['status']) ?>
                        </span>
                    </div>

                    <p class="event-description"><?= htmlspecialchars($event['description'] ?? 'No description available.') ?></p>

                    <div class="event-meta">
                        <div class="meta-row">
                            <i class="fas fa-calendar"></i>
                            <?= date('F d, Y', strtotime($event['event_date'])) ?>
                        </div>
                        <div class="meta-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($event['location'] ?? 'TBD') ?>
                        </div>
                        <div class="meta-row">
                            <i class="fas fa-clock"></i>
                            Submitted <?= date('M d, Y', strtotime($event['created_at'])) ?>
                        </div>
                    </div>

                    <div class="participants-bar">
                        <span>Registered Participants</span>
                        <span class="participants-count">
                            <?= $event['registered_count'] ?> / <?= $event['max_participants'] ?? '∞' ?>
                        </span>
                    </div>

                    <div class="event-actions">
                        <button class="action-btn btn-view" onclick="viewEvent(<?= $event['id'] ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <?php if ($event['status'] === 'pending'): ?>
                            <button class="action-btn btn-approve" onclick="approveEvent(<?= $event['id'] ?>)">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="action-btn btn-decline" onclick="openDeclineModal(<?= $event['id'] ?>)">
                                <i class="fas fa-times"></i> Decline
                            </button>
                        <?php elseif ($event['status'] === 'approved' || $event['status'] === 'upcoming'): ?>
                            <button class="action-btn btn-sign-pdf" onclick="signEventPDF(<?= $event['id'] ?>)">
                                <i class="fas fa-file-signature"></i> Sign PDF
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon"><i class="fas fa-calendar-times"></i></div>
                    <h3>No Events Found</h3>
                    <p>There are currently no events to manage.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>
</div>

<!-- View Event Modal -->
<div id="eventModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3>Event Details</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer">
            <button class="btn-ghost" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<!-- Decline Event Modal -->
<div id="declineModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 460px;">
        <div class="modal-head">
            <h3>Decline Event</h3>
            <button class="modal-close" onclick="closeDeclineModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <label>Reason for declining</label>
                <textarea id="declineReason" class="reject-textarea"
                    placeholder="e.g. Missing venue details, conflicting schedule…"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-ghost" onclick="closeDeclineModal()">Cancel</button>
            <button class="btn-danger-solid" onclick="submitDecline()">
                <i class="fas fa-times"></i> Decline Event
            </button>
        </div>
    </div>
</div>

<div id="toast" class="toast">
    <span id="toastIcon"></span>
    <span id="toastMsg"></span>
</div>

<script>
    const eventsData = <?php echo json_encode($all_events); ?>;
    let currentDeclineId = null;

    document.getElementById('headerDate').textContent =
        new Date().toLocaleDateString('en-US', { weekday:'long', month:'long', day:'numeric', year:'numeric' });

    // ===== FILTER & SORT =====
    function filterAndSort() {
        const status = document.getElementById('statusFilter').value;
        const sort   = document.getElementById('sortFilter').value;
        const search = document.getElementById('searchInput').value.toLowerCase();
        const cards  = Array.from(document.querySelectorAll('.event-card'));
        const visible = [];

        cards.forEach(c => {
            const s     = c.dataset.status;
            const title = c.querySelector('.event-title').textContent.toLowerCase();
            const org   = c.querySelector('.event-org').textContent.toLowerCase();
            if (status !== 'all' && s !== status) { c.style.display = 'none'; return; }
            if (search && !title.includes(search) && !org.includes(search)) { c.style.display = 'none'; return; }
            c.style.display = 'flex';
            visible.push(c);
        });

        visible.sort((a, b) => {
            if (sort === 'oldest') return a.dataset.created - b.dataset.created;
            if (sort === 'date')   return a.dataset.date - b.dataset.date;
            return b.dataset.created - a.dataset.created;
        });

        const grid = document.getElementById('eventsGrid');
        visible.forEach(c => grid.appendChild(c));
    }

    document.getElementById('statusFilter').addEventListener('change', filterAndSort);
    document.getElementById('sortFilter').addEventListener('change', filterAndSort);
    document.getElementById('searchInput').addEventListener('input', filterAndSort);

    // ===== VIEW EVENT =====
    function viewEvent(id) {
        const ev = eventsData.find(e => e.id === id);
        if (!ev) return;
        const dateStr    = new Date(ev.event_date).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });
        const createdStr = new Date(ev.created_at).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });
        document.getElementById('modalBody').innerHTML = `
            <div class="modal-field"><label>Event Title</label><p>${ev.title}</p></div>
            <div class="modal-field"><label>Description</label><p>${ev.description || 'No description provided.'}</p></div>
            <div class="modal-grid">
                <div class="modal-field"><label>Event Date</label><p>${dateStr}</p></div>
                <div class="modal-field"><label>Location</label><p>${ev.location || 'TBD'}</p></div>
                <div class="modal-field"><label>Organization</label><p>${ev.org_name || 'N/A'}</p></div>
                <div class="modal-field"><label>Status</label><p>${ev.status.charAt(0).toUpperCase() + ev.status.slice(1)}</p></div>
                <div class="modal-field"><label>Participants</label><p>${ev.registered_count} / ${ev.max_participants || '∞'}</p></div>
                <div class="modal-field"><label>Submitted</label><p>${createdStr}</p></div>
            </div>
        `;
        document.getElementById('eventModal').classList.add('open');
    }

    function closeModal() { document.getElementById('eventModal').classList.remove('open'); }
    document.getElementById('eventModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

    // ===== APPROVE =====
    function approveEvent(id) {
        if (!confirm('Approve this event?')) return;
        const btn = document.querySelector(`[onclick="approveEvent(${id})"]`);
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Approving…'; }

        fetch('approve_event.php?action=approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                toast('Event approved!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                toast('Error: ' + (d.error || 'Unknown error'), 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Approve'; }
            }
        })
        .catch(() => {
            toast('Connection error.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Approve'; }
        });
    }

    // ===== DECLINE MODAL =====
    function openDeclineModal(id) {
        currentDeclineId = id;
        document.getElementById('declineReason').value = '';
        document.getElementById('declineReason').style.borderColor = '';
        document.getElementById('declineModal').classList.add('open');
        setTimeout(() => document.getElementById('declineReason').focus(), 60);
    }

    function closeDeclineModal() {
        document.getElementById('declineModal').classList.remove('open');
        currentDeclineId = null;
    }

    function submitDecline() {
        const reason = document.getElementById('declineReason').value.trim();
        if (!reason) {
            document.getElementById('declineReason').style.borderColor = '#ef4444';
            return;
        }

        const submitBtn = document.querySelector('#declineModal .btn-danger-solid');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Declining…';

        fetch('approve_event.php?action=reject', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: currentDeclineId, reason })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                toast('Event declined.', 'warn');
                closeDeclineModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                toast('Error: ' + (d.error || 'Unknown error'), 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-times"></i> Decline Event';
            }
        })
        .catch(() => {
            toast('Connection error.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-times"></i> Decline Event';
        });
    }

    document.getElementById('declineModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeclineModal();
    });
    document.getElementById('declineReason').addEventListener('input', function() {
        this.style.borderColor = '';
    });

    // ===== SIGN PDF =====
    function signEventPDF(id) { window.location.href = 'sign_event_pdf.php?id=' + id; }

    // ===== TOAST =====
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

    lucide.createIcons();

    // Sidebar toggle
    const sidebar = document.querySelector('.sidebar');
    const menuBtn = document.querySelector('.menu-toggle-desktop');
    menuBtn.addEventListener('click', () => sidebar.classList.toggle('collapsed'));
</script>
</body>
</html>