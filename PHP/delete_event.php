<?php
session_start();

// Get logged-in user info FIRST before database connection
$created_by = $_SESSION['user_id'] ?? 0;

// Security check - do this BEFORE any database operations
if ($created_by === 0) {
    header("Location: login.php?error=session_expired");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'user_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if event ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: OrgRep_db.php?error=invalid_id");
    exit;
}

$event_id = intval($_GET['id']);

// Verify that the event belongs to the logged-in user
$stmt = $conn->prepare("SELECT id, image_path FROM events WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $event_id, $created_by);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Event doesn't exist or doesn't belong to this user
    $stmt->close();
    $conn->close();
    header("Location: OrgRep_db.php?error=unauthorized");
    exit;
}

$event = $result->fetch_assoc();
$image_path = $event['image_path'];
$stmt->close();

// Delete the event from database
$stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $event_id, $created_by);

if ($stmt->execute()) {
    // If event had an image, delete it from the server
    if (!empty($image_path) && file_exists($image_path)) {
        unlink($image_path);
    }
    
    $stmt->close();
    $conn->close();
    header("Location: OrgRep_db.php?success=deleted");
    exit;
} else {
    $stmt->close();
    $conn->close();
    header("Location: OrgRep_db.php?error=delete_failed");
    exit;
}
?>