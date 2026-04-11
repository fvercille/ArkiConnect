<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php'; 

// Temporarily comment out authentication for testing
// Uncomment this when you're ready to add authentication back
/*
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'org_rep') {
    header("Location: login.php?error=unauthorized_script_access");
    exit;
}
*/

// Get event ID from URL
$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    header("Location: my_events.php");
    exit;
}

// User info for sidebar
$userName = $_SESSION['fullname'] ?? 'User';
$userRole = 'ASAPHIL - TIP Manila';

// TODO: Replace with actual database query
$event = [
    'id' => $event_id,
    'title' => 'Design Expo 2025',
    'date' => '2025-10-22',
    'start_time' => '09:00',
    'end_time' => '17:00',
    'location' => 'TIP Manila',
    'description' => 'The Design Expo 2025 is a premier gathering for architects and designers, showcasing innovative projects, new materials, and creative concepts for the future. The event includes keynote talks from industry leaders, networking opportunities, and exhibits of groundbreaking ideas. Join us for a day full of inspiration, learning, and connections!',
    'banner_image' => 'design-expo-banner.jpg',
    'organizer_name' => 'ASAPHIL',
    'organizer_image' => 'organizer1.jpg',
    'registered_count' => 42
];

// Format date and time
$formatted_date = date('F j, Y', strtotime($event['date']));
$formatted_time = date('g:i A', strtotime($event['start_time'])) . ' – ' . date('g:i A', strtotime($event['end_time']));

// Helper
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($event['title']); ?> - Event Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/OrgRep_db.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #23272f;
        }
        
        .main-content-layout {
            padding: 20px;
        }
        
        .back-button-container {
    width: 100%;
    background: #f8f9fa;
    padding: 32px 48px 0;
    border-bottom: 2px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

@media (max-width: 768px) {
    .back-button-container {
        padding: 24px 20px 0;
    }
}

        .back-button {
            margin-bottom: 20px;
        }
        
        .back-button a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #a43825;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .back-button a:hover {
            color: #8a2e1f;
            transform: translateX(-4px);
        }
        
        .event-wide-container {
            width: 100%;
            margin: 0;
            padding: 0;
            background: #ffffff;
            box-shadow: 0 4px 24px rgba(44, 62, 80, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            gap: 0;
            border-radius: 12px;
        }
        
        .event-image-section {
            flex: 1 1 420px;
            min-width: 350px;
            background: #eaf0fb;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .event-banner {
            width: 90%;
            max-width: 540px;
            max-height: 480px;
            object-fit: cover;
            box-shadow: 0 8px 32px rgba(44,62,80,0.10);
            margin: 48px 0;
            background: #ddd;
        }
        
        .event-details-section {
            flex: 2 1 600px;
            padding: 52px 56px 44px 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .event-title {
            font-size: 2.7rem;
            font-weight: 700;
            color: #a43825;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 16px;
        }
        
        .event-meta {
            font-size: 1.18rem;
            color: #6c757d;
            margin-bottom: 18px;
        }
        
        .event-meta > div {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        
        .event-meta i {
            color: #ffc107;
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .section-label {
            font-size: 1.22rem;
            font-weight: 700;
            color: #a43825;
            margin: 24px 0 12px 0;
            letter-spacing: 0.01em;
        }
        
        .event-description {
            font-size: 1.15rem;
            line-height: 1.7;
            color: #23272f;
            margin-bottom: 18px;
        }
        
        .stats-container {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            padding: 16px;
            background: #f9fafb;
            border-radius: 12px;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #a43825;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .organizer-section {
            margin-bottom: 13px;
        }
        
        .organizer-list {
            display: flex;
            gap: 14px;
            align-items: center;
            margin-bottom: 7px;
        }
        
        .organizer-img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(44,62,80,0.07);
            border: 2px solid #ffc107;
            background: #f9f9fc;
        }
        
        .organizer-name {
            font-size: 1.09rem;
            color: #6c757d;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #a43825;
            color: white;
        }
        
        .btn-primary:hover {
            background: #8a2e1f;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(164, 56, 37, 0.3);
        }
        
        .btn-secondary {
            background: #e5e7eb;
            color: #23272f;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
        }
        
        /* EDIT FORM STYLES */
        .edit-form-container {
            width: 100%;
            margin: 40px 0 80px;
            padding: 0;
            background: #ffffff;
            box-shadow: 0 10px 40px rgba(44, 62, 80, 0.12);
            font-family: 'Segoe UI', Arial, sans-serif;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 32px;
            border-radius: 12px;
        }

        .edit-form-header {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),
                        linear-gradient(135deg, #a43825 0%, #fdf3e7 50%, #a43825 100%);
            padding: 36px 48px;
        }

        .edit-form-header h2 {
            font-size: 2rem;
            color: #ffffff;
            margin: 0;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .edit-form-header h2 i {
            color: #ffc107;
            font-size: 1.8rem;
        }

        .edit-form-header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 8px 0 0 0;
            font-size: 1.05rem;
        }

        .edit-form-content {
            padding: 48px;
            background: #f8f9fa;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .edit-form-container label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #23272f;
            font-size: 1.05rem;
            letter-spacing: 0.01em;
        }

        .edit-form-container label i {
            color: #a43825;
            font-size: 1rem;
            width: 18px;
        }

        .label-required::after {
            content: '*';
            color: #dc3545;
            margin-left: 4px;
            font-weight: 700;
        }

        .edit-form-container input[type="text"],
        .edit-form-container input[type="date"],
        .edit-form-container input[type="time"],
        .edit-form-container input[type="file"],
        .edit-form-container textarea,
        .edit-form-container select {
            width: 100%;
            padding: 14px 7px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            font-size: 1.05rem;
            font-family: inherit;
            background: #ffffff;
            transition: all 0.3s ease;
            color: #23272f;
        }

        .edit-form-container input:focus,
        .edit-form-container textarea:focus,
        .edit-form-container select:focus {
            border-color: #a43825;
            outline: none;
            box-shadow: 0 0 0 4px rgba(164, 56, 37, 0.1);
            background: #fff;
        }

        .edit-form-container input:hover,
        .edit-form-container textarea:hover,
        .edit-form-container select:hover {
            border-color: #a43825;
        }

        .edit-form-container textarea {
            resize: vertical;
            min-height: 140px;
            line-height: 1.6;
        }

        .file-input-wrapper input[type="file"]::file-selector-button {
            padding: 10px 20px;
            border: none;
            background: #a43825;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            margin-right: 12px;
        }

        .file-input-wrapper input[type="file"]::file-selector-button:hover {
            background: #8a2e1f;
        }

        .helper-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .helper-text i {
            color: #ffc107;
            font-size: 0.85rem;
        }

        .form-section {
            margin-top: 32px;
            padding-top: 32px;
            border-top: 2px solid #e9ecef;
        }

        .form-section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #a43825;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: #ffc107;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 40px;
            padding: 32px 48px;
            background: #ffffff;
            border-top: 2px solid #e9ecef;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .edit-form-container .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            white-space: nowrap;
        }

        .edit-form-container .btn i {
            font-size: 1.1rem;
        }

        .edit-form-container .btn-primary {
            background: #a43825;
            color: white;
            box-shadow: 0 4px 12px rgba(164, 56, 37, 0.2);
        }

        .edit-form-container .btn-primary:hover {
            background: #8a2e1f;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(164, 56, 37, 0.35);
        }

        .edit-form-container .btn-cancel {
            background: #e9ecef;
            color: #23272f;
        }

        .edit-form-container .btn-cancel:hover {
            background: #d1d5db;
        }

        @media (max-width: 1000px) {
            .event-wide-container {
                flex-direction: column;
            }
            .event-image-section {
                min-height: 260px;
            }
            .event-details-section {
                padding: 30px 16px;
            }
            .event-title {
                font-size: 2rem;
            }
            .event-banner {
                margin: 18px 0;
            }
            .action-buttons {
                flex-direction: column;
            }
            .stats-container {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .edit-form-content {
                padding: 24px 20px;
            }
            
            .edit-form-header {
                padding: 24px 20px;
            }
            
            .edit-form-header h2 {
                font-size: 1.5rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-actions {
                padding: 24px 20px;
                flex-direction: column;
            }
            
            .edit-form-container .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">
    
    <!-- LEFT SIDEBAR -->
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
            
            <a href="OrgRep_db.php" class="nav-link" title="Org Dashboard">
                <i class="fas fa-chart-line" aria-hidden="true"></i>
                <span class="link-text">Dashboard</span>
            </a>
            
            <a href="create_event.php" class="nav-link" title="Create New Event">
                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                <span class="link-text">Create Event</span>
            </a>

            <a href="my_events.php" class="nav-link active" title="My Events List & Calendar">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <span class="link-text">My Events</span>
            </a>

            <a href="registration_reports.php" class="nav-link" title="Registration Reports & Export">
                <i class="fas fa-file-export" aria-hidden="true"></i>
                <span class="link-text">Registration Reports</span>
            </a>
            
            <hr class="separator"> 
            
            <a href="about.php" class="nav-link" title="About My Organization">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <span class="link-text">About</span>
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
            <img src="https://placehold.co/40x40/A43825/white?text=<?php echo strtoupper(substr($userName, 0, 1)); ?>" alt="Organization Avatar" loading="lazy"> 
            <div class="user-info">
                <div class="name"><?php echo h($userName); ?></div>
                <div class="role"><?php echo h($userRole); ?></div>
            </div>
        </div>

    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="content-area" id="content-area">
        <header class="content-header">
            <button id="menu-toggle-left" class="menu-toggle" title="Open Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <h2>Event Details</h2>
        </header>

        <section class="main-content-layout">
            
           <div class="back-button-container">
    <div class="back-button">
        <a href="my_events.php">
            <i class="fas fa-arrow-left"></i>
            Back to My Events
        </a>
    </div>
</div>


            <div class="event-wide-container">
                <!-- Image Section -->
                <div class="event-image-section">
                    <img class="event-banner" src="<?php echo h($event['banner_image']); ?>" alt="<?php echo h($event['title']); ?> Banner">
                </div>
                
                <!-- Details Section -->
                <div class="event-details-section">
                    <!-- Event Title / Name -->
                    <div class="event-title"><?php echo h($event['title']); ?></div>
                    
                    <!-- Date / Time / Location -->
                    <div class="event-meta">
                        <div><i class="fas fa-calendar-day"></i> <?php echo $formatted_date; ?></div>
                        <div><i class="fas fa-clock"></i> <?php echo $formatted_time; ?></div>
                        <div><i class="fas fa-map-marker-alt"></i> <?php echo h($event['location']); ?></div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="stats-container">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $event['registered_count']; ?></div> 
                            <div class="stat-label">Registered</div>
                        </div>
                    </div>
                    
                    <!-- Description / Details -->
                    <div class="section-label">Description</div>
                    <div class="event-description">
                        <?php echo nl2br(h($event['description'])); ?>
                    </div>
                    
                    <!-- Organizer / Host -->
                    <div class="section-label organizer-section">Host</div>
                    <div class="organizer-list">
                        <img class="organizer-img" src="<?php echo h($event['organizer_image']); ?>" alt="Organizer">
                        <div class="organizer-name"><?php echo h($event['organizer_name']); ?></div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="btn btn-secondary" onclick="toggleEditForm()">
                           Edit Event
                        </button>

                        <a href="view_registrants.php?event_id=<?php echo urlencode($event['id']); ?>" class="btn btn-primary">
                           View Registrants
                        </a>
                    </div>
                </div>
            </div>

            <!-- Professional Edit Form -->
            <div id="edit-form-container" class="edit-form-container" style="display: none;">
                
                <!-- Form Header -->
                <div class="edit-form-header">
                    <h2>
                        Edit Event Details
                    </h2>
                    <p>Update your event information and settings</p>
                </div>
                
                <!-- Form Content -->
                <div class="edit-form-content">
                    
                    <form id="edit-event-form" method="POST" enctype="multipart/form-data">
                        
                        <!-- Basic Information Section -->
                        <div class="form-grid">
                            
                            <!-- Event Title -->
                            <div class="form-group full-width">
                                <label for="title" class="label-required">
                                    Event Title
                                </label>
                                <input 
                                    type="text" 
                                    id="title" 
                                    name="title"
                                    value="<?php echo h($event['title']); ?>"
                                    placeholder="Enter event title"
                                    required
                                >
                                <span class="helper-text">
                                    <i class="fas fa-info-circle"></i>
                                    Make it clear and descriptive
                                </span>
                            </div>
                            
                            <!-- Event Date -->
                            <div class="form-group">
                                <label for="date" class="label-required">
                                    Event Date
                                </label>
                                <input 
                                    type="date" 
                                    id="date" 
                                    name="date"
                                    value="<?php echo h($event['date']); ?>"
                                    required
                                >
                            </div>
                            
                            <!-- Location -->
                            <div class="form-group">
                                <label for="location" class="label-required">
                                    Location
                                </label>
                                <input 
                                    type="text" 
                                    id="location" 
                                    name="location"
                                    value="<?php echo h($event['location']); ?>"
                                    placeholder="Enter venue or location"
                                    required
                                >
                            </div>
                            
                            <!-- Start Time -->
                            <div class="form-group">
                                <label for="start_time" class="label-required">
                                    Start Time
                                </label>
                                <input 
                                    type="time" 
                                    id="start_time" 
                                    name="start_time"
                                    value="<?php echo h($event['start_time']); ?>"
                                    required
                                >
                            </div>
                            
                            <!-- End Time -->
                            <div class="form-group">
                                <label for="end_time" class="label-required">
                                    End Time
                                </label>
                                <input 
                                    type="time" 
                                    id="end_time" 
                                    name="end_time"
                                    value="<?php echo h($event['end_time']); ?>"
                                    required
                                >
                            </div>
                            
                        </div>
                        
                        <!-- Description Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                Event Description
                            </div>
                            
                            <div class="form-group">
                                <textarea 
                                    id="description" 
                                    name="description"
                                    placeholder="Provide detailed information about your event"
                                    required
                                ><?php echo h($event['description']); ?></textarea>
                                <span class="helper-text">
                                    <i class="fas fa-info-circle"></i>
                                    Include key details, schedule, and what attendees can expect
                                </span>
                            </div>
                        </div>

                        <!-- Media Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                Event Banner
                            </div>
                            
                            <div class="form-group">
                                <div class="file-input-wrapper">
                                    <input 
                                        type="file" 
                                        id="banner_image" 
                                        name="banner_image"
                                        accept="image/*"
                                    >
                                </div>
                                <span class="helper-text">
                                    <i class="fas fa-info-circle"></i>
                                    Recommended size: 1200x600px (JPG or PNG)
                                </span>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="toggleEditForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" form="edit-event-form">
                        Save Changes
                    </button>
                </div>
            </div>
        </section>
        
    </main>

    <div class="overlay" id="mobile-overlay"></div>

</div>

<script src="../JavaScript/OrgRep_db.js"></script>
<script>
    function toggleEditForm() {
        const form = document.getElementById('edit-form-container');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            setTimeout(() => {
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } else {
            form.style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // Form submission handler
    document.getElementById('edit-event-form').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');
        alert('Event updated successfully!');
        toggleEditForm();
    });

    // Image preview on file select
    document.getElementById('banner_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.image-preview img');
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Validation for time inputs
    document.getElementById('end_time').addEventListener('change', function() {
        const startTime = document.getElementById('start_time').value;
        const endTime = this.value;
        
        if (startTime && endTime && endTime <= startTime) {
            alert('End time must be after start time');
            this.value = '';
        }
    });

    // Date validation - prevent past dates
    document.getElementById('date').addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            alert('Event date cannot be in the past');
            this.value = '<?php echo h($event['date']); ?>';
        }
    });
</script>

</body>
</html>