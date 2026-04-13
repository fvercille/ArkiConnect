<?php
session_start();
require_once __DIR__ . '/db_connect.php';
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

// ===== GET UPCOMING EVENTS =====
if ($action === 'get_events') {
    $query = "SELECT 
        e.id,
        e.title,
        e.description,
        e.event_date as date,
        e.organizer as hostOrg,
        e.registrants,
        CASE WHEN er.user_id = ? THEN 1 ELSE 0 END as registered
    FROM events e
    LEFT JOIN event_registrations er ON e.id = er.event_id AND er.user_id = ?
    WHERE e.status IN ('approved', 'upcoming') 
    AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
    LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    echo json_encode(['success' => true, 'events' => $events]);
    exit;
}

// ===== REGISTER FOR EVENT =====
if ($action === 'register') {
    $data = json_decode(file_get_contents('php://input'), true);
    $event_id = $data['event_id'] ?? null;
    
    if (!$event_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid event ID']);
        exit;
    }

    // Check if already registered
    $checkQuery = "SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $event_id, $user_id);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Already registered for this event']);
        exit;
    }

    // Get event and user details
    $eventQuery = "SELECT e.title, e.organizer, u.email, u.fullname 
                   FROM events e, users u 
                   WHERE e.id = ? AND u.id = ?";
    $eventStmt = $conn->prepare($eventQuery);
    $eventStmt->bind_param("ii", $event_id, $user_id);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();
    
    if ($eventResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Event not found']);
        exit;
    }

    $eventData = $eventResult->fetch_assoc();

    // Register user
    $registerQuery = "INSERT INTO event_registrations (event_id, user_id, status) VALUES (?, ?, 'confirmed')";
    $registerStmt = $conn->prepare($registerQuery);
    $registerStmt->bind_param("ii", $event_id, $user_id);
    
    if ($registerStmt->execute()) {
        // Update registrants count
        $updateQuery = "UPDATE events SET registrants = registrants + 1 WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $event_id);
        $updateStmt->execute();

        // Send confirmation email
        //sendRegistrationEmail($eventData['email'], $eventData['fullname'], $eventData['title'], $eventData['organizer']);

        // Create notification
        $notifQuery = "INSERT INTO notifications (event_id, user_id, recipient_id, notification_type, message) 
                       VALUES (?, ?, ?, 'registration', ?)";
        $notifMessage = $eventData['fullname'] . " registered for " . $eventData['title'];
        $notifStmt = $conn->prepare($notifQuery);
        $notifStmt->bind_param("iiis", $event_id, $user_id, $user_id, $notifMessage);
        $notifStmt->execute();

        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registration failed']);
    }
    exit;
}

// ===== GET NOTIFICATIONS =====
if ($action === 'get_notifications') {
    $query = "SELECT id, notification_type, message, created_at 
              FROM notifications 
              WHERE recipient_id = ? OR user_id = ?
              ORDER BY created_at DESC 
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    // TEMPORARY DEBUG - remove later
    echo json_encode([
        'success' => true, 
        'notifications' => $notifications,
        'debug_user_id' => $user_id,
        'debug_count' => count($notifications),
        'debug_raw_query' => "WHERE recipient_id = $user_id OR user_id = $user_id"
    ]);
    exit;
}

// ===== MARK NOTIFICATION AS READ =====
if ($action === 'mark_notification_read') {
    $data = json_decode(file_get_contents('php://input'), true);
    $notif_id = $data['notification_id'] ?? null;
    
    if ($notif_id) {
        $query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $notif_id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true]);
    exit;
}

// ===== GET REGISTERED EVENTS =====
if ($action === 'get_registered_events') {
    $query = "SELECT 
        e.id,
        e.title,
        e.event_date as date,
        e.organizer
    FROM events e
    INNER JOIN event_registrations er ON e.id = er.event_id
    WHERE er.user_id = ? AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    echo json_encode(['success' => true, 'events' => $events]);
    exit;
}

// ===== GET ATTENDED EVENTS =====
if ($action === 'get_attended_events') {
    $query = "SELECT 
        e.id,
        e.title,
        e.event_date as date,
        e.organizer
    FROM events e
    INNER JOIN event_registrations er ON e.id = er.event_id
    WHERE er.user_id = ? AND er.status = 'attended' AND e.event_date < CURDATE()
    ORDER BY e.event_date DESC
    LIMIT 5";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    echo json_encode(['success' => true, 'events' => $events]);
    exit;
}

// ===== GET CALENDAR EVENTS FOR A GIVEN MONTH =====
if ($action === 'get_calendar_events') {
    $year  = intval($_GET['year']  ?? date('Y'));
    $month = intval($_GET['month'] ?? date('m'));
    $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $endDate   = date('Y-m-t', strtotime($startDate));

    $stmt = $conn->prepare(
        "SELECT DISTINCT DATE(e.event_date) as event_date, e.title
         FROM events e
         INNER JOIN event_registrations er ON er.event_id = e.id
         WHERE e.event_date BETWEEN ? AND ?
         AND e.status IN ('approved', 'upcoming', 'ongoing')
         AND er.user_id = ?
         ORDER BY e.event_date"
    );
    $stmt->bind_param('ssi', $startDate, $endDate, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $eventDates = [];
    while ($row = $result->fetch_assoc()) {
        $eventDates[$row['event_date']] = $row['title'];
    }

    echo json_encode(['success' => true, 'eventDates' => $eventDates]);
    exit;
}

// ===== SEND REGISTRATION EMAIL =====
function sendRegistrationEmail($email, $fullname, $eventTitle, $organizer) {
    require_once 'PHPMailer/src/PHPMailer.php';
    require_once 'PHPMailer/src/SMTP.php';
    require_once 'PHPMailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yourgmail@gmail.com'; // palitan ng iyong Gmail
        $mail->Password   = 'your_app_password';   // yung 16-char app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        $mail->setFrom('yourgmail@gmail.com', 'Arki Connect');
        $mail->addAddress($email, $fullname);
        $mail->isHTML(true);
        $mail->Subject = 'Registration Confirmed - ' . $eventTitle;
        $mail->Body    = "
            <h2>Registration Confirmed!</h2>
            <p>Hi <strong>$fullname</strong>,</p>
            <p>You have successfully registered for <strong>$eventTitle</strong> organized by <strong>$organizer</strong>.</p>
            <p>Visit your dashboard to view your registered events.</p>
            <br>
            <p>Arki Connect Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log('Email error: ' . $mail->ErrorInfo);
    }
}



$conn->close();

