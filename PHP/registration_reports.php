<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

 
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'org_rep') {
    header("Location: login.php?error=unauthorized_script_access");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'ASAPHIL Representative';
$user_org  = $_SESSION['user_org']  ?? 'ASAPHIL - TIP Manila';
$user_id   = $_SESSION['user_id'] ?? 0;

$reports = [];
try {
    $query = "
        SELECT 
            e.id,
            e.title as event,
            e.event_date as date,
            e.status,
            (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed') as registered
        FROM events e
        WHERE e.created_by = ?
        ORDER BY e.event_date DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reports[] = [
            'id'         => (int)$row['id'],
            'event'      => $row['event'],
            'date'       => $row['date'],
            'registered' => (int)$row['registered'],
            'status'     => ucfirst($row['status'])
        ];
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $reports = [];
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Reports - Org Representative</title>
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
        .header-left h1 { font-size: 1.9rem; font-weight: 700; color: #fff; letter-spacing: -0.03em; line-height: 1.1; margin: 0; }
        .header-left p { font-size: 0.82rem; color: rgba(255,255,255,0.65); font-weight: 500; margin: 0; }

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

        /* ══════════════════════════════
           CONTENT
        ══════════════════════════════ */
        .content-inner {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .filters {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: nowrap;
            background: #fff;
            padding: 16px 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }


        .filters input[type="date"] {
            padding: 9px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 0.88rem;
            font-family: 'Montserrat', sans-serif;
            color: var(--text);
            width: auto;          /* add this */
    min-width: 140px;
        }

        .filters select {
    padding: 9px 36px 9px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%23888' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 10px center;
    background-size: 16px;
    font-size: 0.88rem;
    font-family: 'Montserrat', sans-serif;
    color: var(--text);
    width: auto;
    min-width: 150px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
}

        #applyFilterBtn {
            padding: 9px 18px;
            background-color: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.88rem;
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
            transition: background 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 10px rgba(164,56,37,0.28);
        }

        #applyFilterBtn:hover { background-color: var(--primary-dk); }

        .report-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 20px;
        }

        .report-table-wrap { overflow: auto; }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.report-table th,
        table.report-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px dashed #eef1f6;
            font-size: 0.88rem;
        }

        table.report-table th {
            color: var(--muted);
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .report-status-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 20px;
        }

        .report-status-card h2 {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .report-status-card h2 i { color: var(--primary); font-size: 0.8rem; }

        .btn {
            padding: 9px 16px;
            border-radius: 9px;
            border: 1.5px solid var(--border);
            background: white;
            color: #374151;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.83rem;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover { background: #f9fafb; }
        .btn.view i { color: var(--primary); }
        .btn.view:hover { background: #fdf0ed; border-color: #f5c5bc; }

        #refreshBtn { margin-left: auto; }
        #refreshBtn i { color: var(--primary); }

.no-events {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    gap: 10px;
}

.no-events h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.no-events p {
    font-size: 0.85rem;
    color: var(--muted);
    margin: 0;
}

.no-events a {
    margin-top: 10px;
    padding: 10px 24px;
    background: var(--primary);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.85rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.15s ease;
    box-shadow: 0 3px 10px rgba(164,56,37,0.25);
}

.no-events a:hover { background: var(--primary-dk); }

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
        }

        @media (max-width: 768px) {
            .content-area { padding: 14px; }
            .filters { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">

    <!-- LEFT SIDEBAR — exact copy from my_events.php -->
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
            <a href="registration_reports.php" class="nav-link active" title="Registration Reports">
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
            <img src="https://placehold.co/40x40/A43825/white?text=<?= substr($user_name, 0, 1) ?>" alt="Avatar" loading="lazy">
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
                <h1>Registration Reports</h1>
                <p>View and export your organization's event registrations</p>
            </div>
            <div class="time-display">
                <i class="fas fa-clock"></i>
                <span class="time-text"></span>
            </div>
        </header>

        <div class="content-inner">

            <!-- Filters -->
            <div class="filters">
                <select id="eventFilter">
                    <option value="">All events</option>
                    <?php foreach($reports as $r): ?>
                        <option value="<?= h($r['id']) ?>"><?= h($r['event']) ?></option>
                    <?php endforeach; ?>
                </select>

                <div style="display:flex; gap:8px;">
                    <input type="date" id="fromDate">
                    <input type="date" id="toDate">
                </div>

                <button id="applyFilterBtn">Apply</button>

                <button class="btn" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <?php if (empty($reports)): ?>
                <div class="report-card">
    <div class="no-events">
        <h3>No Events Created Yet</h3>
        <p>Create your first event to see registration reports here.</p>
        <a href="create_event.php">
            <i class="fas fa-plus-circle"></i> Create Event
        </a>
    </div>
</div>
            <?php else: ?>

                <!-- Registration Table -->
                <div class="report-card">
                    <div class="report-table-wrap">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Registrants</th>
                                    <th style="text-align:right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <?php foreach($reports as $r): ?>
                                    <tr data-event-id="<?= h($r['id']) ?>" data-date="<?= h($r['date']) ?>">
                                        <td><?= h($r['event']) ?></td>
                                        <td><?= date("F j, Y", strtotime($r['date'])) ?></td>
                                        <td><?= h($r['registered']) ?></td>
                                        <td style="text-align:right">
                                            <button class="btn view" onclick="viewEventRegistrants(<?= h($r['id']) ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Event Status Overview -->
                <div class="report-status-card">
                    <h2><i class="fas fa-chart-bar"></i> Event Status Overview</h2>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reports as $r):
                                $status = strtolower($r['status']);
                                $color  = $status === 'upcoming'  ? '#3b82f6'
                                        : ($status === 'ongoing'   ? '#10b981'
                                        : ($status === 'completed' ? '#6b7280'
                                        :                            '#ef4444'));
                            ?>
                                <tr>
                                    <td><?= h($r['event']) ?></td>
                                    <td><?= date("F j, Y", strtotime($r['date'])) ?></td>
                                    <td>
                                        <span style="font-weight:700; color:<?= $color ?>;">
                                            <?= h($r['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
    </main>

</div>

<script>
    function viewEventRegistrants(eventId) {
        alert('View registrants for event ID: ' + eventId);
    }

    // Lucide icons — same as my_events.php
    lucide.createIcons();

    document.getElementById('collapse-toggle').addEventListener('click', function () {
        document.getElementById('left-sidebar').classList.toggle('collapsed');
    });

    document.getElementById('refreshBtn').addEventListener('click', () => {
        location.reload();
    });

    document.getElementById('applyFilterBtn').addEventListener('click', () => {
        const eventId  = document.getElementById('eventFilter').value;
        const fromDate = document.getElementById('fromDate').value;
        const toDate   = document.getElementById('toDate').value;
        const rows     = document.querySelectorAll('#reportBody tr');

        rows.forEach(row => {
            const rowEventId = row.dataset.eventId;
            const rowDate    = row.dataset.date;
            let show = true;
            if (eventId  && rowEventId !== eventId) show = false;
            if (fromDate && rowDate < fromDate)      show = false;
            if (toDate   && rowDate > toDate)        show = false;
            row.style.display = show ? '' : 'none';
        });
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
</script>

</body>
</html>