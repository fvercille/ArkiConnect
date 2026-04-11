<?php
// organization_details.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$login_page_path = 'http://localhost/FinalProject1/PHP/login.php';

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

if (empty($user_id) || empty($role) || !in_array($role, $allowed_roles)) {
    session_unset();
    session_destroy();
    header("Location: " . $login_page_path);
    exit();
}

// Mock data
$organizations = [
    1 => [
        'id' => 1,
        'name' => 'Architectural Students\' Association of the Philippines - TIP Manila',
        'acronym' => 'ASAPHIL',
        'logo_path' => '../Images/ASPHIL.jpg',
        'description' => 'Embark on an unimaginable adventure with us, the Architectural Students Association of the Philippines, Incorporated - Technological Institute of the Philippines Manila (ASAPhil, Inc. - TIPM) Chapter, towards boundless exploration, and limitless possibilities, as Isagani leads our way to a world where imagination and unforeseen grounds of architecture coalesced. Fan the flames of curiosity and venture an adventure like no other!',
        'members_count' => 0,
        'upcoming_events' => 0,
        'president' => 'Juan Dela Cruz',
        'vice_president' => 'Maria Santos',
        'founded' => '2014',
        'contact_email' => 'asaphiltipmanila@gmail.com',
        'contact_phone' => '0908 211 7835',
        'images' => ['../Images/Image1.jpg', '../Images/Image2.jpg', '../Images/Image3.jpg']
    ],
    2 => [
        'id' => 2,
        'name' => 'United Architects of the Philippines Student Auxiliary - TIP Manila',
        'acronym' => 'UAPSA',
        'logo_path' => '../Images/UAPSA.jpg',
        'description' => 'The United Architects of the Philippines Student Auxiliary also known as UAPSA is a recognized student organization under the United Architects of the Philippines (UAP) whose aim is to gear up Architecture Students towards professionalism, camaraderie, and selfless service.',
        'members_count' => 0,
        'upcoming_events' => 0,
        'president' => 'Carlos Rodriguez',
        'vice_president' => 'Anna Gonzales',
        'founded' => '2013',
        'contact_email' => 'tipmanilauapsa@gmail.com',
        'contact_phone' => '(02) 8555-0001',
        'images' => ['../Images/Image4.jpg', '../Images/Image5.jpg', '../Images/Image6.jpg']
    ]
];



$past_events_mock = [
    1 => [
        ['title' => 'ArkiFeud', 'date' => '2024-11-26', 'desc' => 'Gather your teammates and get ready to bring the heat as we take the fun to a whole new level! ASAPHIL-TIP introduces ArkiFeud. This isn\'t just a game – it\'s a battle of wits, teamwork, and quick thinking.', 'img' => '../Images/ArkiFeud.jpg'],
        ['title' => 'Liwayway', 'date' => '2024-11-26', 'desc' => 'Rising above the challenge of designing spaces, ASAPHIL-TIP introduces a typhoon-resilient housing design competition entitled LIWAYWAY: Crafting Visions Through Greater Heights.', 'img' => '../Images/Liwayway.jpg'],
    ],
    2 => [
        ['title' => 'Verdantia', 'date' => '2025-03-25', 'desc' => 'Welcome to Verdantia, the seminar where innovative minds come together to turn that vision into reality!', 'img' => '../Images/Verdantia.jpg'],
        ['title' => 'NAW', 'date' => '2024-11-27', 'desc' => 'In celebration of AREX, we are formally inviting you to attend the seminar in celebration of the National Architecture Week.', 'img' => '../Images/NAW.jpg'],
    ]
];

$officers_mock = [
    1 => [
        ['name' => 'Reign Sieltel Agtagma', 'position' => 'Chapter Chairperson', 'img' => '../Images/Asaphil2.jpg'],
        ['name' => 'Yvonne Gwen Rosco', 'position' => 'VP in Academic Affairs', 'img' => '../Images/Asaphil3.jpg'],
        ['name' => 'Aleah Hope Arbolado', 'position' => 'VP in Internal Affairs', 'img' => '../Images/Asaphil4.jpg'],
        ['name' => 'Cyan Manalese', 'position' => 'VP in External Affairs', 'img' => '../Images/Asaphil6.jpg'],
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
    ],
    2 => [
        ['name' => 'Kristian Cabral', 'position' => 'Chapter President', 'img' => '../Images/UAPSA2.jpg'],
        ['name' => 'Ricja Alicia Dela Cruz', 'position' => 'VP For Planning and Development', 'img' => '../Images/UAPSA3.jpg'],
        ['name' => 'John Christian Miranda', 'position' => 'VP For Operations', 'img' => '../Images/UAPSA4.jpg'],
        ['name' => 'Vincent Nino Rull', 'position' => 'Secretary', 'img' => '../Images/UAPSA5.jpg'],
        ['name' => 'Hanne Broqueza', 'position' => 'Treasurer', 'img' => '../Images/UAPSA6.jpg'],
        ['name' => 'Kimberly Tayson', 'position' => 'Auditor', 'img' => '../Images/UAPSA7.jpg'],
        ['name' => 'Francis Andrei Gison', 'position' => 'Creative Director', 'img' => '../Images/UAPSA8.jpg'],
        ['name' => 'Dwight Ivan Mallillin', 'position' => 'Creative Director', 'img' => '../Images/UAPSA9.jpg'],
        ['name' => 'Erika Shane Aoalin', 'position' => 'Creative Staff', 'img' => '../Images/UAPSA10.jpg'],
        ['name' => 'Jello Mae Abad', 'position' => 'Creative Staff', 'img' => '../Images/UAPSA11.jpg'],
        ['name' => 'Louise Jamilla Sy', 'position' => 'Creative Staff', 'img' => '../Images/UAPSA13.jpg'],
        ['name' => 'Fatima Paula de Torres', 'position' => 'Creative Staff', 'img' => '../Images/UAPSA14.jpg'],
        ['name' => 'Elisha Chloe Valera', 'position' => 'Photo Documentation', 'img' => '../Images/UAPSA15.jpg'],
        ['name' => 'Krizzchelle Dewdeah Agapolo', 'position' => 'Photo Documentation', 'img' => '../Images/UAPSA16.jpg'],
        ['name' => 'Ashiel Zai Valdecantos', 'position' => 'Photo Documentation', 'img' => '../Images/UAPSA17.jpg'],
    ],
];

$org_id_raw = $_GET['id'] ?? 1;
$org_id = is_numeric($org_id_raw) ? (int)$org_id_raw : 1;

if (!isset($organizations[$org_id])) {
    header("Location: organizations.php");
    exit();
}

$current_org = $organizations[$org_id];
$current_upcoming_events = $upcoming_events_mock[$org_id] ?? [];
$current_past_events = $past_events_mock[$org_id] ?? [];
$current_officers = $officers_mock[$org_id] ?? [];

$userAvatarText = strtoupper(substr($userName, 0, 1));
$pageTitle = $current_org['acronym'] . ' - Organization Details';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/Student_db.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: #a43825;
            --primary-dark: #8a2e1f;
            --primary-light: rgba(164, 56, 37, 0.08);
            --accent: #ffdd1b;
            --bg: #f8f7f5;
            --card-bg: #ffffff;
            --text-dark: #1a1a2e;
            --text-muted: #6b7280;
            --border: #f0f0f0;
            --success: #10b981;
            --shadow-sm: 0 1px 4px rgba(0,0,0,0.06);
            --shadow: 0 4px 16px rgba(0,0,0,0.07);
            --shadow-lg: 0 8px 28px rgba(0,0,0,0.10);
            --radius: 16px;
            --radius-sm: 10px;
        }

        body {
            background: var(--bg);
            color: var(--text-dark);
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ── Header ── */
        .org-header {
            background: linear-gradient(135deg, #a43825 0%, #c0482e 60%, #8a2f1c 100%);
            color: white;
            padding: 18px 0;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
        }

        .org-header::after {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            pointer-events: none;
        }

        .org-header .container {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 1;
        }

        .org-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .back-btn {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1.5px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            padding: 9px 18px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateX(-2px);
        }

        /* ── Hero Section ── */
        .hero-section {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 28px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .hero-body {
            background: linear-gradient(135deg, #a43825 0%, #c0482e 60%, #8a2f1c 100%);
            padding: 28px 36px;
            display: flex;
            gap: 24px;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-body::after {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            pointer-events: none;
        }

        .hero-logo-wrap { flex-shrink: 0; }

        .hero-logo {
            width: 100px;
            height: 100px;
            border-radius: 20px;
            border: 4px solid rgba(255,255,255,0.4);
            object-fit: cover;
            box-shadow: var(--shadow);
            background: white;
        }

        .hero-content {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .hero-acronym {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .hero-content h2 {
            font-size: 1.4rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.02em;
            line-height: 1.3;
            margin-bottom: 16px;
        }

        .hero-stats {
            display: flex;
            gap: 24px;
            padding: 16px 0;
            border-top: 1px solid rgba(255,255,255,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 16px;
        }

        .stat { text-align: center; min-width: 80px; }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: white;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .hero-actions { display: flex; gap: 10px; }

        /* ── Main Grid ── */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
            margin-bottom: 40px;
            align-items: start;
        }

        /* ── Sections ── */
        .section {
            background: var(--card-bg);
            padding: 28px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            border: 1px solid var(--border);
            transition: box-shadow 0.2s ease;
        }

        .section:hover { box-shadow: var(--shadow-lg); }

        .section h3 {
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 18px;
            color: var(--text-dark);
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            border-bottom: 1.5px solid var(--border);
        }

        .section h3 i { color: var(--primary); font-size: 0.9rem; }

        .about-text {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.8;
        }

        /* ── Events ── */
        .events-grid { display: flex; flex-direction: column; gap: 16px; }

        .event-item {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 16px;
            display: flex;
            gap: 16px;
            transition: all 0.2s ease;
        }

        .event-item:hover {
            border-color: rgba(164,56,37,0.25);
            background: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .event-image {
            width: 90px;
            height: 90px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid var(--border);
        }

        .event-content h4 {
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-dark);
        }

        .event-content p {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .event-date {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--primary);
            font-weight: 700;
            font-size: 0.78rem;
            background: var(--primary-light);
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 6px;
        }

        /* ── Gallery ── */
        .gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .gallery-item {
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--border);
            transition: transform 0.2s ease;
            cursor: pointer;
        }

        .gallery-item:hover { transform: scale(1.03); }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* ── Lightbox ── */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.88);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .lightbox.active { display: flex; }

        .lightbox-img {
            max-width: 90vw;
            max-height: 88vh;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            object-fit: contain;
            animation: zoomIn 0.25s ease;
        }

        .lightbox-close {
            position: fixed;
            top: 20px;
            right: 24px;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.3);
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 10000;
        }

        .lightbox-close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.85); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* ── Sidebar ── */
        .sidebar-card {
            background: var(--card-bg);
            padding: 22px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            border: 1px solid var(--border);
            transition: box-shadow 0.2s ease;
        }

        .sidebar-card:hover { box-shadow: var(--shadow-lg); }

        .sidebar-card h4 {
            font-size: 0.92rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-card h4 i { color: var(--primary); }

        .contact-info { display: flex; flex-direction: column; gap: 12px; }

        .contact-item {
            font-size: 0.82rem;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            color: var(--text-muted);
        }

        .contact-item i { color: var(--primary); margin-top: 2px; min-width: 16px; }

        .contact-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .contact-item a:hover { text-decoration: underline; }

        /* ── Officers ── */
        .officers-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .officers-grid::-webkit-scrollbar { width: 4px; }
        .officers-grid::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 4px; }
        .officers-grid::-webkit-scrollbar-thumb:hover { background: #c0c0c0; }

        .officer-card {
            text-align: center;
            padding: 14px 10px;
            background: var(--bg);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            transition: all 0.2s ease;
        }

        .officer-card:hover {
            background: var(--primary-light);
            border-color: rgba(164,56,37,0.2);
            transform: translateY(-2px);
        }

        .officer-image {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 2px solid var(--border);
            object-fit: cover;
            margin: 0 auto 8px;
            transition: border-color 0.2s;
        }

        .officer-card:hover .officer-image { border-color: rgba(164,56,37,0.4); }

        .officer-name {
            font-weight: 700;
            font-size: 0.78rem;
            color: var(--text-dark);
            margin-bottom: 3px;
            line-height: 1.3;
        }

        .officer-position {
            font-size: 0.72rem;
            color: var(--primary);
            font-weight: 600;
            line-height: 1.3;
        }

        /* ── Buttons ── */
        .btn {
            padding: 11px 22px;
            font-size: 0.85rem;
            font-weight: 700;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.01em;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(164,56,37,0.25);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(164,56,37,0.35);
        }

        .btn-secondary {
            background: var(--accent);
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background: #e6c800;
            transform: translateY(-1px);
        }

        /* ── Modal ── */
        .modal {
            position: fixed;
            inset: 0;
            display: none;
            justify-content: center;
            align-items: center;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            z-index: 1000;
            padding: 1rem;
        }

        .modal.active { display: flex; }

        .modal-content {
            background: var(--card-bg);
            border-radius: 20px;
            width: 100%;
            max-width: 560px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: popUp 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            background: linear-gradient(135deg, #a43825 0%, #c0482e 60%, #8a2f1c 100%);
            color: white;
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 { font-size: 1.1rem; font-weight: 800; margin: 0; }

        .modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.35);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            max-height: 65vh;
        }

        .org-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding: 12px;
            background: var(--bg);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .org-info img {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            border: 2px solid var(--border);
            object-fit: cover;
            flex-shrink: 0;
        }

        .org-info-text h3 {
            color: var(--text-dark);
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 2px;
            border: none;
            padding: 0;
            text-align: left;
        }

        .org-info-text p {
            color: var(--text-muted);
            font-size: 0.75rem;
            line-height: 1.3;
            text-align: left;
        }

        .form-group { margin-bottom: 14px; }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.82rem;
            text-align: left; 
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            transition: all 0.2s;
            background: white;
            color: var(--text-dark);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(164,56,37,0.08);
        }

        .form-group textarea { resize: vertical; min-height: 80px; }

        .checkbox-group {
            margin: 14px 0;
            padding: 12px;
            background: var(--bg);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        .checkbox-group p {
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.82rem;
            text-align: left;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            background: var(--bg);
        }

        .btn-cancel {
            background: white;
            color: var(--text-dark);
            border: 1.5px solid var(--border);
        }

        .btn-cancel:hover { background: var(--bg); }

        .btn-submit {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(164,56,37,0.25);
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* ── Success Modal ── */
        .success-modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            z-index: 1001;
            align-items: center;
            justify-content: center;
        }

        .success-modal.active { display: flex; }

        .success-content {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 380px;
            box-shadow: var(--shadow-lg);
            animation: popUp 0.25s ease;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.6rem;
            box-shadow: 0 4px 14px rgba(16,185,129,0.3);
        }

        .success-content h3 {
            color: var(--text-dark);
            margin-bottom: 10px;
            font-size: 1.3rem;
            font-weight: 800;
        }

        .success-content p {
            color: var(--text-muted);
            margin-bottom: 24px;
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .success-content .btn { width: 100%; justify-content: center; }

        /* ── Animations ── */
        @keyframes popUp {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .hero-body { flex-direction: column; padding: 20px; gap: 16px; }
            .hero-stats { gap: 16px; }
            .main-grid { grid-template-columns: 1fr; }
            .gallery { grid-template-columns: repeat(2, 1fr); }
            .officers-grid { grid-template-columns: 1fr; }
            .modal-footer { flex-direction: column; }
            .modal-footer .btn { width: 100%; justify-content: center; }
        }

        @media (max-width: 480px) {
            .container { padding: 0 16px; }
            .section { padding: 20px; }
            .hero-body { padding: 16px; }
        }
    </style>
</head>
<body>

<header class="org-header">
    <div class="container">
        <button class="back-btn" onclick="
    <?php if (isset($_GET['from']) && $_GET['from'] === 'org_rep'): ?>
        window.location.href='my_organization.php'
    <?php else: ?>
        window.location.href='organizations.php'
    <?php endif; ?>
">
    <i class="fas fa-arrow-left"></i> Back
</button>
        <h1><?php echo htmlspecialchars($current_org['acronym']); ?> Details</h1>
    </div>
</header>

<div class="container">

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-body">
            <div class="hero-logo-wrap">
                <img src="<?php echo htmlspecialchars($current_org['logo_path']); ?>"
                     alt="<?php echo htmlspecialchars($current_org['acronym']); ?>"
                     class="hero-logo"
                     onerror="this.src='https://placehold.co/180x180/a43825/white?text=<?php echo htmlspecialchars($current_org['acronym']); ?>'">
            </div>

            <div class="hero-content">
                <span class="hero-acronym"><?php echo htmlspecialchars($current_org['acronym']); ?></span>
                <h2><?php echo htmlspecialchars($current_org['name']); ?></h2>

                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-value"><?php echo $current_org['members_count']; ?></div>
                        <div class="stat-label">Members</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo $current_org['upcoming_events']; ?></div>
                        <div class="stat-label">Upcoming Events</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo $current_org['founded']; ?></div>
                        <div class="stat-label">Founded</div>
                    </div>
                </div>

                <div class="hero-actions">
                    <button class="btn btn-primary" onclick="openJoinModal()">
                        <i class="fas fa-user-plus"></i> Join Organization
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-grid">
        <div>
            <!-- About -->
            <div class="section">
                <h3><i class="fas fa-info-circle"></i> About</h3>
                <p class="about-text"><?php echo htmlspecialchars($current_org['description']); ?></p>
            </div>

            <!-- Upcoming Events -->
            <div class="section">
                <h3><i class="fas fa-calendar-check"></i> Upcoming Events</h3>
                <div class="events-grid">
                    <?php if (!empty($current_upcoming_events)): ?>
                        <?php foreach ($current_upcoming_events as $event): ?>
                            <div class="event-item">
                                <img src="<?php echo htmlspecialchars($event['img']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                                <div class="event-content">
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p class="event-date"><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['date'])); ?></p>
                                    <p><?php echo htmlspecialchars($event['desc']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--text-muted);">No upcoming events scheduled.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Past Events -->
            <?php if (!empty($current_past_events)): ?>
                <div class="section">
                    <h3><i class="fas fa-history"></i> Past Events</h3>
                    <div class="events-grid">
                        <?php foreach ($current_past_events as $event): ?>
                            <div class="event-item">
                                <img src="<?php echo htmlspecialchars($event['img']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                                <div class="event-content">
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p class="event-date"><i class="fas fa-calendar"></i> <?php echo date('M Y', strtotime($event['date'])); ?></p>
                                    <p><?php echo htmlspecialchars($event['desc']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Gallery -->
            <?php if (!empty($current_org['images'])): ?>
                <div class="section">
                    <h3><i class="fas fa-images"></i> Gallery</h3>
                    <div class="gallery">
                        <?php foreach ($current_org['images'] as $image): ?>
                            <div class="gallery-item" onclick="openLightbox('<?php echo htmlspecialchars($image); ?>')">
                                <img src="<?php echo htmlspecialchars($image); ?>"
                                     alt="Gallery"
                                     onerror="this.src='https://placehold.co/300x300/a43825/white?text=Image'">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Contact Info -->
            <div class="sidebar-card">
                <h4><i class="fas fa-address-card"></i> Contact</h4>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo htmlspecialchars($current_org['contact_email']); ?>"><?php echo htmlspecialchars($current_org['contact_email']); ?></a>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($current_org['contact_phone']); ?></span>
                    </div>
                    <?php if (isset($current_org['room'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($current_org['room']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Officers -->
            <div class="sidebar-card">
                <h4><i class="fas fa-users"></i> Officers</h4>
                <div class="officers-grid">
                    <?php if (!empty($current_officers)): ?>
                        <?php foreach ($current_officers as $officer): ?>
                            <div class="officer-card">
                                <img src="<?php echo htmlspecialchars($officer['img']); ?>"
                                     alt="<?php echo htmlspecialchars($officer['name']); ?>"
                                     class="officer-image"
                                     onerror="this.src='https://placehold.co/80x80/a43825/white?text=<?php echo substr(htmlspecialchars($officer['name']), 0, 1); ?>'">
                                <div class="officer-name"><?php echo htmlspecialchars($officer['name']); ?></div>
                                <div class="officer-position"><?php echo htmlspecialchars($officer['position']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Join Organization Modal -->
    <div class="modal" id="joinModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Join Organization</h2>
                <button class="modal-close" onclick="closeJoinModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="org-info">
                    <img src="<?php echo htmlspecialchars($current_org['logo_path']); ?>"
                         alt="<?php echo htmlspecialchars($current_org['acronym']); ?>"
                         onerror="this.src='https://placehold.co/70x70/a43825/white?text=<?php echo htmlspecialchars($current_org['acronym']); ?>'">
                    <div class="org-info-text">
                        <h3><?php echo htmlspecialchars($current_org['acronym']); ?></h3>
                        <p><?php echo htmlspecialchars($current_org['name']); ?></p>
                    </div>
                </div>

                <form id="joinForm">
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="year">Year Level</label>
                        <select id="year" required>
                            <option value="">Select year level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                            <option value="5th">5th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason">Why do you want to join?</label>
                        <textarea id="reason" placeholder="Tell us why you're interested in joining this organization..." required></textarea>
                    </div>
                    <div class="checkbox-group">
                        <p>I agree to:</p>
                        <div class="checkbox-item">
                            <input type="checkbox" id="terms" required>
                            <label for="terms">Organization rules and code of conduct</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="updates">
                            <label for="updates">Receive organization announcements and updates</label>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeJoinModal()">Cancel</button>
                <button class="btn btn-submit" onclick="submitJoinForm()">
                    <i class="fas fa-check"></i> Send Request
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="success-modal" id="successModal">
        <div class="success-content">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h3>Request Sent!</h3>
            <p>Your join request has been successfully sent to <?php echo htmlspecialchars($current_org['acronym']); ?>. You'll receive a notification once the organization reviews your application.</p>
            <button class="btn btn-primary" onclick="closeSuccessModal()">Back to Organization</button>
        </div>
    </div>

</div>

<!-- Lightbox (outside .container so it covers full screen) -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
    </button>
    <img class="lightbox-img" id="lightbox-img" src="" alt="Gallery Image">
</div>

<script>
    // ── Lightbox ──
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Click dark background to close
    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this) closeLightbox();
    });

    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });

    // ── Join Modal ──
    function openJoinModal() {
        document.getElementById('joinModal').classList.add('active');
    }

    function closeJoinModal() {
        document.getElementById('joinModal').classList.remove('active');
    }

    function submitJoinForm() {
        const fullname = document.getElementById('fullname').value;
        const email    = document.getElementById('email').value;
        const year     = document.getElementById('year').value;
        const reason   = document.getElementById('reason').value;
        const terms    = document.getElementById('terms').checked;

        if (!fullname || !email || !year || !reason || !terms) {
            alert('Please fill in all required fields and agree to the terms.');
            return;
        }

        closeJoinModal();
        showSuccessModal();
        document.getElementById('joinForm').reset();
    }

    function showSuccessModal() {
        document.getElementById('successModal').classList.add('active');
    }

    function closeSuccessModal() {
        document.getElementById('successModal').classList.remove('active');
    }

    document.getElementById('joinModal').addEventListener('click', function(e) {
        if (e.target === this) closeJoinModal();
    });

    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) closeSuccessModal();
    });
</script>

</body>
</html>