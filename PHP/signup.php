<?php
require __DIR__ . '/../vendor/autoload.php';
include 'db_connect.php';

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// To handle form submission
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "user_db"); 
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data with proper validation
    $fullname = trim($_POST['signupName']);
    $studentnum = trim($_POST['signupStudentNumber']);
    $email = trim($_POST['signupEmail']);
    $password = $_POST['signupPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // validation
    if (empty($fullname) || empty($studentnum) || empty($email) || empty($password)) {
        $message = "All fields are required!";
        $message_type = "error";
    } elseif (!preg_match('/^[0-9]{6,15}$/', $studentnum)) {
        $message = "Invalid student number!";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
        $message_type = "error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match!";
        $message_type = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long!";
        $message_type = "error";
    } else {

        // Check if student number or email already exists
        $checkSql = "SELECT studentnum, email FROM users WHERE studentnum = ? OR email = ?";
        $checkStmt = $conn->prepare($checkSql);
        
        if (!$checkStmt) {
            $message = "Database error occurred";
            $message_type = "error";
        } else {
            $checkStmt->bind_param("ss", $studentnum, $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $message = "Student number or email already exists!";
                $message_type = "error";
            } else {
                // Hash password for security
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Generate verification token
                $verification_token = bin2hex(random_bytes(32));

                // Insert new user with email_verified = 0 and status = 'inactive'
                $sql = "INSERT INTO users (fullname, studentnum, email, password, email_verified, status, created_at) 
                        VALUES (?, ?, ?, ?, 0, 'inactive', NOW())";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    $message = "Database error occurred";
                    $message_type = "error";
                } else {
                    $stmt->bind_param("ssss", $fullname, $studentnum, $email, $hashedPassword);
                    
                    if ($stmt->execute()) {
                        $user_id = $stmt->insert_id;
                        
                        // Store verification token in email_verifications table
                        $tokenSql = "INSERT INTO email_verifications (user_id, token, expires_at, created_at) 
                                     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())";
                        $tokenStmt = $conn->prepare($tokenSql);
                        $tokenStmt->bind_param("is", $user_id, $verification_token);
                        $tokenStmt->execute();
                        $tokenStmt->close();
                        
                        // Send verification email
                        $verification_link = "http://localhost/FinalProject1/PHP/verify_email.php?token=" . $verification_token;
                        $email_body = getVerificationEmailTemplate($fullname, $verification_link);
                        
                        if (sendEmail($email, "Verify Your Arki Connect Account", $email_body)) {
                            $message = "Registration successful! Please check your email to verify your account.";
                            $message_type = "success";
                        } else {
                            $message = "Account created but email verification failed. Please contact support.";
                            $message_type = "warning";
                        }
                    } else {
                        $message = "Registration failed. Please try again.";
                        $message_type = "error";
                    }
                    $stmt->close();
                }
            }
            $checkStmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arki Connect - Sign Up</title>
    <link rel="stylesheet" href="../CSS/style.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

    body {
        font-family: 'Montserrat', sans-serif;
    }

    /* Override main-content for signup — landscape layout */
    .main-content {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 100px 20px 20px;
    }

    /* Card — wider for landscape */
    .login-section {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 40px;
        width: 100%;
        max-width: 820px;         /* wider than login */
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.6);
        animation: slideUp 0.6s ease-out forwards;
    }

    /* Title */
    .form-title {
        text-align: center;
        margin-bottom: 24px;
        color: #a43825;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* Two-column grid for form fields */
    #signupForm {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    /* Full width items */
    #signupForm .submit-btn,
    #signupForm .back-btn,
    #signupForm .forgot-link {
        grid-column: span 2;
    }

    /* Input fields */
    .form-group {
        margin-bottom: 0;
    }

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

    /* Buttons */
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
        margin-bottom: 0;
    }

    .submit-btn:hover {
        background-color: #8a2f1c;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(164, 56, 37, 0.35);
    }

    .back-btn {
        background-color: transparent !important;
        color: #a43825 !important;
        border: 1.5px solid #a43825;
        box-shadow: none;
        margin-top: 0;
    }

    .back-btn:hover {
        background-color: #a43825 !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(164, 56, 37, 0.25) !important;
    }

    /* Divider */
    .divider {
        text-align: center;
        margin: 20px 0 14px;
        position: relative;
    }

    .divider:before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background-color: #e0e0e0;
    }

    .divider span {
        background: rgba(255,255,255,0.92);
        padding: 0 14px;
        color: #aaa;
        font-size: 13px;
        font-weight: 600;
        position: relative;
        letter-spacing: 1px;
    }

.social-btns {
    display: flex;
    justify-content: center;
    gap: 16px;
}

.social-btn {
    background: white;
    border: 1.5px solid #e0e0e0;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.social-btn:hover {
    border-color: #a43825;
    background-color: #fff0ee;
    box-shadow: 0 4px 14px rgba(164, 56, 37, 0.2);
    transform: translateY(-2px);
}

    /* Already have account */
    .login-section > div[style] {
        text-align: center;
        margin-top: 16px;
        font-size: 13px;
        color: #666;
    }

    .login-section > div[style] a {
        color: #a43825;
        font-weight: 600;
    }

    .login-section > div[style] a:hover {
        text-decoration: underline;
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

    .message.warning {
        background-color: #fffbea;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    /* Responsive — back to single column on mobile */
    @media screen and (max-width: 600px) {
        #signupForm {
            grid-template-columns: 1fr;
        }

        #signupForm .submit-btn,
        #signupForm .back-btn {
            grid-column: span 1;
        }

        .login-section {
            padding: 30px 20px;
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
                <a href="signup.php" class="btn signup-btn active">Sign Up</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="login-section show">
            <h2 class="form-title">Sign Up</h2>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form id="signupForm" method="POST" action="signup.php">
                <div class="form-group">
                    <input type="text" name="signupName" id="signupName" placeholder="Full Name" 
                           value="<?php echo isset($_POST['signupName']) ? htmlspecialchars($_POST['signupName']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <input type="text" name="signupStudentNumber" id="signupStudentNumber" placeholder="Student Number" 
                           value="<?php echo isset($_POST['signupStudentNumber']) ? htmlspecialchars($_POST['signupStudentNumber']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <input type="email" name="signupEmail" id="signupEmail" placeholder="Email" 
                           value="<?php echo isset($_POST['signupEmail']) ? htmlspecialchars($_POST['signupEmail']) : ''; ?>" required>
                </div>
                <div class="form-group password-group">
                    <input type="password" name="signupPassword" id="signupPassword" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePassword('signupPassword')"></span>
                </div>
                <div class="form-group password-group">
                    <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
                    <span class="toggle-password" onclick="togglePassword('confirmPassword')"></span>
                </div>
                <button type="submit" class="submit-btn">Sign Up</button>
                <a href="index.php" class="submit-btn back-btn">Back to Home</a>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
           <!-- WITH THIS -->
<div class="social-btns">
    <button class="social-btn" onclick="googleSignup()" title="Sign up with Google">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" style="width: 24px; height: 24px;">
    </button>
    <button class="social-btn" onclick="facebookSignup()" title="Sign up with Facebook">
        <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" alt="Facebook" style="width: 24px; height: 24px;">
    </button>
</div>
            
            <div style="text-align: center; margin-top: 20px;">
                <p>Already have an account? <a href="login.php">Log In</a> </p>
            </div>
        </div>
    </main>

    <!-- ADD THIS just before </body> in signup.php -->
<script>
    function googleSignup() {
        alert('Google Sign Up coming soon!');
    }

    function facebookSignup() {
        alert('Facebook Sign Up coming soon!');
    }
</script>

<script src="../JavaScript/script.js"></script>
</body>
</html>