<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

// Check admin authorization
if ($role !== 'admin' || empty($user_id)) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$filter_status = $_GET['status'] ?? 'all';
$filter_org = $_GET['org'] ?? 'all';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$export = $_GET['export'] ?? 'pdf';

// Build query with filters
$where_conditions = ["1=1"];
$params = [];
$param_types = '';

if ($filter_status !== 'all') {
    $where_conditions[] = "e.status = ?";
    $params[] = $filter_status;
    $param_types .= 's';
}

if ($filter_org !== 'all') {
    $where_conditions[] = "e.created_by = ?";
    $params[] = $filter_org;
    $param_types .= 'i';
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "e.event_date >= ?";
    $params[] = $filter_date_from;
    $param_types .= 's';
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "e.event_date <= ?";
    $params[] = $filter_date_to;
    $param_types .= 's';
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get events with details
$events_sql = "SELECT 
                e.id,
                e.title,
                e.description,
                e.event_date,
                e.event_time,
                e.location,
                e.status,
                e.created_at,
                e.approved_at,
                e.rejection_reason,
                u.fullname as org_name,
                u.email as org_email,
                (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed') as total_registrations
              FROM events e
              LEFT JOIN users u ON e.created_by = u.id
              {$where_clause}
              ORDER BY e.created_at DESC";

$events_stmt = $conn->prepare($events_sql);
if (!empty($params)) {
    $events_stmt->bind_param($param_types, ...$params);
}
$events_stmt->execute();
$events_result = $events_stmt->get_result();
$events = $events_result->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats_sql = "SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_events,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_events,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_events,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_events,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_events,
                SUM((SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed')) as total_registrations
              FROM events e
              {$where_clause}";

$stats_stmt = $conn->prepare($stats_sql);
if (!empty($params)) {
    $stats_stmt->bind_param($param_types, ...$params);
}
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get organization name if filtered
$org_name = 'All Organizations';
if ($filter_org !== 'all') {
    $org_stmt = $conn->query("SELECT fullname FROM users WHERE id = {$filter_org}");
    if ($org_result = $org_stmt->fetch_assoc()) {
        $org_name = $org_result['fullname'];
    }
}

$conn->close();

// Export to CSV
if ($export === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="event_report_' . date('Y-m-d_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // CSV Headers
    fputcsv($output, [
        'Event ID',
        'Title',
        'Organization',
        'Organization Email',
        'Event Date',
        'Event Time',
        'Location',
        'Status',
        'Total Registrations',
        'Created Date',
        'Approved Date',
        'Rejection Reason'
    ]);
    
    // CSV Data
    foreach ($events as $event) {
        fputcsv($output, [
            $event['id'],
            $event['title'],
            $event['org_name'],
            $event['org_email'],
            $event['event_date'],
            $event['event_time'] ?? 'N/A',
            $event['location'],
            strtoupper($event['status']),
            $event['total_registrations'],
            $event['created_at'],
            $event['approved_at'] ?? 'N/A',
            $event['rejection_reason'] ?? 'N/A'
        ]);
    }
    
    fclose($output);
    exit();
}

// Generate PDF (export === 'pdf')
$html = generateReportHTML($events, $stats, $org_name, $filter_status, $filter_date_from, $filter_date_to);

// Output HTML that can be printed to PDF using browser's print function
header('Content-Type: text/html; charset=utf-8');
echo $html;
exit();

function generateReportHTML($events, $stats, $org_name, $filter_status, $filter_date_from, $filter_date_to) {
    $filter_info = [];
    if ($filter_status !== 'all') {
        $filter_info[] = "Status: " . strtoupper($filter_status);
    }
    if ($org_name !== 'All Organizations') {
        $filter_info[] = "Organization: " . $org_name;
    }
    if (!empty($filter_date_from)) {
        $filter_info[] = "From: " . date('M d, Y', strtotime($filter_date_from));
    }
    if (!empty($filter_date_to)) {
        $filter_info[] = "To: " . date('M d, Y', strtotime($filter_date_to));
    }
    
    $filter_text = !empty($filter_info) ? implode(' | ', $filter_info) : 'No filters applied';
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Event Status Report</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 landscape;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #a43825;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #a43825;
            font-size: 28pt;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            font-weight: 700;
        }
        
        .header .subtitle {
            font-size: 13pt;
            color: #666;
            margin: 8px 0;
            font-weight: 600;
        }
        
        .header .filter-info {
            font-size: 9pt;
            color: #999;
            margin-top: 8px;
            font-style: italic;
        }
        
        .header .date-range {
            font-size: 9pt;
            color: #999;
            margin-top: 5px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background-color: #a43825;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        .print-button:hover {
            background-color: #8a2f1e;
        }
        
        .summary-box {
            background-color: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 15px;
            border-right: 2px solid #e5e7eb;
        }
        
        .summary-item:last-child {
            border-right: none;
        }
        
        .summary-label {
            font-size: 9pt;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .summary-value {
            font-size: 24pt;
            font-weight: bold;
            color: #a43825;
        }
        
        .summary-value.approved { color: #10b981; }
        .summary-value.pending { color: #f59e0b; }
        .summary-value.rejected { color: #ef4444; }
        .summary-value.cancelled { color: #6b7280; }
        .summary-value.completed { color: #3b82f6; }
        
        .event-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        
        .event-table thead {
            background-color: #2d2d2d;
            color: white;
        }
        
        .event-table th {
            padding: 12px 10px;
            text-align: left;
            font-size: 9pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #1a1a1a;
        }
        
        .event-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .event-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .event-table td {
            padding: 10px;
            font-size: 9pt;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            white-space: nowrap;
        }
        
        .status-badge.approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-badge.rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .status-badge.cancelled {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .status-badge.completed {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-badge.upcoming {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }
        
        .no-events {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
            font-style: italic;
            font-size: 12pt;
        }
        
        .text-truncate {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print / Save as PDF
    </button>

    <div class="header">
        <h1>Event Status Report</h1>
        <div class="subtitle">' . htmlspecialchars($org_name) . '</div>
        <div class="filter-info">' . htmlspecialchars($filter_text) . '</div>
        <div class="date-range">Generated on ' . date('F d, Y \a\t g:i A') . '</div>
    </div>
    
    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Events</div>
            <div class="summary-value">' . $stats['total_events'] . '</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Approved</div>
            <div class="summary-value approved">' . $stats['approved_events'] . '</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Pending</div>
            <div class="summary-value pending">' . $stats['pending_events'] . '</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Rejected</div>
            <div class="summary-value rejected">' . $stats['rejected_events'] . '</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Cancelled</div>
            <div class="summary-value cancelled">' . $stats['cancelled_events'] . '</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Completed</div>
            <div class="summary-value completed">' . $stats['completed_events'] . '</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Registrations</div>
            <div class="summary-value">' . ($stats['total_registrations'] ?? 0) . '</div>
        </div>
    </div>';
    
    if (empty($events)) {
        $html .= '<div class="no-events">No events found matching the selected criteria.</div>';
    } else {
        $html .= '
    <table class="event-table">
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 18%;">Event Title</th>
                <th style="width: 15%;">Organization</th>
                <th style="width: 10%;">Event Date</th>
                <th style="width: 12%;">Location</th>
                <th style="width: 8%;">Registrations</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 12%;">Created</th>
                <th style="width: 10%;">Approved</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($events as $event) {
            $event_date = date('M d, Y', strtotime($event['event_date']));
            $created_date = date('M d, Y', strtotime($event['created_at']));
            $approved_date = $event['approved_at'] ? date('M d, Y', strtotime($event['approved_at'])) : 'N/A';
            
            $html .= '
            <tr>
                <td><strong>#' . $event['id'] . '</strong></td>
                <td>
                    <strong>' . htmlspecialchars($event['title']) . '</strong><br>
                    <span style="font-size: 8pt; color: #6b7280;">' 
                    . htmlspecialchars(substr($event['description'] ?? '', 0, 60)) 
                    . (strlen($event['description'] ?? '') > 60 ? '...' : '') . '</span>
                </td>
                <td>
                    ' . htmlspecialchars($event['org_name']) . '<br>
                    <span style="font-size: 8pt; color: #6b7280;">' . htmlspecialchars($event['org_email']) . '</span>
                </td>
                <td>' . $event_date . '<br><span style="font-size: 8pt; color: #6b7280;">' . ($event['event_time'] ?? 'TBA') . '</span></td>
                <td>' . htmlspecialchars($event['location']) . '</td>
                <td style="text-align: center;"><strong>' . $event['total_registrations'] . '</strong></td>
                <td>
                    <span class="status-badge ' . $event['status'] . '">' . $event['status'] . '</span>
                </td>
                <td>' . $created_date . '</td>
                <td>' . $approved_date . '</td>
            </tr>';
        }
        
        $html .= '
        </tbody>
    </table>';
    }
    
    $html .= '
    <div class="footer">
        <p><strong>Arki Connect - Event Management System</strong></p>
        <p>© ' . date('Y') . ' All rights reserved. This report is confidential and intended for administrative use only.</p>
        <p>Total Events Listed: ' . count($events) . ' | Report Generated: ' . date('F d, Y g:i A') . '</p>
    </div>
    
    <script>
        // Auto-print dialog on page load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>';
    
    return $html;
}