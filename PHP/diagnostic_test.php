<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set session for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 18;
    $_SESSION['role'] = 'student';
    $_SESSION['fullname'] = 'Test Student';
}

include 'db_connect.php';

echo "<h1>🔍 Diagnostic Test</h1>";
echo "<pre style='background: #f5f5f5; padding: 20px; border-radius: 8px;'>";

// Test 1: Database Connection
echo "TEST 1: Database Connection\n";
if ($conn && $conn->connect_error === null) {
    echo "✅ Connected successfully\n\n";
} else {
    echo "❌ Connection failed: " . $conn->connect_error . "\n\n";
    exit;
}

// Test 2: Check Tables
echo "TEST 2: Check Tables\n";
$tables = ['events', 'event_registrations', 'notifications', 'users'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Table '$table' exists\n";
    } else {
        echo "❌ Table '$table' MISSING\n";
    }
}
echo "\n";

// Test 3: Check Events Data
echo "TEST 3: Check Events in Database\n";
$query = "SELECT id, title, event_date, status, created_by FROM events WHERE status IN ('upcoming', 'ongoing') ORDER BY event_date DESC";
$result = $conn->query($query);
if ($result) {
    $count = $result->num_rows;
    echo "Found $count events\n";
    if ($count > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "- ID: {$row['id']}, Title: {$row['title']}, Date: {$row['event_date']}, Status: {$row['status']}, CreatedBy: {$row['created_by']}\n";
        }
    } else {
        echo "⚠️  No upcoming/ongoing events found. Please create one as OrgRep.\n";
    }
} else {
    echo "❌ Query error: " . $conn->error . "\n";
}
echo "\n";

// Test 4: Check Registrations
echo "TEST 4: Check Event Registrations\n";
$query = "SELECT id, event_id, user_id, registration_date, status FROM event_registrations LIMIT 5";
$result = $conn->query($query);
if ($result) {
    $count = $result->num_rows;
    echo "Found $count registrations\n";
    if ($count > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "- ID: {$row['id']}, EventID: {$row['event_id']}, UserID: {$row['user_id']}, Status: {$row['status']}\n";
        }
    }
} else {
    echo "❌ Query error: " . $conn->error . "\n";
}
echo "\n";

// Test 5: Check Notifications
echo "TEST 5: Check Notifications Table\n";
$query = "SELECT id, event_id, user_id, message, created_at FROM notifications LIMIT 5";
$result = $conn->query($query);
if ($result) {
    $count = $result->num_rows;
    echo "Found $count notifications\n";
    if ($count > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "- ID: {$row['id']}, EventID: {$row['event_id']}, UserID: {$row['user_id']}, Message: {$row['message']}\n";
        }
    }
} else {
    echo "❌ Query error: " . $conn->error . "\n";
}
echo "\n";

// Test 6: Simulate API Call
echo "TEST 6: Simulate API Call (Get Events)\n";
$query = "
    SELECT 
        e.id,
        e.title,
        e.description,
        e.event_date,
        e.event_time,
        e.image_path,
        e.status,
        u.fullname as hostOrg,
        (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed') as registrants
    FROM events e
    LEFT JOIN users u ON e.created_by = u.id
    WHERE e.status IN ('upcoming', 'ongoing')
    AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
    LIMIT 10
";

$result = $conn->query($query);
if ($result) {
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'date' => $row['event_date'],
            'status' => $row['status'],
            'hostOrg' => $row['hostOrg'],
            'registrants' => (int)$row['registrants']
        ];
    }
    echo "✅ Query successful! Found " . count($events) . " events\n";
    echo json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo "❌ Query error: " . $conn->error . "\n";
}

echo "</pre>";

// Test 7: File Check
echo "<h2>File Check</h2>";
echo "<pre style='background: #f5f5f5; padding: 20px; border-radius: 8px;'>";
$files = [
    'event_api.php' => __DIR__ . '/event_api.php',
    'db_connect.php' => __DIR__ . '/db_connect.php',
    '../JavaScript/Student_ds.js' => __DIR__ . '/../JavaScript/Student_ds.js'
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name exists at $path\n";
    } else {
        echo "❌ $name NOT found at $path\n";
    }
}
echo "</pre>";

?>