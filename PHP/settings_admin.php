<?php
session_start();
$pageTitle = 'Admin Settings';

$userName        = 'Admin User';
$userAvatarText  = 'A';
$userEmail       = 'admin@university.edu';
$userRole        = 'Administrator';
$userAffiliation = 'System Administrator';

$words        = explode(' ', trim($userName));
$userInitials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | ArkiConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
    :root {
        --brand:        #A43825;
        --brand-hover:  #8a2d1f;
        --brand-light:  #c9503a;
        --brand-dim:    rgba(164,56,37,0.15);

        --bg:           #1e1e24;
        --surface:      #2a2a32;
        --surface-2:    #32323c;
        --surface-3:    #3c3c48;

        --border:       rgba(255,255,255,0.10);
        --border-soft:  rgba(255,255,255,0.06);

        --text:         #f5f5f3;
        --muted:        #b0b0bb;

        --success:      #22c55e;
        --danger:       #ef4444;
        --danger-dim:   rgba(239,68,68,0.15);

        --sidebar-w:    270px;
        --radius:       14px;
        --ease:         0.22s cubic-bezier(0.4,0,0.2,1);
        --font:         'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

 body {
        font-family: var(--font);
        background: var(--bg);
        color: var(--text);
        display: flex;
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
    }

    .dashboard-container { display: flex; width: 100%; min-height: 100vh; }

    /* ══ SIDEBAR ══ */
.sidebar {
    width: var(--sidebar-w);
    background: var(--surface);
    display: flex;
    flex-direction: column;
    padding: 16px 10px 16px;
    position: sticky;
    top: 16px;
    height: calc(100vh - 32px);
    overflow: hidden; /* ← changed from overflow-y: auto */
    flex-shrink: 0;
    z-index: 100;
    box-shadow: 0 8px 32px rgba(0,0,0,0.35);
    border-radius: 20px;
    margin: 16px 0 16px 16px;
    border: 1px solid var(--border);
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                padding 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                clip-path: inset(0 round 20px);
}
    .sidebar-top-icons {
        display: flex;
        justify-content: space-between;
        flex-direction: row-reverse;
        align-items: center;
        padding: 4px 6px 12px;
        margin-bottom: 4px;
        border-bottom: 1px solid var(--border);
    }

    .menu-toggle-desktop {
        background: var(--surface-2);
        border: none;
        color: var(--muted);
        cursor: pointer;
        padding: 8px;
        border-radius: 10px;
        width: 34px; height: 34px;
        display: flex; align-items: center; justify-content: center;
        transition: var(--ease);
        flex-shrink: 0;
    }
    .menu-toggle-desktop:hover { background: var(--surface-3); color: var(--text); }
    .menu-toggle-desktop svg { width: 17px; height: 17px; }

    .logo a { display: flex; align-items: center; text-decoration: none; }
    .logo img {
    height: 32px;
    object-fit: contain;
    filter: none;           /* remove the invert — it's causing the dark box to show */
    background: transparent;
    border-radius: 0;
}
    .logo-fallback { font-size: 0.95rem; font-weight: 800; color: var(--text); }
    .logo-fallback span { color: var(--brand); }

.nav-section {
    flex: 1;
    padding: 10px 4px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow-y: auto; /* ← scroll only the nav links */
    overflow-x: hidden;
}
    .nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 14px;
        border-radius: 12px;
        color: var(--muted);
        text-decoration: none;
        font-size: 0.855rem;
        font-weight: 600;
        transition: var(--ease);
        white-space: nowrap;
        letter-spacing: 0.01em;
    }
    .nav-link svg { width: 17px; height: 17px; flex-shrink: 0; }
    .nav-link .link-text { flex: 1; }
    .nav-link:hover:not(.active) { background: var(--surface-3); color: var(--text); }
    .nav-link.active {
        background: var(--brand);
        color: #fff;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(164,56,37,0.35);
    }
    .nav-link.logout-link:hover { background: var(--danger-dim); color: var(--danger); }

    .nav-badge {
        margin-left: auto;
        background: #fff;
        color: var(--brand);
        font-size: 0.66rem;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 20px;
    }
    .nav-link.active .nav-badge { background: rgba(255,255,255,0.25); color: #fff; }

    .separator { border: none; border-top: 1px solid var(--border); margin: 8px 4px; }

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    margin: 8px 4px 8px; /* ← bottom margin */
    border-radius: 12px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    transition: var(--ease);
    flex-shrink: 0; /* ← never shrink */
    overflow: hidden;
    min-width: 0;
     margin-bottom: -17px;
}
    .user-profile:hover { background: var(--surface-3); }
    .user-profile img { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
    .user-profile .name { font-size: 0.8rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-profile .role { font-size: 0.7rem; color: var(--muted); margin-top: 2px; }

    /* Collapsed sidebar */
.sidebar.collapsed {
    width: 68px;
    padding: 16px 8px;
    overflow: hidden;
}

.sidebar.collapsed .user-profile {
    justify-content: center;
    align-items: center;
    padding: 10px 0;
    margin: 0;
    overflow: visible;
    width: 100%;
    margin-bottom: -17px;
}

.sidebar.collapsed .user-profile img { 
    margin: 0 auto; 
    display: block;
    flex-shrink: 0;
    margin-left: 11px;  
}

.user-profile-wrap {
    padding: 0 4px 16px;
    flex-shrink: 0;
}

.sidebar.collapsed .user-profile-wrap {
    padding: 0 0 16px;
}
.sidebar.collapsed .nav-link {
    justify-content: center;
    padding: 10px 0;
    margin: 2px 0;
}

.sidebar.collapsed .nav-link i,
.sidebar.collapsed .nav-link svg { margin: 0; }

.sidebar.collapsed .separator { margin: 8px 2px; }

.sidebar.collapsed .nav-section { padding: 0 2px; }

.sidebar.collapsed .link-text,
.sidebar.collapsed .user-profile .name,
.sidebar.collapsed .user-profile .role,
.sidebar.collapsed .logo img,
.sidebar.collapsed .logo-fallback { display: none; }

    /* ─── MAIN ─── */
    .content-area {
        flex:1 1 0%; padding:24px;
        min-height:100vh; overflow-y:auto;
        background:var(--bg);
        display:flex; flex-direction:column; gap:22px;
    }

    /* ─── HEADER BANNER ─── */
    .content-header {
        background:linear-gradient(120deg, var(--brand) 0%, var(--brand-hover) 100%);
        border-radius:18px; padding:26px 30px;
        display:flex; align-items:center; justify-content:space-between;
        box-shadow:0 8px 28px rgba(164,56,37,0.35);
        position:relative; overflow:hidden; flex-shrink:0;
    }
    .content-header::before {
        content:''; position:absolute; top:-40px; right:-40px;
        width:180px; height:180px; border-radius:50%;
        background:rgba(255,255,255,0.06);
    }
    .content-header::after {
        content:''; position:absolute; bottom:-60px; right:120px;
        width:220px; height:220px; border-radius:50%;
        background:rgba(255,255,255,0.04);
    }
    .header-left { display:flex; flex-direction:column; gap:4px; position:relative; z-index:1; }
    .header-left h1 { font-size:1.85rem; font-weight:800; color:#fff; line-height:1.1; }
    .header-left p  { font-size:.82rem; color:rgba(255,255,255,0.65); font-weight:500; }
    .header-right   { display:flex; align-items:center; gap:10px; position:relative; z-index:1; }
    .time-display {
        display:flex; align-items:center; gap:10px;
        padding:10px 18px;
        background:rgba(255,255,255,0.14);
        border:1px solid rgba(255,255,255,0.2);
        border-radius:12px;
    }
    .time-display i { color:rgba(255,255,255,.8); }
    .time-text { font-size:1rem; font-weight:700; color:#fff; letter-spacing:.02em; }

    /* ─── SETTINGS WRAP ─── */
    .st-wrap {
        display:grid; grid-template-columns:220px 1fr; gap:24px;
        animation:stFade .4s ease both;
    }
    @keyframes stFade { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

    /* ─── LEFT NAV ─── */
    .st-nav {
        display:flex; flex-direction:column;
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius); overflow:hidden;
        height:fit-content; box-shadow:0 2px 12px rgba(0,0,0,.2);
    }
    .st-nav-header {
        padding:14px 18px 12px; border-bottom:1px solid var(--border);
        font-size:.68rem; font-weight:700; letter-spacing:.1em;
        text-transform:uppercase; color:var(--brand);
    }
    .st-nav-btn {
        width:100%; display:flex; align-items:center; gap:12px;
        padding:13px 18px; border:none; background:transparent; cursor:pointer;
        font-family:var(--font); font-size:.86rem; font-weight:600;
        color:var(--muted); text-align:left;
        transition:background var(--ease), color var(--ease);
        position:relative; border-bottom:1px solid var(--border-soft);
    }
    .st-nav-btn:last-child { border-bottom:none; }
    .st-nav-btn i { width:16px; text-align:center; font-size:.85rem; flex-shrink:0; }
    .st-nav-btn:hover { background:var(--surface-2); color:var(--text); }
    .st-nav-btn.active { background:var(--brand-dim); color:var(--brand); font-weight:700; }
    .st-nav-btn.active::before {
        content:''; position:absolute; left:0; top:0; bottom:0;
        width:3px; background:var(--brand); border-radius:0 2px 2px 0;
    }

    /* ─── RIGHT PANEL ─── */
    .st-panel { display:flex; flex-direction:column; }

    .st-panel-header {
        background:linear-gradient(135deg, var(--brand-hover) 0%, var(--brand) 55%, var(--brand-light) 100%);
        border-radius:var(--radius) var(--radius) 0 0;
        padding:22px 28px; display:flex; align-items:center; gap:14px;
        position:relative; overflow:hidden;
    }
    .st-panel-header::before {
        content:''; position:absolute; inset:0;
        background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .st-panel-icon {
        width:42px; height:42px; background:rgba(255,255,255,.18);
        border-radius:10px; display:grid; place-items:center;
        color:#fff; font-size:1.1rem; flex-shrink:0; position:relative;
    }
    .st-panel-text { position:relative; }
    .st-panel-text h3 { font-size:1.15rem; font-weight:700; color:#fff; margin-bottom:2px; }
    .st-panel-text p  { font-size:.78rem; color:rgba(255,255,255,.75); }

    .st-panel-body {
        background:var(--surface); border:1px solid var(--border);
        border-top:none; border-radius:0 0 var(--radius) var(--radius);
        box-shadow:0 2px 12px rgba(0,0,0,.2); overflow:hidden;
    }

    /* ─── TAB PANELS ─── */
    .st-tab { display:none; padding:26px; animation:stFade .3s ease; }
    .st-tab.active { display:block; }

    /* ─── AVATAR ROW ─── */
    .avatar-row {
        display:flex; align-items:center; gap:20px;
        padding:18px 20px; background:var(--surface-2);
        border:1px solid var(--border); border-radius:10px;
        margin-bottom:22px; flex-wrap:wrap;
    }
    .avatar-circle {
        width:80px; height:80px; border-radius:50%;
        background:linear-gradient(135deg, var(--brand) 0%, var(--brand-hover) 100%);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-size:2rem; font-weight:800;
        box-shadow:0 4px 14px rgba(164,56,37,.35);
        flex-shrink:0; background-size:cover; background-position:center;
    }
    .avatar-info { flex:1; }
    .avatar-info h3 { font-size:1.1rem; font-weight:700; color:var(--text); margin-bottom:4px; }
    .avatar-info p  { font-size:.78rem; color:var(--muted); }
    .avatar-actions { display:flex; gap:8px; margin-top:10px; flex-wrap:wrap; }

    /* ─── INFO GRID ─── */
    .info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:22px; }
    .info-box {
        background:var(--surface-2); border:1px solid var(--border);
        border-radius:10px; padding:13px 16px;
    }
    .info-box-label { font-size:.67rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); margin-bottom:4px; }
    .info-box-val   { font-size:.9rem; font-weight:700; color:#ffff; }

    /* ─── FORM ─── */
    .form-divider { border:none; border-top:1px solid var(--border); margin:4px 0 20px; }
    .form-section-title { font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--brand); margin-bottom:16px; }
    .form-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
    .form-field { display:flex; flex-direction:column; gap:6px; }
    .field-label { font-size:.78rem; font-weight:600; color:var(--muted); display:flex; align-items:center; gap:6px; }
    .field-label i { color:var(--brand); font-size:.76rem; }
    .field-row { display:flex; gap:8px; }
    .form-input {
        flex:1; padding:10px 13px;
        background:var(--surface-2); border:1px solid var(--border);
        border-radius:9px; font-family:var(--font);
        font-size:.86rem; color:var(--text); font-weight:600;
        outline:none; transition:var(--ease);
    }
    .form-input:focus { border-color:var(--brand); box-shadow:0 0 0 3px var(--brand-dim); }
    .form-input:disabled { opacity:.4; cursor:not-allowed; }

    /* ─── BUTTONS ─── */
    .btn-save {
        padding:10px 15px; background:var(--brand); color:#fff;
        border:none; border-radius:8px; font-family:var(--font);
        font-size:.8rem; font-weight:700; cursor:pointer;
        white-space:nowrap; transition:var(--ease);
    }
    .btn-save:hover { background:var(--brand-hover); transform:translateY(-1px); }

    .btn-upload {
        padding:8px 14px; background:#1f318e; color:#fff; border:none;
        border-radius:8px; font-family:var(--font); font-size:.8rem; font-weight:700;
        cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:opacity .15s;
    }
    .btn-upload:hover { opacity:.85; }

    .btn-ghost {
        padding:8px 14px; background:var(--surface-3); color:var(--muted);
        border:1px solid var(--border); border-radius:8px; font-family:var(--font);
        font-size:.8rem; font-weight:600; cursor:pointer;
        display:inline-flex; align-items:center; gap:6px; transition:var(--ease);
    }
    .btn-ghost:hover { background:var(--danger-dim); color:var(--danger); border-color:rgba(239,68,68,.3); }

    .btn-change-pw {
        padding:10px 15px; background:transparent; color:var(--brand);
        border:1.5px solid var(--brand); border-radius:8px; font-family:var(--font);
        font-size:.8rem; font-weight:700; cursor:pointer; white-space:nowrap; transition:var(--ease);
    }
    .btn-change-pw:hover { background:var(--brand); color:#fff; }

    .form-actions { margin-top:22px; display:flex; justify-content:flex-end; }
    .btn-primary-lg {
        padding:11px 24px; background:var(--brand); color:#fff; border:none;
        border-radius:9px; font-family:var(--font); font-size:.88rem; font-weight:700;
        cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:var(--ease);
    }
    .btn-primary-lg:hover { background:var(--brand-hover); transform:translateY(-1px); box-shadow:0 4px 14px rgba(164,56,37,.35); }

    /* ─── TOGGLE ITEMS ─── */
    .toggle-item {
        display:flex; align-items:center; justify-content:space-between;
        padding:16px 0; border-bottom:1px solid var(--border-soft); gap:16px;
    }
    .toggle-item:last-child { border-bottom:none; }
    .toggle-info h4 { font-size:.9rem; font-weight:700; color:var(--text); margin-bottom:3px; }
    .toggle-info p  { font-size:.78rem; color:var(--muted); }

    /* ─── SWITCH ─── */
    .switch { position:relative; display:inline-block; width:46px; height:24px; flex-shrink:0; }
    .switch input { opacity:0; width:0; height:0; }
    .switch-slider {
        position:absolute; cursor:pointer; inset:0;
        background:var(--surface-3); border:1px solid var(--border);
        border-radius:24px; transition:var(--ease);
    }
    .switch-slider::before {
        content:''; position:absolute; width:16px; height:16px;
        left:3px; bottom:3px; background:#fff; border-radius:50%; transition:var(--ease);
    }
    .switch input:checked + .switch-slider { background:var(--brand); border-color:var(--brand); }
    .switch input:checked + .switch-slider::before { transform:translateX(22px); }

    /* ─── PASSWORD MODAL ─── */
    #passwordModal {
        display:none; position:fixed; inset:0;
        background:rgba(0,0,0,.65); z-index:9999;
        justify-content:center; align-items:center;
        padding:2rem; backdrop-filter:blur(4px);
    }
    .modal-inner {
        width:100%; max-width:480px;
        background:var(--surface); border:1px solid var(--border);
        border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,.5);
        overflow:hidden; animation:stFade .25s ease;
    }
    .modal-head {
        background:linear-gradient(135deg, var(--brand-hover) 0%, var(--brand) 100%);
        padding:20px 24px; display:flex; align-items:center; gap:12px;
    }
    .modal-head-icon {
        width:38px; height:38px; background:rgba(255,255,255,.18);
        border-radius:9px; display:grid; place-items:center; color:#fff; font-size:.9rem;
    }
    .modal-head h3 { font-size:1.05rem; font-weight:700; color:#fff; margin:0; }
    .modal-head p  { font-size:.75rem; color:rgba(255,255,255,.75); margin:2px 0 0; }
    .modal-close-btn {
        margin-left:auto; background:rgba(255,255,255,.18); border:none; color:#fff;
        width:30px; height:30px; border-radius:50%; font-size:1rem; cursor:pointer;
        display:grid; place-items:center; transition:var(--ease);
    }
    .modal-close-btn:hover { background:rgba(255,255,255,.32); }

    .modal-body { padding:22px 24px; }
    .modal-field { margin-bottom:14px; }
    .modal-field label { display:block; font-size:.8rem; font-weight:600; color:var(--muted); margin-bottom:6px; }
    .pw-wrap { display:flex; align-items:center; position:relative; }
    .pw-wrap .form-input { padding-right:40px; width:100%; }
    .pw-toggle { position:absolute; right:10px; background:none; border:none; color:var(--muted); cursor:pointer; font-size:.85rem; }

    .strength-bar { height:5px; background:var(--surface-3); border-radius:4px; overflow:hidden; margin-top:8px; }
    .strength-fill { height:100%; width:0; background:linear-gradient(90deg,#ef4444,#f59e0b,#22c55e); transition:width .2s; }

    .pw-checklist { margin-top:10px; padding:0; list-style:none; }
    .pw-checklist li { font-size:.78rem; color:var(--muted); display:flex; align-items:center; gap:7px; margin-bottom:4px; }
    .pw-checklist li.valid   { color:#22c55e; }
    .pw-checklist li.invalid { color:#ef4444; }

    .modal-foot {
        padding:16px 24px; border-top:1px solid var(--border);
        display:flex; justify-content:flex-end; gap:10px;
        background:var(--surface-2);
    }
    .btn-modal-cancel {
        padding:9px 18px; background:var(--surface-3); color:var(--muted);
        border:1px solid var(--border); border-radius:8px;
        font-family:var(--font); font-size:.85rem; font-weight:600; cursor:pointer; transition:var(--ease);
    }
    .btn-modal-cancel:hover { color:var(--text); }
    .btn-modal-apply {
        padding:9px 18px; background:var(--brand); color:#fff; border:none;
        border-radius:8px; font-family:var(--font); font-size:.85rem; font-weight:700;
        cursor:pointer; transition:var(--ease);
    }
    .btn-modal-apply:hover { background:var(--brand-hover); }
    .btn-modal-apply:disabled { opacity:.4; cursor:not-allowed; }

    /* ─── RESPONSIVE ─── */
    @media (max-width:900px) {
        .sidebar { position:fixed; transform:translateX(-100%); margin:0; border-radius:0; height:100vh; top:0; }
        .content-area { padding:16px; }
        .st-wrap { grid-template-columns:1fr; }
        .st-nav { display:grid; grid-template-columns:repeat(3,1fr); }
        .st-nav-header { display:none; }
        .st-nav-btn { border-bottom:none; border-right:1px solid var(--border-soft); justify-content:center; }
        .st-nav-btn:last-child { border-right:none; }
        .st-nav-btn.active::before { top:auto; bottom:0; left:0; right:0; width:auto; height:3px; }
        .form-grid { grid-template-columns:1fr; }
        .info-grid { grid-template-columns:1fr 1fr; }
    }
    </style>
</head>
<body>
<div class="dashboard-container">

<!-- ─── SIDEBAR ─── -->
<aside class="sidebar" id="left-sidebar">
    <div class="sidebar-top-icons">
        <button class="menu-toggle-desktop" id="collapse-toggle" title="Toggle sidebar">
            <i data-lucide="menu"></i>
        </button>
        <div class="logo">
            <a href="Admin_db.php">
                <img src="../Images/newlogo.png" alt="Arki Connect"
                     onerror="this.style.display='none';document.getElementById('logo-fb').style.display='block'">
                <span class="logo-fallback" id="logo-fb" style="display:none;">Arki<span>Admin</span></span>
            </a>
        </div>
    </div>

    <div class="nav-section">
        <a href="Admin_db.php" class="nav-link">
            <i data-lucide="layout-dashboard"></i>
            <span class="link-text">Dashboard</span>
        </a>
        <a href="manage_events.php" class="nav-link">
            <i data-lucide="calendar-days"></i>
            <span class="link-text">Manage Events</span>
        </a>
        <a href="event_reports.php" class="nav-link">
            <i data-lucide="file-bar-chart"></i>
            <span class="link-text">Event Reports</span>
        </a>
        <hr class="separator">
        <a href="settings_admin.php" class="nav-link active">
            <i data-lucide="settings"></i>
            <span class="link-text">Settings</span>
        </a>
        <a href="login.php?logout=true" class="nav-link logout-link">
            <i data-lucide="log-out"></i>
            <span class="link-text">Logout</span>
        </a>
    </div>

    <div class="user-profile-wrap">
    <div class="user-profile">
        <img src="https://placehold.co/36x36/A43825/ffffff?text=<?= htmlspecialchars($userInitials) ?>" alt="Admin">
        <div class="user-info">
            <div class="name"><?= htmlspecialchars($userName) ?></div>
            <div class="role">System Administrator</div>
        </div>
    </div>
</div>
</aside>

<!-- ─── MAIN ─── -->
<main class="content-area">

    <!-- Header Banner -->
    <header class="content-header">
        <div class="header-left">
            <h1>Settings</h1>
            <p>Manage your account and administrator preferences</p>
        </div>
        <div class="header-right">
            <div class="time-display">
                <i class="fas fa-clock"></i>
                <span class="time-text" id="liveTime"></span>
            </div>
        </div>
    </header>

    <!-- Settings wrap -->
    <div class="st-wrap">

        <!-- Left nav -->
        <nav class="st-nav">
            <div class="st-nav-header">Settings</div>
            <button class="st-nav-btn active" data-tab="account">
                <i class="fas fa-user-circle"></i> Account
            </button>
            <button class="st-nav-btn" data-tab="notifications">
                <i class="fas fa-bell"></i> Notifications
            </button>
            <button class="st-nav-btn" data-tab="privacy">
                <i class="fas fa-shield-alt"></i> Privacy &amp; Security
            </button>
        </nav>

        <!-- Right panel -->
        <div class="st-panel">

            <!-- Dynamic gradient header -->
            <div class="st-panel-header">
                <div class="st-panel-icon"><i class="fas fa-user-circle" id="st-header-icon"></i></div>
                <div class="st-panel-text">
                    <h3 id="st-header-title">Account Settings</h3>
                    <p id="st-header-sub">Manage your profile, email and password</p>
                </div>
            </div>

            <div class="st-panel-body">

                <!-- ── ACCOUNT TAB ── -->
                <div class="st-tab active" id="account-tab">

                    <div class="avatar-row">
                        <div class="avatar-circle" id="avatarCircle"><?= htmlspecialchars($userAvatarText) ?></div>
                        <div class="avatar-info">
                            <h3><?= htmlspecialchars($userName) ?></h3>
                            <p><?= htmlspecialchars($userAffiliation) ?></p>
                            <div class="avatar-actions">
                                <label class="btn-upload">
                                    <i class="fas fa-upload"></i> Upload New
                                    <input type="file" id="avatarUpload" accept="image/*" style="display:none;">
                                </label>
                                <button type="button" class="btn-ghost" id="deleteAvatarBtn">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-box-label">Account Role</div>
                            <div class="info-box-val"><?= htmlspecialchars($userRole) ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">Access Level</div>
                            <div class="info-box-val">Full Access</div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">Department</div>
                            <div class="info-box-val">Administration</div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">Institution</div>
                            <div class="info-box-val">TIP Manila</div>
                        </div>
                    </div>

                    <hr class="form-divider">
                    <div class="form-section-title">Edit Profile Details</div>

                    <div class="form-grid">
                        <div class="form-field">
                            <label class="field-label"><i class="fas fa-user"></i> Full Name</label>
                            <div class="field-row">
                                <input type="text" class="form-input" id="editableName" value="<?= htmlspecialchars($userName) ?>">
                                <button class="btn-save" onclick="updateField('name')">Save</button>
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="field-label"><i class="fas fa-envelope"></i> Email Address</label>
                            <div class="field-row">
                                <input type="email" class="form-input" id="editableEmail" value="<?= htmlspecialchars($userEmail) ?>">
                                <button class="btn-save" onclick="updateField('email')">Save</button>
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="field-label"><i class="fas fa-lock"></i> Password</label>
                            <div class="field-row">
                                <input type="password" class="form-input" value="••••••••" disabled>
                                <button class="btn-change-pw" onclick="openChangePasswordModal()">Change Password</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn-primary-lg" onclick="showToast('All changes saved!','success')">
                            <i class="fas fa-check-circle"></i> Save All Changes
                        </button>
                    </div>
                </div>

                <!-- ── NOTIFICATIONS TAB ── -->
                <div class="st-tab" id="notifications-tab">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Email Notifications</h4>
                            <p>Receive email updates about system activity</p>
                        </div>
                        <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Event Approvals</h4>
                            <p>Get notified when events need approval</p>
                        </div>
                        <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>System Alerts</h4>
                            <p>Important system updates and security alerts</p>
                        </div>
                        <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                    </div>
                    <div class="form-actions">
                        <button class="btn-primary-lg" onclick="showToast('Notification preferences saved!','success')">
                            <i class="fas fa-check"></i> Save Preferences
                        </button>
                    </div>
                </div>

                <!-- ── PRIVACY TAB ── -->
                <div class="st-tab" id="privacy-tab">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Activity Logging</h4>
                            <p>Track all admin actions and system changes</p>
                        </div>
                        <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Two-Factor Authentication</h4>
                            <p>Add an extra layer of security to your admin account</p>
                        </div>
                        <button class="btn-change-pw" onclick="showToast('2FA setup requires backend configuration.','info')">Enable 2FA</button>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Session Timeout</h4>
                            <p>Automatically log out after 30 minutes of inactivity</p>
                        </div>
                        <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Data Export</h4>
                            <p>Download all system data and user information</p>
                        </div>
                        <button class="btn-change-pw" onclick="showToast('Data export triggered.','info')">Export Data</button>
                    </div>
                    <div class="form-actions">
                        <button class="btn-primary-lg" onclick="showToast('Privacy settings saved!','success')">
                            <i class="fas fa-check"></i> Save Security Settings
                        </button>
                    </div>
                </div>

            </div><!-- /.st-panel-body -->
        </div><!-- /.st-panel -->
    </div><!-- /.st-wrap -->

</main>
</div>

<!-- ─── PASSWORD MODAL ─── -->
<div id="passwordModal" role="dialog" aria-modal="true">
    <div class="modal-inner">
        <div class="modal-head">
            <div class="modal-head-icon"><i class="fas fa-lock"></i></div>
            <div>
                <h3>Change Password</h3>
                <p>Update your password for enhanced security</p>
            </div>
            <button class="modal-close-btn" onclick="closeChangePasswordModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="changePasswordForm" novalidate>
                <div class="modal-field">
                    <label>Current Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="oldPassword" class="form-input" autocomplete="current-password">
                        <button type="button" class="pw-toggle" onclick="togglePassword('oldPassword',this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="modal-field">
                    <label>New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="newPassword" class="form-input" autocomplete="new-password">
                        <button type="button" class="pw-toggle" onclick="togglePassword('newPassword',this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="modal-field">
                    <label>Confirm New Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="confirmNewPassword" class="form-input" autocomplete="new-password">
                        <button type="button" class="pw-toggle" onclick="togglePassword('confirmNewPassword',this)"><i class="fas fa-eye"></i></button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <ul class="pw-checklist">
                        <li id="checkUpper"  class="invalid"><i class="fas fa-times-circle"></i> At least 1 uppercase</li>
                        <li id="checkNumber" class="invalid"><i class="fas fa-times-circle"></i> At least 1 number</li>
                        <li id="checkLength" class="invalid"><i class="fas fa-times-circle"></i> At least 8 characters</li>
                    </ul>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn-modal-cancel" onclick="closeChangePasswordModal()">Cancel</button>
            <button class="btn-modal-apply" id="applyPasswordBtn" disabled>Apply Changes</button>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

// ── Sidebar collapse ──
document.getElementById('collapse-toggle').addEventListener('click', () => {
    document.getElementById('left-sidebar').classList.toggle('collapsed');
});

// ── Live clock ──
function updateTime() {
    const now  = new Date();
    let h      = now.getHours();
    const m    = String(now.getMinutes()).padStart(2,'0');
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    document.getElementById('liveTime').textContent = `${String(h).padStart(2,'0')}:${m} ${ampm}`;
}
updateTime();
setInterval(updateTime, 60000);

// ── Toast ──
window.showToast = function(msg, type) {
    let el = document.getElementById('st-toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'st-toast';
        el.style.cssText = `position:fixed;bottom:28px;right:28px;padding:12px 22px;
            border-radius:10px;font-family:var(--font);font-weight:700;font-size:.85rem;
            z-index:99999;box-shadow:0 4px 20px rgba(0,0,0,.5);
            transition:opacity .3s;opacity:0;color:#fff;`;
        document.body.appendChild(el);
    }
    const colors = { success:'#22c55e', error:'#ef4444', info:'#A43825' };
    el.style.background = colors[type] || colors.info;
    el.textContent = msg;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.opacity = '0'; }, 3000);
};

// ── Tab switching ──
const tabMeta = {
    account:       ['fa-user-circle', 'Account Settings',          'Manage your profile, email and password'],
    notifications: ['fa-bell',         'Notification Preferences',  'Control how and when you receive alerts'],
    privacy:       ['fa-shield-alt',   'Privacy & Security',        'Control your visibility and security settings'],
};
document.querySelectorAll('.st-nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        document.querySelectorAll('.st-nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.st-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(tab + '-tab').classList.add('active');
        const [icon, title, sub] = tabMeta[tab];
        document.getElementById('st-header-icon').className = 'fas ' + icon;
        document.getElementById('st-header-title').textContent = title;
        document.getElementById('st-header-sub').textContent  = sub;
    });
});

// ── Update field stub ──
window.updateField = function(field) {
    showToast(field.charAt(0).toUpperCase() + field.slice(1) + ' updated!', 'success');
};

// ── Avatar ──
document.getElementById('avatarUpload').addEventListener('change', function() {
    const file = this.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => {
        const ac = document.getElementById('avatarCircle');
        ac.style.backgroundImage    = `url('${e.target.result}')`;
        ac.style.backgroundSize     = 'cover';
        ac.style.backgroundPosition = 'center';
        ac.textContent = '';
    };
    reader.readAsDataURL(file);
});
document.getElementById('deleteAvatarBtn').addEventListener('click', () => {
    const ac = document.getElementById('avatarCircle');
    ac.style.backgroundImage = 'none';
    ac.style.background = 'linear-gradient(135deg,#a43825 0%,#8a2d1f 100%)';
    ac.textContent = '<?= $userAvatarText ?>';
    showToast('Avatar removed.', 'info');
});

// ── Password modal ──
window.openChangePasswordModal = function() {
    document.getElementById('passwordModal').style.display = 'flex';
};
window.closeChangePasswordModal = function() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('changePasswordForm').reset();
    document.getElementById('strengthFill').style.width = '0';
    document.getElementById('applyPasswordBtn').disabled = true;
    ['checkUpper','checkNumber','checkLength'].forEach(id => {
        const li = document.getElementById(id);
        li.className = 'invalid';
        li.querySelector('i').className = 'fas fa-times-circle';
    });
};
window.togglePassword = function(id, btn) {
    const inp = document.getElementById(id);
    const ico = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text';     ico.className = 'fas fa-eye-slash'; }
    else                         { inp.type = 'password'; ico.className = 'fas fa-eye'; }
};

const newPwInput  = document.getElementById('newPassword');
const confPwInput = document.getElementById('confirmNewPassword');
const applyBtn    = document.getElementById('applyPasswordBtn');
const fillEl      = document.getElementById('strengthFill');

function evalPassword() {
    const pw        = newPwInput.value;
    const hasUpper  = /[A-Z]/.test(pw);
    const hasNumber = /[0-9]/.test(pw);
    const hasLength = pw.length >= 8;
    let score = 0;
    if (hasUpper)  score += 33;
    if (hasNumber) score += 33;
    if (hasLength) score += 34;
    fillEl.style.width = score + '%';
    setCheck('checkUpper',  hasUpper);
    setCheck('checkNumber', hasNumber);
    setCheck('checkLength', hasLength);
    applyBtn.disabled = !(hasUpper && hasNumber && hasLength && pw === confPwInput.value);
}
function setCheck(id, valid) {
    const li = document.getElementById(id);
    li.className = valid ? 'valid' : 'invalid';
    li.querySelector('i').className = valid ? 'fas fa-check-circle' : 'fas fa-times-circle';
}
newPwInput.addEventListener('input',  evalPassword);
confPwInput.addEventListener('input', evalPassword);

applyBtn.addEventListener('click', () => {
    closeChangePasswordModal();
    showToast('Password updated successfully!', 'success');
});
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) closeChangePasswordModal();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.getElementById('passwordModal').style.display === 'flex')
        closeChangePasswordModal();
});
</script>
</body>
</html>