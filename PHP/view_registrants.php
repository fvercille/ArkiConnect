<?php
// view_registrants.php
// Mock UI only — no SQL. Shows a professional registrants list (name, year level, email).
// Added left sidebar from OrgRep_db.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/db_connect.php';

// Simple auth check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'org_rep') {
    header("Location: login.php?error=unauthorized_script_access");
    exit;
}

// simple user info from session for sidebar
$userName = $_SESSION['fullname'] ?? 'User';
$userRole = 'ASAPHIL - TIP Manila';

// ---------- MOCK REGISTRANTS DATA ----------
$registrants = [
    ['id'=>1, 'name'=>'George Lindelof',     'year'=>'3rd Year', 'email'=>'george.l@example.com',   'avatar'=>'https://placehold.co/48x48/6c5ce7/fff?text=G'],
    ['id'=>2, 'name'=>'Eric Dyer',          'year'=>'2nd Year', 'email'=>'eric.d@example.com',      'avatar'=>'https://placehold.co/48x48/00b894/fff?text=E'],
    ['id'=>3, 'name'=>'Haitam Alessami',    'year'=>'4th Year', 'email'=>'haitam.a@example.com',    'avatar'=>'https://placehold.co/48x48/0984e3/fff?text=H'],
    
];

// Helper
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/OrgRep_db.css">
    <style>
        /* Additional styles for registrants table */
        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(20,24,31,0.06);
            overflow: hidden;
            border: 1px solid #e6e9ef;
        }

        .card-header {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e6e9ef;
            background: linear-gradient(90deg, rgba(164,56,37,0.03), transparent);
        }

        .card-title {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .card-title h1 {
            margin: 0;
            font-size: 1.5rem;
            color: #a43825;
            letter-spacing: 0.02em;
            font-weight: 700;
        }

        .card-title p {
            margin: 0;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .actions {
            margin-left: auto;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #e6e9ef;
            padding: 8px 12px;
            border-radius: 10px;
            background: #fff;
            min-width: 320px;
        }

        .search input {
            border: 0;
            outline: 0;
            font-size: 0.95rem;
            width: 100%;
        }

        .btn {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #e6e9ef;
            background: #fff;
            cursor: pointer;
            font-weight: 600;
            color: #1f2937;
            display: inline-flex;
            gap: 8px;
            align-items: center;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #f9fafb;
        }

        .btn.primary {
            background: #a43825;
            color: #fff;
            border-color: transparent;
        }

        .btn.primary:hover {
            background: #8b2f1f;
        }

        .table-wrap {
            padding: 18px;
        }

        table.reg-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.reg-table thead th {
            text-align: left;
            font-size: 0.85rem;
            color: #6b7280;
            padding: 12px 8px;
            border-bottom: 1px solid #e6e9ef;
            font-weight: 700;
            vertical-align: middle;
        }

        table.reg-table tbody td {
            padding: 14px 8px;
            border-bottom: 1px dashed #eef1f6;
            vertical-align: middle;
            font-size: 0.96rem;
        }

        .col-photo {
            width: 72px;
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 4px 12px rgba(20,24,31,0.06);
        }

        .name-block {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .name-block .meta {
            display: flex;
            flex-direction: column;
        }

        .name-block .meta .name {
            font-weight: 700;
            color: #111827;
        }

        .name-block .meta .sub {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .year {
            font-weight: 600;
            color: #111827;
        }

        .email {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .empty {
            padding: 32px;
            text-align: center;
            color: #6b7280;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 18px;
            border-top: 1px solid #e6e9ef;
            background: #fafbfd;
        }

        .pagination {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .page-btn {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e6e9ef;
            background: #fff;
            cursor: pointer;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .main-content-layout {
            padding: 20px;
        }

        @media (max-width: 900px) {
            .search {
                min-width: 160px;
            }
            table.reg-table thead th:nth-child(4),
            table.reg-table tbody td:nth-child(4) {
                display: none;
            }
        }

        .back-button-wrapper {
    width: 100%;
    padding: 0 20px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e6e9ef;
}

.back-button {
    max-width: 1600px;
    margin: 0 auto;
    padding-top: 24px;
}

.back-button a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #a43825;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.back-button a:hover {
    color: #8a2e1f;
    transform: translateX(-4px);
}

    </style>
</head>
<body>

<div class="dashboard-container" id="dashboard-container">
    
    <!-- LEFT SIDEBAR (from OrgRep_db.php) -->
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

            <a href="my_events.php" class="nav-link" title="My Events List & Calendar">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <span class="link-text">My Events</span>
            </a>

            <a href="#" class="nav-link" title="Registration Reports & Export">
                <i class="fas fa-file-export" aria-hidden="true"></i>
                <span class="link-text">Registration Reports</span>
            </a>
            
            <hr class="separator"> 
            
            <a href="#" class="nav-link" title="About My Organization">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <span class="link-text">About</span>
            </a>
            
            <a href="#" class="nav-link" title="Help Center (FAQ)">
                <i class="fas fa-life-ring" aria-hidden="true"></i>
                <span class="link-text">Help Center</span>
            </a>
            
            <hr class="separator"> 
            
            <a href="#" class="nav-link" title="Settings">
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
            <h2>View Registrants</h2>
        </header>

        <section class="main-content-layout">
           <div class="back-button-wrapper">
    <div class="back-button">
        <a href="event_detailsedit.php?id=<?php echo urlencode($_GET['event_id'] ?? ''); ?>">
            <i class="fas fa-arrow-left"></i> Back to Event Details
        </a>
    </div>
</div>


            <div class="card" role="main" aria-labelledby="registrantsTitle">
                
                <div class="card-header">
                    <div class="card-title">
                        <h1 id="registrantsTitle">Registrants</h1>
                       
                    </div>

                    <div class="actions" role="toolbar" aria-label="Controls">
                        <div class="search" title="Search registrants">
                            <i class="fas fa-search" style="color:#6b7280"></i>
                            <input id="searchInput" type="search" placeholder="Search" aria-label="Search registrants">
                        </div>

                        <button class="btn" id="exportBtn"> Export (CSV)</button>
                        <button class="btn primary" id="refreshBtn"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                </div>

                <div class="table-wrap" id="tableWrap">
                    <table class="reg-table" role="table" aria-label="Registrants table">
                        <thead>
                            <tr>
                                <th class="col-photo">Photo</th>
                                <th>Full name</th>
                                <th>Year level</th>
                                <th>Email address</th>
                                <th style="text-align:right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php foreach($registrants as $r): ?>
                            <tr data-name="<?php echo strtolower(h($r['name'])); ?>" data-email="<?php echo strtolower(h($r['email'])); ?>">
                                <td><img class="avatar" src="<?php echo h($r['avatar']); ?>" alt="<?php echo h($r['name']); ?>"></td>
                                <td>
                                    <div class="name-block">
                                        <div class="meta">
                                            <div class="name"><?php echo h($r['name']); ?></div>
                                            <div class="sub">Registrant #<?php echo h($r['id']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="year"><?php echo h($r['year']); ?></div></td>
                                <td><div class="email"><?php echo h($r['email']); ?></div></td>
                                <td style="text-align:right">
                                    <button class="btn" onclick="viewRegistrant(<?php echo $r['id']; ?>)"><i class="fas fa-eye"></i> View</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div id="noResults" class="empty" style="display:none;">
                        <div style="font-size:1.05rem; font-weight:700; margin-bottom:8px;">No registrants found</div>
                        <div>Try adjusting your search or filters.</div>
                    </div>
                </div>

                <div class="table-footer" role="contentinfo">
                    <div class="summary"><strong id="countText"><?php echo count($registrants); ?></strong> registrants</div>
                    <div class="pagination" aria-label="Pagination">
                        <button class="page-btn" id="prevPage" disabled>&lt; Prev</button>
                        <div style="color:#6b7280">Page <strong id="pageNum">1</strong> of <strong id="pageTotal">1</strong></div>
                        <button class="page-btn" id="nextPage" disabled>Next &gt;</button>
                    </div>
                </div>
            </div>
        </section>
        
    </main>

    <div class="overlay" id="mobile-overlay"></div>

</div>

<script src="../JavaScript/OrgRep_db.js"></script>
<script>
    // Simple client-side search + mock view action
    (function(){
        const rows = Array.from(document.querySelectorAll('#tableBody tr'));
        const input = document.getElementById('searchInput');
        const noResults = document.getElementById('noResults');
        const countText = document.getElementById('countText');

        // Simple client-side filtering
        input.addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            let visible = 0;
            rows.forEach(r=>{
                const name = r.getAttribute('data-name') || '';
                const email = r.getAttribute('data-email') || '';
                const match = !q || name.includes(q) || email.includes(q);
                r.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            noResults.style.display = visible ? 'none' : '';
            countText.textContent = visible;
        });

        document.getElementById('refreshBtn').addEventListener('click', function(){
            input.value = '';
            input.dispatchEvent(new Event('input'));
        });

        document.getElementById('exportBtn').addEventListener('click', function(){
            const visibleRows = rows.filter(r => r.style.display !== 'none');
            if (!visibleRows.length) { alert('No registrants to export'); return; }
            const csv = ['Name,Year level,Email'];
            visibleRows.forEach(r => {
                const name = r.querySelector('.name').textContent.trim();
                const year = r.querySelector('.year').textContent.trim();
                const email = r.querySelector('.email').textContent.trim();
                csv.push(`"${name}","${year}","${email}"`);
            });
            const blob = new Blob([csv.join('\n')], {type:'text/csv'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'registrants.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        });

        window.viewRegistrant = function(id){
            alert('Mock view for registrant id: ' + id + '\nIn production this would navigate to a details page.');
        };

        document.getElementById('pageNum').textContent = '1';
        document.getElementById('pageTotal').textContent = '1';
    })();
</script>
</body>
</html>