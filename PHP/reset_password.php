<?php
session_start();
$message = "";
$message_type = "";
$valid_token = false;
$email = "";

// Check if token is provided
if (!isset($_GET['token']) && !isset($_POST['token'])) {
    header("Location: forgot_password.php");
    exit();
}

$token = isset($_POST['token']) ? $_POST['token'] : $_GET['token'];

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify token
$sql = "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $valid_token = true;
    $row = $result->fetch_assoc();
    $email = $row['email'];
} else {
    $message = "❌ Invalid or expired reset link.";
    $message_type = "error";
}

$stmt->close();

// Process password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
        $message_type = "error";
    } elseif (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $updateSql = "UPDATE users SET password = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $hashed_password, $email);
        
        if ($updateStmt->execute()) {
            // Mark token as used
            $markUsedSql = "UPDATE password_resets SET used = 1 WHERE token = ?";
            $markStmt = $conn->prepare($markUsedSql);
            $markStmt->bind_param("s", $token);
            $markStmt->execute();
            $markStmt->close();
            
            $message = "✅ Password reset successful! Redirecting to login...";
            $message_type = "success";
            
            header("refresh:3;url=login1.php");
        } else {
            $message = "❌ Failed to reset password. Please try again.";
            $message_type = "error";
        }
        
        $updateStmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Arki Connect</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
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
        .info-text {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="index.php">
                    <img src="../Images/arkiconnect.png" alt="Arki Connect Logo">
                </a>
            </div>
            
            <nav class="navlinks">
                <a href="index.php">Home</a>
                <a href="#events">Events</a>
                <a href="#calendar">Calendar</a>
                <a href="#announcements">Announcements</a>
            </nav>
            
            <div class="auth-buttons">
                <a href="login1.php" class="btn login-btn">Log In</a>
                <a href="signup.php" class="btn signup-btn">Sign Up</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="login-section show">
            <h2 class="form-title">Reset Password</h2>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token && $message_type !== 'success'): ?>
                <p class="info-text">Enter your new password below.</p>
                
                <form method="POST" action="reset_password.php">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group password-group">
                        <input type="password" name="new_password" id="new_password" 
                               placeholder="New Password" required>
                        <span class="toggle-password" onclick="togglePassword('new_password')"></span>
                    </div>
                    
                    <div class="form-group password-group">
                        <input type="password" name="confirm_password" id="confirm_password" 
                               placeholder="Confirm New Password" required>
                        <span class="toggle-password" onclick="togglePassword('confirm_password')"></span>
                    </div>
                    
                    <button type="submit" class="submit-btn">Reset Password</button>
                </form>
            <?php else: ?>
                <a href="forgot_password.php" class="submit-btn">Request New Reset Link</a>
            <?php endif; ?>
            
            <a href="login1.php" class="submit-btn back-btn" style="margin-top: 10px;">Back to Login</a>
        </div>
    </main>

    <script src="../JavaScript/script1.js"></script>
</body>
</html>