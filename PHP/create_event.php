<?php
session_start();
require_once __DIR__ . '/db_connect.php'; 
 
$created_by = $_SESSION['user_id'] ?? 0;
 
require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
$editId = isset($_GET['editId']) ? intval($_GET['editId']) : 0;
$error = '';
$success = '';
$event = [
    'title' => '',
    'event_date' => '',
    'event_time' => '',
    'end_time' => '',
    'description' => '',
    'image_path' => '',
    'pdf_path' => ''
];
 
// BLOCK 1 — Ownership check (only owner can edit)
if ($editId && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND created_by = ?");
    $stmt->bind_param("ii", $editId, $created_by);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $event = $result->fetch_assoc();
    } else {
        header("Location: OrgRep_db.php?error=unauthorized");
        exit;
    }
    $stmt->close();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    // BLOCK 2 — Sanitize + trim + empty check
    $title       = trim($conn->real_escape_string($_POST['event-title']));
    $date        = $conn->real_escape_string($_POST['event-date']);
    $start_time  = !empty($_POST['event-start-time']) ? $conn->real_escape_string($_POST['event-start-time']) : 'TBD';
    $end_time    = !empty($_POST['event-end-time'])   ? $conn->real_escape_string($_POST['event-end-time'])   : 'TBD';
    $description = trim($conn->real_escape_string($_POST['event-desc']));
 
    if (empty($title)) {
        $error = "Event title cannot be blank.";
    } elseif (empty($description)) {
        $error = "Description cannot be blank.";
    }
 
    // BLOCK 3 — Date & time validation
    $today = date('Y-m-d');
    $now   = date('H:i');
 
    if (empty($error) && $date < $today) {
        $error = "Event date cannot be in the past.";
    }
    if (empty($error) && $date === $today && $start_time !== 'TBD' && $start_time < $now) {
        $error = "Event start time cannot be in the past.";
    }
    if (empty($error) && $start_time !== 'TBD' && $end_time !== 'TBD' && $end_time <= $start_time) {
        $error = "End time must be after start time.";
    }
 
    $image_path = $event['image_path'];
    $pdf_path   = $event['pdf_path'];
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
 
    // BLOCK 4 — File size + upload
    if (empty($error) && isset($_FILES['event-image']) && $_FILES['event-image']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['event-image']['size'] > 5 * 1024 * 1024) {
            $error = "Image must be under 5MB.";
        } else {
            $tmp_name    = $_FILES['event-image']['tmp_name'];
            $image_name  = basename($_FILES['event-image']['name']);
            $target_file = $upload_dir . time() . '_' . $image_name;
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['event-image']['type'], $allowed_types)) {
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $image_path = $conn->real_escape_string($target_file);
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image file type.";
            }
        }
    }
 
    if (empty($error) && isset($_FILES['signed-pdf']) && $_FILES['signed-pdf']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['signed-pdf']['size'] > 10 * 1024 * 1024) {
            $error = "PDF must be under 10MB.";
        } else {
            $pdf_tmp    = $_FILES['signed-pdf']['tmp_name'];
            $pdf_name   = basename($_FILES['signed-pdf']['name']);
            $pdf_target = $upload_dir . time() . '_' . $pdf_name;
            if ($_FILES['signed-pdf']['type'] === 'application/pdf') {
                if (move_uploaded_file($pdf_tmp, $pdf_target)) {
                    $pdf_path = $conn->real_escape_string($pdf_target);
                } else {
                    $error = "Failed to upload PDF.";
                }
            } else {
                $error = "Only PDF files are allowed.";
            }
        }
    }
 
    // BLOCK 5 — Duplicate check (new events only)
    if (empty($error) && !$editId) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE created_by = ? AND title = ? AND event_date = ?");
        $stmt->bind_param("iss", $created_by, $title, $date);
        $stmt->execute();
        $dup = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        if ($dup > 0) {
            $error = "You already have an event with the same title on that date.";
        }
    }
 
    // BLOCK 6 — INSERT or UPDATE
    if (empty($error)) {
        if ($editId) {
            $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, organizer = ?, image_path = ?, pdf_path = ? WHERE id = ? AND created_by = ?");
            $location  = "TBD";
            $organizer = "Admin";
            $stmt->bind_param("ssssssssii", $title, $description, $date, $start_time, $location, $organizer, $image_path, $pdf_path, $editId, $created_by);
        } else {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, location, organizer, status, created_by, image_path, pdf_path) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
            $location  = "TBD";
            $organizer = "Admin";
            $stmt->bind_param("ssssssiss", $title, $description, $date, $start_time, $location, $organizer, $created_by, $image_path, $pdf_path);
        }
 
        if ($stmt->execute()) {
            if (!$editId) {
                $newEventId = $conn->insert_id;
                $message    = "New event posted: " . $title;
                $students   = $conn->query("SELECT id FROM users WHERE role = 'student'");
                $notifStmt  = $conn->prepare("INSERT INTO notifications (event_id, user_id, recipient_id, notification_type, message, created_at, is_read) VALUES (?, ?, ?, 'new_event', ?, NOW(), 0)");
                while ($student = $students->fetch_assoc()) {
                    $notifStmt->bind_param("iiis", $newEventId, $created_by, $student['id'], $message);
                    $notifStmt->execute();
                }
                $notifStmt->close();
            }
            $stmt->close();
            header("Location: OrgRep_db.php?success=1");
            exit();
        } else {
            $error = "Database error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editId ? "Edit Event" : "New Event" ?> — Arki Connect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
           --crimson:    #a43825;
--crimson-dk: #8a2d1f;
--crimson-lt: #c24d38;
--ink:        #333333;
--ink-soft:   #6b7280;
--mist:       #fafafa;
--mist-dk:    #f3f4f6;
--border:     #e5e7eb;
            --white:       #FFFFFF;
            --gold:        #C49A3C;
            --success:     #2D7A4F;
            --error:       #B23A3A;

           --font-display: 'Montserrat', sans-serif;
--font-body:    'Montserrat', sans-serif;

            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 20px;

            --shadow-card: 0 2px 8px rgba(26,26,31,0.06), 0 8px 32px rgba(26,26,31,0.08);
            --shadow-btn:  0 2px 8px rgba(155,35,53,0.25);
            --shadow-btn-hover: 0 6px 20px rgba(155,35,53,0.35);

            --transition: 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html { font-size: 16px; }

        body {
            font-family: var(--font-body);
            background-color: #f0eeeb;
            min-height: 100vh;
            color: var(--ink);
            padding: 48px 20px 80px;
            display: flex;
            justify-content: center;
            align-items: flex-start;

             -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
        }

        /* ─── Page Shell ─────────────────────────────────── */
        .page-shell {
            width: 100%;
            max-width: 780px;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        /* ─── Top Nav Strip ──────────────────────────────── */
        .top-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }

        .back-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    letter-spacing: 0.02em;
    transition: all var(--transition);
    background: var(--crimson);
    padding: 8px 16px;
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-btn);
}

.back-link:hover { 
    background: var(--crimson-dk);
    box-shadow: var(--shadow-btn-hover);
    transform: translateY(-1px);
}

        .nav-sep {
            color: var(--border);
            font-size: 0.85rem;
        }

        .nav-crumb {
            font-size: 0.85rem;
            color: var(--crimson);
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        /* ─── Card ───────────────────────────────────────── */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }

        /* ─── Card Header ────────────────────────────────── */
        .card-header {
            padding: 40px 48px 36px;
            border-bottom: 1px solid var(--mist-dk);
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--crimson) 0%, var(--crimson-lt) 50%, var(--gold) 100%);
        }

        .header-eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .header-eyebrow .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--crimson);
        }

        .header-eyebrow span {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--crimson);
        }

       .card-header h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.25rem;
    font-weight: 700;
    color: #a43825;
    letter-spacing: -0.025em;
    line-height: 1.25;
}

        .card-header h1 em {
            font-style: italic;
            color: var(--crimson);
        }

        .header-sub {
            margin-top: 8px;
            font-size: 0.92rem;
            color: #6b7280;  /* slightly darker than current */
    font-weight: 500; /* change from 300 to 500 */
        }

        /* ─── Card Body ──────────────────────────────────── */
        .card-body {
            padding: 40px 48px;
        }

        /* ─── Alerts ─────────────────────────────────────── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 28px;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.5;
        }

        .alert i { margin-top: 2px; flex-shrink: 0; }

        .alert-error {
            background: #FDF2F2;
            color: var(--error);
            border: 1px solid #F5CACA;
        }

        .alert-success {
            background: #F0FAF4;
            color: var(--success);
            border: 1px solid #B8E8CE;
        }

        /* ─── Section Titles ─────────────────────────────── */
        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink-soft);
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--mist-dk);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label i {
            color: var(--crimson);
            font-size: 0.75rem;
        }

        /* ─── Form Groups ────────────────────────────────── */
        .form-section {
            margin-bottom: 36px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-row {
            display: grid;
            gap: 18px;
        }

        .form-row-3 { grid-template-columns: 2fr 1fr 1fr; }
        .form-row-2 { grid-template-columns: 1fr 1fr; }

        label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: var(--ink-soft);
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .required-star {
            color: var(--crimson);
            margin-left: 2px;
        }

        /* ─── Inputs ─────────────────────────────────────── */
        input[type="text"],
        input[type="date"],
        input[type="time"],
        textarea {
            width: 100%;
            padding: 11px 15px;
            font-family: var(--font-body);
            font-size: 0.95rem;
            font-weight: 400;
            color: var(--ink);
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
            outline: none;
            -webkit-appearance: none;
        }

        input[type="text"]::placeholder,
        textarea::placeholder {
            color: #BDB8B0;
            font-weight: 300;
        }

        input:focus,
        textarea:focus {
            border-color: var(--crimson);
            background: #FFFBFB;
            box-shadow: 0 0 0 3px rgba(155, 35, 53, 0.1);
        }

        input:hover:not(:focus),
        textarea:hover:not(:focus) {
            border-color: #BDB8B0;
        }

        textarea {
            resize: vertical;
            min-height: 130px;
            line-height: 1.7;
        }

        /* Character hint */
        .input-hint {
            font-size: 0.78rem;
            color: #AAA4A0;
            margin-top: 5px;
            font-weight: 300;
        }

        /* ─── Upload Zones ───────────────────────────────── */
        .upload-zone {
            border: 2px dashed var(--border);
            border-radius: var(--radius-md);
            padding: 30px 24px;
            text-align: center;
            cursor: pointer;
            transition: all var(--transition);
            background: var(--mist);
            position: relative;
        }

        .upload-zone:hover {
            border-color: var(--crimson);
            background: #FDF5F6;
        }

        .upload-zone.dragover {
            border-color: var(--crimson);
            background: #FDF0F2;
            transform: scale(1.01);
        }

        .upload-zone.has-file {
            border-color: var(--success);
            border-style: solid;
            background: #F4FCF7;
        }

        .upload-zone input[type="file"] { display: none; }

        .upload-icon {
            width: 48px; height: 48px;
            border-radius: 50%;
            background: var(--white);
            border: 1.5px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            transition: all var(--transition);
        }

        .upload-zone:hover .upload-icon,
        .upload-zone.has-file .upload-icon {
            border-color: var(--crimson);
            background: var(--crimson);
        }

        .upload-icon i {
            font-size: 1rem;
            color: var(--ink-soft);
            transition: color var(--transition);
        }

        .upload-zone:hover .upload-icon i,
        .upload-zone.has-file .upload-icon i {
            color: var(--white);
        }

        .upload-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .upload-sub {
            font-size: 0.82rem;
            color: #AAA4A0;
            font-weight: 300;
        }

        .upload-browse {
            background: none;
            border: none;
            color: var(--crimson);
            font-family: var(--font-body);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .upload-browse:hover { color: var(--crimson-dk); }

        .upload-filename {
            display: none;
            margin-top: 12px;
            font-size: 0.83rem;
            color: var(--success);
            font-weight: 600;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .upload-filename.visible { display: flex; }

        .existing-file-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            font-size: 0.82rem;
            color: var(--crimson);
            text-decoration: none;
            font-weight: 500;
        }

        .existing-file-link:hover { text-decoration: underline; }

        /* Upload row: two side by side */
        .upload-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .upload-cell label {
            display: block;
            margin-bottom: 8px;
        }

        /* ─── Divider ────────────────────────────────────── */
        .form-divider {
            height: 1px;
            background: var(--mist-dk);
            margin: 32px 0;
        }

        /* ─── Footer Actions ─────────────────────────────── */
        .card-footer {
            padding: 24px 48px 40px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            background: var(--mist);
            border-top: 1px solid var(--mist-dk);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 26px;
            font-family: var(--font-body);
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-ghost {
            background: transparent;
            color: var(--ink-soft);
            border: 1.5px solid var(--border);
        }

        .btn-ghost:hover {
            background: var(--mist-dk);
            border-color: #BDB8B0;
            color: var(--ink);
        }

        .btn-primary {
            background: var(--crimson);
            color: var(--white);
            box-shadow: var(--shadow-btn);
        }

        .btn-primary:hover {
            background: var(--crimson-dk);
            box-shadow: var(--shadow-btn-hover);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn i { font-size: 0.82rem; }

        /* Spinner (on submit) */
        .btn-primary.loading .btn-text { display: none; }
        .btn-primary .spinner { display: none; }
        .btn-primary.loading .spinner {
            display: inline-block;
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ─── Responsive ─────────────────────────────────── */
        @media (max-width: 720px) {
            body { padding: 24px 12px 60px; }

            .card-header,
            .card-body { padding: 28px 24px; }

            .card-footer { padding: 20px 24px 30px; flex-direction: column-reverse; }

            .btn { width: 100%; justify-content: center; }

            .card-header h1 { font-size: 1.7rem; }

            .form-row-3,
            .form-row-2,
            .upload-row { grid-template-columns: 1fr; }
        }

        /* ─── Subtle entrance animation ─────────────────── */
        .card {
            animation: cardIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="page-shell">

    <!-- Breadcrumb -->
    <nav class="top-nav">
        <a href="OrgRep_db.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
       
    </nav>

    <div class="card">

        <!-- Header -->
        <div class="card-header">
           
            <h1><?= $editId ? 'Edit Existing Event' : 'Create a New Event' ?></h1>
            <p class="header-sub">
                <?= $editId
                    ? 'Update the event details below. Changes will reflect immediately upon saving.'
                    : 'Fill in the details below. All registered students will be notified once submitted.'
                ?>
            </p>
        </div>

        <!-- Body -->
        <div class="card-body">

            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            <?php endif; ?>

            <form id="event-form" method="POST" enctype="multipart/form-data" autocomplete="off">

                <!-- Event Info -->
                <div class="form-section">
                    <div class="section-label">
                        <i class="fas fa-tag"></i> Event Information
                    </div>

                    <div class="form-group">
                        <label for="event-title">Event Title <span class="required-star">*</span></label>
                        <input
                            type="text"
                            id="event-title"
                            name="event-title"
                            placeholder="e.g., Annual Architecture Design Week"
                            required
                            maxlength="150"
                            value="<?= htmlspecialchars($event['title']) ?>">
                    </div>

                    <div class="form-row form-row-3">
                        <div class="form-group" style="margin-bottom:0">
                            <label for="event-date">Date <span class="required-star">*</span></label>
                            <input type="date" id="event-date" name="event-date" required
       min="<?= date('Y-m-d') ?>"
       value="<?= htmlspecialchars($event['event_date']) ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label for="event-start-time">Start Time</label>
                            <input type="time" id="event-start-time" name="event-start-time"
                                   value="<?= htmlspecialchars($event['event_time']) ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label for="event-end-time">End Time</label>
                            <input type="time" id="event-end-time" name="event-end-time"
                                   value="<?= htmlspecialchars($event['end_time']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                <!-- Description -->
                <div class="form-section">
                    <div class="section-label">
                        <i class="fas fa-align-left"></i> Description
                    </div>
                    <div class="form-group">
                        <label for="event-desc">Event Details <span class="required-star">*</span></label>
                        <textarea
                            id="event-desc"
                            name="event-desc"
                            rows="5"
                            placeholder="Describe the purpose, agenda, and any special instructions for attendees…"
                            required><?= htmlspecialchars($event['description']) ?></textarea>
                       
                    </div>
                </div>

                <div class="form-divider"></div>

                <!-- Uploads -->
                <div class="form-section">
                    <div class="section-label">
                        <i class="fas fa-paperclip"></i> Attachments
                    </div>

                    <div class="upload-row">
                        <!-- Image Upload -->
                        <div class="upload-cell">
                            <label>Event Poster / Image</label>
                            <div class="upload-zone" id="imgZone">
                                <input type="file" id="event-image" name="event-image" accept=".jpg,.jpeg,.png,.gif">
                                <div class="upload-icon"><i class="fas fa-image"></i></div>
                                <p class="upload-title">Drop image here</p>
                                <p class="upload-sub">JPG, PNG, GIF &nbsp;·&nbsp;
                                    <button type="button" class="upload-browse" id="imgBrowse">Browse</button>
                                </p>
                                <div class="upload-filename" id="imgFilename">
                                    <i class="fas fa-check-circle"></i>
                                    <span></span>
                                </div>
                            </div>
                            <?php if (!empty($event['image_path'])): ?>
                            <div style="text-align:center; margin-top:10px;">
                                <img src="<?= htmlspecialchars($event['image_path']) ?>"
                                     alt="Current Poster"
                                     style="max-width:100%;max-height:90px;border-radius:8px;border:1.5px solid var(--border);">
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- PDF Upload -->
                        <div class="upload-cell">
                            <label>Signed PDF Document</label>
                            <div class="upload-zone" id="pdfZone">
                                <input type="file" id="signed-pdf" name="signed-pdf" accept=".pdf">
                                <div class="upload-icon"><i class="fas fa-file-signature"></i></div>
                                <p class="upload-title">Drop PDF here</p>
                                <p class="upload-sub">PDF only &nbsp;·&nbsp;
                                    <button type="button" class="upload-browse" id="pdfBrowse">Browse</button>
                                </p>
                                <div class="upload-filename" id="pdfFilename">
                                    <i class="fas fa-check-circle"></i>
                                    <span></span>
                                </div>
                            </div>
                            <?php if (!empty($event['pdf_path'])): ?>
                            <a href="<?= htmlspecialchars($event['pdf_path']) ?>" target="_blank" class="existing-file-link">
                                <i class="fas fa-file-pdf"></i> View current PDF
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </form>
        </div><!-- /card-body -->

        <!-- Footer -->
        <div class="card-footer">
            <button type="button" class="btn btn-ghost" onclick="window.location.href='OrgRep_db.php';">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="submit" form="event-form" class="btn btn-primary" id="submitBtn">
                <span class="btn-text">
                    <i class="fas fa-<?= $editId ? 'save' : 'paper-plane' ?>"></i>
                    <?= $editId ? 'Save Changes' : 'Create Event' ?>
                </span>
                <div class="spinner"></div>
            </button>
        </div>

    </div><!-- /card -->
</div><!-- /page-shell -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Generic upload zone setup ── */
    function setupUploadZone(zoneId, browseId, inputId, labelId) {
        const zone    = document.getElementById(zoneId);
        const browse  = document.getElementById(browseId);
        const input   = document.getElementById(inputId);
        const label   = document.getElementById(labelId);

        if (!zone) return;

        browse.addEventListener('click', () => input.click());
        zone.addEventListener('click', (e) => {
            if (e.target !== browse) input.click();
        });

        zone.addEventListener('dragover', e => {
            e.preventDefault(); e.stopPropagation();
            zone.classList.add('dragover');
        });
        zone.addEventListener('dragleave', e => {
            zone.classList.remove('dragover');
        });
        zone.addEventListener('drop', e => {
            e.preventDefault(); e.stopPropagation();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                setFile(e.dataTransfer.files[0]);
            }
        });
        input.addEventListener('change', () => {
            if (input.files.length) setFile(input.files[0]);
        });

        function setFile(file) {
            // Reassign to input if drag-dropped
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            } catch(e) {}

            label.querySelector('span').textContent = file.name;
            label.classList.add('visible');
            zone.classList.add('has-file');
        }
    }

    setupUploadZone('imgZone', 'imgBrowse', 'event-image', 'imgFilename');
    setupUploadZone('pdfZone', 'pdfBrowse', 'signed-pdf',  'pdfFilename');

    /* ── Submit loading state ── */
    const form = document.getElementById('event-form');
    const btn  = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
    const dateInput  = document.getElementById('event-date').value;
    const startInput = document.getElementById('event-start-time').value;
    const endInput   = document.getElementById('event-end-time').value;

    const today = new Date();
    today.setSeconds(0, 0);

    const selectedDate = new Date(dateInput);
    const todayDate    = new Date(today.toDateString());

    if (selectedDate < todayDate) {
        e.preventDefault();
        alert('Event date cannot be in the past.');
        return;
    }

    if (startInput && endInput && endInput <= startInput) {
        e.preventDefault();
        alert('End time must be after start time.');
        return;
    }

    const todayStr = today.toISOString().slice(0, 10);
    if (dateInput === todayStr && startInput) {
        const nowTime = today.toTimeString().slice(0, 5);
        if (startInput < nowTime) {
            e.preventDefault();
            alert('Start time cannot be in the past.');
            return;
        }
    }

    btn.classList.add('loading');
    btn.disabled = true;
});
});
</script>
</body>
</html>