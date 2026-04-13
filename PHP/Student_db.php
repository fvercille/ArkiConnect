<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db_connect.php';


$login_page_path = '/PHP/login.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_regenerate_id(true); 
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path); 
    exit(); 
}

// Redirect if not logged in
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

// Determine dashboard link based on role
$homeLink = '#';
switch ($role) {
    case 'student':
        $homeLink = 'Student_db.php';
        $pageTitle = 'Student Dashboard';
        break;
    case 'org_rep':
        $homeLink = 'OrgRep_db.php';
        $pageTitle = 'Organization Dashboard';
        break;
    case 'admin':
        $homeLink = 'Admin_db.php';
        $pageTitle = 'Admin Dashboard';
        break;
    default:
        $homeLink = '#';
        $pageTitle = 'Dashboard';
}

$userAvatarText = strtoupper(substr($userName, 0, 1));

// Fetch current month's events for calendar
$currentYear = date('Y');
$currentMonth = date('m');
require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get calendar events for the current month
$startDate = "$currentYear-$currentMonth-01";
$endDate = date('Y-m-t', strtotime($startDate));

// Get calendar events for the current month — only events the user registered for
$calendarQuery = "SELECT DISTINCT DATE(e.event_date) as event_date, e.title 
                  FROM events e
                  INNER JOIN event_registrations er ON er.event_id = e.id
                  WHERE e.event_date BETWEEN '$startDate' AND '$endDate' 
                  AND e.status IN ('approved', 'upcoming', 'ongoing')
                  AND er.user_id = '$user_id'
                  ORDER BY e.event_date";
$calendarResult = $conn->query($calendarQuery);
$eventDates = [];

if ($calendarResult) {
    while ($row = $calendarResult->fetch_assoc()) {
        $eventDates[$row['event_date']] = $row['title'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/Student_db.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">
    
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
            
            <a href="<?php echo htmlspecialchars($homeLink); ?>" class="nav-link active active-toggle" title="Go to Dashboard">
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

<a href="helpcenter.php" class="nav-link" title="Help Center (FAQ)">
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

    <main class="content-area" id="content-area">

    <header class="content-header">
        <button id="menu-toggle-left" class="menu-toggle" title="Open Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2> 
            
            <button id="menu-toggle-right" class="menu-toggle" style="margin-left: auto;" title="Open Notifications">
                <i class="fas fa-bell"></i>
            </button>
            
            
          
        </header>

        <section class="main-content-layout">
            <div class="dashboard-card calendar-card">
                <div class="card-header">
                    <h3>October 2025 Calendar</h3>
                    <div style="display: flex; gap: 10px;">
                        <button class="card-icon-button"><i class="fas fa-chevron-left"></i></button>
                        <button class="card-icon-button"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="calendar-grid">
                    <!-- Calendar will be rendered by JavaScript -->
                </div>
            </div>

            <div id="event-modal" class="modal">
                <div class="modal-content">
                    <span class="close-button">&times;</span>
                    <h3 id="modal-event-title"></h3>
                </div>
            </div>

            <div class="secondary-content-grid">
                
                <div class="dashboard-card featured-event-card">
    <!-- Background decorative circles -->
    <div style="position:absolute; top:-30px; right:-30px; width:180px; height:180px; border-radius:50%; background:rgba(255,255,255,0.06); pointer-events:none;"></div>
    <div style="position:absolute; bottom:-20px; left:-20px; width:120px; height:120px; border-radius:50%; background:rgba(255,255,255,0.04); pointer-events:none;"></div>

    <!-- Top row -->
    <div class="card-header" style="margin-bottom:0;">
        <h4 style="font-size:0.72rem; color:rgba(255,255,255,0.7); letter-spacing:0.12em;">FEATURED EVENT</h4>
        <span class="org-tag">PROMOTED</span>
    </div>

    <hr style="border-color:rgba(255,255,255,0.12); margin:12px 0;">

    <!-- Event title + description -->
    <div style="flex-grow:1;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
            <span style="background:rgba(255,255,255,0.15); color:white; font-size:0.72rem; font-weight:700; padding:3px 10px; border-radius:20px; letter-spacing:0.05em;">ARCHITECTURE</span>
        </div>
        <h3 style="font-size:1.4rem; font-weight:800; color:white; margin-bottom:8px; line-height:1.2;">Architecture General Assembly</h3>
        <p style="font-size:0.88rem; color:rgba(255,255,255,0.85); margin-bottom:16px; line-height:1.5;">A united gathering of TIP Manila's future architects — come connect, collaborate, and celebrate.</p>

        <!-- Date + Venue pills -->
        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
            <span style="display:flex; align-items:center; gap:6px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); color:white; font-size:0.78rem; font-weight:600; padding:6px 12px; border-radius:20px;">
                <i class="fas fa-calendar-day" style="color:#ffdd1b;"></i> August 2, 2026
            </span>
            <span style="display:flex; align-items:center; gap:6px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); color:white; font-size:0.78rem; font-weight:600; padding:6px 12px; border-radius:20px;">
                <i class="fas fa-map-marker-alt" style="color:#ffdd1b;"></i> P.E. Center
            </span>
        </div>

    

<button id="featured-register-btn" disabled style="background:white; color:#a43825; border:none; padding:13px; border-radius:12px; font-weight:700; cursor:not-allowed; transition:all 0.2s; width:100%; font-size:0.9rem; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 14px rgba(0,0,0,0.15); margin-top:141px; ">
    Registration Opening Soon <i class="fas fa-clock"></i>
</button>
</div>
</div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Organizations</h3>
                        <a href="organizations.php" class="view-all-link" style="margin-top: 9px;margin-right: 9px;">View All</a>
                    </div>
                    <div class="organization-list">
                        
                        <div class="org-item">
                            <img class="org-logo" src="../Images/ASPHIL.jpg" alt="ASPHIL Logo"> 
                            Architectural Students' Association of the Philippines - TIP Manila
                        </div>
                        
                        <div class="org-item">
                            <img class="org-logo" src="../Images/UAPSA.jpg" alt="UAPSA Logo">
                            UAPSA TIP - Manila
                        </div>
                        
                        <div class="org-item">
                            <img class="org-logo" src="../Images/ACE.jpg" alt="Capstone Logo">
                            Architecture Capstone Exhibit TIP Manila
                        </div>
                        
                        <div class="org-item">
                            <img class="org-logo" src="../Images/AE.jpg" alt="AE Logo">
                            Architectural Exploration - TIP Manila
                        </div>

                    </div>
                </div>

                <div class="dashboard-card" id="upcoming-events-card">
                    <div class="card-header">
                        <h3>Upcoming Events</h3>
                        <a href="upcoming_events.php" class="view-all-link" style="margin-top: 9px;margin-right: 13px;">See All</a>
                    </div>
                    <div class="upcoming-events-list" id="student-event-list">
                        <p style="text-align: center; color: #666; padding: 20px;">Loading events...</p>
                    </div>
                </div>
            </div>
        </section>
        
    </main>
    
    <aside class="right-sidebar" id="right-sidebar">

        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 20px; color: #a43825;">My Activity</h3>
        
        <section class="sidebar-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h4 class="section-header">Notifications</h4>
        <a href="#" class="view-all-link">View all</a>
    </div>
    <div id="notifications-container">
        <div style="text-align: center; color: #999; padding: 15px; font-size: 0.9rem;">
            <i class="fas fa-bell-slash" style="display: block; margin-bottom: 5px;"></i>
            Loading notifications...
        </div>
    </div>
</section>

        <section class="sidebar-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h4 class="section-header">Registered Events</h4>
                <a href="registered_events.php" class="view-all-link">View all</a>
            </div>
            <ul class="event-list" id="registered-events-list">
                <li style="text-align: center; color: #666; padding: 10px;">Loading...</li>
            </ul>
        </section>
        
        <section class="sidebar-section">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 10px;">
    <h4 class="section-header">Past Events Attended</h4>
    <a href="#" class="view-all-link">View all</a>
</div>
            <ul class="event-list" id="attended-events-list">
                <li style="text-align: center; color: #666; padding: 10px;">Loading...</li>
            </ul>
        </section>

    </aside>

    <div class="overlay" id="mobile-overlay"></div>

</div> 
<script>
    // Pass PHP data to JavaScript
    const eventDates = <?php echo json_encode($eventDates); ?>;
    const currentYear = <?php echo $currentYear; ?>;
    const currentMonth = <?php echo $currentMonth; ?>;
    const userId = <?php echo $user_id; ?>;

    document.getElementById('featured-register-btn').addEventListener('click', function() {
    if (window.featuredEventId) {
        registerForEventDB(window.featuredEventId, this);
    } else {
        alert('No event loaded yet. Please wait a moment and try again.');
    }
});

</script>
<script src="../JavaScript/Student_ds.js?v=<?php echo time(); ?>"></script>

</body>
</html>