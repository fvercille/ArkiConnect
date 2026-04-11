<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Design Expo 2025 - Event Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #a43825;
      --primary-dark: #83291a;
      --accent: #ffc107;
      --bg: #f5f7fa;
      --card-bg: #fff;
      --text-dark: #23272f;
      --text-muted: #6c757d;
      --border: #e7eaf1;
      --shadow-lg: 0 8px 24px rgba(44,62,80,0.13);
      --gradient: linear-gradient(120deg, var(--primary-dark) 0%, var(--primary) 100%);
    }
    body {
      background: var(--bg);
      font-family: 'Segoe UI', 'Arial', sans-serif;
      margin: 0;
      color: var(--text-dark);
    }
    .hero-event {
      background: var(--gradient), url('../Images/design-expo-bg.jpg') center/cover no-repeat;
      color: #fff;
      padding: 70px 0 50px 0;
      text-align: center;
      position: relative;
    }
    .hero-event .event-title {
      font-size: 2.7rem;
      font-weight: 800;
      margin-bottom: 8px;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }
    .hero-event .event-date {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--accent);
      margin-bottom: 16px;
    }
    .hero-event .event-location {
      font-size: 1.05rem;
      color: #ffe8d6;
      margin-bottom: 22px;
    }
    .hero-event .register-btn {
      background: var(--accent);
      color: var(--primary);
      font-size: 1.13rem;
      font-weight: 700;
      border: none;
      border-radius: 7px;
      padding: 15px 32px;
      box-shadow: 0 4px 16px rgba(44,62,80,0.13);
      cursor: pointer;
      transition: background 0.2s, color 0.2s;
    }
    .hero-event .register-btn:hover {
      background: var(--primary);
      color: #fff;
    }
    .main-content {
      max-width: 1100px;
      margin: -40px auto 0 auto;
      background: var(--card-bg);
      border-radius: 18px;
      box-shadow: var(--shadow-lg);
      padding: 48px 36px 36px 36px;
      position: relative;
      z-index: 5;
    }
    .about-section {
      display: flex;
      flex-wrap: wrap;
      gap: 28px;
      margin-bottom: 40px;
      align-items: flex-start;
    }
    .about-details {
      flex: 1 1 320px;
      min-width: 280px;
    }
    .about-details h2 {
      font-size: 1.7rem;
      font-weight: 800;
      margin-bottom: 16px;
      color: var(--primary-dark);
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }
    .about-details p {
      font-size: 1.08rem;
      color: var(--text-dark);
      margin-bottom: 16px;
      line-height: 1.6;
    }
    .about-details .info-list {
      display: flex;
      gap: 24px;
      margin-bottom: 12px;
      flex-wrap: wrap;
    }
    .about-details .info-card {
      background: #fdf3e7;
      border-radius: 8px;
      padding: 16px 20px;
      font-size: 1rem;
      font-weight: 600;
      color: var(--primary-dark);
      display: flex;
      align-items: center;
      gap: 11px;
      margin-bottom: 8px;
      box-shadow: 0 2px 8px rgba(44,62,80,0.04);
    }
    .about-details .info-card i {
      color: var(--primary);
      font-size: 1.2rem;
      min-width: 22px;
    }
    .event-image {
      flex: 1 1 320px;
      min-width: 280px;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .event-image img {
      width: 100%;
      max-width: 400px;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(44,62,80,0.17);
      object-fit: cover;
    }
    .why-join {
      margin-top: 18px;
    }
    .why-join h3 {
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 24px;
      color: var(--primary-dark);
      text-transform: uppercase;
      letter-spacing: 0.02em;
    }
    .why-list {
      display: flex;
      gap: 32px;
      flex-wrap: wrap;
    }
    .why-item {
      background: #fff9f0;
      border-radius: 10px;
      padding: 24px 22px;
      flex: 1 1 220px;
      min-width: 220px;
      box-shadow: 0 2px 8px rgba(44,62,80,0.04);
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }
    .why-item i {
      font-size: 2rem;
      color: var(--accent);
      margin-bottom: 5px;
    }
    .why-item h4 {
      font-size: 1.08rem;
      font-weight: 700;
      color: var(--primary-dark);
      margin-bottom: 2px;
    }
    .why-item p {
      font-size: 0.97rem;
      color: var(--text-muted);
      line-height: 1.5;
    }
    @media (max-width: 900px) {
      .main-content {padding: 20px 8px 28px 8px;}
      .about-section {flex-direction: column;}
      .event-image img {max-width: 98vw;}
      .why-list {flex-direction: column; gap: 20px;}
      .why-item {padding: 16px 10px;}
    }
  </style>
</head>
<body>
  <!-- Hero Section -->
  <section class="hero-event">
    <div class="event-title">Design Expo 2025</div>
    <div class="event-date"><i class="fas fa-calendar-alt"></i> October 22, 2025 &nbsp;|&nbsp; 9:00 AM - 5:00 PM</div>
    <div class="event-location"><i class="fas fa-map-marker-alt"></i> TIP Manila Auditorium</div>
    <button class="register-btn">Register Now</button>
  </section>

  <!-- Main Content -->
  <div class="main-content">
    <!-- About Section -->
    <div class="about-section">
      <div class="about-details">
        <h2>About the Event</h2>
        <p>
          The <strong>Design Expo 2025</strong> is a premier event showcasing innovative architecture and design projects from students and professionals. Discover groundbreaking concepts, new materials, and creative approaches shaping the future of design.
        </p>
        <div class="info-list">
          <div class="info-card"><i class="fas fa-calendar-day"></i> <span>Wednesday, October 22, 2025</span></div>
          <div class="info-card"><i class="fas fa-clock"></i> <span>9:00 AM – 5:00 PM</span></div>
          <div class="info-card"><i class="fas fa-map-marker-alt"></i> <span>TIP Manila Auditorium</span></div>
        </div>
        <div class="info-card" style="background:#ffe8d6;">
          <i class="fas fa-user"></i> <span>Open to: Architecture & Design Community</span>
        </div>
      </div>
      <div class="event-image">
        <img src="../Images/design-expo-feature.jpg" alt="Design Expo 2025" />
      </div>
    </div>

    <!-- Why Join Section -->
    <div class="why-join">
      <h3>Why Join Us?</h3>
      <div class="why-list">
        <div class="why-item">
          <i class="fas fa-bullhorn"></i>
          <h4>Media Coverage</h4>
          <p>Your work may be featured in local and national media during the expo.</p>
        </div>
        <div class="why-item">
          <i class="fas fa-lightbulb"></i>
          <h4>New Trends</h4>
          <p>Explore the latest techniques, materials, and ideas in architecture and design.</p>
        </div>
        <div class="why-item">
          <i class="fas fa-coffee"></i>
          <h4>Networking & Coffee Breaks</h4>
          <p>Connect with professionals and students, share ideas, and expand your network over refreshments.</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>