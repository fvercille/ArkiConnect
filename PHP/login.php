<?php   
session_start();
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    $_SESSION = array(); 
    session_destroy();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['role'])) {
    
    $user_role_lower = strtolower($_SESSION['role']);

    if ($user_role_lower === 'admin') {
        header("Location: Admin_db.php");
        exit();
    } elseif ($user_role_lower === 'org_rep') {
        header("Location: OrgRep_db.php");
        exit();
    } else {
        header("Location: Student_db.php");
        exit();
    }
}


error_reporting(0);
ini_set('display_errors', 0);
   
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once __DIR__ . '/db_connect.php'; 

    $studentnum = trim($_POST['studentnum']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Email and password are required!";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
        $message_type = "error";
    } else {
        $sql = "SELECT id, fullname, studentnum, email, password, status, role 
         FROM users 
         WHERE email = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            $message = "Database error occurred";
            $message_type = "error";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    if (!isset($user['status']) || $user['status'] === 'active' || $user['status'] === null) {

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['studentnum'] = $user['studentnum'];
                        $_SESSION['fullname'] = $user['fullname'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        
                        $user_role_lower = strtolower($user['role']);
                        
                        if ($user_role_lower === 'admin') {
                            header("Location: Admin_db.php");
                            exit();
                        } elseif ($user_role_lower === 'org_rep') {
                            header("Location: OrgRep_db.php");
                            exit();
                        } else {
                            header("Location: Student_db.php");
                            exit();
                        }

                    } else {
                        $message = "Account is inactive. Please contact administrator.";
                        $message_type = "error";
                    }
                } else {
                    $message = "Incorrect password.";
                    $message_type = "error";
                }
            } else {
                $message = "No matching student found with those credentials.";
                $message_type = "error";
            }
            $stmt->close();
        }
        $conn->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arki Connect - Login</title>
    <link rel="stylesheet" href="../CSS/style.css">
     <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <style>
    /* Font */
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

    /* Overall page */
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
        margin-bottom: -105px;
    }

    /* Title */
    .form-title {
        text-align: center;
        margin-bottom: 28px;
        color: #a43825;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* Input fields */
    .form-group input,
    #role {
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

    .form-group input:focus,
    #role:focus {
        border-color: #a43825;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(164, 56, 37, 0.08);
    }

    .form-group input::placeholder {
        color: #aaa;
        font-weight: 400;
    }

    /* Select dropdown */
    #role {
        appearance: none;
        cursor: pointer;
        color: #555;
    }

    .select-wrapper::after {
        content: '▼';
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        font-size: 11px;
        color: #a43825;
        pointer-events: none;
    }

    /* Forgot link */
    .forgot-link a {
        font-size: 13px;
        font-weight: 500;
        color: #a43825;
        letter-spacing: 0.3px;
    }

    .forgot-link a:hover {
        text-decoration: underline;
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
            <h2 class="form-title">Log In</h2>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
           <form id="loginForm" method="POST" action="login.php">

    <div class="form-group select-wrapper">
        <select id="role" name="role" onchange="toggleStudentField()">
            <option value="student" selected>Student</option>
            <option value="org_rep">Organization Representative</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    <div class="form-group" id="studentNumberGroup">
        <input type="text" name="studentnum" id="studentNumber" placeholder="Student Number"
                value="<?php echo isset($_POST['studentnum']) ? htmlspecialchars($_POST['studentnum']) : ''; ?>">
    </div>

    <div class="form-group">
        <input type="email" name="email" id="email" placeholder="Email"
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
    </div>

    <div class="form-group password-group">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <span class="toggle-password" onclick="togglePassword('password')"></span>
    </div>

    <div class="forgot-link">
        <a href="forgot_password.php">Forgot password?</a>
    </div>

    <button type="submit" class="submit-btn">Log In</button>
    <a href="index.php" class="submit-btn back-btn">Back to Home</a>
</form>
        </div>
    </main>

    <script src="../JavaScript/script.js"></script>
</body>
</html>