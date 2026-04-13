<?php
session_start();

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$created_by = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ??'ASAPHIL Representative';
$user_org  = $_SESSION['user_org']  ?? 'ASAPHIL - TIP Manila';

if ($created_by === 0) {
    header("Location: login.php?error=session_expired");
    exit;
}

// ── STATS ──────────────────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE created_by = ?");
$stmt->bind_param("i", $created_by);
$stmt->execute();
$total_events = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

if ($conn->query("SHOW TABLES LIKE 'event_registrations'")->num_rows > 0) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM event_registrations er
        INNER JOIN events e ON er.event_id = e.id
        WHERE e.created_by = ?
    ");
    $stmt->bind_param("i", $created_by);
    $stmt->execute();
    $total_registrants = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
} else {
    $total_registrants = 0;
}

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE created_by = ? AND status = 'ongoing'");
$stmt->bind_param("i", $created_by);
$stmt->execute();
$ongoing_events = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// ── RECENT EVENTS ──────────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT id, title, event_date, event_time, status, image_path
    FROM events
    WHERE created_by = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $created_by);
$stmt->execute();
$result = $stmt->get_result();
$recent_events = [];
while ($row = $result->fetch_assoc()) {
    $recent_events[] = $row;
}
$stmt->close();

// ── MESSAGES ───────────────────────────────────────────────────────────────
$success_message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1)          $success_message = "Event created successfully!";
    elseif ($_GET['success'] == 'deleted') $success_message = "Event deleted successfully!";
}
$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'unauthorized')    $error_message = "You don't have permission to delete that event.";
    elseif ($_GET['error'] == 'delete_failed') $error_message = "Failed to delete event. Please try again.";
    elseif ($_GET['error'] == 'invalid_id')    $error_message = "Invalid event ID.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Org Representative Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/OrgRep_db.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
    /* ════════════════════════════════════════════════
       REDESIGNED MAIN CONTENT + RIGHT SIDEBAR
       (font / size / colour unchanged from original)
    ════════════════════════════════════════════════ */

    /* ── Alert banners ─────────────────────────────── */
    .alert-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 20px;
        animation: slideDown 0.35s ease;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .alert-banner.success {
        background: #dcfce7;
        border-left: 4px solid #15803d;
        color: #15803d;
    }
    .alert-banner.error {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
        color: #dc2626;
    }

    /* ── Stats row ─────────────────────────────────── */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .stat-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 16px;
        background: #ffffff;
        padding: 20px 18px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        overflow: hidden;
        transition: box-shadow 0.2s, transform 0.2s;
    }
   
    .stat-card:hover {
        box-shadow: 0 6px 20px rgba(164,56,37,0.12);
        transform: translateY(-3px);
    }
    .stat-icon-box {
        width: 50px; height: 50px;
        background: #a43825;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(164,56,37,0.3);
    }
    .stat-icon-box i {
        font-size: 1.4rem;
        color: #ffffff;
    }
    .stat-text .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }
    .stat-text .stat-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: #6b7280;
        margin-top: 4px;
    }

    /* ── Create-event banner ───────────────────────── */
    .create-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        background: #ffffff;
        border: 2px dashed #e5e7eb;
        border-radius: 14px;
        padding: 22px 24px;
        margin-bottom: 20px;
        cursor: pointer;
        text-decoration: none;
        transition: border-color 0.2s, background 0.2s;
    }
    .create-banner:hover {
        border-color: #a43825;
        background: #fff8f7;
    }
    .create-banner-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .create-banner-icon {
        width: 52px; height: 52px;
        background: #fdf0ed;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .create-banner-icon i {
        font-size: 1.5rem;
        color: #a43825;
    }
    .create-banner-info h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 4px;
    }
    .create-banner-info p {
        font-size: 0.85rem;
        color: #6b7280;
        margin: 0;
    }
    .create-banner-btn {
        background: #a43825;
        color: #ffffff;
        border: none;
        padding: 11px 22px;
        border-radius: 9px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        font-family: inherit;
        box-shadow: 0 3px 10px rgba(164,56,37,0.25);
        transition: background 0.2s, transform 0.2s;
    }
    .create-banner-btn:hover {
        background: #8a2d1f;
        transform: translateY(-1px);
    }

    /* ── Event table card ──────────────────────────── */
    .table-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
    }
    .table-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        border-bottom: 1px solid #f3f4f6;
        background: #fafafa;
    }
    .table-card-head h2 {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }
    .table-manage-link {
        font-size: 0.8rem;
        font-weight: 700;
        color: #a43825;
        background: #fdf0ed;
        padding: 6px 14px;
        border-radius: 6px;
        text-decoration: none;
        transition: background 0.2s;
    }
    .table-manage-link:hover { background: #fce0db; }

    .evt-table {
        width: 100%;
        border-collapse: collapse;
    }
    .evt-table thead tr {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .evt-table th {
        padding: 11px 22px;
        text-align: left;
        font-size: 0.72rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .evt-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
    }
    .evt-table tbody tr:last-child { border-bottom: none; }
    .evt-table tbody tr:hover { background: #f9fafb; }
    .evt-table td {
        padding: 13px 22px;
        font-size: 0.875rem;
        color: #374151;
    }
    .evt-name-cell {
        display: flex;
        align-items: center;
        gap: 11px;
    }
    .evt-thumb {
        width: 38px; height: 38px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }
    .evt-thumb-placeholder {
        width: 38px; height: 38px;
        border-radius: 8px;
        background: #fdf0ed;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        color: #a43825;
        flex-shrink: 0;
    }
    .evt-name-text {
        font-weight: 600;
        color: #1f2937;
    }

    /* status badges */
    .s-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .s-badge.upcoming   { background: #dbeafe; color: #1e40af; }
    .s-badge.ongoing    { background: #dcfce7; color: #15803d; }
    .s-badge.completed  { background: #e5e7eb; color: #4b5563; }
    .s-badge.on-progress,
    .s-badge.on_progress { background: #fef3c7; color: #92400e; }

    /* action buttons */
    .tbl-act-row { display: flex; gap: 6px; }
    .tbl-act-btn {
        width: 32px; height: 32px;
        border-radius: 7px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #6b7280;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
    }
    .tbl-act-btn:hover { background: #f3f4f6; color: #374151; }
    .tbl-act-btn.btn-del { color: #ef4444; }
    .tbl-act-btn.btn-del:hover { background: #fee2e2; color: #dc2626; }

    .no-events-empty {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    .no-events-empty i {
        font-size: 3rem;
        color: #d1d5db;
        margin-bottom: 16px;
        display: block;
    }
    .no-events-empty h3 { font-size: 1.05rem; color: #6b7280; margin-bottom: 6px; }
    .no-events-empty p  { font-size: 0.875rem; }

    /* ════════════════════════════════════════════════
       RIGHT SIDEBAR — redesigned
    ════════════════════════════════════════════════ */
    .right-sidebar {
        width: var(--right-sidebar-width, 350px);
        background: #f9fafb;
        padding: 20px 16px;
        flex-shrink: 0;
        overflow-y: auto;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        margin: 16px 16px 16px 0;
        height: calc(100vh - 32px);
        position: sticky;
        top: 16px;
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .rs-page-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #a43825;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        font-family: 'Montserrat', sans-serif;
        flex-shrink: 0;
    }

    /* generic sidebar card */
    .rs-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .rs-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border-bottom: 1px solid #f3f4f6;
        background: #fafafa;
    }
    .rs-card-head span {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1f2937;
    }
    .rs-view-link {
        font-size: 0.7rem;
        font-weight: 700;
        color: #a43825;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .rs-view-link:hover { text-decoration: underline; }

    /* approvals */
    .approval-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
    }
    .approval-row:last-child { border-bottom: none; }
    .approval-row:hover { background: #f9fafb; }
    .appr-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .appr-dot.pending  { background: #f59e0b; }
    .appr-dot.approved { background: #10b981; }
    .appr-dot.declined { background: #ef4444; }
    .appr-details { flex: 1; min-width: 0; }
    .appr-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .appr-sub {
        font-size: 0.7rem;
        color: #9ca3af;
        margin-top: 1px;
    }
    .appr-pill {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 10px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .appr-pill.pending  { background: #fef3c7; color: #92400e; }
    .appr-pill.approved { background: #d1fae5; color: #065f46; }
    .appr-pill.declined { background: #fee2e2; color: #991b1b; }

    /* mini metric grid */
    .mini-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding: 12px 14px;
    }
    .mini-metric {
        background: #f9fafb;
        border: 1px solid #f3f4f6;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .mini-metric-val {
        font-size: 1.3rem;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }
    .mini-metric-lbl {
        font-size: 0.7rem;
        font-weight: 500;
        color: #6b7280;
        margin-top: 3px;
    }

    /* recent registrations */
    .reg-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
    }
    .reg-row:last-child { border-bottom: none; }
    .reg-row:hover { background: #f9fafb; }
    .reg-avatar {
        width: 34px; height: 34px;
        border-radius: 8px;
        background: #fdf0ed;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        color: #a43825;
        flex-shrink: 0;
    }
    .reg-info { flex: 1; min-width: 0; }
    .reg-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1f2937;
    }
    .reg-evt {
        font-size: 0.7rem;
        color: #9ca3af;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 1px;
    }
    .reg-time {
        font-size: 0.7rem;
        color: #9ca3af;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* loading placeholder */
    .rs-loading {
        text-align: center;
        color: #9ca3af;
        padding: 16px;
        font-size: 0.8rem;
    }

    /* ── Responsive ────────────────────────────────── */
    @media (max-width: 768px) {
        .stats-row { grid-template-columns: 1fr; }
        .create-banner { flex-direction: column; align-items: flex-start; }
        .create-banner-btn { width: 100%; text-align: center; }
    }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">

    <!-- ── LEFT SIDEBAR (unchanged) ───────────────── -->
    <aside class="sidebar" id="left-sidebar">

        <div class="sidebar-top-icons">
            <button class="menu-toggle-desktop" id="collapse-toggle" title="Collapse sidebar">
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
            <a href="OrgRep_db.php" class="nav-link active" title="Org Dashboard">
                <i data-lucide="layout-dashboard"></i>
                <span class="link-text">Dashboard</span>
            </a>
            <a href="create_event.php" class="nav-link" title="Create New Event">
                <i data-lucide="plus-circle"></i>
                <span class="link-text">Create Event</span>
            </a>
            <a href="my_events.php" class="nav-link" title="My Events List & Calendar">
                <i data-lucide="calendar-days"></i>
                <span class="link-text">My Events</span>
            </a>
            <a href="registration_reports.php" class="nav-link" title="Registration Reports & Export">
                <i data-lucide="file-text"></i>
                <span class="link-text">Registration Reports</span>
            </a>
            <hr class="separator">
            <a href="my_organization.php" class="nav-link" title="About My Organization">
                <i data-lucide="info"></i>
                <span class="link-text">My Organization</span>
            </a>
            <a href="helpcenter_org.php" class="nav-link" title="Help Center (FAQ)">
                <i data-lucide="life-buoy"></i>
                <span class="link-text">Help Center</span>
            </a>
            <hr class="separator">
            <a href="settings_org.php" class="nav-link" title="Settings">
                <i data-lucide="settings"></i>
                <span class="link-text">Settings</span>
            </a>
            <a href="login.php?logout=true" class="nav-link logout-link" title="Sign out">
                <i data-lucide="log-out"></i>
                <span class="link-text">Logout</span>
            </a>
        </div>

        <div class="user-profile">
            <img src="https://placehold.co/40x40/A43825/white?text=<?= substr($user_name, 0, 1) ?>" alt="Avatar" loading="lazy">
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($user_name) ?></div>
                <div class="role"><?= htmlspecialchars($user_org) ?></div>
            </div>
        </div>

    </aside>

    <!-- ── MAIN CONTENT ────────────────────────────── -->
    <main class="content-area" id="content-area">

        <header class="content-header">
            <h2>Dashboard</h2>
        </header>

        <section class="main-content-layout">

            <!-- Alert banners -->
            <?php if ($success_message): ?>
                <div class="alert-banner success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($success_message) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert-banner error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <!-- Stats row -->
            <div class="stats-row">
                <div class="stat-card">
    <div class="stat-card-top">
        <div class="stat-icon-wrapper">
            <i class="fas fa-calendar-check"></i>
        </div>
    </div>
    <div class="stat-content">
        <div class="stat-value"><?= $total_events ?></div>
        <div class="stat-label">Total Events Created</div>
    </div>
</div>

<div class="stat-card">
    <div class="stat-card-top">
        <div class="stat-icon-wrapper">
            <i class="fas fa-users"></i>
        </div>
    </div>
    <div class="stat-content">
        <div class="stat-value"><?= $total_registrants ?></div>
        <div class="stat-label">Registered Students</div>
    </div>
</div>

<div class="stat-card">
    <div class="stat-card-top">
        <div class="stat-icon-wrapper">
            <i class="fas fa-bullhorn"></i>
        </div>
    </div>
    <div class="stat-content">
        <div class="stat-value"><?= $ongoing_events ?></div>
        <div class="stat-label">Ongoing Events</div>
    </div>
</div>
            </div>

            <!-- Create event banner -->
            <a href="create_event.php" class="create-banner">
                <div class="create-banner-left">
                    
                    <div class="create-banner-info">
                        <h3>Create a New Event</h3>
                        <p>Publish a new event for students to register and join.</p>
                    </div>
                </div>
                <button class="create-banner-btn" type="button"
                        onclick="event.preventDefault(); window.location.href='create_event.php'">
                    <i class="fas fa-plus"></i> Create New Event
                </button>
            </a>

            <!-- Event table -->
            <div class="table-card">
                <div class="table-card-head">
                    <h2>Event Management</h2>
                    <a href="event_management.php" class="table-manage-link">Manage All →</a>
                </div>

                <?php if (count($recent_events) > 0): ?>
                <table class="evt-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_events as $ev): ?>
                        <tr>
                            <td>
                                <div class="evt-name-cell">
                                    <?php if (!empty($ev['image_path']) && file_exists($ev['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($ev['image_path']) ?>"
                                             alt="<?= htmlspecialchars($ev['title']) ?>"
                                             class="evt-thumb">
                                    <?php else: ?>
                                        <div class="evt-thumb-placeholder">
                                            <?= strtoupper(substr($ev['title'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="evt-name-text"><?= htmlspecialchars($ev['title']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="s-badge <?= strtolower(str_replace(' ', '-', $ev['status'])) ?>">
                                    <?= ucfirst($ev['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="tbl-act-row">
                                    <a href="create_event.php?editId=<?= $ev['id'] ?>"
                                       class="tbl-act-btn" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_event.php?id=<?= $ev['id'] ?>"
                                       class="tbl-act-btn btn-del" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this event?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-events-empty">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No events yet</h3>
                    <p>Start creating your first event to engage with students.</p>
                </div>
                <?php endif; ?>
            </div>

        </section>
    </main>

  <!-- ── RIGHT SIDEBAR ───────────────────────────── -->
<aside class="right-sidebar" id="right-sidebar">

    <div class="rs-page-title">Organization Activity</div>

    <!-- My Event Status (replaces Event Approvals) -->
    <div class="rs-card">
        <div class="rs-card-head">
            <span>My Event Status</span>
            <a href="my_events.php" class="rs-view-link">View all</a>
        </div>
        <div id="approvals-container">
            <?php
            $stmt = $conn->prepare("
                SELECT title, status 
                FROM events 
                WHERE created_by = ? 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->bind_param("i", $created_by);
            $stmt->execute();
            $myEvents = $stmt->get_result();

            if ($myEvents->num_rows === 0):
            ?>
                <div class="rs-loading">No events submitted yet</div>
            <?php else: while($ev = $myEvents->fetch_assoc()): 
                $dotClass = match($ev['status']) {
                    'approved' => 'approved',
                    'rejected' => 'declined',
                    default    => 'pending'
                };
                $pillClass = $dotClass;
                $pillLabel = match($ev['status']) {
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    default    => 'Pending'
                };
            ?>
                <div class="approval-row">
                    <span class="appr-dot <?= $dotClass ?>"></span>
                    <div class="appr-details">
                        <div class="appr-name"><?= htmlspecialchars($ev['title']) ?></div>
                        <div class="appr-sub">Submitted for review</div>
                    </div>
                    <span class="appr-pill <?= $pillClass ?>"><?= $pillLabel ?></span>
                </div>
            <?php endwhile; endif; $stmt->close(); ?>
        </div>
    </div>

    <!-- Registrations Overview -->
    <?php
    // Get pending count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE created_by = ? AND status = 'pending'");
    $stmt->bind_param("i", $created_by);
    $stmt->execute();
    $pending_count = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
    ?>
    <div class="rs-card">
        <div class="rs-card-head">
            <span>Registrations Overview</span>
            <a href="registration_reports.php" class="rs-view-link">View all</a>
        </div>
        <div class="mini-metrics">
            <div class="mini-metric">
                <div class="mini-metric-val"><?= $total_registrants ?></div>
                <div class="mini-metric-lbl">Total Registered</div>
            </div>
            <div class="mini-metric">
                <div class="mini-metric-val"><?= $ongoing_events ?></div>
                <div class="mini-metric-lbl">Active Events</div>
            </div>
            <div class="mini-metric">
                <div class="mini-metric-val"><?= $total_events ?></div>
                <div class="mini-metric-lbl">Total Events</div>
            </div>
            <div class="mini-metric">
                <div class="mini-metric-val"><?= $pending_count ?></div>
                <div class="mini-metric-lbl">Pending Reviews</div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations -->
    <div class="rs-card">
        <div class="rs-card-head">
            <span>Recent Registrations</span>
            <a href="registration_reports.php" class="rs-view-link">View all</a>
        </div>
        <div id="recent-registrations-list">
            <?php
           $stmt = $conn->prepare("
    SELECT u.fullname, e.title, er.registration_date as registered_at
    FROM event_registrations er
    INNER JOIN users u ON er.user_id = u.id
    INNER JOIN events e ON er.event_id = e.id
    WHERE e.created_by = ?
    ORDER BY er.registration_date DESC
    LIMIT 5
");
            $stmt->bind_param("i", $created_by);
            $stmt->execute();
            $regs = $stmt->get_result();

            if ($regs->num_rows === 0):
            ?>
                <div class="rs-loading">No registrations yet</div>
            <?php else: while($reg = $regs->fetch_assoc()): ?>
                <div class="reg-row">
                    <div class="reg-avatar">
                        <?= strtoupper(substr($reg['fullname'], 0, 1)) ?>
                    </div>
                    <div class="reg-info">
                        <div class="reg-name"><?= htmlspecialchars($reg['fullname']) ?></div>
                        <div class="reg-evt"><?= htmlspecialchars($reg['title']) ?></div>
                    </div>
                    <div class="reg-time">
                        <?= date('M d', strtotime($reg['registered_at'])) ?>
                    </div>
                </div>
            <?php endwhile; endif; $stmt->close(); ?>
        </div>
    </div>

</aside>
    <div class="overlay" id="mobile-overlay"></div>

</div>

<script src="../JavaScript/OrgRep_db.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>