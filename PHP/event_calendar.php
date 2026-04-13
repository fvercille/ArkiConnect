<?php

// Fix: Added necessary error reporting and explicit type casting for better debugging and security.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db_connect.php';
// IMPORTANT: Ensure 'db_connect.php' uses prepared statements (e.g., PDO or mysqli)


$login_page_path = '/PHP/login.php'; 

// --- Security and Session Management ---

// Handle logout: Always exit after a header redirect.
if (isset($_GET['logout'])) {
    // Regenerate session ID before destroying the session to prevent session fixation.
    session_regenerate_id(true); 
    session_unset();
    session_destroy();
    
    // ⭐ LOGOUT REDIRECT: Gagamitin ang tamang full URL.
    header("Location: " . $login_page_path); 
    exit(); 
}

// Redirect if not logged in: Use strict checks and define allowed roles.
$allowed_roles = ['student', 'org_rep', 'admin'];
// Recommended: Use data from session, not just placeholders.
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;
$userName = $_SESSION['fullname'] ?? 'Architecture Student'; 
$userAffiliation = 'TIP Manila'; // You can make this dynamic if needed

if (empty($user_id) || empty($role) || !in_array($role, $allowed_roles)) {
    // Fix: Clear session data even if role is invalid before redirecting.
    session_unset();
    session_destroy();
    // Gagamitin din ang ABSOLUTE URL dito.
    header("Location: " . $login_page_path); 
    exit();
}

// Ensure role is a string for the switch statement.
$role = (string)$role;

// --- Role-based Link Configuration ---

// Determine dashboard link based on role
$homeLink = '#'; // Default safe link
switch ($role) {
    case 'student':
        $homeLink = 'Student_db.php';
        $pageTitle = 'Event Calendar'; // Updated page title
        break;
    case 'org_rep':
        $homeLink = 'OrgRep_db.php';
        $pageTitle = 'Event Calendar';
        break;
    case 'admin':
        $homeLink = 'Admin_db.php';
        $pageTitle = 'Event Calendar';
        break;
    default:
        $homeLink = '#';
        $pageTitle = 'Event Calendar';
}

// Data for the avatar placeholder
$userAvatarText = strtoupper(substr($userName, 0, 1)); // First letter for avatar placeholder

$eventsFromDB = [];
$evtStmt = $conn->prepare(
    "SELECT e.title, e.event_date, e.location, TIME_FORMAT(e.event_time, '%h:%i %p') as time 
     FROM events e
     INNER JOIN event_registrations er ON er.event_id = e.id
     WHERE e.status IN ('approved', 'upcoming', 'ongoing')
     AND er.user_id = ?
     ORDER BY e.event_date"
);
$evtStmt->bind_param('i', $user_id);
$evtStmt->execute();
$evtResult = $evtStmt->get_result();
while ($row = $evtResult->fetch_assoc()) {
    $eventsFromDB[] = $row;
}


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
</head>
<body>

<div class="dashboard-container" id="dashboard-container">
    
    <!-- LEFT SIDEBAR NAVIGATION -->
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
        <a href="registered_events.php" class="nav-link sub-link-item " title="My Saved Events">
            <i data-lucide="bookmark"></i>
            <span class="link-text">Registered Events</span>
        </a>
        <a href="event_calendar.php" class="nav-link sub-link-item active" title="Event Calendar">
            <i data-lucide="calendar-days"></i>
            <span class="link-text">Event Calendar</span>
        </a>

        <hr class="separator"> 

        <a href="helpcenter.php" class="nav-link sub-link-item" title="Help Center (FAQ)">
            <i data-lucide="life-buoy"></i>
            <span class="link-text">Help Center</span>
        </a>

        <a href="settings.php" class="nav-link" title="Settings">
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

    <!-- MAIN CONTENT AREA -->
    <main class="content-area" id="content-area">
        <header class="content-header">
            <button id="menu-toggle-left" class="menu-toggle" title="Open Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2> 
          
        </header>

        <section class="main-content-layout">
 <style>
            @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');
 
            :root {
                --primary:       #a43825;
                --primary-dark:  #7c2a1b;
                --primary-light: #c9503a;
                --accent:        #e8a87c;
                --accent-gold:   #d4956a;
                --bg:            #f7f3f0;
                --card-bg:       #ffffff;
                --text-dark:     #1e1210;
                --text-mid:      #4a2e27;
                --text-muted:    #9e7f78;
                --border:        #ecddd8;
                --shadow-sm:     0 1px 4px rgba(164,56,37,0.07);
                --shadow-md:     0 4px 16px rgba(164,56,37,0.11);
                --shadow-lg:     0 12px 40px rgba(164,56,37,0.15);
                --radius:        14px;
            }
 
            /* ── Page wrapper ─────────────────────────────────────── */
            .ec-wrap {
                font-family: 'DM Sans', sans-serif;
                display: grid;
                grid-template-columns: 1fr 308px;
                gap: 28px;
                padding: 0 4px 40px;
                animation: ecFadeIn .45s ease both;
            }
            @keyframes ecFadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:none} }
 
            /* ── Calendar card ────────────────────────────────────── */
            .ec-calendar {
                background: var(--card-bg);
                border-radius: var(--radius);
                box-shadow: var(--shadow-md);
                overflow: hidden;
                border: 1px solid var(--border);
            }
 
            /* header strip */
            .ec-cal-header {
                background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 55%, var(--primary-light) 100%);
                padding: 22px 28px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: relative;
                overflow: hidden;
            }
            .ec-cal-header::before {
                content: '';
                position: absolute;
                inset: 0;
                background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
            .ec-month-label {
                  
                font-size: 1.45rem;
                font-weight: 1000;
                color: #fff;
                letter-spacing: .3px;
                text-shadow: 0 2px 8px rgba(0,0,0,.2);
                position: relative;
            }
            .ec-nav-btns { display: flex; gap: 8px; position: relative; }
            .ec-nav-btn {
                width: 36px; height: 36px;
                background: rgba(255,255,255,.18);
                border: 1px solid rgba(255,255,255,.3);
                border-radius: 8px;
                color: #fff;
                cursor: pointer;
                display: grid; place-items: center;
                font-size: .85rem;
                transition: background .2s, transform .15s;
                backdrop-filter: blur(6px);
            }
            .ec-nav-btn:hover { background: rgba(255,255,255,.32); transform: scale(1.08); }
 
            /* day-of-week row */
            .ec-dow-row {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                background: #fdf7f5;
                border-bottom: 1px solid var(--border);
            }
            .ec-dow {
                padding: 11px 4px;
                text-align: center;
                font-size: .72rem;
                font-weight: 600;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: var(--primary);
            }
 
            /* grid */
            .ec-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
            }
            .ec-cell {
                border-right: 1px solid var(--border);
                border-bottom: 1px solid var(--border);
                min-height: 100px;
                padding: 10px 8px 6px;
                cursor: pointer;
                transition: background .18s;
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 3px;
            }
            .ec-cell:nth-child(7n) { border-right: none; }
            .ec-cell:hover:not(.ec-empty) { background: #fdf3ef; }
            .ec-empty { background: #fafafa; cursor: default; }
            .ec-empty:hover { background: #fafafa; }
 
            .ec-date-num {
                font-size: .92rem;
                font-weight: 600;
                color: var(--text-mid);
                width: 28px; height: 28px;
                display: grid; place-items: center;
                border-radius: 50%;
                flex-shrink: 0;
                transition: background .18s, color .18s;
            }
            .ec-cell:hover:not(.ec-empty) .ec-date-num { background: var(--border); }
 
            /* today highlight */
            .ec-cell.ec-today {
                background: linear-gradient(140deg, #fff5f2 0%, #fff0eb 100%);
            }
            .ec-cell.ec-today .ec-date-num {
                background: var(--primary);
                color: #fff;
                box-shadow: 0 2px 8px rgba(164,56,37,.35);
            }
 
            /* event pill */
            .ec-pill {
                font-size: .67rem;
                font-weight: 500;
                color: #fff;
                background: var(--primary);
                border-radius: 4px;
                padding: 2px 6px;
                line-height: 1.3;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                cursor: pointer;
                transition: background .15s, transform .12s;
            }
            .ec-pill:hover { background: var(--primary-dark); transform: scale(1.02); }
 
            /* ── Sidebar ──────────────────────────────────────────── */
            .ec-sidebar {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
 
            /* mini stats strip */
            .ec-stat-strip {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .ec-stat {
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                padding: 16px 14px;
                box-shadow: var(--shadow-sm);
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .ec-stat-icon {
                width: 34px; height: 34px;
                background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
                border-radius: 9px;
                display: grid; place-items: center;
                color: #fff;
                font-size: .85rem;
                margin-bottom: 6px;
            }
            .ec-stat-val {
                font-size: 1.6rem;
                font-weight: 900;
                color: var(--primary-dark);
                line-height: 1;
            }
            .ec-stat-lbl {
                font-size: .72rem;
                color: var(--text-muted);
                font-weight: 500;
                letter-spacing: .02em;
            }
 
            /* upcoming events panel */
            .ec-events-panel {
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                box-shadow: var(--shadow-md);
                overflow: hidden;
                flex: 1;
            }
            .ec-panel-header {
                padding: 18px 20px 14px;
                border-bottom: 1px solid var(--border);
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .ec-panel-icon {
                width: 32px; height: 32px;
                background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
                border-radius: 8px;
                display: grid; place-items: center;
                color: #fff;
                font-size: .8rem;
            }
            .ec-panel-title {
                font-size: 1rem;
                font-weight: 900;
                color: var(--text-dark);
            }
            .ec-panel-count {
                margin-left: auto;
                background: var(--primary);
                color: #fff;
                font-size: .68rem;
                font-weight: 700;
                padding: 3px 9px;
                border-radius: 20px;
                letter-spacing: .03em;
            }
 
            /* event list */
            .ec-event-list {
                padding: 12px 0;
                max-height: 500px;
                overflow-y: auto;
            }
            .ec-event-list::-webkit-scrollbar { width: 4px; }
            .ec-event-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
 
            .ec-event-item {
                display: flex;
                gap: 14px;
                padding: 12px 20px;
                border-bottom: 1px solid #f5eeeb;
                cursor: pointer;
                transition: background .18s;
                position: relative;
            }
            .ec-event-item:last-child { border-bottom: none; }
            .ec-event-item:hover { background: #fdf7f5; }
 
            /* colored date badge */
            .ec-event-badge {
                flex-shrink: 0;
                width: 44px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
                border-radius: 10px;
                padding: 6px 4px;
                box-shadow: 0 2px 8px rgba(164,56,37,.18);
            }
            .ec-badge-day {     
                font-size: 1.2rem;
                font-weight: 700;
                color: #fff;
                line-height: 1;
            }
            .ec-badge-mon {
                font-size: .6rem;
                font-weight: 600;
                color: rgba(255,255,255,.8);
                text-transform: uppercase;
                letter-spacing: .06em;
            }
 
            .ec-event-info { flex: 1; min-width: 0; }
            .ec-event-name {
                font-size: .88rem;
                font-weight: 600;
                color: var(--text-dark);
                margin-bottom: 5px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .ec-event-meta {
                display: flex;
                flex-direction: column;
                gap: 3px;
            }
            .ec-meta-row {
                display: flex;
                align-items: center;
                gap: 5px;
                font-size: .75rem;
                color: var(--text-muted);
            }
            .ec-meta-row i { color: var(--accent-gold); font-size: .7rem; width: 12px; }
 
            /* ── Modal ────────────────────────────────────────────── */
            .ec-modal-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(30, 18, 16, .6);
                backdrop-filter: blur(4px);
                z-index: 9999;
                justify-content: center;
                align-items: center;
            }
            .ec-modal-overlay.active { display: flex; }
            .ec-modal {
                background: var(--card-bg);
                border-radius: 18px;
                width: 90%; max-width: 460px;
                box-shadow: var(--shadow-lg);
                overflow: hidden;
                animation: modalIn .3s cubic-bezier(.34,1.56,.64,1) both;
            }
            @keyframes modalIn { from{opacity:0;transform:scale(.92)} to{opacity:1;transform:none} }
 
            .ec-modal-top {
                background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
                padding: 22px 24px 20px;
                position: relative;
            }
            .ec-modal-label {
                font-size: .7rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: .1em;
                color: rgba(255,255,255,.7);
                margin-bottom: 6px;
            }
            .ec-modal-title {
                font-family: 'Playfair Display', serif;
                font-size: 1.3rem;
                font-weight: 700;
                color: #fff;
                line-height: 1.3;
            }
            .ec-modal-close {
                position: absolute;
                top: 16px; right: 18px;
                width: 30px; height: 30px;
                background: rgba(255,255,255,.18);
                border: none;
                border-radius: 50%;
                color: #fff;
                font-size: 1rem;
                cursor: pointer;
                display: grid; place-items: center;
                transition: background .18s;
            }
            .ec-modal-close:hover { background: rgba(255,255,255,.32); }
 
            .ec-modal-body {
                padding: 22px 24px;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            .ec-modal-row {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: .88rem;
                color: var(--text-mid);
            }
            .ec-modal-row-icon {
                width: 32px; height: 32px;
                background: #fdf3ef;
                border-radius: 8px;
                display: grid; place-items: center;
                color: var(--primary);
                font-size: .8rem;
                flex-shrink: 0;
            }
 
            /* ── Responsive ───────────────────────────────────────── */
            @media (max-width: 1100px) {
                .ec-wrap { grid-template-columns: 1fr; }
                .ec-sidebar { display: grid; grid-template-columns: 1fr 1fr; }
                .ec-events-panel { grid-column: 1/-1; }
            }
            @media (max-width: 680px) {
                .ec-sidebar { grid-template-columns: 1fr; }
                .ec-cell { min-height: 70px; padding: 6px 4px; }
                .ec-date-num { font-size: .8rem; width: 24px; height: 24px; }
            }
        </style>
 
        <div class="ec-wrap">
 
            <!-- ════════════ CALENDAR ════════════ -->
            <div class="ec-calendar">
                <div class="ec-cal-header">
                    <span class="ec-month-label" id="ec-month-label">October 2025</span>
                    <div class="ec-nav-btns">
                        <button class="ec-nav-btn" id="ec-prev"><i class="fas fa-chevron-left"></i></button>
                        <button class="ec-nav-btn" id="ec-next"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="ec-dow-row">
                    <div class="ec-dow">Sun</div>
                    <div class="ec-dow">Mon</div>
                    <div class="ec-dow">Tue</div>
                    <div class="ec-dow">Wed</div>
                    <div class="ec-dow">Thu</div>
                    <div class="ec-dow">Fri</div>
                    <div class="ec-dow">Sat</div>
                </div>
                <div class="ec-grid" id="ec-grid">
                    <!-- JS renders cells here -->
                </div>
            </div>
 
            <!-- ════════════ SIDEBAR ════════════ -->
            <aside class="ec-sidebar">
 
                <!-- stat cards -->
                <div class="ec-stat-strip">
                    <div class="ec-stat">
                        <div class="ec-stat-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="ec-stat-val" id="ec-stat-total">0</div>
                        <div class="ec-stat-lbl">Events This Month</div>
                    </div>
                    <div class="ec-stat">
                        <div class="ec-stat-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="ec-stat-val" id="ec-stat-upcoming">0</div>
                        <div class="ec-stat-lbl">Upcoming Events</div>
                    </div>
                </div>
 
                <!-- upcoming events panel -->
                <div class="ec-events-panel">
                    <div class="ec-panel-header">
                        <div class="ec-panel-icon"><i class="fas fa-bell"></i></div>
                        <span class="ec-panel-title">Upcoming Events</span>
                        <span class="ec-panel-count" id="ec-panel-count">0</span>
                    </div>
                    <div class="ec-event-list" id="ec-event-list">
                        <!-- JS renders event cards here -->
                    </div>
                </div>
            </aside>
        </div>
 
        <!-- ════════════ MODAL ════════════ -->
        <div class="ec-modal-overlay" id="ec-modal-overlay">
            <div class="ec-modal">
                <div class="ec-modal-top">
                    <div class="ec-modal-label">Event Details</div>
                    <div class="ec-modal-title" id="ec-modal-title">—</div>
                    <button class="ec-modal-close" id="ec-modal-close"><i class="fas fa-times"></i></button>
                </div>
                <div class="ec-modal-body">
                    <div class="ec-modal-row">
                        <div class="ec-modal-row-icon"><i class="fas fa-calendar-alt"></i></div>
                        <span id="ec-modal-date">—</span>
                    </div>
                    <div class="ec-modal-row">
                        <div class="ec-modal-row-icon"><i class="fas fa-clock"></i></div>
                        <span id="ec-modal-time">—</span>
                    </div>
                    <div class="ec-modal-row">
                        <div class="ec-modal-row-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <span id="ec-modal-location">—</span>
                    </div>
                </div>
            </div>
        </div>
 
        <script>
(function() {
    const eventsData = <?php echo json_encode(array_map(function($e) {
        $d = new DateTime($e['event_date']);
        return [
            'date'     => (int)$d->format('j'),
            'month'    => (int)$d->format('n') - 1,
            'year'     => (int)$d->format('Y'),
            'title'    => $e['title'],
            'location' => $e['location'] ?? '',
            'time'     => $e['time'] ?? '',
        ];
    }, $eventsFromDB)); ?>;

    const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const SHORT_M = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    let cur = new Date();
    cur.setDate(1);

    function eventsForMonth(y, m) {
        return eventsData.filter(e => e.year === y && e.month === m);
    }

    function renderCalendar() {
        const y = cur.getFullYear(), m = cur.getMonth();
        document.getElementById('ec-month-label').textContent = MONTHS[m] + ' ' + y;

        const grid = document.getElementById('ec-grid');
        grid.innerHTML = '';

        const firstDay = new Date(y, m, 1).getDay();
        const daysInMonth = new Date(y, m + 1, 0).getDate();
        const today = new Date();
        const monthEvents = eventsForMonth(y, m);

        for (let i = 0; i < firstDay; i++) {
            const blank = document.createElement('div');
            blank.className = 'ec-cell ec-empty';
            grid.appendChild(blank);
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const cell = document.createElement('div');
            cell.className = 'ec-cell';

            const isToday = (d === today.getDate() && m === today.getMonth() && y === today.getFullYear());
            if (isToday) cell.classList.add('ec-today');

            const num = document.createElement('div');
            num.className = 'ec-date-num';
            num.textContent = d;
            cell.appendChild(num);

            const dayEvts = monthEvents.filter(e => e.date === d);
            dayEvts.forEach(ev => {
                const pill = document.createElement('div');
                pill.className = 'ec-pill';
                pill.textContent = ev.title;
                pill.addEventListener('click', (e) => { e.stopPropagation(); openModal(ev, y, m); });
                cell.appendChild(pill);
            });

            grid.appendChild(cell);
        }

        renderSidebar(y, m);
    }

    function renderSidebar(y, m) {
        const today = new Date(); today.setHours(0,0,0,0);
        const evts = eventsData.filter(e => {
            const d = new Date(e.year, e.month, e.date);
            return d >= today;
        }).sort((a,b) => new Date(a.year,a.month,a.date) - new Date(b.year,b.month,b.date));

        const monthEvts = eventsForMonth(y, m);
        document.getElementById('ec-stat-total').textContent = monthEvts.length;
        document.getElementById('ec-stat-upcoming').textContent = evts.length;
        document.getElementById('ec-panel-count').textContent = evts.length;

        const list = document.getElementById('ec-event-list');
        list.innerHTML = '';

        if (evts.length === 0) {
            list.innerHTML = '<p style="padding:20px;text-align:center;color:var(--text-muted);font-size:.85rem;">No upcoming events</p>';
            return;
        }

        evts.forEach(ev => {
            const item = document.createElement('div');
            item.className = 'ec-event-item';
            item.innerHTML = `
                <div class="ec-event-badge">
                    <div class="ec-badge-day">${ev.date}</div>
                    <div class="ec-badge-mon">${SHORT_M[ev.month]}</div>
                </div>
                <div class="ec-event-info">
                    <div class="ec-event-name">${ev.title}</div>
                    <div class="ec-event-meta">
                        <div class="ec-meta-row"><i class="fas fa-clock"></i>${ev.time}</div>
                        <div class="ec-meta-row"><i class="fas fa-map-marker-alt"></i>${ev.location}</div>
                    </div>
                </div>
            `;
            item.addEventListener('click', () => openModal(ev, ev.year, ev.month));
            list.appendChild(item);
        });
    }

    function openModal(ev, y, m) {
        document.getElementById('ec-modal-title').textContent = ev.title;
        document.getElementById('ec-modal-date').textContent = `${MONTHS[m]} ${ev.date}, ${y}`;
        document.getElementById('ec-modal-time').textContent = ev.time;
        document.getElementById('ec-modal-location').textContent = ev.location;
        document.getElementById('ec-modal-overlay').classList.add('active');
    }

    document.getElementById('ec-modal-close').addEventListener('click', () => {
        document.getElementById('ec-modal-overlay').classList.remove('active');
    });
    document.getElementById('ec-modal-overlay').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });

    document.getElementById('ec-prev').addEventListener('click', () => {
        cur.setMonth(cur.getMonth() - 1);
        renderCalendar();
    });
    document.getElementById('ec-next').addEventListener('click', () => {
        cur.setMonth(cur.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar();
})();
</script>


<script src="../JavaScript/calendar.js"></script>
<script src="../JavaScript/Student_ds.js"></script> 

</body>
</html>