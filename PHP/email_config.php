<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

define('ARKICONNECT_EMAIL', 'mkanguinto@tip.edu.ph');
define('ARKICONNECT_NAME', 'Arki Connect');

// ── Core mailer setup ──
function createMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mkanguinto@tip.edu.ph';
    $mail->Password   = 'oehz ymhi cllk rdnk';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ];
    $mail->setFrom('mkanguinto@tip.edu.ph', 'Arki Connect');
    $mail->isHTML(true);
    return $mail;
}

// ── Approval email ──
function //sendApprovalEmail($orgName, $eventTitle, $adminName = 'Admin') {
    try {
        $mail = createMailer();
        $mail->addAddress(ARKICONNECT_EMAIL);
        $mail->Subject = "Event Approved - $eventTitle by $orgName";
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:#10b981;color:white;padding:20px;text-align:center;'>
                    <h2>✓ Event Approved!</h2>
                </div>
                <div style='padding:30px;'>
                    <p><strong>Organization:</strong> " . htmlspecialchars($orgName) . "</p>
                    <p><strong>Event:</strong> " . htmlspecialchars($eventTitle) . "</p>
                    <p><strong>Approved By:</strong> " . htmlspecialchars($adminName) . "</p>
                    <p>This event is now visible to all students.</p>
                </div>
            </div>
        ";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Approval email error: " . $e->getMessage());
        return false;
    }
}

// ── Rejection email ──
function sendRejectionEmail($orgName, $eventTitle, $reason, $adminName = 'Admin') {
    try {
        $mail = createMailer();
        $mail->addAddress(ARKICONNECT_EMAIL);
        $mail->Subject = "Event Rejected - $eventTitle by $orgName";
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:#ef4444;color:white;padding:20px;text-align:center;'>
                    <h2>⚠️ Event Rejected</h2>
                </div>
                <div style='padding:30px;'>
                    <p><strong>Organization:</strong> " . htmlspecialchars($orgName) . "</p>
                    <p><strong>Event:</strong> " . htmlspecialchars($eventTitle) . "</p>
                    <p><strong>Rejected By:</strong> " . htmlspecialchars($adminName) . "</p>
                    <p><strong>Reason:</strong> " . nl2br(htmlspecialchars($reason)) . "</p>
                </div>
            </div>
        ";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Rejection email error: " . $e->getMessage());
        return false;
    }
}

// ── Bulk email to all students ──
function sendEventCreatedEmailToStudents($conn, $eventTitle, $eventDate, $eventTime, $description, $orgName) {
    try {
        $stmt = $conn->prepare("SELECT email, fullname FROM users WHERE role = 'student' AND email IS NOT NULL");
        $stmt->execute();
        $result = $stmt->get_result();

        $formattedDate = date('F j, Y', strtotime($eventDate));
        $formattedTime = $eventTime ? date('g:i A', strtotime($eventTime)) : 'TBA';
        $count = 0;

        while ($row = $result->fetch_assoc()) {
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) continue;
            try {
                $mail = createMailer();
                $mail->addAddress($row['email'], $row['fullname']);
                $mail->Subject = "New Event: $eventTitle";
                $mail->Body = "
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                        <div style='background:#a43825;color:white;padding:20px;text-align:center;'>
                            <h2>New Event!</h2>
                        </div>
                        <div style='padding:30px;'>
                            <p>Hi <strong>" . htmlspecialchars($row['fullname']) . "</strong>,</p>
                            <h3 style='color:#a43825;'>" . htmlspecialchars($eventTitle) . "</h3>
                            <p><strong>Organized by:</strong> " . htmlspecialchars($orgName) . "</p>
                            <p><strong>Date:</strong> $formattedDate</p>
                            <p><strong>Time:</strong> $formattedTime</p>
                            <p>" . nl2br(htmlspecialchars($description)) . "</p>
                            <p>Log in to Arki Connect to register!</p>
                        </div>
                    </div>
                ";
                $mail->send();
                $count++;
            } catch (Exception $e) {
                error_log("Failed to send to {$row['email']}: " . $e->getMessage());
            }
        }
        $stmt->close();
        return $count;
    } catch (Exception $e) {
        error_log("Bulk email error: " . $e->getMessage());
        return 0;
    }
}

// ── Registration confirmation email ──
function //sendRegistrationEmailWithDebug($studentEmail, $studentName, $event) {
    try {
        $mail = createMailer();
        $mail->addAddress($studentEmail, $studentName);
        $mail->Subject = "Registration Confirmed - " . $event['title'];
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:#a43825;color:white;padding:20px;text-align:center;'>
                    <h2>✓ Registration Confirmed!</h2>
                </div>
                <div style='padding:30px;'>
                    <p>Hi <strong>" . htmlspecialchars($studentName) . "</strong>,</p>
                    <p>You are registered for <strong>" . htmlspecialchars($event['title']) . "</strong>.</p>
                    <p>See you there!</p>
                    <p>— Arki Connect Team</p>
                </div>
            </div>
        ";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Registration email error: " . $e->getMessage());
        return false;
    }
}
?>