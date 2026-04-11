<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Details - Design Expo 2025</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: #f5f7fa;
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      padding: 0;
      color: #23272f;
    }
    .event-wide-container {
      max-width: 1100px;
      margin: 48px auto;
      background: #fff;
      border-radius: 22px;
      box-shadow: 0 8px 32px rgba(44,62,80,0.13);
      overflow: hidden;
      display: flex;
      flex-direction: row;
      gap: 0;
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
      border-radius: 28px;
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
      font-weight: 800;
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
    .event-meta i {
      color: #ffc107;
      margin-right: 7px;
    }
    .event-tags {
      margin-bottom: 14px;
    }
    .tag {
      background: #fdf3e7;
      color: #a43825;
      display: inline-block;
      border-radius: 13px;
      padding: 4px 15px;
      font-size: 1.01rem;
      font-weight: 600;
      margin: 0 6px 6px 0;
    }
    .section-label {
      font-size: 1.22rem;
      font-weight: 700;
      color: #a43825;
      margin: 24px 0 8px 0;
      letter-spacing: 0.01em;
    }
    .event-description {
      font-size: 1.15rem;
      line-height: 1.7;
      color: #23272f;
      margin-bottom: 18px;
    }
    .organizer-section {
      margin-bottom: 13px;
    }
    .organizer-list {
      display: flex;
      gap: 14px;
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
      margin-top: 16px;
    }
    @media (max-width: 1000px) {
      .event-wide-container {
        flex-direction: column;
        max-width: 99vw;
      }
      .event-image-section {min-height: 260px;}
      .event-details-section {padding: 30px 16px;}
      .event-title {font-size: 2rem;}
      .event-banner {margin: 18px 0;}
    }
  </style>
</head>
<body>
  <div class="event-wide-container">
    <!-- Image Section -->
    <div class="event-image-section">
      <img class="event-banner" src="design-expo-banner.jpg" alt="Design Expo 2025 Banner">
    </div>
    <!-- Details Section -->
    <div class="event-details-section">
      <!-- Event Title / Name -->
      <div class="event-title">Design Expo 2025</div>
      <!-- Date / Time / Location -->
      <div class="event-meta">
        <div><i class="fas fa-calendar-day"></i> October 22, 2025</div>
        <div><i class="fas fa-clock"></i> 9:00 AM – 5:00 PM</div>
        <div><i class="fas fa-map-marker-alt"></i> TIP Manila</div>
      </div>

      <!-- Description / Details -->
      <div class="section-label">Description</div>
      <div class="event-description">
        The Design Expo 2025 is a premier gathering for architects and designers, showcasing innovative projects, new materials, and creative concepts for the future. The event includes keynote talks from industry leaders, networking opportunities, and exhibits of groundbreaking ideas. Join us for a day full of inspiration, learning, and connections!
      </div>
      <!-- Organizer / Host -->
      <div class="section-label organizer-section">Host</div>
      <div class="organizer-list">
        <img class="organizer-img" src="organizer1.jpg" alt="Organizer 1">
        <div class="organizer-name">ASAPHIL</div>
      </div>
    </div>
  </div>
</body>
</html>