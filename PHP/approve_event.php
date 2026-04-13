<?php
session_start();
require_once __DIR__ . '/db_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);


include 'email_config.php'; // was email_functions.php

if ($_SESSION['role'] !== 'admin' || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action     = $_GET['action'] ?? '';
$admin_id   = $_SESSION['user_id'];
$admin_name = $_SESSION['fullname'] ?? 'Admin';

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$event_id = (int)($input['event_id'] ?? 0);

// ── Helper: flush response to browser immediately ──
function flushResponse(string $json): void {
    // Clear any accidental output before our JSON
    if (ob_get_level()) ob_end_clean();

    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($json));
    header('Connection: close');
    header('X-Accel-Buffering: no'); // disable nginx buffering if present
    echo $json;

    // Push it out
    if (ob_get_level()) ob_end_flush();
    flush();

    // Tell PHP to keep running after browser disconnects
    ignore_user_abort(true);
    set_time_limit(120);

    // Best method — works on XAMPP/Apache with php-fpm, or nginx
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
}

// ===== APPROVE =====
if ($action === 'approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($event_id <= 0) throw new Exception('Invalid event ID');

        $stmt = $conn->prepare("
            SELECT e.id, e.title, e.event_date, e.event_time, e.description, e.created_by, u.fullname as org_name
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ?
        ");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$event) throw new Exception('Event not found');

        $stmt = $conn->prepare("
            UPDATE events 
            SET status = 'approved', approved_by = ?, approved_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $admin_id, $event_id);
        if (!$stmt->execute()) throw new Exception('Failed to approve event: ' . $stmt->error);
        $stmt->close();

        // DB notifications (fast)
        $notifMsg = 'Your event "' . $event['title'] . '" has been approved!';
        createNotification($conn, $event_id, $admin_id, 'event_approved', $notifMsg, $event['created_by']);
        $studentNotifMsg = $event['org_name'] . ' created a new event: ' . $event['title'];
        createNotification($conn, $event_id, $admin_id, 'new_event', $studentNotifMsg, 0);

        // ── Send response NOW, emails after ──
        flushResponse(json_encode(['success' => true, 'message' => 'Event approved successfully!']));

        // Slow emails run in background after browser already got success
        //sendApprovalEmail($event['org_name'], $event['title'], $admin_name);
        //sendEventCreatedEmailToStudents($conn, $event['title'], $event['event_date'], $event['event_time'], $event['description'], $event['org_name']);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ===== REJECT =====
if ($action === 'reject' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $reason = $conn->real_escape_string($input['reason'] ?? 'No reason provided');
        if ($event_id <= 0) throw new Exception('Invalid event ID');

        $stmt = $conn->prepare("
            SELECT e.id, e.title, e.created_by, u.fullname as org_name
            FROM events e
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ?
        ");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$event) throw new Exception('Event not found');

        $stmt = $conn->prepare("
            UPDATE events 
            SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ?
            WHERE id = ?
        ");
        $stmt->bind_param("isi", $admin_id, $reason, $event_id);
        if (!$stmt->execute()) throw new Exception('Failed to reject event: ' . $stmt->error);
        $stmt->close();

        $notifMsg = 'Your event "' . $event['title'] . '" has been rejected. Reason: ' . $reason;
        createNotification($conn, $event_id, $admin_id, 'event_rejected', $notifMsg, $event['created_by']);

        // ── Send response NOW, email after ──
        flushResponse(json_encode(['success' => true, 'message' => 'Event rejected successfully!']));

        // Slow email runs in background
        sendRejectionEmail($event['org_name'], $event['title'], $reason, $admin_name);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ===== HELPER =====
function createNotification($conn, $eventId, $userId, $type, $message, $recipientId = null) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (event_id, user_id, notification_type, message, recipient_id, created_at, is_read)
            VALUES (?, ?, ?, ?, ?, NOW(), 0)
        ");
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        $stmt->bind_param("iissi", $eventId, $userId, $type, $message, $recipientId);
        $stmt->execute();
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}
?>