<?php
require_once 'email_config.php';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = "Please enter your email address.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } else {
        $conn = new mysqli("localhost", "root", "", "user_db");
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Check if email exists
        $sql = "SELECT id, fullname, email FROM users WHERE email = ? AND status = 'active'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            
            // Delete old reset tokens for this email
            $deleteSql = "DELETE FROM password_resets WHERE email = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("s", $email);
            $deleteStmt->execute();
            $deleteStmt->close();
            
            // Insert new reset token
            $insertSql = "INSERT INTO password_resets (email, token, expires_at, created_at) 
                         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ss", $email, $reset_token);
            $insertStmt->execute();
            $insertStmt->close();
            
            // Send reset email
            $reset_link = "http://localhost/FinalProject1/PHP/reset_password.php?token=" . $reset_token;
            $email_body = getPasswordResetEmailTemplate($user['fullname'], $reset_link);
            
            if (sendEmail($email, "Reset Your Arki Connect Password", $email_body)) {
                $message = "✅ Password reset link has been sent to your email.";
                $message_type = "success";
            } else {
                $message = "⚠️ Failed to send email. Please try again later.";
                $message_type = "error";
            }
        } else {
            $message = "✅ If an account exists with this email, you will receive a password reset link.";
            $message_type = "success";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Arki Connect</title>
    <link rel="stylesheet" href="../CSS/style.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

    body {
        font-family: 'Montserrat', sans-serif;
    }

    /* Login card */
    .login-section {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 45px 40px;
        width: 100%;
        max-width: 420px;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.6);
        animation: slideUp 0.6s ease-out forwards;
    }

    /* Title */
    .form-title {
        text-align: center;
        margin-bottom: 8px;
        color: #a43825;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* Subtitle info text */
    .info-text {
        text-align: center;
        color: #888;
        font-size: 13px;
        font-weight: 500;
        margin: 0 0 24px;
        line-height: 1.6;
        letter-spacing: 0.2px;
    }

    /* Input field */
    .form-group input {
        width: 100%;
        padding: 13px 18px;
        border: 1.5px solid #e0e0e0;
        border-radius: 12px;
        font-size: 14px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        color: #333;
        background-color: #fafafa;
        transition: all 0.3s ease;
        outline: none;
    }

    .form-group input:focus {
        border-color: #a43825;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(164, 56, 37, 0.08);
    }

    .form-group input::placeholder {
        color: #aaa;
        font-weight: 400;
    }

    /* Submit button */
    .submit-btn {
        display: block;
        width: 100%;
        background-color: #a43825;
        color: white;
        padding: 13px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
        margin-bottom: 12px;
    }

    .submit-btn:hover {
        background-color: #8a2f1c;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(164, 56, 37, 0.35);
    }

    /* Back button */
    .back-btn {
        background-color: transparent !important;
        color: #a43825 !important;
        border: 1.5px solid #a43825;
        box-shadow: none;
        margin-bottom: 0;
    }

    .back-btn:hover {
        background-color: #a43825 !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(164, 56, 37, 0.25) !important;
    }

    /* Messages */
    .message {
        padding: 12px 16px;
        margin: 0 0 16px;
        border-radius: 10px;
        text-align: center;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0.3px;
        line-height: 1.5;
    }

    .message.success {
        background-color: #edfaf1;
        color: #1a6b3a;
        border: 1px solid #b7e4c7;
    }

    .message.error {
        background-color: #fff0ee;
        color: #a43825;
        border: 1px solid #f5c6cb;
    }

    /* Responsive */
    @media screen and (max-width: 480px) {
        .login-section {
            padding: 30px 20px;
        }

        .form-title {
            font-size: 22px;
        }
    }
</style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="index.php">
                    <img src="../Images/newlogo.png" alt="Arki Connect Logo">
                </a>
            </div>
            
            
            <div class="auth-buttons">
                <a href="login.php" class="btn login-btn">Log In</a>
                <a href="signup.php" class="btn signup-btn">Sign Up</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="login-section show">
            <h2 class="form-title">Forgot Password?</h2>
            <p class="info-text">Enter your email address and we'll send you a link to reset your password.</p>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="forgot_password.php">
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="Enter your email address" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <button type="submit" class="submit-btn">Send Reset Link</button>
                <a href="login.php" class="submit-btn back-btn">Back to Login</a>
            </form>
        </div>
    </main>

    <script src="../JavaScript/script1.js"></script>
</body>
</html>