<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connect.php';

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=not_logged_in");
    exit;
}

$userName = $_SESSION['fullname'] ?? 'Student';
$user_id = $_SESSION['user_id'];
$pageTitle = 'Upcoming Events';
$userAvatarText = strtoupper(substr($userName, 0, 1));

// Fetch events from database
$events = [];
try {
    $query = "
        SELECT 
            e.id,
            e.title,
            e.description,
            e.event_date,
            e.event_time,
            e.image_path,
            e.location,
            e.status,
            u.fullname as organizer,
            (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND status = 'confirmed') as registrants,
            (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND user_id = ? AND status = 'confirmed') as is_registered
        FROM events e
        LEFT JOIN users u ON e.created_by = u.id
        WHERE e.status IN ('approved', 'upcoming', 'ongoing')
        AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'date' => $row['event_date'],
            'time' => $row['event_time'],
            'description' => $row['description'],
            'image_path' => $row['image_path'],
            'location' => $row['location'],
            'organizer' => $row['organizer'],
            'registrants' => (int)$row['registrants'],
            'is_registered' => (int)$row['is_registered'] > 0
        ];
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $events = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/Student_db.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        /* ── Page-specific overrides ── */

        .upcoming-events-container {
            padding: 28px 28px 40px;
            max-width: 900px;
        }

        /* Page heading row */
        .page-heading-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            gap: 16px;
        }

        .page-heading-row h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 4px 0;
        }

        .page-heading-row p {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: 1px solid #a43825;
            border-radius: 8px;
            color: #a43825;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.15s;
            flex-shrink: 0;
        }

        .back-button:hover {
            background: #fff5f3;
        }

        /* Alert banner */
        .alert-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 16px;
            animation: fadeSlideIn 0.3s ease;
        }

        .alert-banner.success {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .alert-banner.error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Events list */
        .events-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Individual event card – inherits .dashboard-card feel */
        .event-list-card {
            display: flex;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            border-left: 4px solid #a43825;
            overflow: hidden;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .event-list-card:hover {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        /* Left image column */
        .event-thumb {
            width: 160px;
            min-width: 160px;
            background: #f3f4f6;
            overflow: hidden;
            flex-shrink: 0;
        }

        .event-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Right detail column */
        .event-detail {
            flex: 1;
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 0;
        }

        .event-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            line-height: 1.3;
        }

        /* Meta chips row */
        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .meta-chip {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
        }

        .meta-chip i {
            color: #a43825;
            font-size: 0.75rem;
            width: 14px;
            text-align: center;
        }

        .event-desc {
            font-size: 0.875rem;
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
            /* clamp to 2 lines */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Actions row */
        .event-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-top: 10px;
            border-top: 1px solid #f3f4f6;
            margin-top: auto;
        }

        .register-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            background: #a43825;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.15s, transform 0.15s, box-shadow 0.15s;
        }

        .register-btn:hover:not(:disabled) {
            background: #8a2d1e;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(164, 56, 37, 0.25);
        }

        .register-btn:disabled {
            background: #059669;
            cursor: default;
            transform: none;
            box-shadow: none;
        }

        .register-btn.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .registrant-count {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #a43825;
            margin-left: auto;
        }

        .registrant-count i {
            font-size: 0.85rem;
        }

        /* Empty state */
        .no-events {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .no-events i {
            font-size: 3rem;
            color: #e5e7eb;
            margin-bottom: 16px;
            display: block;
        }

        .no-events h3 {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .no-events p {
            font-size: 0.875rem;
        }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .upcoming-events-container {
                padding: 16px;
            }

            .event-list-card {
                flex-direction: column;
            }

            .event-thumb {
                width: 100%;
                min-width: unset;
                height: 180px;
            }

            .page-heading-row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">

    <!-- ── Sidebar (same as Student_db.php) ── -->
    <aside class="sidebar" id="left-sidebar">
        <div class="sidebar-top-icons">
            <button class="menu-toggle-desktop" id="collapse-toggle" title="Collapse sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-container">
                <div class="logo">
                    <a href="Student_db.php">
                        <img src="../Images/newlogo.png" alt="Arki Connect Logo">
                    </a>
                </div>
            </div>
        </div>

        <div class="nav-section">
            <a href="Student_db.php" class="nav-link" title="Go to Dashboard">
                <i data-lucide="layout-dashboard"></i>
                <span class="link-text">Dashboard</span>
            </a>

            <hr class="separator">

            <a href="organizations.php" class="nav-link sub-link-item" title="Organization Directory">
                <i data-lucide="users"></i>
                <span class="link-text">Organizations</span>
            </a>
            <a href="registered_events.php" class="nav-link sub-link-item" title="My Registered Events">
                <i data-lucide="bookmark"></i>
                <span class="link-text">Registered Events</span>
            </a>
            <a href="event_calendar.php" class="nav-link sub-link-item" title="Event Calendar">
                <i data-lucide="calendar-days"></i>
                <span class="link-text">Event Calendar</span>
            </a>

            <hr class="separator">

            <a href="helpcenter.php" class="nav-link" title="Help Center">
                <i data-lucide="life-buoy"></i>
                <span class="link-text">Help Center</span>
            </a>
            <a href="settings.php" class="nav-link" title="Settings">
                <i data-lucide="settings"></i>
                <span class="link-text">Settings</span>
            </a>
            <a href="login.php?logout=true" class="nav-link" title="Logout">
                <i data-lucide="log-out"></i>
                <span class="link-text">Logout</span>
            </a>
        </div>

        <div class="user-profile">
            <img src="https://placehold.co/40x40/A43825/white?text=<?php echo htmlspecialchars($userAvatarText); ?>"
                 alt="User Avatar" loading="lazy">
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                <div class="role">TIP Manila</div>
            </div>
        </div>
    </aside>

    <!-- ── Main content ── -->
    <main class="content-area" id="content-area">

        <header class="content-header">
            <button id="menu-toggle-left" class="menu-toggle" title="Open Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            <button id="menu-toggle-right" class="menu-toggle" style="margin-left: auto;" title="Open Notifications">
                <i class="fas fa-bell"></i>
            </button>
        </header>

        <section class="main-content-layout">
            <div class="upcoming-events-container">

                <!-- Page heading -->
                <div class="page-heading-row">
                    <div>
                        <h1>Upcoming Events</h1>
                        <p>Discover and register for events created by organizations</p>
                    </div>
                    <a href="Student_db.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <!-- Alert container -->
                <div id="alert-container"></div>

                <?php if (empty($events)): ?>
                    <div class="no-events">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No upcoming events</h3>
                        <p>Check back later for new events from organizations!</p>
                    </div>
                <?php else: ?>
                    <div class="events-list">
                        <?php foreach ($events as $event):
                            $eventDate = date('M d, Y', strtotime($event['date']));
                            $eventTime = (!empty($event['time']) && $event['time'] !== 'TBD')
                                ? date('g:i A', strtotime($event['time']))
                                : 'Time TBD';
                            $imageSrc = !empty($event['image_path']) && file_exists($event['image_path'])
                                ? htmlspecialchars($event['image_path'])
                                : 'https://placehold.co/400x200/a43825/white?text=' . urlencode(substr($event['title'], 0, 12));
                            $isRegistered   = $event['is_registered'];
                            $btnDisabled    = $isRegistered ? 'disabled' : '';
                            $btnIcon        = $isRegistered ? 'fa-check' : 'fa-check';
                            $btnLabel       = $isRegistered ? 'Registered' : 'Register Now';
                        ?>
                            <div class="event-list-card">
                                <!-- Thumbnail -->
                                <div class="event-thumb">
                                    <img src="<?php echo $imageSrc; ?>"
                                         alt="<?php echo htmlspecialchars($event['title']); ?>"
                                         onerror="this.src='https://placehold.co/400x200/a43825/white?text=Event';">
                                </div>

                                <!-- Details -->
                                <div class="event-detail">
                                    <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>

                                    <div class="event-meta">
                                        <span class="meta-chip">
                                            <i class="fas fa-building"></i>
                                            <?php echo htmlspecialchars($event['organizer'] ?? 'Unknown Organization'); ?>
                                        </span>
                                        <span class="meta-chip">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($event['location'] ?? 'Location TBD'); ?>
                                        </span>
                                        <span class="meta-chip">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo $eventDate; ?>
                                        </span>
                                        <span class="meta-chip">
                                            <i class="fas fa-clock"></i>
                                            <?php echo $eventTime; ?>
                                        </span>
                                    </div>

                                    <p class="event-desc">
                                        <?php echo htmlspecialchars($event['description']); ?>
                                    </p>

                                    <div class="event-actions">
                                        <button class="register-btn"
                                                onclick="registerForEvent(<?php echo $event['id']; ?>, this)"
                                                <?php echo $btnDisabled; ?>>
                                            <i class="fas <?php echo $btnIcon; ?>"></i>
                                            <?php echo $btnLabel; ?>
                                        </button>
                                        <div class="registrant-count">
                                            <i class="fas fa-users"></i>
                                            <span id="count-<?php echo $event['id']; ?>"><?php echo $event['registrants']; ?></span>
                                            registered
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </main>

    <div class="overlay" id="mobile-overlay"></div>
</div>

<script>
async function registerForEvent(eventId, buttonElement) {
    buttonElement.disabled = true;
    buttonElement.classList.add('loading');
    const originalHTML = buttonElement.innerHTML;
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';

    try {
        const response = await fetch('event_api.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: eventId })
        });

        const data = await response.json();

        if (data.success) {
            buttonElement.innerHTML = '<i class="fas fa-check"></i> Registered';
            buttonElement.style.backgroundColor = '#059669';

            const countEl = document.getElementById(`count-${eventId}`);
            if (countEl) {
                countEl.textContent = parseInt(countEl.textContent) + 1;
            }

            showAlert('Registration successful! Check your email for confirmation.', 'success');
        } else {
            buttonElement.disabled = false;
            buttonElement.classList.remove('loading');
            buttonElement.innerHTML = originalHTML;
            showAlert(data.error || 'Registration failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        buttonElement.disabled = false;
        buttonElement.classList.remove('loading');
        buttonElement.innerHTML = originalHTML;
        showAlert('Connection error. Please try again.', 'error');
    }
}

function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert-banner ${type}`;
    alert.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    container.appendChild(alert);

    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 4000);
}

// Sidebar toggle (same as Student_db.php)
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();

    const collapseToggle = document.getElementById('collapse-toggle');
    const leftSidebar    = document.getElementById('left-sidebar');
    const menuToggleLeft = document.getElementById('menu-toggle-left');
    const overlay        = document.getElementById('mobile-overlay');

    if (collapseToggle) {
        collapseToggle.addEventListener('click', () => {
            leftSidebar.classList.toggle('collapsed');
        });
    }

    if (menuToggleLeft && overlay) {
        menuToggleLeft.addEventListener('click', () => {
            leftSidebar.classList.add('active');
            overlay.classList.add('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            leftSidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
});
</script>

</body>
</html>