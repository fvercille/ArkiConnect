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
$userAffiliation = 'TIP Manila';

if (empty($user_id) || empty($role) || !in_array($role, $allowed_roles)) {
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path); 
    exit();
}

$role = (string)$role;

$homeLink = '#';
switch ($role) {
    case 'student':  $homeLink = 'Student_db.php';  $pageTitle = 'Help Center'; break;
    case 'org_rep':  $homeLink = 'OrgRep_db.php';   $pageTitle = 'Help Center'; break;
    case 'admin':    $homeLink = 'Admin_db.php';     $pageTitle = 'Help Center'; break;
    default:         $homeLink = '#';                $pageTitle = 'Help Center';
}

$userAvatarText = strtoupper(substr($userName, 0, 1));
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
            <a href="registered_events.php" class="nav-link sub-link-item" title="My Saved Events">
                <i data-lucide="bookmark"></i>
                <span class="link-text">Registered Events</span>
            </a>
            <a href="event_calendar.php" class="nav-link sub-link-item" title="Event Calendar">
                <i data-lucide="calendar-days"></i>
                <span class="link-text">Event Calendar</span>
            </a>

            <hr class="separator">

            <a href="helpcenter.php" class="nav-link sub-link-item active" title="Help Center (FAQ)">
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

    <!-- MAIN CONTENT -->
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
                    --accent-gold:   #d4956a;
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

                .hc-wrap {
                    font-family: 'DM Sans', sans-serif;
                    display: grid;
                    grid-template-columns: 260px 1fr;
                    gap: 28px;
                    padding: 0 4px 40px;
                    animation: hcFade .4s ease both;
                }
                @keyframes hcFade { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

                .hc-sidebar { display: flex; flex-direction: column; gap: 16px; }

                .hc-search-card {
                    background: var(--card-bg);
                    border: 1px solid var(--border);
                    border-radius: var(--radius);
                    box-shadow: var(--shadow-sm);
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
                .hc-search-wrap i { padding: 0 12px; color: var(--text-muted); font-size: .85rem; }
                .hc-search-input {
                    flex: 1; border: none; outline: none;
                    padding: 10px 0; font-family: inherit;
                    font-size: .88rem; color: var(--text-dark); background: transparent;
                }
                .hc-search-input::placeholder { color: var(--text-muted); }

                .hc-cat-card {
                    background: var(--card-bg);
                    border: 1px solid var(--border);
                    border-radius: var(--radius);
                    box-shadow: var(--shadow-sm);
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
                    cursor: pointer; font-family: inherit; font-size: .88rem;
                    font-weight: 500; color: var(--text-mid); text-align: left;
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
                    margin-left: auto; background: var(--border); color: var(--text-muted);
                    font-size: .68rem; font-weight: 700; padding: 2px 8px;
                    border-radius: 20px; transition: background .15s, color .15s;
                }
                .hc-cat-btn.active .hc-cat-count,
                .hc-cat-btn:hover .hc-cat-count { background: var(--primary); color: #fff; }

                .hc-contact-card {
                    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
                    border-radius: var(--radius); padding: 22px 20px;
                    color: #fff; box-shadow: var(--shadow-md);
                }
                .hc-contact-card h4 {
                    font-size: 1rem; font-weight: 700; margin-bottom: 8px;
                }
                .hc-contact-card p {
                    font-size: .78rem; color: rgba(255,255,255,.8);
                    margin-bottom: 16px; line-height: 1.5;
                }
                .hc-contact-btn {
                    display: block; text-align: center;
                    background: rgba(255,255,255,.18);
                    border: 1px solid rgba(255,255,255,.35);
                    color: #fff; text-decoration: none;
                    padding: 10px 16px; border-radius: 8px;
                    font-size: .82rem; font-weight: 600;
                    transition: background .2s; backdrop-filter: blur(4px);
                }
                .hc-contact-btn:hover { background: rgba(255,255,255,.28); }

                .hc-faq-panel { display: flex; flex-direction: column; gap: 0; }

                .hc-faq-header {
                    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 55%, var(--primary-light) 100%);
                    border-radius: var(--radius) var(--radius) 0 0;
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
                    color: #fff; font-size: 1.1rem; flex-shrink: 0;
                    position: relative; backdrop-filter: blur(4px);
                }
                .hc-faq-header-text { position: relative; }
                .hc-faq-header-text h3 {
                    font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 2px;
                }
                .hc-faq-header-text p { font-size: .78rem; color: rgba(255,255,255,.75); }
                .hc-faq-result-count {
                    margin-left: auto;
                    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.28);
                    color: #fff; font-size: .72rem; font-weight: 700;
                    padding: 5px 12px; border-radius: 20px;
                    position: relative; backdrop-filter: blur(4px);
                }

                .hc-faq-list {
                    background: var(--card-bg); border: 1px solid var(--border);
                    border-top: none; border-radius: 0 0 var(--radius) var(--radius);
                    box-shadow: var(--shadow-md); overflow: hidden;
                }

                .hc-empty { display: none; padding: 48px 30px; text-align: center; color: var(--text-muted); }
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
                    color: var(--text-dark); line-height: 1.4;
                }
                .hc-faq-q-chevron {
                    color: var(--text-muted); font-size: .8rem;
                    transition: transform .25s; flex-shrink: 0;
                }
                .hc-faq-item.active .hc-faq-q-chevron { transform: rotate(180deg); }

                .hc-faq-a {
                    display: none; padding: 0 24px 18px 68px;
                    font-size: .88rem; color: var(--text-mid); line-height: 1.8;
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

                @media (max-width: 1000px) {
                    .hc-wrap { grid-template-columns: 1fr; }
                    .hc-sidebar { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
                    .hc-contact-card { grid-column: 1/-1; }
                    .hc-faq-header { border-radius: var(--radius); }
                    .hc-faq-list { border-radius: var(--radius); border-top: 1px solid var(--border); margin-top: 8px; }
                }
                @media (max-width: 600px) {
                    .hc-sidebar { grid-template-columns: 1fr; }
                    .hc-faq-q { padding: 14px 16px; }
                    .hc-faq-a { padding: 0 16px 14px 54px; }
                }
            </style>

            <div class="hc-wrap">

                <!-- ════════ LEFT SIDEBAR ════════ -->
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
                                <span class="hc-cat-count">9</span>
                            </button>
                            <button class="hc-cat-btn" data-cat="events" onclick="hcFilter('events', this)">
                                <i class="fas fa-calendar-alt"></i> Events & Registration
                                <span class="hc-cat-count">4</span>
                            </button>
                            <button class="hc-cat-btn" data-cat="profile" onclick="hcFilter('profile', this)">
                                <i class="fas fa-user-circle"></i> Profile & Account
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
                        <a href="mailto:arkiconnect.support@tip.edu.ph" class="hc-contact-btn">
                            <i class="fas fa-envelope" style="margin-right:6px;"></i> Get in Touch
                        </a>
                    </div>

                </aside>

                <!-- ════════ FAQ PANEL ════════ -->
                <div class="hc-faq-panel">

                    <div class="hc-faq-header">
                        <div class="hc-faq-header-icon"><i class="fas fa-book-open"></i></div>
                        <div class="hc-faq-header-text">
                            <h3 id="hc-panel-title">Frequently Asked Questions</h3>
                            <p id="hc-panel-sub">Browse all topics or filter by category</p>
                        </div>
                        <div class="hc-faq-result-count" id="hc-result-count">9 articles</div>
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
                                <span class="hc-faq-q-text">How do I register for an event?</span>
                                <span class="hc-faq-tag events">Events</span>
                                <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                            </div>
                            <div class="hc-faq-a">
                                Navigate to the <strong>Upcoming Events</strong> section. Find the event you're interested in, click on it to view details, and then click the <strong>"Register Now"</strong> button to complete your registration.
                            </div>
                        </div>

                        <div class="hc-faq-item" data-cat="events">
                            <div class="hc-faq-q">
                                <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                                <span class="hc-faq-q-text">Where can I find my registered events?</span>
                                <span class="hc-faq-tag events">Events</span>
                                <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                            </div>
                            <div class="hc-faq-a">
                                Your registered events appear under <strong>Registered Events</strong> in the sidebar. You can view them organized as <strong>Upcoming Events</strong> or <strong>Past Events Attended</strong>.
                            </div>
                        </div>

                        <div class="hc-faq-item" data-cat="events">
                            <div class="hc-faq-q">
                                <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                                <span class="hc-faq-q-text">Can I unregister from an event?</span>
                                <span class="hc-faq-tag events">Events</span>
                                <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                            </div>
                            <div class="hc-faq-a">
                                Once you register, you cannot unregister since your details will already be sent to the organization.
                            </div>
                        </div>

                        <div class="hc-faq-item" data-cat="events">
                            <div class="hc-faq-q">
                                <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                                <span class="hc-faq-q-text">Can I see past events I've attended?</span>
                                <span class="hc-faq-tag events">Events</span>
                                <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                            </div>
                            <div class="hc-faq-a">
                                Yes! Your attendance history is available in the <strong>Registered Events</strong> sidebar under <strong>Past Events Attended</strong>.
                            </div>
                        </div>

                        <!-- PROFILE -->
                        <div class="hc-faq-item" data-cat="profile">
                            <div class="hc-faq-q">
                                <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                                <span class="hc-faq-q-text">Can I edit my profile information?</span>
                                <span class="hc-faq-tag profile">Profile</span>
                                <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                            </div>
                            <div class="hc-faq-a">
                                Yes! Go to your <strong>Settings</strong> page. Under Account, you can edit your Full Name, Email Address, and Password.
                            </div>
                        </div>

                        <div class="hc-faq-item" data-cat="profile">
                            <div class="hc-faq-q">
                                <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                                <span class="hc-faq-q-text">How do I change my password?</span>
                                <span class="hc-faq-tag profile">Profile</span>
                                <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                            </div>
                            <div class="hc-faq-a">
                                Go to <strong>Settings</strong> from the sidebar, then look for the <strong>Account</strong> section where you can update your password.
                            </div>
                        </div>

                        <div class="hc-faq-item" data-cat="profile">
                            <div class="hc-faq-q">
                                <div class="hc-faq-q-icon"><i class="fas fa-question"></i></div>
                                <span class="hc-faq-q-text">How do I delete my account?</span>
                                <span class="hc-faq-tag profile">Profile</span>
                                <i class="fas fa-chevron-down hc-faq-q-chevron"></i>
                            </div>
                            <div class="hc-faq-a">
                                Account deletion must be requested via email at <a href="mailto:arkiconnect.support@tip.edu.ph">arkiconnect.support@tip.edu.ph</a>. Include your full name and student ID. This action is permanent and cannot be undone.
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
                                Contact the <strong>ArkiConnect Support Team</strong> at <a href="mailto:arkiconnect.support@tip.edu.ph">arkiconnect.support@tip.edu.ph</a> or use the "Get in Touch" button on the left.
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

            <script>
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
                        all:     ['Frequently Asked Questions',  'Browse all topics or filter by category'],
                        events:  ['Events & Registration',        'How to find, join, and manage events'],
                        profile: ['Profile & Account',            'Manage your personal information'],
                        support: ['Technical Support',            'Troubleshooting and browser compatibility'],
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
             // Dummy variables so Student_ds.js doesn't crash on helpcenter
                const currentMonth = new Date().getMonth() + 1;
                const currentYear = new Date().getFullYear();
                const eventDates = {};

        // Patch missing right sidebar so toggle doesn't break
        document.addEventListener('DOMContentLoaded', () => {
            if (!document.getElementById('right-sidebar')) {
            const dummy = document.createElement('div');
            dummy.id = 'right-sidebar';
            dummy.style.display = 'none';
            document.body.appendChild(dummy);
        }
    });
            </script>

        </section>
    </main>

    <div class="overlay" id="mobile-overlay"></div>

</div>

<script src="../JavaScript/Student_ds.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>