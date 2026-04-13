<?php
/**
 * EMAIL CONFIGURATION FOR ARKI CONNECT
 * 
 * This file contains the centralized email sending functions
 * All emails are sent from: arkiconnect@example.com
 * All notifications go to this address for records
 */

// ==========================================
// EMAIL CONFIGURATION
// ==========================================
define('ARKICONNECT_EMAIL', 'arkiconnect@example.com');
define('ARKICONNECT_NAME', 'Arki Connect');
define('ARKICONNECT_SUPPORT', 'support@arkiconnect.example.com');

// ==========================================
// SEND REGISTRATION EMAIL TO STUDENT
// ==========================================
function //sendRegistrationEmailWithDebug($studentEmail, $studentName, $event) {
    error_log("=== REGISTRATION EMAIL DEBUG START ===");
    
    // Validate student email
    $studentEmail = filter_var($studentEmail, FILTER_SANITIZE_EMAIL);
    error_log("TO Email: " . $studentEmail);
    
    if (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
        error_log("❌ INVALID STUDENT EMAIL: " . $studentEmail);
        return false;
    }
    
    // Prepare email content
    $eventDate = date('F j, Y', strtotime($event['event_date']));
    $eventTime = $event['event_time'] ? date('g:i A', strtotime($event['event_time'])) : 'TBA';
    $location = $event['location'] ?? 'TBA';
    
    $subject = "Registration Confirmed - " . $event['title'];
    error_log("Subject: " . $subject);
    
    $message = "
    <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                <div style='background: #a43825; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✓ Registration Confirmed!</h2>
                </div>
                <div style='padding: 30px;'>
                    <p>Hi <strong>" . htmlspecialchars($studentName) . "</strong>,</p>
                    <p>Thank you for registering! Your registration has been confirmed for the following event:</p>
                    
                    <div style='background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #a43825;'>
                        <h3 style='margin-top: 0; color: #a43825;'>" . htmlspecialchars($event['title']) . "</h3>
                        <p style='margin: 10px 0;'><strong>📅 Date:</strong> " . $eventDate . "</p>
                        <p style='margin: 10px 0;'><strong>🕐 Time:</strong> " . $eventTime . "</p>
                        <p style='margin: 10px 0;'><strong>📍 Location:</strong> " . htmlspecialchars($location) . "</p>
                        <p style='margin: 10px 0;'><strong>📝 Description:</strong></p>
                        <p style='color: #666;'>" . nl2br(htmlspecialchars($event['description'] ?? '')) . "</p>
                    </div>
                    
                    <p style='color: #666; font-size: 0.95rem;'>Please arrive on time. If you have any questions, feel free to contact us.</p>
                    
                    <p style='margin-top: 20px;'>We look forward to seeing you there!</p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                    <p style='text-align: center; color: #999; font-size: 0.85rem;'>This is an automated email from Arki Connect. Please do not reply directly to this email.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    error_log("Message length: " . strlen($message));
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . ARKICONNECT_NAME . " <" . ARKICONNECT_EMAIL . ">\r\n";
    
    error_log("Headers configured");
    
    // Attempt to send
    $result = mail($studentEmail, $subject, $message, $headers);
    
    if ($result) {
        error_log("✅ REGISTRATION EMAIL SENT to: " . $studentEmail);
        // Also log to Arki Connect email for records
        logEmailToArkiConnect("Student Registration", $studentName, $studentEmail, $event['title']);
    } else {
        error_log("❌ REGISTRATION EMAIL FAILED to send to: " . $studentEmail);
    }
    
    error_log("=== REGISTRATION EMAIL DEBUG END ===\n");
    
    return $result;
}

// ==========================================
// SEND EVENT APPROVAL EMAIL TO ORG REP
// ==========================================
function //sendApprovalEmail($orgName, $eventTitle, $adminName = 'Admin') {
    error_log("=== APPROVAL EMAIL DEBUG START ===");
    
    // Send to Arki Connect email (system email)
    $to = ARKICONNECT_EMAIL;
    
    $subject = "Event Approved - " . $eventTitle . " by " . $orgName;
    error_log("Subject: " . $subject);
    
    $message = "
    <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                <div style='background: #10b981; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✓ Event Approved!</h2>
                </div>
                <div style='padding: 30px;'>
                    <p><strong>Organization:</strong> " . htmlspecialchars($orgName) . "</p>
                    <p><strong>Approved By:</strong> " . htmlspecialchars($adminName) . "</p>
                    
                    <div style='background: #f0fdf4; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10b981;'>
                        <h3 style='margin-top: 0; color: #10b981;'>" . htmlspecialchars($eventTitle) . "</h3>
                        <p style='color: #065f46;'>This event has been approved and is now visible to all students on Arki Connect!</p>
                        <p style='color: #065f46;'>Students can now register for this event.</p>
                    </div>
                    
                    <p style='color: #666; font-size: 0.95rem;'>Thank you for creating an engaging event for the architecture community!</p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                    <p style='text-align: center; color: #999; font-size: 0.85rem;'>This is a system notification from Arki Connect.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . ARKICONNECT_NAME . " <" . ARKICONNECT_EMAIL . ">\r\n";
    
    $result = mail($to, $subject, $message, $headers);
    
    if ($result) {
        error_log("✅ APPROVAL EMAIL SENT to: " . $to);
    } else {
        error_log("❌ APPROVAL EMAIL FAILED to send to: " . $to);
    }
    
    error_log("=== APPROVAL EMAIL DEBUG END ===\n");
    
    return $result;
}

// ==========================================
// SEND EVENT REJECTION EMAIL TO ORG REP
// ==========================================
function sendRejectionEmail($orgName, $eventTitle, $reason, $adminName = 'Admin') {
    error_log("=== REJECTION EMAIL DEBUG START ===");
    
    // Send to Arki Connect email (system email)
    $to = ARKICONNECT_EMAIL;
    
    $subject = "Event Rejected - " . $eventTitle . " by " . $orgName;
    error_log("Subject: " . $subject);
    
    $message = "
    <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                <div style='background: #ef4444; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>⚠️ Event Rejected</h2>
                </div>
                <div style='padding: 30px;'>
                    <p><strong>Organization:</strong> " . htmlspecialchars($orgName) . "</p>
                    <p><strong>Rejected By:</strong> " . htmlspecialchars($adminName) . "</p>
                    
                    <div style='background: #fef2f2; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ef4444;'>
                        <h3 style='margin-top: 0; color: #ef4444;'>" . htmlspecialchars($eventTitle) . "</h3>
                        <p><strong>Rejection Reason:</strong></p>
                        <p style='color: #666;'>" . nl2br(htmlspecialchars($reason)) . "</p>
                    </div>
                    
                    <p style='color: #666; font-size: 0.95rem;'>You can modify your event and resubmit for approval. Please contact support if you have any questions.</p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                    <p style='text-align: center; color: #999; font-size: 0.85rem;'>This is a system notification from Arki Connect.</p>
                </div>
            </div>
        </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . ARKICONNECT_NAME . " <" . ARKICONNECT_EMAIL . ">\r\n";
    
    $result = mail($to, $subject, $message, $headers);
    
    if ($result) {
        error_log("✅ REJECTION EMAIL SENT to: " . $to);
    } else {
        error_log("❌ REJECTION EMAIL FAILED to send to: " . $to);
    }
    
    error_log("=== REJECTION EMAIL DEBUG END ===\n");
    
    return $result;
}

// ==========================================
// LOG EMAIL TO ARKI CONNECT FOR RECORDS
// ==========================================
function logEmailToArkiConnect($emailType, $userName, $userEmail, $eventName) {
    error_log("📧 EMAIL LOG: [$emailType] User: $userName ($userEmail) | Event: $eventName");
}

// ==========================================
// SEND BULK EMAIL TO ALL STUDENTS
// ==========================================
function sendEventCreatedEmailToStudents($conn, $eventTitle, $eventDate, $eventTime, $description, $orgName) {
    try {
        // Get all student emails
        $studentQuery = $conn->prepare("
            SELECT email, fullname FROM users WHERE role = 'student' AND email IS NOT NULL
        ");
        $studentQuery->execute();
        $result = $studentQuery->get_result();
        
        $eventDate = date('F j, Y', strtotime($eventDate));
        $eventTime = $eventTime ? date('g:i A', strtotime($eventTime)) : 'TBA';
        
        $subject = "New Event: " . $eventTitle;
        
        $message = "
        <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                    <div style='background: #a43825; color: white; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0;'>📢 New Event Created!</h2>
                    </div>
                    <div style='padding: 30px;'>
                        <p>Hi,</p>
                        <p>A new event has been created and approved on Arki Connect!</p>
                        
                        <div style='background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #a43825;'>
                            <h3 style='margin-top: 0; color: #a43825;'>" . htmlspecialchars($eventTitle) . "</h3>
                            <p style='margin: 10px 0;'><strong>🏢 Organized by:</strong> " . htmlspecialchars($orgName) . "</p>
                            <p style='margin: 10px 0;'><strong>📅 Date:</strong> " . $eventDate . "</p>
                            <p style='margin: 10px 0;'><strong>🕐 Time:</strong> " . $eventTime . "</p>
                            <p style='margin: 10px 0;'><strong>📝 Description:</strong></p>
                            <p style='color: #666;'>" . nl2br(htmlspecialchars($description)) . "</p>
                        </div>
                        
                        <p style='color: #666; font-size: 0.95rem;'>Visit Arki Connect now to register for this event and don't miss out!</p>
                        
                        <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                        <p style='text-align: center; color: #999; font-size: 0.85rem;'>This is an automated email from Arki Connect.</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . ARKICONNECT_NAME . " <" . ARKICONNECT_EMAIL . ">\r\n";
        
        // Send email to all students
        $emailCount = 0;
        while ($row = $result->fetch_assoc()) {
            if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                mail($row['email'], $subject, $message, $headers);
                error_log("✅ Event email sent to student: " . $row['fullname'] . " (" . $row['email'] . ")");
                $emailCount++;
            }
        }
        
        $studentQuery->close();
        error_log("📧 Sent event notification to $emailCount students");
        return $emailCount;
    } catch (Exception $e) {
        error_log("❌ Error sending event emails: " . $e->getMessage());
        return 0;
    }
}

?>