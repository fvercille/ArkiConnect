<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$autoload_path = __DIR__ . '/../vendor/autoload.php';

require_once $autoload_path;

// Include database connection
if (!file_exists('db_connect.php')) {
    die("Error: db_connect.php not found in " . __DIR__);
}
include 'db_connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Security check: Only admin can access this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: Only administrators can generate PDF reports.<br><br>
         <a href='login.php'>Go to Login</a>");
}

// Fetch all events from database
$sql = "SELECT e.id, e.title, e.description, e.event_date, e.event_time, 
               e.location, e.organizer, e.status, u.fullname as creator_name
        FROM events e
        LEFT JOIN users u ON e.created_by = u.id
        ORDER BY e.event_date ASC";

$result = $conn->query($sql);
$events = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

$conn->close();

// Configure Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);

// Create HTML content for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #A43825;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #A43825;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .info-box {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #A43825;
        }
        .info-box p {
            margin: 5px 0;
            font-size: 12px;
            color: #555;
        }
        .event-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .event-title {
            font-size: 18px;
            font-weight: bold;
            color: #A43825;
            margin: 0;
        }
        .event-id {
            background-color: #A43825;
            color: white;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .event-details {
            margin: 15px 0;
        }
        .detail-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .detail-label {
            display: table-cell;
            width: 120px;
            font-weight: bold;
            color: #555;
            font-size: 13px;
            vertical-align: top;
        }
        .detail-value {
            display: table-cell;
            color: #333;
            font-size: 13px;
        }
        .event-description {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 13px;
            line-height: 1.6;
            color: #444;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-upcoming { background-color: #fff3b0; color: #856404; }
        .status-ongoing { background-color: #d4edda; color: #155724; }
        .status-completed { background-color: #d1ecf1; color: #0c5460; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            font-size: 11px;
            color: #999;
        }
        .no-events {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Arki Connect - Events Report</h1>
        <p>Complete List of Events</p>
    </div>
    
    <div class="info-box">
        <p><strong>Report Generated:</strong> ' . date('F d, Y - h:i A') . '</p>
        <p><strong>Generated By:</strong> ' . htmlspecialchars($_SESSION['fullname'] ?? 'Admin User') . '</p>
        <p><strong>Total Events:</strong> ' . count($events) . '</p>
    </div>
';

if (empty($events)) {
    $html .= '<div class="no-events">No events found in the system.</div>';
} else {
    foreach ($events as $event) {
        $status_class = 'status-' . strtolower($event['status']);
        $formatted_date = date('F d, Y', strtotime($event['event_date']));
        $formatted_time = $event['event_time'] ? date('h:i A', strtotime($event['event_time'])) : 'TBD';
        
        $html .= '
        <div class="event-card">
            <div class="event-header">
                <h2 class="event-title">' . htmlspecialchars($event['title']) . '</h2>
                <span class="event-id">ID: ' . $event['id'] . '</span>
            </div>
            
            <div class="event-details">
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">' . $formatted_date . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value">' . $formatted_time . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">' . htmlspecialchars($event['location'] ?? 'Not specified') . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Organizer:</span>
                    <span class="detail-value">' . htmlspecialchars($event['organizer'] ?? 'N/A') . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Created By:</span>
                    <span class="detail-value">' . htmlspecialchars($event['creator_name'] ?? 'Unknown') . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge ' . $status_class . '">' . htmlspecialchars($event['status']) . '</span>
                    </span>
                </div>
            </div>
            
            <div class="event-description">
                <strong>Description:</strong><br>
                ' . nl2br(htmlspecialchars($event['description'] ?? 'No description available.')) . '
            </div>
        </div>';
    }
}

$html .= '
    <div class="footer">
        <p>© ' . date('Y') . ' Arki Connect. All rights reserved.</p>
        <p>This document was automatically generated by the Arki Connect system.</p>
    </div>
</body>
</html>';

// Load HTML to Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render PDF
$dompdf->render();

// Output PDF to browser
$filename = 'ArkiConnect_Events_Report_' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, array('Attachment' => 0)); // 0 = view in browser, 1 = force download
?>