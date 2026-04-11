<?php
session_start(); 

$homeLink = 'index.php'; // default public homepage
$isLoggedIn = isset($_SESSION['user_id']); // Check if user is logged in

if($isLoggedIn) {
    switch($_SESSION['role'] ?? ''){ 
        case 'student':
            $homeLink = 'Student_db.php';
            break;
        case 'org_rep':
            $homeLink = 'OrgRep_db.php';
            break;
        case 'admin':
            $homeLink = 'Admin_db.php';
            break;
        default:
            $homeLink = 'Student_db.php'; 
    }
    
    header("Location: " . $homeLink);
    exit(); 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arki Connect</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    
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
    <div class="portal-image" id="homeContent">
        <img src="../Images/newlogo.png" alt="Arki Connect">
    </div>
    
</main>

    <script src="script.js"></script>
    

</body>
</html>