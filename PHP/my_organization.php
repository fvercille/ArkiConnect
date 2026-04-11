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

$current_org = [
    'id' => 1,
    'name' => 'Architectural Students\' Association of the Philippines - TIP Manila',
    'acronym' => 'ASAPHIL',
    'logo_path' => '../Images/ASPHIL.jpg',
    'description' => 'Embark on an unimaginable adventure with us, the Architectural Students Association of the Philippines, Incorporated - Technological Institute of the Philippines Manila (ASAPhil, Inc. - TIPM) Chapter, towards boundless exploration, and limitless possibilities, as Isagani leads our way to a world where imagination and unforeseen grounds of architecture coalesced.
Fan the flames of curiosity and venture an adventure like no other!',
    'members_count' => 0,
    'upcoming_events' => 0,
    'founded' => '2014',
    'contact_email' => 'asaphiltipmanila@gmail.com',
    'contact_phone' => '0908 211 7835',
    'images' => ['../Images/Image1.jpg', '../Images/Image2.jpg', '../Images/Image3.jpg']
];



$current_past_events = [
    ['title' => 'ArkiFeud', 'date' => '2024-11-26', 'desc' => 'Gather your teammates and get ready to bring the heat as we take the fun to a whole new level!, ASAPHIL-TIP introduces ArkiFeud. This isnt just a game - its a battle of wits, teamwork, and quick thinking.', 'img' => '../Images/ArkiFeud.jpg'],
    ['title' => 'Liwayway', 'date' => '2024-11-26', 'desc' => 'Rising above the challenge of designing spaces, ASAPHIL-TIP introduces a typhoon-resilient housing design competition entitled LIWAYWAY: Crafting Visions Through Greater Heights.', 'img' => '../Images/Liwayway.jpg'],
];

$current_officers = [
    ['name' => 'Reign Sieltel Agtagma', 'position' => 'Chapter Chairperson', 'img' => '../Images/Asaphil2.jpg'],
    ['name' => 'Yvonne Gwen Rosco', 'position' => 'Vice President in Academic Affairs', 'img' => '../Images/Asaphil3.jpg'],
    ['name' => 'Aleah Hope Arbolado', 'position' => 'Vice President in Internal Affairs', 'img' => '../Images/Asaphil4.jpg'],
    ['name' => 'Cyan Manalese', 'position' => 'Vice President in External Affairs', 'img' => '../Images/Asaphil6.jpg'],
    ['name' => 'Misha Angela Chua', 'position' => 'Chapter Secretary', 'img' => '../Images/Asaphil7.jpg'],
    ['name' => 'Franz Shane Ranola', 'position' => 'Associate Secretary', 'img' => '../Images/Asaphil8.jpg'],
    ['name' => 'Rhizza Garbee Ladines', 'position' => 'Chapter Treasurer', 'img' => '../Images/Asaphil9.jpg'],
    ['name' => 'Jasmine Batalla', 'position' => 'Audit Director', 'img' => '../Images/Asaphil10.jpg'],
    ['name' => 'Lezlei Joi Del Mundo', 'position' => 'Membership Directress', 'img' => '../Images/Asaphil12.jpg'],
    ['name' => 'Aaron Roxas', 'position' => 'Creative Director', 'img' => '../Images/Asaphil13.jpg'],
    ['name' => 'Devaki Dasi Quiambao', 'position' => 'Creative Media Staff', 'img' => '../Images/Asaphil14.jpg'],
    ['name' => 'Aaron Jarred Doria', 'position' => 'Creative Media Staff', 'img' => '../Images/Asaphil15.jpg'],
    ['name' => 'Jana Monique Tarayao', 'position' => 'Photo Documentation', 'img' => '../Images/Asaphil16.jpg'],
    ['name' => 'Yuriel Rani Benitiz', 'position' => 'Photo Documentation', 'img' => '../Images/Asaphil17.jpg'],
    ['name' => 'Ivy Figuracion', 'position' => 'Social Media Directress', 'img' => '../Images/Asaphil18.jpg'],
    ['name' => 'Shaine Alcaide', 'position' => 'Commissioner on Social Media', 'img' => '../Images/Asaphil19.jpg'],
    ['name' => 'Jhustine Ogario', 'position' => 'Commissioner on Logistics', 'img' => '../Images/Asaphil20.jpg'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Organization - Org Representative</title>
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
            --red-light: #faece7;
            --red-mid:   #f0997b;
            --amber:     #ffc107;
            --amber-light:#faeeda;
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
           SIDEBAR
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

        .nav-link i, .nav-link svg { width: 20px; text-align: center; font-size: 1rem; flex-shrink: 0; }
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
        .sidebar.collapsed .nav-link i,
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
           ORG CONTENT GRID
        ══════════════════════════════ */
        .org-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            align-items: start;
        }

        .org-left { display: flex; flex-direction: column; gap: 20px; }
        .org-right { display: flex; flex-direction: column; gap: 20px; }

        /* Cards */
        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i { color: var(--primary); font-size: 0.8rem; }
        .card-body { padding: 20px; }

        /* Hero card */
        .hero-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 24px;
            display: flex;
            gap: 24px;
            align-items: flex-start;
        }

        .hero-logo {
            width: 88px; height: 88px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            object-fit: cover;
            flex-shrink: 0;
        }

        .hero-right { flex: 1; min-width: 0; }

        .hero-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .hero-badge {
            display: inline-block;
            background: var(--red-light);
            color: var(--primary-dk);
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 14px;
        }

        .hero-desc {
            font-size: 0.82rem;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .stats-row {
            display: flex;
            gap: 0;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .stat {
            flex: 1;
            padding: 12px 10px;
            text-align: center;
            border-right: 1px solid var(--border);
        }

        .stat:last-child { border-right: none; }
        .stat-val { font-size: 1.3rem; font-weight: 700; color: var(--primary); }
        .stat-lbl { font-size: 0.72rem; color: var(--muted); margin-top: 2px; }

        .hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        /* Buttons */
        .btn {
            padding: 8px 16px;
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

        .btn-primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 3px 10px rgba(164,56,37,0.28);
        }

        .btn-primary:hover { background: var(--primary-dk); border-color: var(--primary-dk); }

        .btn-sm { padding: 5px 12px; font-size: 0.78rem; }

        /* About */
        .about-text {
            font-size: 0.88rem;
            color: #555;
            line-height: 1.75;
            white-space: pre-wrap;
        }

        /* Events */
        .event-list { display: flex; flex-direction: column; gap: 12px; }

        .event-row {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            padding: 14px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid var(--border);
            transition: border-color 0.15s;
        }

        .event-row:hover { border-color: var(--primary); }

        .event-img {
            width: 70px; height: 70px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid var(--border);
            background: var(--red-light);
        }

        .event-info { flex: 1; min-width: 0; }
        .event-title { font-size: 0.88rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }

        .event-date {
            font-size: 0.78rem;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 5px;
        }

        .event-desc {
            font-size: 0.78rem;
            color: var(--muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 700;
        }

        .tag-upcoming { background: #d1fae5; color: #065f46; }
        .tag-past { background: #f3f4f6; color: #6b7280; border: 1px solid var(--border); }

        .section-divider {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0 12px;
        }

        .section-divider span {
            font-size: 0.72rem;
            color: var(--muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
        }

        .section-divider hr { flex: 1; border: none; border-top: 1px dashed #e5e7eb; }

        /* Gallery */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .gal-item {
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
            position: relative;
        }

        .gal-item img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .gal-item .delete-btn {
            position: absolute;
            top: 6px; right: 6px;
            background: rgba(220,53,69,0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px; height: 28px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .gal-item:hover .delete-btn { display: flex; }

        .gal-add {
            aspect-ratio: 1;
            border: 2px dashed var(--border);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            gap: 6px;
            transition: all 0.15s;
            background: #fafafa;
        }

        .gal-add:hover { border-color: var(--primary); background: #fff; }
        .gal-add i { font-size: 1.2rem; color: var(--muted); }
        .gal-add span { font-size: 0.72rem; color: var(--muted); font-weight: 600; }

        /* Officers */
        .officers-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .officer-card {
            padding: 14px 10px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid var(--border);
            text-align: center;
            transition: border-color 0.15s;
        }

        .officer-card:hover { border-color: var(--primary); }

        .officer-avatar {
            width: 52px; height: 52px;
            border-radius: 50%;
            border: 2px solid var(--red-mid);
            object-fit: cover;
            margin: 0 auto 8px;
            display: block;
            background: var(--red-light);
        }

        .officer-name { font-size: 0.78rem; font-weight: 700; color: #1a1a2e; margin-bottom: 2px; line-height: 1.3; }
        .officer-pos { font-size: 0.72rem; color: var(--primary); font-weight: 600; line-height: 1.3; }

        .officer-actions {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 8px;
        }

        .officer-add {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 2px dashed var(--border);
            background: transparent;
            cursor: pointer;
            transition: all 0.15s;
        }

        .officer-add:hover { border-color: var(--primary); background: #fff; }
        .officer-add i { font-size: 1.2rem; color: var(--muted); }
        .officer-add span { font-size: 0.72rem; color: var(--muted); font-weight: 600; }

        /* Sidebar cards */
        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.82rem;
        }

        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-row i { color: var(--primary); font-size: 0.85rem; margin-top: 2px; min-width: 16px; }
        .info-label { font-size: 0.72rem; color: var(--muted); font-weight: 600; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.04em; }
        .info-val { color: #1a1a2e; font-weight: 600; }

        .quick-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 0.83rem;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
            width: 100%;
            font-family: 'Montserrat', sans-serif;
        }

        .quick-btn:hover { background: var(--red-light); color: var(--primary); border-color: var(--red-mid); }
        .quick-btn i { color: var(--primary); font-size: 0.85rem; }

        .metric-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .metric-box {
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid var(--border);
            padding: 14px;
            text-align: center;
        }

        .metric-val { font-size: 1.4rem; font-weight: 700; color: var(--primary); }
        .metric-lbl { font-size: 0.72rem; color: var(--muted); font-weight: 600; margin-top: 2px; }

        /* Edit mode */
        .editable-field {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.88rem;
            color: var(--text);
            transition: border-color 0.15s;
        }

        .editable-field:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(164,56,37,0.08); }
        textarea.editable-field { resize: vertical; min-height: 100px; }

        /* Save bar */
        .save-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 4px;
        }

        /* Responsive */
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
            .org-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .hero-card { flex-direction: column; }
            .officers-grid { grid-template-columns: repeat(2, 1fr); }
            .gallery-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">

    <!-- SIDEBAR -->
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
            <a href="registration_reports.php" class="nav-link" title="Registration Reports">
                <i data-lucide="file-text"></i>
                <span class="link-text">Registration Reports</span>
            </a>
            <hr class="separator">
            <a href="my_organization.php" class="nav-link active" title="My Organization">
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
                <h1>My Organization</h1>
                <p>Manage your organization's profile, events, gallery and officers</p>
            </div>
            <div class="time-display">
                <i class="fas fa-clock"></i>
                <span class="time-text"></span>
            </div>
        </header>

        <!-- Hero Card -->
        <div class="hero-card">
            <div style="position:relative;">
                <img src="<?= htmlspecialchars($current_org['logo_path']) ?>" alt="Logo" class="hero-logo" id="heroLogo">
                <input type="file" id="logoUploadHero" accept="image/*" style="display:none;" onchange="previewHeroLogo(event)">
            </div>
            <div class="hero-right">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px;">
                    <span class="hero-name" id="orgNameDisplay"><?= htmlspecialchars($current_org['name']) ?></span>
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:0.72rem;padding:2px 8px;border-radius:20px;font-weight:700;background:var(--amber-light);color:#633806;"> Active Org</span>
                </div>
                <span class="hero-badge"><?= htmlspecialchars($current_org['acronym']) ?></span>
                <p class="hero-desc"><?= htmlspecialchars($current_org['description']) ?></p>
                <div class="stats-row">
                    <div class="stat">
                        <div class="stat-val"><?= $current_org['members_count'] ?></div>
                        <div class="stat-lbl">Members</div>
                    </div>
                    <div class="stat">
                        <div class="stat-val"><?= $current_org['upcoming_events'] ?></div>
                        <div class="stat-lbl">Upcoming</div>
                    </div>
                    <div class="stat">
                        <div class="stat-val"><?= $current_org['founded'] ?></div>
                        <div class="stat-lbl">Founded</div>
                    </div>
                    <div class="stat">
                        <div class="stat-val"><?= count($current_officers) ?></div>
                        <div class="stat-lbl">Officers</div>
                    </div>
                </div>
                <div class="hero-actions">
                    <button class="btn btn-primary" onclick="toggleEditMode('hero')">
                        <i class="fas fa-edit"></i> Edit Organization Info
                    </button>
                    <a href="http://localhost:8080/ArkiConnect/PHP/organizations_details.php?id=<?= htmlspecialchars($current_org['id']) ?>&from=org_rep" class="btn">
    <i class="fas fa-eye"></i> View Public Page
</a>
                </div>
            </div>
        </div>

        <!-- Org Grid -->
        <div class="org-grid">

            <!-- LEFT COLUMN -->
            <div class="org-left">

                <!-- About -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title"><i class="fas fa-info-circle"></i> About</span>
                        <button class="btn btn-sm" onclick="toggleEditMode('about')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="aboutView">
                            <p class="about-text"><?= htmlspecialchars($current_org['description']) ?></p>
                        </div>
                        <div id="aboutEdit" style="display:none;">
                            <textarea class="editable-field" id="aboutText"><?= htmlspecialchars($current_org['description']) ?></textarea>
                            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                                <button class="btn btn-sm" onclick="cancelEdit('about')">Cancel</button>
                                <button class="btn btn-sm btn-primary" onclick="saveAbout()">Save</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Events -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title"><i class="fas fa-calendar-alt"></i> Events</span>
                        <a href="create_event.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Event
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Upcoming -->
                        <div class="section-divider"><hr><span>Upcoming</span><hr></div>
                        <div class="event-list" style="margin-bottom:16px;">
                            <?php if (!empty($current_upcoming_events)): ?>
                                <?php foreach ($current_upcoming_events as $event): ?>
                                    <div class="event-row">
                                        <img src="<?= htmlspecialchars($event['img']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="event-img">
                                        <div class="event-info">
                                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                                <span class="event-title"><?= htmlspecialchars($event['title']) ?></span>
                                                <span class="tag tag-upcoming">Upcoming</span>
                                            </div>
                                            <div class="event-date">
                                                <i class="fas fa-calendar"></i>
                                                <?= date('M d, Y', strtotime($event['date'])) ?>
                                            </div>
                                            <p class="event-desc"><?= htmlspecialchars($event['desc']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="font-size:0.82rem;color:var(--muted);">No upcoming events.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Past -->
                        <div class="section-divider"><hr><span>Past</span><hr></div>
                        <div class="event-list">
                            <?php if (!empty($current_past_events)): ?>
                                <?php foreach ($current_past_events as $event): ?>
                                    <div class="event-row">
                                        <img src="<?= htmlspecialchars($event['img']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="event-img">
                                        <div class="event-info">
                                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                                <span class="event-title"><?= htmlspecialchars($event['title']) ?></span>
                                                <span class="tag tag-past">Past</span>
                                            </div>
                                            <div class="event-date" style="color:var(--muted);">
                                                <i class="fas fa-calendar"></i>
                                                <?= date('M Y', strtotime($event['date'])) ?>
                                            </div>
                                            <p class="event-desc"><?= htmlspecialchars($event['desc']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="font-size:0.82rem;color:var(--muted);">No past events.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Gallery -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title"><i class="fas fa-images"></i> Gallery</span>
                        <button class="btn btn-sm" onclick="document.getElementById('galleryUpload').click()">
                            <i class="fas fa-upload"></i> Add Image
                        </button>
                        <input type="file" id="galleryUpload" accept="image/*" style="display:none;">
                    </div>
                    <div class="card-body">
                        <div class="gallery-grid">
                            <?php foreach ($current_org['images'] as $img): ?>
                                <div class="gal-item">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Gallery Image">
                                    <button class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            <?php endforeach; ?>
                            <div class="gal-add" onclick="document.getElementById('galleryUpload').click()">
                                <i class="fas fa-plus"></i>
                                <span>Add photo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Officers -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title">
                            <i class="fas fa-users"></i> Officers
                            <span style="font-size:0.72rem;color:var(--muted);font-weight:400;text-transform:none;letter-spacing:0;"><?= count($current_officers) ?> members</span>
                        </span>
                        <button class="btn btn-sm"><i class="fas fa-edit"></i> Edit</button>
                    </div>
                    <div class="card-body">
                        <div class="officers-grid">
                            <?php foreach ($current_officers as $officer): ?>
                                <div class="officer-card">
                                    <img src="<?= htmlspecialchars($officer['img']) ?>" alt="<?= htmlspecialchars($officer['name']) ?>" class="officer-avatar">
                                    <div class="officer-name"><?= htmlspecialchars($officer['name']) ?></div>
                                    <div class="officer-pos"><?= htmlspecialchars($officer['position']) ?></div>
                                    <div class="officer-actions">
                                        <button class="btn btn-sm" style="padding:3px 8px;font-size:0.72rem;">Edit</button>
                                        <button class="btn btn-sm" style="padding:3px 8px;font-size:0.72rem;color:var(--muted);">Remove</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="officer-card officer-add">
                                <i class="fas fa-plus"></i>
                                <span>Add officer</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end org-left -->

            <!-- RIGHT COLUMN -->
            <div class="org-right">

                <!-- Contact Info -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title"><i class="fas fa-address-card"></i> Contact</span>
                        <button class="btn btn-sm"><i class="fas fa-edit"></i> Edit</button>
                    </div>
                    <div class="card-body" style="padding:12px 20px;">
                        <div class="info-row">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <div class="info-label">Email</div>
                                <div class="info-val"><?= htmlspecialchars($current_org['contact_email']) ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-phone"></i>
                            <div>
                                <div class="info-label">Phone</div>
                                <div class="info-val"><?= htmlspecialchars($current_org['contact_phone']) ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <div class="info-label">Founded</div>
                                <div class="info-val"><?= htmlspecialchars($current_org['founded']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title"><i class="fas fa-bolt"></i> Quick Actions</span>
                    </div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                        <a href="create_event.php" class="quick-btn">
                            <i class="fas fa-plus-circle"></i> Create new event
                        </a>
                        <a href="registration_reports.php" class="quick-btn">
                            <i class="fas fa-calendar-alt"></i> View registrations
                        </a>
                        <a href="registration_reports.php" class="quick-btn">
                            <i class="fas fa-file-export"></i> Export member list
                        </a>
                    </div>
                </div>

                <!-- Membership -->
                <div class="card">
                    <div class="card-head">
                        <span class="card-title"><i class="fas fa-users"></i> Membership</span>
                    </div>
                    <div class="card-body">
                        <div class="metric-grid">
                            <div class="metric-box">
                                <div class="metric-val"><?= $current_org['members_count'] ?></div>
                                <div class="metric-lbl">Total members</div>
                            </div>
                            <div class="metric-box">
                                <div class="metric-val"><?= count($current_officers) ?></div>
                                <div class="metric-lbl">Officers</div>
                            </div>
                        </div>
                        <button class="btn" style="width:100%;margin-top:12px;justify-content:center;font-size:0.78rem;">
                            Manage members
                        </button>
                    </div>
                </div>

            </div><!-- end org-right -->

        </div><!-- end org-grid -->

        <!-- Save Bar -->
        <div class="save-bar">
            <button class="btn btn-sm">Discard changes</button>
            <button class="btn btn-sm btn-primary">
                <i class="fas fa-check"></i> Save all changes
            </button>
        </div>

    </main>
</div>

<script>
lucide.createIcons();

// Sidebar collapse
document.getElementById('collapse-toggle').addEventListener('click', function () {
    document.getElementById('left-sidebar').classList.toggle('collapsed');
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

// Hero logo preview
window.previewHeroLogo = function(evt) {
    const file = evt.target.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const url = URL.createObjectURL(file);
    const img = document.getElementById('heroLogo');
    img.src = url;
    img.onload = () => URL.revokeObjectURL(url);
};

// Toggle edit mode
window.toggleEditMode = function(mode) {
    if (mode === 'about') {
        const view = document.getElementById('aboutView');
        const edit = document.getElementById('aboutEdit');
        const visible = edit.style.display !== 'none';
        edit.style.display = visible ? 'none' : '';
        view.style.display = visible ? '' : 'none';
        if (!visible) setTimeout(() => document.getElementById('aboutText').focus(), 50);
        return;
    }

    if (mode === 'hero') {
        let container = document.getElementById('heroEditContainer');
        if (container) { container.remove(); return; }

        container = document.createElement('div');
        container.id = 'heroEditContainer';
        container.style.cssText = 'margin-top:16px;padding-top:14px;border-top:1px dashed var(--border);';
        container.innerHTML = `
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <label style="display:block;font-size:0.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Organization Name</label>
                    <input id="heroNameInput" class="editable-field" type="text" value="${(document.getElementById('orgNameDisplay').textContent || '').trim()}">
                </div>
                <div style="min-width:140px;">
                    <label style="display:block;font-size:0.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Logo</label>
                    <button type="button" class="btn btn-sm" id="heroLogoPickBtn"><i class="fas fa-camera"></i> Choose</button>
                    <input type="file" id="heroLogoUploadInline" accept="image/*" style="display:none;">
                </div>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                <button class="btn btn-sm" id="heroCancelBtn">Cancel</button>
                <button class="btn btn-sm btn-primary" id="heroSaveBtn"><i class="fas fa-check"></i> Save</button>
            </div>
        `;

        document.querySelector('.hero-card').appendChild(container);

        document.getElementById('heroLogoPickBtn').addEventListener('click', () =>
            document.getElementById('heroLogoUploadInline').click()
        );
        document.getElementById('heroLogoUploadInline').addEventListener('change', (e) =>
            window.previewHeroLogo(e)
        );
        document.getElementById('heroCancelBtn').addEventListener('click', () => container.remove());
        document.getElementById('heroSaveBtn').addEventListener('click', () => {
            const newName = (document.getElementById('heroNameInput').value || '').trim();
            if (newName) document.getElementById('orgNameDisplay').textContent = newName;
            container.remove();
            showMsg('Organization info updated.');
        });
    }
};

window.cancelEdit = function(mode) {
    if (mode === 'about') {
        document.getElementById('aboutEdit').style.display = 'none';
        document.getElementById('aboutView').style.display = '';
    }
};

window.saveAbout = function() {
    const text = document.getElementById('aboutText').value.trim();
    document.getElementById('aboutView').innerHTML =
        `<p class="about-text">${escHtml(text)}</p>`;
    document.getElementById('aboutEdit').style.display = 'none';
    document.getElementById('aboutView').style.display = '';
    showMsg('About section updated.');
};

// Gallery upload
document.getElementById('galleryUpload').addEventListener('change', function() {
    const grid = document.querySelector('.gallery-grid');
    const addBox = grid.querySelector('.gal-add');
    Array.from(this.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const url = URL.createObjectURL(file);
        const item = document.createElement('div');
        item.className = 'gal-item';
        item.innerHTML = `<img src="${url}" alt="Gallery Image"><button class="delete-btn"><i class="fas fa-trash-alt"></i></button>`;
        item.querySelector('img').onload = () => URL.revokeObjectURL(url);
        grid.insertBefore(item, addBox);
    });
    this.value = '';
});

// Gallery delete (delegation)
document.querySelector('.gallery-grid').addEventListener('click', function(e) {
    const btn = e.target.closest('.delete-btn');
    if (!btn) return;
    if (confirm('Remove this image?')) btn.closest('.gal-item').remove();
});

// Officers delegation
document.querySelector('.officers-grid').addEventListener('click', function(e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    const card = btn.closest('.officer-card');
    if (!card) return;
    const label = btn.textContent.trim().toLowerCase();
    if (label === 'edit') {
        const name = prompt('Officer name:', card.querySelector('.officer-name').textContent);
        if (name === null) return;
        const pos = prompt('Position:', card.querySelector('.officer-pos').textContent);
        if (pos === null) return;
        card.querySelector('.officer-name').textContent = name.trim();
        card.querySelector('.officer-pos').textContent = pos.trim();
        showMsg('Officer updated.');
    } else if (label === 'remove') {
        if (confirm('Remove this officer?')) { card.remove(); showMsg('Officer removed.'); }
    }
});

// Temp message helper
function showMsg(msg) {
    let box = document.getElementById('tempMsgBox');
    if (!box) {
        box = document.createElement('div');
        box.id = 'tempMsgBox';
        box.style.cssText = 'position:fixed;top:18px;right:18px;background:rgba(26,26,26,0.92);color:white;padding:10px 16px;border-radius:10px;z-index:9999;font-size:0.83rem;font-weight:600;font-family:Montserrat,sans-serif;box-shadow:0 6px 24px rgba(0,0,0,0.2);transition:opacity 0.3s;';
        document.body.appendChild(box);
    }
    box.textContent = msg;
    box.style.opacity = '1';
    clearTimeout(box._t);
    box._t = setTimeout(() => { box.style.opacity = '0'; setTimeout(() => box.remove(), 300); }, 2500);
}

function escHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}
</script>

</body>
</html>