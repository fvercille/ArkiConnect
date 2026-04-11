<?php
session_start();
$message = "";
$message_type = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $conn = new mysqli("localhost", "root", "", "user_db");
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if token exists and is not expired
    $sql = "SELECT ev.user_id, u.fullname, u.email 
            FROM email_verifications ev
            JOIN users u ON ev.user_id = u.id
            WHERE ev.token = ? 
            AND ev.expires_at > NOW() 
            AND ev.used = 0";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Update user status
        $updateSql = "UPDATE users SET email_verified = 1, status = 'active' WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $user['user_id']);
        
        if ($updateStmt->execute()) {
            // Mark token as used
            $markUsedSql = "UPDATE email_verifications SET used = 1 WHERE token = ?";
            $markStmt = $conn->prepare($markUsedSql);
            $markStmt->bind_param("s", $token);
            $markStmt->execute();
            $markStmt->close();
            
            $message = "✅ Email verified successfully! You can now log in.";
            $message_type = "success";
            
            // Auto redirect to login after 3 seconds
            header("refresh:3;url=login1.php");
        } else {
            $message = "❌ Verification failed. Please try again.";
            $message_type = "error";
        }
        
        $updateStmt->close();
    } else {
        $message = "❌ Invalid or expired verification link.";
        $message_type = "error";
    }
    
    $stmt->close();
    $conn->close();
} else {
    $message = "❌ No verification token provided.";
    $message_type = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Arki Connect</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .verification-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .verification-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .verification-icon.success { color: #28a745; }
        .verification-icon.error { color: #dc3545; }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-size: 16px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .login-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background-color: #A43825;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .login-link:hover {
            background-color: #8b2f1f;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-icon <?php echo $message_type; ?>">
            <?php echo $message_type === 'success' ? '✓' : '✗'; ?>
        </div>
        
        <h2><?php echo $message_type === 'success' ? 'Verification Successful!' : 'Verification Failed'; ?></h2>
        
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <?php if ($message_type === 'success'): ?>
            <p>Redirecting to login page in 3 seconds...</p>
        <?php endif; ?>
        
        <a href="login1.php" class="login-link">Go to Login</a>
    </div>
</body>
</html>