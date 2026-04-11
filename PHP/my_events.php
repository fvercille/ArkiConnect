<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';
session_start();

$user_role_lower = strtolower($_SESSION['role'] ?? '');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'org_rep') {
    header("Location: login.php?error=unauthorized_script_access");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'ASAPHIL Representative';
$user_org  = $_SESSION['user_org']  ?? 'ASAPHIL - TIP Manila';
$user_id   = $_SESSION['user_id']   ?? 0;

// ── Fetch this org rep's approved/upcoming/ongoing events ──
$events = [];
try {
    $query = "
        SELECT
            id,
            title,
            description,
            event_date,
            event_time,
            location,
            status
        FROM events
        WHERE created_by = ?
          AND status IN ('approved', 'upcoming', 'ongoing')
        ORDER BY event_date ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $ts = strtotime($row['event_date']);
        $events[] = [
            'id'          => (int)$row['id'],
            'title'       => $row['title'],
            'description' => $row['description'] ?? '',
            'date'        => (int)date('j', $ts),           // day number  e.g. 5
            'month'       => date('M', $ts),                 // e.g. "Aug"
            'full_date'   => date('Y-m-d', $ts),             // for JS calendar key
            'time'        => !empty($row['event_time']) ? date('g:i A', strtotime($row['event_time'])) : 'TBD',
            'location'    => $row['location'] ?? 'TBD',
            'status'      => $row['status'],
        ];
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching org events: " . $e->getMessage());
    $events = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - Org Representative</title>
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
           LEFT SIDEBAR
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

        .nav-link::before { display: none; }
        .nav-link i:first-child, .nav-link svg { width: 20px; text-align: center; font-size: 1rem; flex-shrink: 0; }
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
        .sidebar.collapsed .nav-link i:first-child,
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
        .header-left h1 { font-size: 1.9rem; font-weight: 700; color: #fff; letter-spacing: -0.03em; line-height: 1.1; }
        .header-left p { font-size: 0.82rem; color: rgba(255,255,255,0.65); font-weight: 500; }

        .time-display {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 18px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            backdrop-filter: blur(8px);
            position: relative; z-index: 1;
        }

        .time-display i { color: rgba(255,255,255,0.8); font-size: 1rem; }
        .time-text { font-size: 1rem; font-weight: 700; color: #fff; letter-spacing: 0.02em; }

        /* Main body grid */
        .main-body {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 18px;
            flex: 1;
            min-height: 0;
        }

        /* LEFT: Calendar */
        .calendar-section {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }

        .calendar-header {
            background: linear-gradient(110deg, var(--primary) 0%, var(--primary-dk) 100%);
            display: flex; justify-content: space-between; align-items: center;
            padding: 18px 24px; flex-shrink: 0;
        }

        .calendar-header-left { display: flex; flex-direction: column; gap: 2px; }
        .cal-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.55); }
        .calendar-title { font-size: 1.3rem; font-weight: 700; color: #fff; letter-spacing: -0.02em; }

        .calendar-nav { display: flex; gap: 8px; }
        .calendar-nav button {
            width: 34px; height: 34px;
            border: 1.5px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.18s ease; color: #fff; font-size: 0.78rem;
        }
        .calendar-nav button:hover { background: rgba(255,255,255,0.25); border-color: rgba(255,255,255,0.6); transform: scale(1.08); }

        .calendar-day-headers {
            display: grid; grid-template-columns: repeat(7, 1fr);
            background: #fafafa; border-bottom: 1px solid #f0f0f0; flex-shrink: 0;
        }
        .day-header {
            padding: 10px 0; text-align: center;
            font-weight: 700; font-size: 0.68rem;
            color: var(--primary); text-transform: uppercase; letter-spacing: 0.07em;
        }

        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); flex: 1; }

        .calendar-day {
            background: white; padding: 10px 12px;
            min-height: 90px; cursor: pointer;
            transition: background 0.15s ease;
            position: relative; display: flex; flex-direction: column;
            border-right: 1px solid #f5f5f5; border-bottom: 1px solid #f5f5f5;
        }

        .calendar-day:hover:not(.other-month) { background: #fff8f6; }
        .calendar-day.other-month { background: #fafafa; }
        .calendar-day.other-month .day-number { color: #d1d5db; }

        .day-number {
            font-size: 0.78rem; font-weight: 600; color: #374151;
            width: 26px; height: 26px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; flex-shrink: 0; transition: all 0.15s ease;
        }

        .calendar-day.today { background: #fff8f6; }
        .calendar-day.today .day-number { background: var(--primary); color: #fff; box-shadow: 0 2px 8px rgba(164,56,37,0.35); }

        .event-name {
            font-size: 0.66rem; color: white; font-weight: 600;
            background: var(--primary); padding: 2px 7px;
            border-radius: 4px; margin-top: 5px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            cursor: pointer; transition: all 0.15s ease;
            box-shadow: 0 1px 4px rgba(164,56,37,0.25);
        }
        .event-name:hover { background: var(--primary-dk); box-shadow: 0 3px 8px rgba(164,56,37,0.4); }

        /* RIGHT: Upcoming Events panel */
        .upcoming-panel {
            background: #fff;
            border-radius: 18px;
            padding: 20px 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-self: start;
            position: sticky;
            top: 0;
        }

        .upcoming-panel-header {
            display: flex; align-items: center; justify-content: space-between;
            padding-bottom: 10px; border-bottom: 1px solid #f3f4f6;
        }
        .upcoming-panel-header h2 {
            font-size: 0.82rem; font-weight: 700; color: #1f2937;
            text-transform: uppercase; letter-spacing: 0.07em;
            display: flex; align-items: center; gap: 7px;
        }
        .upcoming-panel-header h2 i { color: var(--primary); font-size: 0.8rem; }

        .strip-count {
            font-size: 0.7rem; font-weight: 700;
            background: #fdf0ed; color: var(--primary);
            padding: 2px 9px; border-radius: 20px;
        }

        .event-card {
            background: #fff; border-radius: 12px; padding: 13px 14px;
            cursor: pointer; transition: all 0.2s ease;
            border: 1.5px solid #f0f0f0;
            display: flex; gap: 12px; align-items: flex-start;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            position: relative; overflow: hidden;
        }
        .event-card::before {
            content: ''; position: absolute;
            left: 0; top: 0; bottom: 0; width: 4px;
            background: var(--primary); border-radius: 4px 0 0 4px;
        }
        .event-card:hover { box-shadow: 0 8px 20px rgba(164,56,37,0.12); transform: translateY(-2px); border-color: #f5c5bc; }
        .event-card.active { border-color: var(--primary); background: #fff8f7; }

        .event-card-date {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-width: 44px; height: 50px;
            background: #fdf0ed; border-radius: 9px; flex-shrink: 0;
        }
        .event-card-date .day-num { font-size: 1.15rem; font-weight: 700; color: var(--primary); line-height: 1; }
        .event-card-date .month-abbr { font-size: 0.6rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; }

        .event-card-body { flex: 1; min-width: 0; }
        .event-card-title { font-size: 0.83rem; font-weight: 700; color: #1f2937; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .event-card-meta { display: flex; flex-direction: column; gap: 3px; }
        .event-card-meta span { font-size: 0.7rem; color: var(--muted); display: flex; align-items: center; gap: 5px; }
        .event-card-meta i { color: var(--primary); font-size: 0.62rem; width: 10px; }

        /* No events state */
        .no-events-msg {
            text-align: center; padding: 30px 10px;
            color: var(--muted); font-size: 0.8rem;
        }
        .no-events-msg i { font-size: 2rem; display: block; margin-bottom: 8px; color: #e5e7eb; }

        /* Modal */
        .event-modal {
            display: none; position: fixed; z-index: 9999;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(15,15,20,0.55);
            backdrop-filter: blur(4px);
            justify-content: center; align-items: center;
        }
        .event-modal.show { display: flex; }

        .event-modal-content {
            background: white; border-radius: 20px;
            width: 90%; max-width: 460px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.18);
            animation: fadeUp 0.28s ease;
            position: relative; overflow: hidden;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-top-bar {
            background: linear-gradient(110deg, var(--primary) 0%, var(--primary-dk) 100%);
            padding: 22px 24px 18px; position: relative;
        }
        .event-modal-close {
            position: absolute; right: 16px; top: 16px;
            width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.15); border-radius: 50%;
            font-size: 1rem; cursor: pointer; color: #fff; transition: all 0.15s ease;
        }
        .event-modal-close:hover { background: rgba(255,255,255,0.3); transform: scale(1.1); }
        .modal-top-bar h2 { color: #fff; font-size: 1.1rem; font-weight: 700; margin: 0; padding-right: 36px; }

        .modal-body { padding: 18px 24px; }
        .modal-meta-row {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 0; border-bottom: 1px solid #f3f4f6;
            font-size: 0.83rem; color: #374151;
        }
        .modal-meta-row:last-of-type { border-bottom: none; }
        .modal-meta-icon {
            width: 30px; height: 30px; background: #fdf0ed;
            border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .modal-meta-icon i { color: var(--primary); font-size: 0.75rem; }

        .modal-actions {
            padding: 14px 24px; display: flex; gap: 10px;
            justify-content: flex-end; border-top: 1px solid #f3f4f6;
        }
        .btn {
            padding: 9px 20px; border-radius: 9px;
            border: 1.5px solid #e5e7eb; background: white;
            color: #374151; text-decoration: none; cursor: pointer;
            font-size: 0.83rem; font-weight: 600; font-family: inherit;
            transition: all 0.15s ease;
        }
        .btn:hover { background: #f9fafb; }
        .btn.primary {
            background: var(--primary); color: #fff;
            border-color: var(--primary);
            box-shadow: 0 3px 10px rgba(164,56,37,0.28);
        }
        .btn.primary:hover { background: var(--primary-dk); }

        @media (max-width: 1024px) {
            .main-body { grid-template-columns: 1fr; }
            .upcoming-panel { position: static; }
        }

        @media (max-width: 768px) {
            .content-area { padding: 14px; }
            .calendar-day { min-height: 70px; padding: 8px; }
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">

    <!-- LEFT SIDEBAR -->
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
            <a href="my_events.php" class="nav-link active" title="My Events">
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
            <img src="https://placehold.co/40x40/A43825/white?text=<?= htmlspecialchars(substr($user_name, 0, 1)) ?>" alt="Avatar" loading="lazy">
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($user_name) ?></div>
                <div class="role"><?= htmlspecialchars($user_org) ?></div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content-area">

        <!-- Header banner -->
        <header class="content-header">
            <div class="header-left">
                <h1>My Events</h1>
                <p>Manage and view all your organization's events</p>
            </div>
            <div class="time-display">
                <i class="fas fa-clock"></i>
                <span class="time-text">--:-- --</span>
            </div>
        </header>

        <!-- Main body: calendar LEFT, events RIGHT -->
        <div class="main-body">

            <!-- LEFT: Calendar -->
            <div class="calendar-section">
                <div class="calendar-header">
                    <div class="calendar-header-left">
                        <span class="cal-label">Schedule</span>
                        <h2 class="calendar-title" id="calendar-month-display">Loading...</h2>
                    </div>
                    <div class="calendar-nav">
                        <button id="prev-month-btn"><i class="fas fa-chevron-left"></i></button>
                        <button id="next-month-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="calendar-day-headers">
                    <div class="day-header">Mon</div>
                    <div class="day-header">Tue</div>
                    <div class="day-header">Wed</div>
                    <div class="day-header">Thu</div>
                    <div class="day-header">Fri</div>
                    <div class="day-header">Sat</div>
                    <div class="day-header">Sun</div>
                </div>
                <div class="calendar-grid" id="calendar-grid"></div>
            </div>

            <!-- RIGHT: Upcoming Events -->
            <div class="upcoming-panel">
                <div class="upcoming-panel-header">
                    <h2><i class="fas fa-bell"></i> Upcoming Events</h2>
                    <span class="strip-count" id="event-count"><?= count($events) ?> events</span>
                </div>

                <?php if (empty($events)): ?>
                    <div class="no-events-msg">
                        <i class="fas fa-calendar-times"></i>
                        No approved events yet.<br>Events approved by the admin will appear here.
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                    <div class="event-card"
                         data-event-id="<?= htmlspecialchars($event['id']) ?>"
                         data-event-title="<?= htmlspecialchars($event['title']) ?>"
                         data-event-date="<?= htmlspecialchars($event['full_date']) ?>"
                         data-event-date-display="<?= htmlspecialchars($event['month']) ?> <?= $event['date'] ?>"
                         data-event-time="<?= htmlspecialchars($event['time']) ?>"
                         data-event-location="<?= htmlspecialchars($event['location']) ?>"
                         data-event-description="<?= htmlspecialchars($event['description']) ?>">
                        <div class="event-card-date">
                            <span class="day-num"><?= $event['date'] ?></span>
                            <span class="month-abbr"><?= $event['month'] ?></span>
                        </div>
                        <div class="event-card-body">
                            <div class="event-card-title"><?= htmlspecialchars($event['title']) ?></div>
                            <div class="event-card-meta">
                                <span><i class="fas fa-clock"></i><?= htmlspecialchars($event['time']) ?></span>
                                <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($event['location']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<!-- EVENT MODAL -->
<div id="event-modal" class="event-modal">
    <div class="event-modal-content">
        <div class="modal-top-bar">
            <span class="event-modal-close">&times;</span>
            <h2 id="modal-event-title">Event Title</h2>
        </div>
        <div class="modal-body">
            <div class="modal-meta-row">
                <div class="modal-meta-icon"><i class="fas fa-calendar-alt"></i></div>
                <span id="modal-event-date"></span>
            </div>
            <div class="modal-meta-row">
                <div class="modal-meta-icon"><i class="fas fa-clock"></i></div>
                <span id="modal-event-time"></span>
            </div>
            <div class="modal-meta-row">
                <div class="modal-meta-icon"><i class="fas fa-map-marker-alt"></i></div>
                <span id="modal-event-location"></span>
            </div>
            <div class="modal-meta-row">
                <div class="modal-meta-icon"><i class="fas fa-align-left"></i></div>
                <span id="modal-event-description"></span>
            </div>
        </div>
        <div class="modal-actions">
            <a id="modal-view-link" class="btn primary" href="#" role="button">View / Edit</a>
            <button class="btn event-modal-close-btn">Close</button>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

// ── Sidebar toggle ──
document.getElementById('collapse-toggle').addEventListener('click', function () {
    document.getElementById('left-sidebar').classList.toggle('collapsed');
});

// ── PHP events → JS ──
// eventsData keyed by FULL DATE string "YYYY-MM-DD" for reliable calendar matching
const phpEvents = <?php echo json_encode($events); ?>;

const eventsData = {};
phpEvents.forEach(ev => {
    eventsData[ev.full_date] = {
        id:          ev.id,
        title:       ev.title,
        location:    ev.location,
        time:        ev.time,
        description: ev.description,
        full_date:   ev.full_date,
    };
});

// ── Calendar ──
const _today = new Date();
let currentMonth = _today.getMonth();
let currentYear  = _today.getFullYear();

const MONTH_NAMES = ['January','February','March','April','May','June',
                     'July','August','September','October','November','December'];

function pad2(n) { return String(n).padStart(2, '0'); }

function generateCalendar(month, year) {
    const grid    = document.getElementById('calendar-grid');
    const display = document.getElementById('calendar-month-display');

    display.textContent = `${MONTH_NAMES[month]} ${year}`;
    grid.innerHTML = '';

    const firstDay    = new Date(year, month, 1).getDay();   // 0=Sun
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrev  = new Date(year, month, 0).getDate();
    const startDay    = firstDay === 0 ? 6 : firstDay - 1;   // Mon-based

    const todayStr = `${_today.getFullYear()}-${pad2(_today.getMonth()+1)}-${pad2(_today.getDate())}`;

    // Leading days from prev month
    for (let i = startDay - 1; i >= 0; i--) {
        const cell = document.createElement('div');
        cell.className = 'calendar-day other-month';
        cell.innerHTML = `<div class="day-number">${daysInPrev - i}</div>`;
        grid.appendChild(cell);
    }

    // Current month days
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${pad2(month + 1)}-${pad2(day)}`;
        const cell = document.createElement('div');
        cell.className = 'calendar-day';
        if (dateStr === todayStr) cell.classList.add('today');

        const num = document.createElement('div');
        num.className = 'day-number';
        num.textContent = day;
        cell.appendChild(num);

        // ── Attach event tag if this date has an event ──
        if (eventsData[dateStr]) {
            const ev  = eventsData[dateStr];
            const tag = document.createElement('div');
            tag.className   = 'event-name';
            tag.textContent = ev.title;
            tag.title       = ev.title;
            tag.addEventListener('click', e => {
                e.stopPropagation();
                openModal(ev);
            });
            cell.appendChild(tag);
        }

        grid.appendChild(cell);
    }

    // Trailing days
    const remaining = 42 - grid.children.length;
    for (let day = 1; day <= remaining; day++) {
        const cell = document.createElement('div');
        cell.className = 'calendar-day other-month';
        cell.innerHTML = `<div class="day-number">${day}</div>`;
        grid.appendChild(cell);
    }
}

// ── Modal ──
function openModal(ev) {
    document.getElementById('modal-event-title').textContent       = ev.title;
    document.getElementById('modal-event-date').textContent        = ev.full_date;
    document.getElementById('modal-event-time').textContent        = ev.time;
    document.getElementById('modal-event-location').textContent    = ev.location;
    document.getElementById('modal-event-description').textContent = ev.description || 'No description provided.';

    const link = document.getElementById('modal-view-link');
    link.href = ev.id
        ? `event_detailsedit.php?id=${encodeURIComponent(ev.id)}`
        : `event_detailsedit.php?title=${encodeURIComponent(ev.title)}`;

    document.getElementById('event-modal').classList.add('show');
}

function closeModal() {
    document.getElementById('event-modal').classList.remove('show');
}

document.querySelector('.event-modal-close').addEventListener('click', closeModal);
document.querySelector('.event-modal-close-btn').addEventListener('click', closeModal);
document.getElementById('event-modal').addEventListener('click', e => {
    if (e.target.id === 'event-modal') closeModal();
});

// ── Event cards click ──
document.querySelectorAll('.event-card').forEach(card => {
    card.addEventListener('click', function () {
        document.querySelectorAll('.event-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        openModal({
            id:          this.dataset.eventId,
            title:       this.dataset.eventTitle,
            full_date:   this.dataset.eventDate,
            time:        this.dataset.eventTime,
            location:    this.dataset.eventLocation,
            description: this.dataset.eventDescription,
        });
    });
});

// ── Calendar nav ──
document.getElementById('prev-month-btn').addEventListener('click', () => {
    if (--currentMonth < 0) { currentMonth = 11; currentYear--; }
    generateCalendar(currentMonth, currentYear);
});
document.getElementById('next-month-btn').addEventListener('click', () => {
    if (++currentMonth > 11) { currentMonth = 0; currentYear++; }
    generateCalendar(currentMonth, currentYear);
});

// ── Live clock ──
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

// ── Init calendar ──
generateCalendar(currentMonth, currentYear);
</script>

</body>
</html>