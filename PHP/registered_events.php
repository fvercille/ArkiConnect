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
    case 'student':
        $homeLink = 'Student_db.php';
        $pageTitle = 'Registered Events';
        break;
    case 'org_rep':
        $homeLink = 'OrgRep_db.php';
        $pageTitle = 'Registered Events';
        break;
    case 'admin':
        $homeLink = 'Admin_db.php';
        $pageTitle = 'Registered Events';
        break;
    default:
        $homeLink = '#';
        $pageTitle = 'Registered Events';
}

$userAvatarText = strtoupper(substr($userName, 0, 1));

$registered_events = [];

try {
    $query = "
    SELECT 
        e.id,
        e.title as event_name,
        e.description as event_description,
        e.event_date,
        e.event_time as time,
        e.image_path as event_image,
        e.location,
        u.fullname as organizer,
        er.registration_date,
        er.status
    FROM event_registrations er
    INNER JOIN events e ON er.event_id = e.id
    LEFT JOIN users u ON e.created_by = u.id
    WHERE er.user_id = ? AND e.status = 'approved'
    ORDER BY e.event_date ASC
";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $registered_events[] = [
            'id' => (int)$row['id'],
            'event_name' => $row['event_name'],
            'event_date' => $row['event_date'],
            'event_description' => $row['event_description'],
            'event_image' => $row['event_image'] ?? '../Images/default-event.jpg',
            'location' => $row['location'] ?? 'TBA',
            'time' => $row['time'] ? date('g:i A', strtotime($row['time'])) : 'TBA',
            'organizer' => $row['organizer'] ?? 'Unknown Organization',
            'registered_date' => $row['registration_date'],
            'status' => $row['status']
        ];
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching registered events: " . $e->getMessage());
    $registered_events = [];
}

function reGetDaysLabel(string $event_date): string {
    $now   = new DateTime('today');
    $event = new DateTime($event_date);
    $diff  = (int)$now->diff($event)->days;
    $past  = $now > $event;
    if ($past && $diff === 0) return 'Today';
    if ($past)                return 'Ended';
    if ($diff === 0)          return 'Today';
    if ($diff === 1)          return 'Tomorrow';
    return $diff . ' days away';
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
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

:root {
    --primary: #a43825;
    --primary-light: rgba(164, 56, 37, 0.08);
    --primary-mid: rgba(164, 56, 37, 0.15);
    --success: #10B981;
    --warning: #F59E0B;
    --text-dark: #1a1a2e;
    --text-mid: #4b5563;
    --text-muted: #9ca3af;
    --border: #ededea;
    --bg: #f8f7f5;
    --white: #ffffff;

    --sidebar-width: 260px;
    --sidebar-collapsed: 70px;

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

.sidebar-top-icons button:hover { background: #e8e8f0; color: var(--primary); }
.sidebar-top-icons .logo img { height: 25px; width: auto; }

.nav-section { flex-grow: 1; padding: 0 4px; }

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
    letter-spacing: 0.01em;
}

.nav-link svg { width: 20px; height: 20px; min-width: 20px; margin-right: 12px; flex-shrink: 0; }
.nav-link:hover:not(.active) { background: var(--primary-light); color: var(--primary); }
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
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
    transition: all var(--transition-fast);
}

.user-profile:hover { background: var(--primary-light); transform: translateY(-1px); }
.user-profile img { width: 36px; height: 36px; min-width: 36px; border-radius: 8px; object-fit: cover; }
.user-info { display: flex; flex-direction: column; gap: 2px; overflow: hidden; min-width: 0; }
.user-info .name { font-size: 13.5px; font-weight: 600; color: #1a1a2e; overflow: hidden; text-overflow: ellipsis; }
.user-info .role { font-size: 11.5px; color: #888; overflow: hidden; text-overflow: ellipsis; }

/* Collapsed sidebar */
.sidebar.collapsed { width: var(--sidebar-collapsed); overflow: hidden; padding: 16px 8px; }
.sidebar.collapsed .link-text,
.sidebar.collapsed .user-info,
.sidebar.collapsed .header-container,
.sidebar.collapsed .toggle-icon { display: none; }
.sidebar.collapsed .sidebar-top-icons,
.sidebar.collapsed .user-profile { justify-content: center; padding: 10px 0; margin: 0 2px; }
.sidebar.collapsed .user-profile img { margin: 0; }
.sidebar.collapsed .nav-link { justify-content: center; padding: 10px 0; margin: 2px 0; }
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
}

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

.menu-toggle { display: none; }

.sidebar::-webkit-scrollbar { width: 5px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }
.sidebar::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }

/* ════════════════════════════════════
   REGISTERED EVENTS — REDESIGNED
════════════════════════════════════ */

.re-container { max-width: 1400px; margin: 0 auto; }

/* ── Hero ── */
.re-hero {
    background: linear-gradient(135deg, #a43825 0%, #b84131 50%, #7e2e18 100%);
    border-radius: 24px;
    padding: 0;
    margin-bottom: 28px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr auto;
    position: relative;
}

.re-hero::before {
    content: '';
    position: absolute;
    right: -60px; top: -60px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    pointer-events: none; z-index: 1;
}

.re-hero::after {
    content: '';
    position: absolute;
    left: 40%; bottom: -80px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(0,0,0,0.06);
    pointer-events: none; z-index: 1;
}

.re-hero-left {
    padding: 36px 40px;
    z-index: 2;
    position: relative;
}

.re-hero-right {
    display: flex;
    align-items: center;
    padding: 0 40px 0 0;
    gap: 14px;
    flex-shrink: 0;
    z-index: 2;
}

.re-eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.65);
    display: block;
    margin-bottom: 8px;
}

.re-hero-left h3 {
    font-size: 2.2rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 12px;
    line-height: 1.1;
    letter-spacing: -0.03em;
}

.re-hero-sub {
    font-size: 0.88rem;
    color: rgba(255,255,255,0.72);
    max-width: 380px;
    line-height: 1.6;
    margin-bottom: 22px;
}

.re-hero-tags { display: flex; gap: 8px; flex-wrap: wrap; }

.re-hero-tag {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 5px 12px;
    border-radius: 100px;
    background: rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.85);
    border: 1px solid rgba(255,255,255,0.18);
}

.re-stat-pill {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 18px;
    padding: 20px 24px;
    text-align: center;
    min-width: 90px;
}

.re-stat-pill .stat-num {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}

.re-stat-pill .stat-label {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.6);
    margin-top: 6px;
}

/* ── Controls ── */
.re-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.re-search-wrap { position: relative; flex: 1; min-width: 200px; max-width: 320px; }

.re-search-icon {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #c0c0c0; font-size: 13px;
}

.re-search {
    width: 100%;
    padding: 11px 14px 11px 38px;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    font-size: 0.85rem;
    font-family: 'Montserrat', sans-serif;
    background: var(--white);
    color: var(--text-dark);
    transition: all 0.2s;
}

.re-search:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(164,56,37,0.09); }
.re-search::placeholder { color: #bbb; }

.re-right-controls { display: flex; align-items: center; gap: 10px; margin-left: auto; }

.re-tabs {
    display: flex;
    background: #eceae5;
    border-radius: 10px;
    padding: 3px; gap: 2px;
}

.re-tab {
    padding: 8px 18px;
    font-size: 0.8rem; font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    color: #888;
    background: transparent; border: none;
    border-radius: 8px; cursor: pointer;
    transition: all var(--transition-fast);
}

.re-tab:hover { color: var(--text-dark); }
.re-tab.active { background: var(--white); color: var(--primary); box-shadow: 0 1px 4px rgba(0,0,0,0.08); }

.re-sort { display: flex; align-items: center; gap: 7px; }
.re-sort-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }

.re-select {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.82rem; font-weight: 600;
    color: var(--text-dark);
    background: var(--white);
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 8px 12px;
    outline: none; cursor: pointer;
}

.re-view-toggle { display: flex; gap: 4px; }

.re-view-btn {
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    background: var(--white); cursor: pointer;
    color: var(--text-muted);
    transition: all var(--transition-fast);
}

.re-view-btn.active,
.re-view-btn:hover { color: var(--primary); border-color: rgba(164,56,37,0.3); background: var(--primary-light); }
.re-view-btn svg { width: 14px; height: 14px; }

.re-results-meta { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; margin-bottom: 18px; }

/* ── Grid ── */
.re-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(295px, 1fr)); gap: 20px; }
.re-grid.list-view { grid-template-columns: 1fr; }

/* ── Card ── */
.re-card {
    background: var(--white);
    border-radius: 20px;
    border: 1.5px solid var(--border);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.22s ease;
    animation: reCardIn 0.38s ease both;
}

@keyframes reCardIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.re-card:nth-child(2) { animation-delay: 0.07s; }
.re-card:nth-child(3) { animation-delay: 0.14s; }
.re-card:nth-child(4) { animation-delay: 0.21s; }
.re-card:nth-child(5) { animation-delay: 0.28s; }
.re-card:nth-child(6) { animation-delay: 0.35s; }

.re-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(164,56,37,0.13);
    border-color: rgba(164,56,37,0.22);
}

.re-grid.list-view .re-card { flex-direction: row; height: 152px; }

/* Image */
.re-card-img {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: #1a1a1a;
    flex-shrink: 0;
}

.re-grid.list-view .re-card-img { width: 200px; height: 100%; }

.re-card-img img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    transition: transform 0.4s ease;
    filter: brightness(0.88);
}

.re-card:hover .re-card-img img { transform: scale(1.05); }

.re-img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(160deg, rgba(164,56,37,0.6) 0%, rgba(0,0,0,0.45) 100%);
}

.re-img-top {
    position: absolute; top: 12px; left: 12px; right: 12px;
    display: flex; justify-content: space-between; align-items: center;
}

.re-status-badge {
    font-size: 0.67rem; font-weight: 800;
    letter-spacing: 0.09em; text-transform: uppercase;
    padding: 4px 10px; border-radius: 6px;
    font-family: 'Montserrat', sans-serif;
}

.re-status-badge.upcoming { background: rgba(255,255,255,0.95); color: var(--primary); }
.re-status-badge.past { background: rgba(0,0,0,0.25); color: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.2); }

.re-days-tag {
    font-size: 0.72rem; font-weight: 700;
    color: rgba(255,255,255,0.92);
    background: rgba(0,0,0,0.28);
    padding: 4px 9px; border-radius: 6px;
    display: flex; align-items: center; gap: 4px;
    border: 1px solid rgba(255,255,255,0.15);
}

.re-days-tag svg { width: 10px; height: 10px; }

.re-img-bottom {
    position: absolute; bottom: 0; left: 0; right: 0;
    padding: 14px;
}

.re-card-title-overlay {
    font-size: 1rem; font-weight: 800;
    color: #fff;
    letter-spacing: -0.02em; line-height: 1.2;
    text-shadow: 0 2px 8px rgba(0,0,0,0.5);
}

.re-grid.list-view .re-card-title-overlay { font-size: 0.9rem; }

/* Body */
.re-card-body {
    padding: 16px 18px 14px;
    display: flex; flex-direction: column;
    gap: 10px; flex: 1; min-width: 0;
}

.re-grid.list-view .re-card-body { padding: 14px 16px; gap: 6px; justify-content: center; }

.re-org-line {
    display: flex; align-items: center; gap: 6px;
    font-size: 0.69rem; font-weight: 800;
    color: var(--primary);
    letter-spacing: 0.08em; text-transform: uppercase;
}

.re-org-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--primary); flex-shrink: 0; }

.re-card-meta { display: flex; flex-direction: column; gap: 5px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; }
.re-grid.list-view .re-card-meta { border-bottom: none; padding-bottom: 0; }

.re-meta-item { display: flex; align-items: center; gap: 7px; font-size: 0.77rem; color: var(--text-mid); font-weight: 500; }
.re-meta-item i { color: var(--primary); font-size: 0.72rem; width: 12px; text-align: center; flex-shrink: 0; }

.re-card-desc {
    font-size: 0.77rem; color: var(--text-muted); line-height: 1.65; flex: 1;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.re-grid.list-view .re-card-desc { display: none; }

.re-card-footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; }

.re-reg-chip {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.7rem; font-weight: 800;
    color: #065f46; background: #d1fae5;
    border: 1px solid rgba(6,95,70,0.12);
    padding: 5px 11px; border-radius: 7px;
    font-family: 'Montserrat', sans-serif;
}

.re-reg-chip i { font-size: 0.65rem; }

.re-cancel-btn {
    font-size: 0.77rem; font-weight: 700;
    color: var(--text-muted); background: transparent;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    padding: 6px 14px; cursor: pointer;
    font-family: 'Montserrat', sans-serif;
    transition: all var(--transition-fast);
}

.re-cancel-btn:hover { color: var(--primary); border-color: rgba(164,56,37,0.3); background: var(--primary-light); }

/* ── Empty state ── */
.re-empty {
    grid-column: 1 / -1;
    background: var(--white);
    border-radius: 22px;
    border: 2px dashed #e4e0db;
    padding: 60px 32px;
    text-align: center;
}

.re-empty-icon {
    width: 72px; height: 72px;
    border-radius: 20px;
    background: linear-gradient(135deg, rgba(164,56,37,0.1), rgba(164,56,37,0.04));
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
    border: 1.5px solid rgba(164,56,37,0.12);
}

.re-empty-icon i { font-size: 1.8rem; color: var(--primary); }
.re-empty h3 { font-size: 1.15rem; font-weight: 800; color: var(--text-dark); margin-bottom: 10px; letter-spacing: -0.02em; }
.re-empty p  { font-size: 0.85rem; color: var(--text-muted); max-width: 260px; margin: 0 auto 24px; line-height: 1.7; }

.re-browse-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 24px;
    background: var(--primary); color: white;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.85rem; font-weight: 800;
    border: none; border-radius: 12px;
    cursor: pointer; transition: all var(--transition-fast);
    box-shadow: 0 6px 20px rgba(164,56,37,0.3);
    letter-spacing: 0.01em;
}

.re-browse-btn:hover { background: #8a2f1c; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(164,56,37,0.4); }

/* Toast */
.re-toast {
    position: fixed; bottom: 24px; right: 24px;
    background: var(--text-dark); color: white;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.82rem; font-weight: 500;
    padding: 12px 16px; border-radius: var(--radius-sm);
    display: flex; align-items: center; gap: 8px;
    z-index: 999;
    transform: translateY(60px); opacity: 0;
    transition: transform 0.25s ease, opacity 0.25s ease;
    pointer-events: none;
    box-shadow: var(--shadow-lg);
}

.re-toast.show { transform: translateY(0); opacity: 1; }
.re-toast i { color: #10B981; }

/* Responsive */
@media (max-width: 768px) {
    .sidebar { position: fixed; left: -100%; top: 0; height: 100%; z-index: 200; box-shadow: 8px 0 24px rgba(0,0,0,0.12); transition: left var(--transition); }
    .sidebar.active { left: 0; }
    .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 150; display: none; }
    .overlay.active { display: block; }
    .menu-toggle { display: block; color: var(--primary); background: white; border: 1px solid var(--border); padding: 8px; border-radius: var(--radius); box-shadow: var(--shadow-sm); cursor: pointer; }
    .content-area { width: 100%; padding: 20px 16px; }
    .re-hero { grid-template-columns: 1fr; }
    .re-hero-right { padding: 0 24px 28px; gap: 10px; }
    .re-hero-left { padding: 28px 24px 16px; }
    .re-grid { grid-template-columns: 1fr; }
    .re-controls { flex-direction: column; align-items: stretch; }
    .re-right-controls { margin-left: 0; justify-content: space-between; }
    .re-grid.list-view .re-card { flex-direction: column; height: auto; }
    .re-grid.list-view .re-card-img { width: 100%; height: 150px; }
    .re-grid.list-view .re-card-desc { display: block; }
}

@media (max-width: 480px) {
    .content-area { padding: 14px 12px; }
    .content-header h2 { font-size: var(--font-2xl); }
    .re-hero-left h3 { font-size: 1.6rem; }
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
            <a href="organizations.php" class="nav-link sub-link-item" title="Organization Directory">
                <i data-lucide="users"></i>
                <span class="link-text">Organizations</span>
            </a>
            <a href="registered_events.php" class="nav-link sub-link-item active" title="My Saved Events">
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
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
        </header>

        <div class="re-container">

            <?php
                $total    = count($registered_events);
                $upcoming = count(array_filter($registered_events, fn($e) => reGetDaysLabel($e['event_date']) !== 'Ended'));
                $past     = $total - $upcoming;
            ?>

            <!-- Hero -->
            <div class="re-hero">
                <div class="re-hero-left">
                    <div class="re-eyebrow">
                        <span class="re-eyebrow-dot"></span>TIP Manila · Architecture
                    </div>
                    <h3>My Registered<br>Events</h3>
                    <p class="re-hero-sub">Track your upcoming events and attendance history — all in one place.</p>
                    <div class="re-hero-tags">
                        <span class="re-hero-tag">SY 2024-2025</span>
                        <span class="re-hero-tag"><i class="fas fa-check" style="font-size:9px;margin-right:3px"></i> Active Member</span>
                    </div>
                </div>
                <div class="re-hero-right">
                    <div class="re-stat-pill">
                        <span class="stat-num"><?php echo $total; ?></span>
                        <span class="stat-label">Total</span>
                    </div>
                    <div class="re-stat-pill">
                        <span class="stat-num"><?php echo $upcoming; ?></span>
                        <span class="stat-label">Upcoming</span>
                    </div>
                    <div class="re-stat-pill">
                        <span class="stat-num"><?php echo $past; ?></span>
                        <span class="stat-label">Attended</span>
                    </div>
                </div>
            </div>

            <?php if (!empty($registered_events)): ?>

            <!-- Controls -->
            <div class="re-controls">
                <div class="re-search-wrap">
                    <i class="fas fa-search re-search-icon"></i>
                    <input type="text" class="re-search" id="reSearch"
                           placeholder="Search events, organizers, locations…" oninput="reRender()">
                </div>
                <div class="re-right-controls">
                    <div class="re-tabs" id="reTabs">
                        <button class="re-tab active" data-filter="all"      onclick="reSetFilter(this)">All</button>
                        <button class="re-tab"        data-filter="upcoming" onclick="reSetFilter(this)">Upcoming</button>
                        <button class="re-tab"        data-filter="past"     onclick="reSetFilter(this)">Past</button>
                    </div>
                    <div class="re-sort">
                        <span class="re-sort-label">Sort</span>
                        <select class="re-select" id="reSort" onchange="reRender()">
                            <option value="date-asc">Date ↑</option>
                            <option value="date-desc">Date ↓</option>
                            <option value="name-asc">Name A–Z</option>
                            <option value="name-desc">Name Z–A</option>
                        </select>
                    </div>
                    <div class="re-view-toggle">
                        <button class="re-view-btn active" id="reBtnGrid" title="Grid view" onclick="reSetView('grid')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                            </svg>
                        </button>
                        <button class="re-view-btn" id="reBtnList" title="List view" onclick="reSetView('list')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                                <line x1="8" y1="18" x2="21" y2="18"/>
                                <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/>
                                <line x1="3" y1="18" x2="3.01" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="re-results-meta" id="reResultsMeta"></div>

            <!-- Grid -->
            <div class="re-grid" id="reGrid">
                <?php foreach ($registered_events as $idx => $event): ?>
                <?php
                    $daysLabel = reGetDaysLabel($event['event_date']);
                    $isPast    = $daysLabel === 'Ended';
                    $fmtDate   = date('M j, Y', strtotime($event['event_date']));
                    $img       = htmlspecialchars($event['event_image']);
                    $name      = htmlspecialchars($event['event_name']);
                    $org       = htmlspecialchars($event['organizer']);
                    $loc       = htmlspecialchars($event['location']);
                    $time      = htmlspecialchars($event['time']);
                    $desc      = htmlspecialchars($event['event_description']);
                    $statusCls = $isPast ? 'past' : 'upcoming';
                    $statusTxt = $isPast ? 'Past Event' : 'Upcoming';
                    $eventId   = (int)$event['id'];
                ?>
                <div class="re-card"
                     data-status="<?php echo $statusCls; ?>"
                     data-date="<?php echo htmlspecialchars($event['event_date']); ?>"
                     data-name="<?php echo strtolower($name); ?>"
                     data-org="<?php echo strtolower($org); ?>"
                     data-loc="<?php echo strtolower($loc); ?>">

                    <div class="re-card-img">
                        <img src="<?php echo $img; ?>"
                             alt="<?php echo $name; ?>"
                             loading="lazy"
                             onerror="this.src='https://placehold.co/600x300/1a1a1a/ffffff?text=<?php echo urlencode($name); ?>'">
                        <div class="re-img-overlay"></div>
                        <div class="re-img-top">
                            <span class="re-status-badge <?php echo $statusCls; ?>"><?php echo $statusTxt; ?></span>
                            <?php if (!$isPast): ?>
                            <span class="re-days-tag">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <?php echo htmlspecialchars($daysLabel); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="re-img-bottom">
                            <div class="re-card-title-overlay"><?php echo $name; ?></div>
                        </div>
                    </div>

                    <div class="re-card-body">
                        <div class="re-org-line">
                            <span class="re-org-dot"></span>
                            <?php echo $org; ?>
                        </div>
                        <div class="re-card-meta">
                            <div class="re-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo $fmtDate; ?> &nbsp;·&nbsp; <?php echo $time; ?>
                            </div>
                            <div class="re-meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo $loc; ?>
                            </div>
                        </div>
                        <div class="re-card-desc"><?php echo $desc; ?></div>
                        <div class="re-card-footer">
                            <span class="re-reg-chip">
                                <i class="fas fa-check"></i>
                                <?php echo $isPast ? 'Attended' : 'Registered'; ?>
                            </span>
                            <?php if (!$isPast): ?>
                            <button class="re-cancel-btn" onclick="reCancelReg(<?php echo $eventId; ?>, this)">
                                Cancel
                            </button>
                            <?php else: ?>
                            <button class="re-cancel-btn" style="color:#ccc;cursor:default;pointer-events:none">Ended</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php else: ?>

            <div class="re-grid">
                <div class="re-empty">
                    <div class="re-empty-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>No registered events yet</h3>
                    <p>You haven't signed up for any events. Browse what's happening and reserve your spot.</p>
                    <button class="re-browse-btn" onclick="window.location.href='<?php echo htmlspecialchars($homeLink); ?>'">
                        <i class="fas fa-search"></i> Browse Events
                    </button>
                </div>
            </div>

            <?php endif; ?>

        </div><!-- /.re-container -->
    </main>

    <div class="overlay" id="mobile-overlay"></div>
</div>

<!-- Toast -->
<div class="re-toast" id="reToast">
    <i class="fas fa-check-circle"></i>
    <span id="reToastMsg">Done</span>
</div>

<script>
(function () {
    var currentFilter = 'all';
    var grid = document.getElementById('reGrid');

    function cards() {
        return grid ? Array.from(grid.querySelectorAll('.re-card')) : [];
    }

    window.reSetFilter = function (btn) {
        document.querySelectorAll('.re-tab').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        currentFilter = btn.dataset.filter;
        reRender();
    };

    window.reSetView = function (v) {
        if (!grid) return;
        grid.classList.toggle('list-view', v === 'list');
        document.getElementById('reBtnGrid').classList.toggle('active', v === 'grid');
        document.getElementById('reBtnList').classList.toggle('active', v === 'list');
    };

    window.reRender = function () {
        if (!grid) return;
        var q      = document.getElementById('reSearch') ? document.getElementById('reSearch').value.trim().toLowerCase() : '';
        var sortBy = document.getElementById('reSort')   ? document.getElementById('reSort').value : 'date-asc';

        cards().forEach(function (c) {
            var matchF = currentFilter === 'all' || c.dataset.status === currentFilter;
            var matchQ = !q || c.dataset.name.includes(q) || c.dataset.org.includes(q) || c.dataset.loc.includes(q);
            c.style.display = (matchF && matchQ) ? '' : 'none';
        });

        var visible = cards().filter(function (c) { return c.style.display !== 'none'; });

        visible.sort(function (a, b) {
            if (sortBy === 'date-asc')  return new Date(a.dataset.date) - new Date(b.dataset.date);
            if (sortBy === 'date-desc') return new Date(b.dataset.date) - new Date(a.dataset.date);
            if (sortBy === 'name-asc')  return a.dataset.name.localeCompare(b.dataset.name);
            if (sortBy === 'name-desc') return b.dataset.name.localeCompare(a.dataset.name);
            return 0;
        });

        visible.forEach(function (c) { grid.appendChild(c); });

        var meta = document.getElementById('reResultsMeta');
        if (meta) meta.textContent = visible.length + ' event' + (visible.length !== 1 ? 's' : '');
    };

    window.reCancelReg = function (eventId, btn) {
        if (!confirm('Cancel your registration for this event?')) return;
        var orig = btn.textContent;
        btn.textContent = 'Cancelling…';
        btn.disabled = true;

        fetch('cancel_registration.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: eventId })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var card = btn.closest('.re-card');
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity    = '0';
                card.style.transform  = 'scale(0.96)';
                setTimeout(function () { card.remove(); reRender(); reShowToast('Registration cancelled'); }, 300);
            } else {
                btn.textContent = orig;
                btn.disabled    = false;
                reShowToast('Could not cancel. Please try again.');
            }
        })
        .catch(function () {
            btn.textContent = orig;
            btn.disabled    = false;
        });
    };

    window.reShowToast = function (msg) {
        var t = document.getElementById('reToast');
        document.getElementById('reToastMsg').textContent = msg;
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3000);
    };

    document.addEventListener('DOMContentLoaded', reRender);
})();

document.getElementById("menu-toggle-left").addEventListener("click", function () {
    document.getElementById("left-sidebar").classList.toggle("active");
    document.getElementById("mobile-overlay").classList.toggle("active");
});

document.getElementById("mobile-overlay").addEventListener("click", function () {
    document.getElementById("left-sidebar").classList.remove("active");
    this.classList.remove("active");
});

document.getElementById("collapse-toggle").addEventListener("click", function () {
    document.getElementById("left-sidebar").classList.toggle("collapsed");
});

lucide.createIcons();
</script>

<script src="../JavaScript/Student_ds.js"></script>
</body>
</html>