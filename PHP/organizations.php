<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


$login_page_path = '/PHP/login.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_regenerate_id(true);
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path);
    exit();
}

// Check if user is logged in
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
$pageTitle = 'Organizations';
switch ($role) {
    case 'student':
        $homeLink = 'Student_db.php';
        break;
    case 'org_rep':
        $homeLink = 'OrgRep_db.php';
        break;
    case 'admin':
        $homeLink = 'Admin_db.php';
        break;
}

$userAvatarText = strtoupper(substr($userName, 0, 1));

// Fetch organizations from database
try {
    // TEMPORARY: Using mock data until you have organizations table
    $organizations = [
        [
            'id' => 1,
            'name' => 'Architectural Students\' Association of the Philippines - TIP Manila',
            'acronym' => 'ASAPHIL',
            'logo_path' => '../Images/ASPHIL.jpg',
            'description' => 'The official organization for architecture students promoting excellence in design and professional development.',
            'members_count' => 25,
            'upcoming_events' => 0
        ],
        [
            'id' => 2,
            'name' => 'UAPSA TIP - Manila',
            'acronym' => 'UAPSA',
            'logo_path' => '../Images/UAPSA.jpg',
            'description' => 'United Architects of the Philippines Student Auxiliary connecting future architects with industry professionals.',
            'members_count' => 25,
            'upcoming_events' => 0
        ],
        [
            'id' => 3,
            'name' => 'Architecture Capstone Exhibit TIP Manila',
            'acronym' => 'ACE',
            'logo_path' => '../Images/ACE.jpg',
            'description' => 'Showcasing the best architectural thesis projects and celebrating student achievements.',
            'members_count' => 25,
            'upcoming_events' => 0
        ],
        [
            'id' => 4,
            'name' => 'Architectural Exploration - TIP Manila',
            'acronym' => 'AE',
            'logo_path' => '../Images/AE.jpg',
            'description' => 'Exploring architectural innovations, sustainable design, and contemporary trends.',
            'members_count' => 25,
            'upcoming_events' => 0
        ],
    ];

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
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
    <style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');


:root {
    --primary: #a43825;
    --primary-light: rgba(164, 56, 37, 0.08);
    --primary-mid: rgba(164, 56, 37, 0.15);
    --success: #10B981;
    --warning: #F59E0B;
    --text-dark: #1a1a2e;
    --text-mid: #4b5563;
    --text-muted: #9ca3af;
    --border: #f0f0f0;
    --bg: #f8f7f5;
    --white: #ffffff;

    --sidebar-width: 260px;
    --sidebar-collapsed: 70px;
    --right-sidebar-width: 320px;

    --radius-sm: 8px;
    --radius: 14px;
    --radius-lg: 20px;

    --shadow-sm: 0 1px 4px rgba(0,0,0,0.06);
    --shadow: 0 4px 16px rgba(0,0,0,0.07);
    --shadow-lg: 0 8px 28px rgba(0,0,0,0.10);

    --transition: 0.22s ease;
    --transition-fast: 0.14s ease;

    --font-xs: 0.72rem;
    --font-sm: 0.85rem;
    --font-base: 1rem;
    --font-lg: 1.1rem;
    --font-xl: 1.25rem;
    --font-2xl: 1.5rem;
    --font-3xl: 1.875rem;
    --font-4xl: 2.1rem;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Montserrat', sans-serif;
    background-color: var(--bg);
    color: var(--text-dark);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

h1,h2,h3,h4,h5,h6 {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.25;
}

p { line-height: 1.65; }

/* ── Dashboard Layout ── */
.dashboard-container {
    display: flex;
    width: 100%;
    min-height: 100vh;
    align-items: flex-start;
}

/* ── Sidebar ── */
.sidebar {
    width: var(--sidebar-width);
    background: var(--white);
    border-right: none;
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
    transition: width var(--transition);
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
    font-size: var(--font-base);
    cursor: pointer;
    padding: 8px;
    border-radius: 10px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
}

.sidebar-top-icons button:hover {
    background: #e8e8f0;
    color: var(--primary);
}

.sidebar-top-icons .logo img {
    height: 25px;
    width: auto;
    transition: all var(--transition-fast);
}

/* ── Nav Links ── */
.nav-section {
    flex-grow: 1;
    padding: 0 4px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    color: #555;
    text-decoration: none;
    font-size: var(--font-sm);
    font-weight: 600;
    transition: all var(--transition-fast);
    margin: 2px 0;
    border-radius: 12px;
    white-space: nowrap;
    position: relative;
    letter-spacing: 0.01em;
}

.nav-link i:first-child {
    width: 20px;
    text-align: center;
    font-size: var(--font-base);
    margin-right: 12px;
    flex-shrink: 0;
}

.nav-link svg {
    width: 20px;
    height: 20px;
    min-width: 20px;
    margin-right: 12px;
    flex-shrink: 0;
}

.nav-link:hover:not(.active) {
    background: rgba(164, 56, 37, 0.08);
    color: var(--primary);
    transform: none;
}

.nav-link.active {
    background: var(--primary);
    color: white;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(164,56,37,0.25);
}

/* ── Separator ── */
.separator {
    border: 0;
    height: 1px;
    background: #f0f0f5;
    margin: 8px 4px;
}

/* ── User Profile ── */
.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    margin: 8px 4px 0;
    border-radius: 12px;
    background: #f3f3f7;
    border: none;
    transition: all var(--transition-fast);
    cursor: pointer;
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}

.user-profile:hover {
    background: rgba(164, 56, 37, 0.08);
    transform: translateY(-1px);
}

.user-profile img {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 8px;
    object-fit: cover;
    display: block;
}

.user-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 2px;
    overflow: hidden;
    min-width: 0;
}

.user-info .name {
    font-size: 13.5px;
    font-weight: 600;
    color: #1a1a2e;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-info .role {
    font-size: 11.5px;
    color: #888;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Collapsed ── */
.sidebar.collapsed {
    width: var(--sidebar-collapsed);
    overflow: hidden;
    padding: 16px 8px;
}

.sidebar.collapsed .link-text,
.sidebar.collapsed .user-info,
.sidebar.collapsed .header-container,
.sidebar.collapsed .toggle-icon {
    display: none;
}

.sidebar.collapsed .sidebar-top-icons,
.sidebar.collapsed .user-profile {
    justify-content: center;
    padding: 10px 0;
    margin: 0 2px;
}

.sidebar.collapsed .user-profile img { margin: 0; }

.sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 10px 0;
    margin: 2px 0;
}

.sidebar.collapsed .nav-link i:first-child { margin: 0; width: auto; }
.sidebar.collapsed .nav-link svg { margin-right: 0; }
.sidebar.collapsed .separator { margin: 8px 2px; }
.sidebar.collapsed .nav-section { padding: 0 2px; }

/* ── Content Area ── */
.content-area {
    flex: 1 1 0%;
    padding: 28px 24px;
    min-height: 100vh;
    overflow-y: auto;
    background: var(--bg);
    transition: margin-left var(--transition);
}

/* ── Content Header ── */
.content-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1.5px solid var(--border);
}

.content-header h2 {
    font-size: var(--font-4xl);
    font-weight: 700;
    color: var(--primary);
    letter-spacing: -0.03em;
}

.card-icon-button {
    background: var(--white);
    border: 1px solid var(--border);
    color: var(--text-muted);
    font-size: var(--font-lg);
    cursor: pointer;
    padding: 10px;
    border-radius: var(--radius);
    transition: all var(--transition-fast);
    box-shadow: var(--shadow-sm);
}

.card-icon-button:hover {
    background: var(--primary-light);
    color: var(--primary);
    border-color: var(--primary);
    transform: translateY(-1px);
    box-shadow: var(--shadow);
}

/* ── Menu Toggle ── */
.menu-toggle { display: none; }

/* ── Scrollbar ── */
.sidebar::-webkit-scrollbar { width: 5px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: #c0c0c0; }

/* ── Buttons ── */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 18px;
    border-radius: var(--radius);
    font-family: 'Montserrat', sans-serif;
    font-size: var(--font-sm);
    font-weight: 600;
    text-decoration: none;
    transition: all var(--transition-fast);
    cursor: pointer;
    border: none;
    gap: 8px;
    letter-spacing: 0.01em;
}

.btn-primary {
    background: var(--primary);
    color: white;
    box-shadow: 0 4px 12px rgba(164,56,37,0.25);
}

.btn-primary:hover {
    background: #8a2f1c;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(164,56,37,0.35);
}

/* ── Animations ── */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Focus Styles ── */
.nav-link:focus,
.card-icon-button:focus,
.menu-toggle:focus {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

/* ════════════════════════════════════════
   ORGANIZATIONS PAGE STYLES
════════════════════════════════════════ */

.orgs-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* ── Hero ── */
.orgs-hero {
    background: linear-gradient(135deg, #a43825 0%, #c0482e 60%, #8a2f1c 100%);
    border-radius: 20px;
    padding: 32px 36px;
    margin-bottom: 24px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    position: relative;
    overflow: hidden;
}

.orgs-hero::after {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
    pointer-events: none;
}

.orgs-eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.65);
    display: block;
    margin-bottom: 8px;
}

.orgs-hero-text h3 {
    font-size: 1.8rem;
    font-weight: 800;
    color: white;
    margin-bottom: 8px;
    line-height: 1.2;
}

.orgs-hero-text p {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
    line-height: 1.5;
}

.orgs-hero-stat-row {
    display: flex;
    gap: 16px;
    flex-shrink: 0;
}

.orgs-hero-stat {
    text-align: center;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 14px;
    padding: 14px 20px;
}

.stat-num {
    display: block;
    font-size: 1.6rem;
    font-weight: 800;
    color: white;
    line-height: 1;
}

.stat-label {
    display: block;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.7);
    font-weight: 600;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* ── Search ── */
.orgs-search-wrap {
    position: relative;
    margin-bottom: 24px;
}

.orgs-search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.9rem;
}

.orgs-search-input {
    width: 100%;
    padding: 14px 16px 14px 42px;
    border: 1.5px solid #f0f0f0;
    border-radius: 14px;
    font-size: 0.9rem;
    font-family: 'Montserrat', sans-serif;
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

.orgs-search-input:focus {
    outline: none;
    border-color: #a43825;
    box-shadow: 0 0 0 3px rgba(164,56,37,0.08);
}

.orgs-search-input::placeholder { color: #9ca3af; }

/* ── Grid ── */
.organizations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

/* ── Org Card ── */
.org-card {
    background: white;
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    gap: 16px;
    transition: all 0.22s ease;
    animation: fadeInUp 0.4s ease both;
}

.org-card:nth-child(2) { animation-delay: 0.08s; }
.org-card:nth-child(3) { animation-delay: 0.16s; }
.org-card:nth-child(4) { animation-delay: 0.24s; }

.org-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(164,56,37,0.12);
    border-color: rgba(164,56,37,0.2);
}

.org-card-top {
    display: flex;
    align-items: center;
    gap: 14px;
}

.org-card-logo {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    object-fit: cover;
    border: 2px solid #f0f0f0;
    flex-shrink: 0;
    transition: border-color 0.2s;
}

.org-card:hover .org-card-logo {
    border-color: rgba(164,56,37,0.3);
}

.org-card-acronym {
    font-size: 1rem;
    font-weight: 800;
    color: #a43825;
    line-height: 1.2;
}

.org-card-name {
    font-size: 0.78rem;
    color: #9ca3af;
    line-height: 1.4;
    font-weight: 500;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.org-card-description {
    font-size: 0.82rem;
    color: #6b7280;
    line-height: 1.6;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}

.org-card-stats {
    display: flex;
    gap: 16px;
    padding: 12px 0;
    border-top: 1px solid #f5f5f5;
    border-bottom: 1px solid #f5f5f5;
}

.org-card-stat {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    color: #6b7280;
    font-weight: 600;
}

.org-card-stat i { color: #a43825; font-size: 0.8rem; }

.org-card-button {
    background: #a43825;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: 'Montserrat', sans-serif;
    letter-spacing: 0.01em;
}

.org-card-button:hover {
    background: #8a2f1c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(164,56,37,0.3);
}

.org-card-button i {
    font-size: 0.75rem;
    transition: transform 0.2s;
}

.org-card-button:hover i { transform: translateX(3px); }

/* ── Empty State ── */
.empty-state {
    text-align: center;
    padding: 48px;
    color: #9ca3af;
    grid-column: 1 / -1;
    background: white;
    border-radius: 18px;
    border: 1px solid #f0f0f0;
}

.empty-state i {
    font-size: 2.5rem;
    color: #d1d5db;
    margin-bottom: 12px;
    display: block;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
    .content-area { width: calc(100% - var(--sidebar-width)); }
}

@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        left: -100%;
        top: 0; height: 100%;
        z-index: 200;
        box-shadow: 8px 0 24px rgba(0,0,0,0.12);
        transition: left var(--transition);
    }

    .sidebar.active { left: 0; }

    .overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        z-index: 150;
        display: none;
    }

    .overlay.active { display: block; }

    .menu-toggle {
        display: block;
        color: var(--primary);
        background: var(--white);
        border: 1px solid var(--border);
        padding: 8px;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        transition: all var(--transition-fast);
        cursor: pointer;
    }

    .menu-toggle:hover {
        background: var(--primary-light);
        transform: translateY(-1px);
    }

    .content-area { width: 100%; padding: 20px 16px; }
    .content-header h2 { font-size: var(--font-3xl); }
    .orgs-hero { flex-direction: column; padding: 24px; }
    .orgs-hero-stat-row { width: 100%; justify-content: space-between; }
    .organizations-grid { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
    .content-area { padding: 14px 12px; }
    .content-header h2 { font-size: var(--font-2xl); }
    .orgs-hero-text h3 { font-size: 1.4rem; }
}

@media (min-width: 1200px) {
    .content-area { padding: 32px 28px; }
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

        <a href="organizations.php" class="nav-link sub-link-item active" title="Organization Directory">
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

    <!-- MAIN CONTENT AREA -->
  <main class="content-area" id="content-area">
    <header class="content-header">
        <button id="menu-toggle-left" class="menu-toggle" title="Open Navigation">
            <i class="fas fa-bars"></i>
        </button>
        <h2>Organizations</h2>
    </header>

    <div class="orgs-container">

        <!-- Hero Intro -->
        <div class="orgs-hero">
            <div class="orgs-hero-text">
                <span class="orgs-eyebrow">TIP Manila · Architecture</span>
                <h3>Student Organizations</h3>
                <p>Find your community. Join organizations that shape your future in architecture.</p>
            </div>
            <div class="orgs-hero-stat-row">
                <div class="orgs-hero-stat">
                    <span class="stat-num">4</span>
                    <span class="stat-label">Organizations</span>
                </div>
                <div class="orgs-hero-stat">
                    <span class="stat-num">100</span>
                    <span class="stat-label">Members</span>
                </div>
                <div class="orgs-hero-stat">
                    <span class="stat-num">0</span>
                    <span class="stat-label">Active Events</span>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="orgs-search-wrap">
            <i class="fas fa-search orgs-search-icon"></i>
            <input type="text" class="orgs-search-input" id="org-search" placeholder="Search organizations...">
        </div>

        <!-- Grid -->
        <div class="organizations-grid" id="organizations-grid">
            <?php if (!empty($organizations)): ?>
                <?php foreach ($organizations as $org): ?>
                <div class="org-card" data-name="<?php echo htmlspecialchars(strtolower($org['name'] . ' ' . $org['acronym'])); ?>">
                    
                    <div class="org-card-top">
                        <img class="org-card-logo" 
                             src="<?php echo htmlspecialchars($org['logo_path']); ?>" 
                             alt="<?php echo htmlspecialchars($org['acronym']); ?>"
                             onerror="this.src='https://placehold.co/72x72/a43825/white?text=<?php echo htmlspecialchars($org['acronym']); ?>'">
                        <div class="org-card-meta">
                            <div class="org-card-acronym"><?php echo htmlspecialchars($org['acronym']); ?></div>
                            <div class="org-card-name"><?php echo htmlspecialchars($org['name']); ?></div>
                        </div>
                    </div>

                    <p class="org-card-description"><?php echo htmlspecialchars($org['description']); ?></p>

                    <div class="org-card-stats">
                        <div class="org-card-stat">
                            <i class="fas fa-users"></i>
                            <span><?php echo $org['members_count']; ?> Members</span>
                        </div>
                        <div class="org-card-stat">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?php echo $org['upcoming_events']; ?> Events</span>
                        </div>
                    </div>

                    <a href="organizations_details.php?id=<?php echo $org['id']; ?>" class="org-card-button">
                        View Organization <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No organizations found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

    <div class="overlay" id="mobile-overlay"></div>

</div>

<script src="../JavaScript/Student_ds.js"></script>
<script>
// Search functionality
document.getElementById('org-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const orgCards = document.querySelectorAll('.org-card');
    let visibleCount = 0;
    
    orgCards.forEach(card => {
        const name = card.getAttribute('data-name');
        if (name.includes(searchTerm)) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    const emptyState = document.querySelector('.empty-state');
    if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
});

 const eventDates = {};
    const currentYear = <?php echo date('Y'); ?>;
    const currentMonth = <?php echo date('m'); ?>;
    const userId = <?php echo $user_id; ?>
</script>

</body>
</html>