<?php
session_start();
$pageTitle = 'Manage Users';

// Mockup data - no database connection needed
$userName = 'Admin User'; 
$userAvatarText = 'A';

// Mockup users data
$all_users = [
    [
        'id' => 1,
        'fullname' => 'Juan Dela Cruz',
        'email' => 'juan.delacruz@university.edu',
        'role' => 'student',
        'student_id' => '2021-00123',
        'department' => 'Computer Science',
        'year_level' => '3rd Year',
        'status' => 'active',
        'joined_date' => '2024-01-15',
        'events_registered' => 12
    ],
    [
        'id' => 2,
        'fullname' => 'Maria Santos',
        'email' => 'maria.santos@university.edu',
        'role' => 'org_rep',
        'organization' => 'Computer Science Society',
        'position' => 'President',
        'status' => 'active',
        'joined_date' => '2024-02-20',
        'events_created' => 8
    ],
    [
        'id' => 3,
        'fullname' => 'Pedro Garcia',
        'email' => 'pedro.garcia@university.edu',
        'role' => 'student',
        'student_id' => '2022-00456',
        'department' => 'Business Administration',
        'year_level' => '2nd Year',
        'status' => 'active',
        'joined_date' => '2024-03-10',
        'events_registered' => 5
    ],
    [
        'id' => 4,
        'fullname' => 'Ana Reyes',
        'email' => 'ana.reyes@university.edu',
        'role' => 'org_rep',
        'organization' => 'Green Earth Organization',
        'position' => 'Vice President',
        'status' => 'active',
        'joined_date' => '2024-01-25',
        'events_created' => 6
    ],
    [
        'id' => 5,
        'fullname' => 'Carlos Mendoza',
        'email' => 'carlos.mendoza@university.edu',
        'role' => 'student',
        'student_id' => '2021-00789',
        'department' => 'Engineering',
        'year_level' => '4th Year',
        'status' => 'inactive',
        'joined_date' => '2024-02-05',
        'events_registered' => 3
    ],
    [
        'id' => 6,
        'fullname' => 'Sofia Torres',
        'email' => 'sofia.torres@university.edu',
        'role' => 'org_rep',
        'organization' => 'International Students Association',
        'position' => 'Secretary',
        'status' => 'active',
        'joined_date' => '2024-03-15',
        'events_created' => 10
    ],
    [
        'id' => 7,
        'fullname' => 'Miguel Ramos',
        'email' => 'miguel.ramos@university.edu',
        'role' => 'student',
        'student_id' => '2023-00234',
        'department' => 'Psychology',
        'year_level' => '1st Year',
        'status' => 'active',
        'joined_date' => '2024-04-01',
        'events_registered' => 8
    ],
    [
        'id' => 8,
        'fullname' => 'Isabella Cruz',
        'email' => 'isabella.cruz@university.edu',
        'role' => 'org_rep',
        'organization' => 'Business Administration Club',
        'position' => 'Treasurer',
        'status' => 'active',
        'joined_date' => '2024-02-28',
        'events_created' => 5
    ],
    [
        'id' => 9,
        'fullname' => 'Gabriel Lopez',
        'email' => 'gabriel.lopez@university.edu',
        'role' => 'student',
        'student_id' => '2022-00567',
        'department' => 'Information Technology',
        'year_level' => '2nd Year',
        'status' => 'active',
        'joined_date' => '2024-03-20',
        'events_registered' => 15
    ],
    [
        'id' => 10,
        'fullname' => 'Luisa Fernandez',
        'email' => 'luisa.fernandez@university.edu',
        'role' => 'student',
        'student_id' => '2021-00890',
        'department' => 'Nursing',
        'year_level' => '3rd Year',
        'status' => 'inactive',
        'joined_date' => '2024-01-10',
        'events_registered' => 2
    ],
    [
        'id' => 11,
        'fullname' => 'Ricardo Domingo',
        'email' => 'ricardo.domingo@university.edu',
        'role' => 'org_rep',
        'organization' => 'Programming Club',
        'position' => 'President',
        'status' => 'active',
        'joined_date' => '2024-04-05',
        'events_created' => 7
    ],
    [
        'id' => 12,
        'fullname' => 'Carmela Bautista',
        'email' => 'carmela.bautista@university.edu',
        'role' => 'student',
        'student_id' => '2023-00345',
        'department' => 'Education',
        'year_level' => '1st Year',
        'status' => 'active',
        'joined_date' => '2024-03-25',
        'events_registered' => 6
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, #2d2d2d 0%, #1a1a1a 100%);
            color: #f1f1f1;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header img {
            border-radius: 50%;
            width: 70px;
            height: 70px;
            object-fit: cover;
            border: 3px solid #A43825;
            margin-bottom: 15px;
        }

        .sidebar-header .username {
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .sidebar-header .affiliation {
            font-size: 0.9rem;
            color: #c1c1c1;
        }

        .nav-section {
            flex-grow: 1;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            margin: 8px 0;
            border-radius: 10px;
            color: #f1f1f1;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .nav-link i {
            margin-right: 14px;
            font-size: 1.1rem;
            width: 20px;
        }

        .nav-link:hover, .nav-link.active {
            background-color: #A43825;
            transform: translateX(5px);
        }

        .separator {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 20px 0;
        }

        /* Main Content */
        .content-area {
            flex-grow: 1;
            padding: 40px 50px;
            overflow-y: auto;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }

        .content-header h2 {
            color: #1f2937;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .card-icon-button {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            border: none;
            background: white;
            color: #A43825;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card-icon-button:hover {
            background-color: #fff5f5;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(164,56,37,0.2);
        }

       

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }

        .filter-select {
            padding: 10px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #374151;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #A43825;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 42px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #A43825;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        /* Users Table */
        .users-container {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .users-container h3 {
            color: #1f2937;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .users-container > p {
            color: #6b7280;
            font-size: 1rem;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        thead th {
            text-align: left;
            padding: 16px;
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s ease;
        }

        tbody tr:hover {
            background-color: #f9fafb;
        }

        tbody td {
            padding: 16px;
            color: #374151;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #A43825, #8b2f1f);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #1f2937;
        }

        .user-email {
            font-size: 0.813rem;
            color: #6b7280;
        }

        .role-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .role-badge.student {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .role-badge.org_rep {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-badge.active {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-badge.inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            margin-right: 5px;
        }
        .action-btn {
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    margin-right: 5px;
}

.btn-view {
    background-color: #3b82f6;
    color: white;
}

.btn-view:hover {
    background-color: #2563eb;
}

        .btn-view {
            background-color: #3b82f6;
            color: white;
        }

        .btn-view:hover {
            background-color: #2563eb;
        }

        .btn-edit {
            background-color: #10b981;
            color: white;
        }

        .btn-edit:hover {
            background-color: #059669;
        }

        .btn-deactivate {
            background-color: #ef4444;
            color: white;
        }

        .btn-deactivate:hover {
            background-color: #dc2626;
        }

        .btn-activate {
            background-color: #10b981;
            color: white;
        }

        .btn-activate:hover {
            background-color: #059669;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #A43825 0%, #8b2f1f 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.5rem;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .close:hover {
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-detail-row {
            margin-bottom: 20px;
        }

        .modal-detail-row label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .modal-detail-row p {
            color: #4b5563;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .modal-actions {
            padding: 20px 30px;
            border-top: 2px solid #f3f4f6;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 15px;
            }

            .sidebar-header {
                display: none;
            }

            .nav-section {
                display: flex;
                overflow-x: auto;
            }

            .nav-link {
                white-space: nowrap;
            }

            .content-area {
                padding: 20px;
            }

            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }

            .users-container {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="https://placehold.co/70x70/A43825/ffffff?text=<?php echo htmlspecialchars($userAvatarText); ?>" alt="Admin Avatar">
            <div class="username"><?php echo htmlspecialchars($userName); ?></div>
            <div class="affiliation">System Administrator</div>
        </div>

        <div class="nav-section">
            <a href="Admin_db.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>
            <a href="manage_events.php" class="nav-link">
                <i class="fas fa-calendar-alt"></i>
                Manage Events
            </a>
            <a href="manage_users.php" class="nav-link active">
                <i class="fas fa-users"></i>
                Manage Users
            </a>
            <a href="reports.php" class="nav-link">
                <i class="fas fa-file-alt"></i>
                Reports
            </a>
            
            <hr class="separator">
            
            <a href="settings.php" class="nav-link">
                <i class="fas fa-cog"></i>
                Settings
            </a>
            <a href="login.php?logout=true" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </aside>

    <main class="content-area">
        <div class="content-header">
            <h2>Manage Users</h2>
            <div class="header-actions">
                <button class="card-icon-button">
                    <i class="fas fa-bell"></i>
                </button>
                <button class="card-icon-button">
                    <i class="fas fa-user-plus"></i>
                </button>
            </div>
        </div>

      

        <!-- Filter Section -->
        <section class="filter-section">
            <div class="filter-group">
                <label>Role:</label>
                <select class="filter-select" id="roleFilter">
                    <option value="all">All Roles</option>
                    <option value="student">Students</option>
                    <option value="org_rep">Org Representatives</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Status:</label>
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>



            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search">
            </div>
        </section>

        <!-- Users Table -->
        <section class="users-container">
            <h3>All Users</h3>
            <p>Manage students and organization representatives</p>

            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach ($all_users as $user): ?>
                    <tr data-role="<?= $user['role'] ?>" data-status="<?= $user['status'] ?>">
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                                </div>
                                <div class="user-details">
                                    <span class="user-name"><?= htmlspecialchars($user['fullname']) ?></span>
                                    <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge <?= $user['role'] ?>">
                                <?= $user['role'] === 'student' ? 'Student' : 'Org Rep' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['role'] === 'student'): ?>
                                <div style="font-size: 0.85rem;">
                                    <div><?= htmlspecialchars($user['student_id']) ?></div>
                                    <div style="color: #6b7280;"><?= htmlspecialchars($user['department']) ?></div>
                                    <div style="color: #6b7280;"><?= htmlspecialchars($user['year_level']) ?></div>
                                </div>
                            <?php else: ?>
                                <div style="font-size: 0.85rem;">
                                    <div><?= htmlspecialchars($user['organization']) ?></div>
                                    <div style="color: #6b7280;"><?= htmlspecialchars($user['position']) ?></div>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $user['status'] ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($user['joined_date'])) ?></td>
                        <td>
                            <button class="action-btn btn-view" onclick="viewUserDetails(<?= $user['id'] ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($user['status'] === 'active'): ?>
                                <button class="action-btn btn-deactivate" onclick="deactivateUser(<?= $user['id'] ?>)">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php else: ?>
                                <button class="action-btn btn-activate" onclick="activateUser(<?= $user['id'] ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- User Details Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>User Details</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- User details will be loaded here -->
            </div>
            <div class="modal-actions">
                <button class="action-btn btn-view" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Filter users by role
        document.getElementById('roleFilter').addEventListener('change', function() {
            filterUsers();
        });

        // Filter users by status
        document.getElementById('statusFilter').addEventListener('change', function() {
            filterUsers();
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            filterUsers();
        });

        function filterUsers() {
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#usersTableBody tr');

            rows.forEach(row => {
                const role = row.dataset.role;
                const status = row.dataset.status;
                const name = row.querySelector('.user-name').textContent.toLowerCase();
                const email = row.querySelector('.user-email').textContent.toLowerCase();

                const matchesRole = roleFilter === 'all' || role === roleFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;
                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);

                if (matchesRole && matchesStatus && matchesSearch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // View user details
        function viewUserDetails(userId) {
            document.getElementById('userModal').style.display = 'block';

            // Mockup user details
            const userDetails = {
                1: {
                    name: 'Juan Dela Cruz',
                    email: 'juan.delacruz@university.edu',
                    role: 'Student',
                    student_id: '2021-00123',
                    department: 'Computer Science',
                    year_level: '3rd Year',
                    status: 'Active',
                    joined: 'January 15, 2024',
                    events_registered: 12
                },
                2: {
                    name: 'Maria Santos',
                    email: 'maria.santos@university.edu',
                    role: 'Organization Representative',
                    organization: 'Computer Science Society',
                    position: 'President',
                    status: 'Active',
                    joined: 'February 20, 2024',
                    events_created: 8
                }
            };

            const user = userDetails[userId] || userDetails[1];

            let detailsHTML = `
                <div class="modal-detail-row">
                    <label>Full Name:</label>
                    <p>${user.name}</p>
                </div>
                <div class="modal-detail-row">
                    <label>Email Address:</label>
                    <p>${user.email}</p>
                </div>
                <div class="modal-detail-row">
                    <label>Role:</label>
                    <p>${user.role}</p>
                </div>
            `;

            if (user.student_id) {
                detailsHTML += `
                    <div class="modal-detail-row">
                        <label>Student ID:</label>
                        <p>${user.student_id}</p>
                    </div>
                    <div class="modal-detail-row">
                        <label>Department:</label>
                        <p>${user.department}</p>
                    </div>
                    <div class="modal-detail-row">
                        <label>Year Level:</label>
                        <p>${user.year_level}</p>
                    </div>
                    <div class="modal-detail-row">
                        <label>Events Registered:</label>
                        <p>${user.events_registered}</p>
                    </div>
                `;
            } else {
                detailsHTML += `
                    <div class="modal-detail-row">
                        <label>Organization:</label>
                        <p>${user.organization}</p>
                    </div>
                    <div class="modal-detail-row">
                        <label>Position:</label>
                        <p>${user.position}</p>
                    </div>
                    <div class="modal-detail-row">
                        <label>Events Created:</label>
                        <p>${user.events_created}</p>
                    </div>
                `;
            }

            detailsHTML += `
                <div class="modal-detail-row">
                    <label>Status:</label>
                    <p>${user.status}</p>
                </div>
                <div class="modal-detail-row">
                    <label>Joined Date:</label>
                    <p>${user.joined}</p>
                </div>
            `;

            document.getElementById('modalBody').innerHTML = detailsHTML;
        }

        // Close modal
        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        // Deactivate user
        function deactivateUser(userId) {
            if (confirm('Are you sure you want to deactivate this user?')) {
                alert('User deactivated! (This is a mockup - no database changes made)');
                // In real implementation: window.location.href = 'deactivate_user.php?id=' + userId;
            }
        }

        // Activate user
        function activateUser(userId) {
            if (confirm('Are you sure you want to activate this user?')) {
                alert('User activated! (This is a mockup - no database changes made)');
                // In real implementation: window.location.href = 'activate_user.php?id=' + userId;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

</body>
</html>