<?php
session_start();

require_once __DIR__ . '/db_connect.php';er_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logged-in user info
$created_by = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_org = $_SESSION['user_org'] ?? 'Organization';

// Security check
if ($created_by === 0) {
    header("Location: login.php?error=session_expired");
    exit;
}

// ===================================
// DASHBOARD STATISTICS QUERIES
// ===================================

// Total events created by this user
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE created_by = ?");
$stmt->bind_param("i", $created_by);
$stmt->execute();
$total_events = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total registrants for this user's events
if ($conn->query("SHOW TABLES LIKE 'event_registrations'")->num_rows > 0) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM event_registrations er
        INNER JOIN events e ON er.event_id = e.id
        WHERE e.created_by = ?
    ");
    $stmt->bind_param("i", $created_by);
    $stmt->execute();
    $total_registrants = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
} else {
    $total_registrants = 0;
}

// Ongoing events count
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM events 
    WHERE created_by = ? AND status = 'ongoing'
");
$stmt->bind_param("i", $created_by);
$stmt->execute();
$ongoing_events = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// ===================================
// FETCH RECENT EVENTS FOR EVENT MANAGEMENT LIST
// ===================================
$stmt = $conn->prepare("
    SELECT id, title, event_date, event_time, status, image_path 
    FROM events 
    WHERE created_by = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $created_by);
$stmt->execute();
$result = $stmt->get_result();

$recent_events = [];
while ($row = $result->fetch_assoc()) {
    $recent_events[] = $row;
}

$stmt->close();

// Success/Error messages
$success_message = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $success_message = "Event created successfully!";
    } elseif ($_GET['success'] == 'deleted') {
        $success_message = "Event deleted successfully!";
    }
}

$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'unauthorized') {
        $error_message = "You don't have permission to delete that event.";
    } elseif ($_GET['error'] == 'delete_failed') {
        $error_message = "Failed to delete event. Please try again.";
    } elseif ($_GET['error'] == 'invalid_id') {
        $error_message = "Invalid event ID.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Org Representative Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/OrgRep_db.css"> 
    <style>
        /* Modern Table Layout Styles */
        .events-table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 24px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 28px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .table-controls {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-filter, .btn-export {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-filter:hover, .btn-export:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }

        .view-all-link {
            color: #a43825;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 6px;
            background-color: #fff5f5;
        }

        .view-all-link:hover {
            background-color: #fee2e2;
            color: #7f1d1d;
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
        }

        .events-table thead {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .events-table th {
            padding: 16px 24px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .events-table th.sortable {
            cursor: pointer;
            user-select: none;
        }

        .events-table th.sortable:hover {
            color: #374151;
        }

        .events-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s ease;
        }

        .events-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .events-table tbody tr:last-child {
            border-bottom: none;
        }

        .events-table td {
            padding: 16px 24px;
            color: #374151;
            font-size: 0.875rem;
        }

        .event-name-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .event-image-small {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .event-name-text {
            font-weight: 600;
            color: #1f2937;
        }

        .event-id-text {
            color: #6b7280;
            font-family: 'Courier New', monospace;
            font-size: 0.813rem;
        }

        .status-badge-table {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-badge-table.accepted {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-badge-table.declined {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-badge-table.ongoing,
        .status-badge-table.on-progress,
        .status-badge-table.on_progress {
            background-color: #fef3c7;
            color: #92400e;
        }

        .action-dots {
            color: #9ca3af;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .action-dots:hover {
            background-color: #f3f4f6;
            color: #374151;
        }

        .no-events-state {
            text-align: center;
            padding: 80px 20px;
            color: #9ca3af;
        }

        .no-events-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 24px;
        }

        .no-events-state h3 {
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .no-events-state p {
            font-size: 0.938rem;
            margin-bottom: 24px;
        }

        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-left: 4px solid #059669;
            color: #065f46;
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .events-table {
                display: block;
                overflow-x: auto;
            }

            .events-table thead {
                display: none;
            }

            .events-table tbody tr {
                display: block;
                margin-bottom: 16px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 16px;
            }

            .events-table td {
                display: block;
                padding: 8px 0;
                border: none;
            }

            .events-table td:before {
                content: attr(data-label);
                font-weight: 600;
                color: #6b7280;
                display: block;
                margin-bottom: 4px;
                font-size: 0.75rem;
                text-transform: uppercase;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">
    
    <aside class="sidebar" id="left-sidebar">
        
        <div class="sidebar-top-icons">
            <button class="menu-toggle-desktop" id="collapse-toggle" title="Collapse sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="header-container">
                <div class="logo">
                    <a href="index.php">
                        <img src="../Images/arkiconnect.png" alt="Arki Connect Logo">
                    </a>
                </div>
            </div>
        </div>

        <div class="nav-section">
            
            <a href="OrgRep_db.php" class="nav-link active" title="Org Dashboard">
                <i class="fas fa-chart-line" aria-hidden="true"></i>
                <span class="link-text">Dashboard</span>
            </a>
            
            <a href="create_event.php" class="nav-link" title="Create New Event">
                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                <span class="link-text">Create Event</span>
            </a>

            <a href="my_events.php" class="nav-link" title="My Events List & Calendar">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <span class="link-text">My Events</span>
            </a>

            <a href="registration_reports.php" class="nav-link" title="Registration Reports & Export">
                <i class="fas fa-file-export" aria-hidden="true"></i>
                <span class="link-text">Registration Reports</span>
            </a>
            
            <hr class="separator"> 
            
            <a href="my_organization.php" class="nav-link" title="About My Organization">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <span class="link-text">My Organization</span>
            </a>
            
            <a href="helpcenter_org.php" class="nav-link" title="Help Center (FAQ)">
                <i class="fas fa-life-ring" aria-hidden="true"></i>
                <span class="link-text">Help Center</span>
            </a>
            
            <hr class="separator"> 
            
            <a href="settings_org.php" class="nav-link" title="Settings">
                <i class="fas fa-cog" aria-hidden="true"></i>
                <span class="link-text">Settings</span>
            </a>
            
            <a href="login.php?logout=true" class="nav-link logout-link" title="Sign out">
                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                <span class="link-text">Logout</span>
            </a>
        </div>
        
        <div class="user-profile">
            <img src="https://placehold.co/40x40/A43825/white?text=<?= substr($user_name, 0, 1) ?>" alt="Organization Avatar" loading="lazy"> 
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($user_name) ?></div>
                <div class="role"><?= htmlspecialchars($user_org) ?></div>
            </div>
        </div>

    </aside>

    <main class="content-area" id="content-area">
        <header class="content-header">
            <button id="menu-toggle-left" class="menu-toggle" title="Open Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <h2>Event Management</h2>
            
            <div style="margin-left: auto; display: flex; gap: 12px;">
                <button class="card-icon-button">
                    <i class="fas fa-bell" style="color: #a43825;"></i>
                </button>
            </div>
        </header>

        <section class="main-content-layout">
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= $success_message ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $error_message ?></span>
                </div>
            <?php endif; ?>

            <!-- Modern Table Layout -->
            <div class="events-table-container">
                <div class="table-header">
                    <div class="table-controls">
                        <button class="btn-filter" id="filterBtn">
                            <i class="fas fa-filter"></i>
                            Filter
                        </button>
                        <button class="btn-export">
                            <i class="fas fa-upload"></i>
                            Export
                        </button>
                    </div>
                </div>

                <?php if (count($recent_events) > 0): ?>
                <table class="events-table">
                    <thead>
                        <tr>
                            <th class="sortable">Event Name <i class="fas fa-sort"></i></th>
                            <th class="sortable">Event ID <i class="fas fa-sort"></i></th>
                            <th class="sortable">Date <i class="fas fa-sort"></i></th>
                            <th>Time</th>
                            <th class="sortable">Status <i class="fas fa-sort"></i></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_events as $event): ?>
                        <tr data-status="<?= strtolower($event['status']) ?>">
                            <td data-label="Event Name">
                                <div class="event-name-cell">
                                    <?php if (!empty($event['image_path']) && file_exists($event['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($event['image_path']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="event-image-small">
                                    <?php else: ?>
                                        <img src="https://placehold.co/40x40/a43825/white?text=<?= substr($event['title'], 0, 1) ?>" alt="Event" class="event-image-small">
                                    <?php endif; ?>
                                    <span class="event-name-text"><?= htmlspecialchars($event['title']) ?></span>
                                </div>
                            </td>
                            <td data-label="Event ID">
                                <span class="event-id-text">#EV<?= str_pad($event['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            </td>
                            <td data-label="Date">
                                <?= date('M d, Y', strtotime($event['event_date'])) ?>
                            </td>
                            <td data-label="Time">
                                <?= $event['event_time'] !== 'TBD' ? htmlspecialchars($event['event_time']) : 'TBD' ?>
                            </td>
                            <td data-label="Status">
                                <span class="status-badge-table <?= strtolower($event['status']) ?>">
                                    <?= ucfirst($event['status']) ?>
                                </span>
                            </td>
                            <td data-label="Action">
                                <div style="display: flex; gap: 8px;">
                                    <a href="create_event.php?editId=<?= $event['id'] ?>" class="action-dots" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_event.php?id=<?= $event['id'] ?>" class="action-dots" title="Delete" 
                                       onclick="return confirm('Are you sure you want to delete this event?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-events-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No events yet</h3>
                    <p>Start creating your first event to engage with students</p>
                </div>
                <?php endif; ?>
            </div>
            
        </section>
        
    </main>

    <div class="overlay" id="mobile-overlay"></div>

</div>

<script src="../JavaScript/OrgRep_db.js"></script>
<script>
// Filter Modal/Dropdown functionality
document.getElementById('filterBtn').addEventListener('click', function (e) {
    e.stopPropagation();
    
    // Check if filter menu already exists
    let filterMenu = document.getElementById('filterMenu');
    
    if (filterMenu) {
        // Toggle existing menu
        filterMenu.style.display = filterMenu.style.display === 'none' ? 'block' : 'none';
    } else {
        // Create filter menu
        filterMenu = document.createElement('div');
        filterMenu.id = 'filterMenu';
        filterMenu.style.cssText = `
            position: absolute;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 12px;
            z-index: 1000;
            min-width: 150px;
            margin-top: 8px;
        `;
        
        filterMenu.innerHTML = `
            <div style="margin-bottom: 8px; font-weight: 600; color: #374151; font-size: 0.875rem; padding: 4px 0;">Filter by Status:</div>
            <label style="display: flex; align-items: center; padding: 8px; cursor: pointer; border-radius: 4px; transition: background 0.2s;" 
                   onmouseover="this.style.background='#f9fafb'" 
                   onmouseout="this.style.background='white'">
                <input type="checkbox" value="all" checked style="margin-right: 8px; cursor: pointer;"> 
                <span style="font-size: 0.875rem; color: #374151;">All</span>
            </label>
            <label style="display: flex; align-items: center; padding: 8px; cursor: pointer; border-radius: 4px; transition: background 0.2s;" 
                   onmouseover="this.style.background='#f9fafb'" 
                   onmouseout="this.style.background='white'">
                <input type="checkbox" value="accepted" style="margin-right: 8px; cursor: pointer;"> 
                <span style="font-size: 0.875rem; color: #374151;">Accepted</span>
            </label>
            <label style="display: flex; align-items: center; padding: 8px; cursor: pointer; border-radius: 4px; transition: background 0.2s;" 
                   onmouseover="this.style.background='#f9fafb'" 
                   onmouseout="this.style.background='white'">
                <input type="checkbox" value="ongoing" style="margin-right: 8px; cursor: pointer;"> 
                <span style="font-size: 0.875rem; color: #374151;">Ongoing</span>
            </label>
            <label style="display: flex; align-items: center; padding: 8px; cursor: pointer; border-radius: 4px; transition: background 0.2s;" 
                   onmouseover="this.style.background='#f9fafb'" 
                   onmouseout="this.style.background='white'">
                <input type="checkbox" value="declined" style="margin-right: 8px; cursor: pointer;"> 
                <span style="font-size: 0.875rem; color: #374151;">Declined</span>
            </label>
        `;
        
        // Position the menu relative to the filter button
        const btnRect = this.getBoundingClientRect();
        filterMenu.style.position = 'fixed';
        filterMenu.style.top = (btnRect.bottom + 5) + 'px';
        filterMenu.style.left = btnRect.left + 'px';
        
        document.body.appendChild(filterMenu);
        
        // Add filter logic
        const checkboxes = filterMenu.querySelectorAll('input[type="checkbox"]');
        const allCheckbox = filterMenu.querySelector('input[value="all"]');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.value === 'all') {
                    // If "All" is checked, uncheck others and show all
                    if (this.checked) {
                        checkboxes.forEach(cb => {
                            if (cb !== this) cb.checked = false;
                        });
                        showAllRows();
                    }
                } else {
                    // If any specific status is checked, uncheck "All"
                    if (this.checked) {
                        allCheckbox.checked = false;
                    }
                    // Apply filter
                    applyFilter();
                }
                
                // If no checkbox is checked, check "All" by default
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                if (!anyChecked) {
                    allCheckbox.checked = true;
                    showAllRows();
                }
            });
        });
    }
});

function applyFilter() {
    const rows = document.querySelectorAll('.events-table tbody tr');
    const checkboxes = document.querySelectorAll('#filterMenu input[type="checkbox"]:not([value="all"]):checked');
    const selectedStatuses = Array.from(checkboxes).map(cb => cb.value);
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (selectedStatuses.length === 0 || selectedStatuses.includes(status)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function showAllRows() {
    const rows = document.querySelectorAll('.events-table tbody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
}

// Close filter menu when clicking outside
document.addEventListener('click', function(e) {
    const filterMenu = document.getElementById('filterMenu');
    const filterBtn = document.getElementById('filterBtn');
    
    if (filterMenu && !filterMenu.contains(e.target) && e.target !== filterBtn) {
        filterMenu.style.display = 'none';
    }
});
</script>

</body>
</html>