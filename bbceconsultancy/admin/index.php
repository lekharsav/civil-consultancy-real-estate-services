<?php
require_once "../config/config.php";

if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: login.php");
  exit;
}


$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard — BBC Engineering Consultancy</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">

  <style>
    /* ================= ADMIN DASHBOARD ================= */

    body { background:#f6f9ff; }

    .admin-wrap { padding:40px 0; }

    .admin-title {
      font-family:Poppins,sans-serif;
      font-size:34px;
      font-weight:800;
      color:#021428;
      margin-bottom:6px;
    }

    .admin-sub {
      color:#475569;
      font-size:15px;
      margin-bottom:30px;
    }

    /* STAT CARDS */
    .stat-grid {
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:24px;
      margin-bottom:40px;
    }

    .stat-card {
      background:#fff;
      border-radius:18px;
      padding:26px;
      box-shadow:0 10px 30px rgba(2,8,20,.08);
      position:relative;
      overflow:hidden;
    }

    .stat-card::after {
      content:'';
      position:absolute;
      right:-30px;
      top:-30px;
      width:120px;
      height:120px;
      background:linear-gradient(135deg,#00D4FF,#635BFF);
      opacity:.08;
      border-radius:50%;
    }

    .stat-label {
      font-size:13px;
      font-weight:700;
      color:#64748b;
      text-transform:uppercase;
      letter-spacing:.4px;
    }

    .stat-value {
      font-size:36px;
      font-weight:800;
      color:#021428;
      margin-top:8px;
    }

    /* QUICK ACTIONS */
    .quick-grid {
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:24px;
    }

    .quick-card {
      background:#fff;
      border-radius:18px;
      padding:30px;
      box-shadow:0 10px 30px rgba(2,8,20,.08);
      transition:.3s;
      cursor:pointer;
    }

    .quick-card:hover {
      transform:translateY(-6px);
      box-shadow:0 20px 40px rgba(2,8,20,.12);
    }

    .quick-card h4 {
      font-weight:800;
      color:#021428;
      margin-bottom:10px;
    }

    .quick-card p {
      font-size:14px;
      color:#475569;
      line-height:1.6;
    }

    @media(max-width:1024px){
      .stat-grid { grid-template-columns:1fr 1fr; }
      .quick-grid { grid-template-columns:1fr; }
    }
  </style>
</head>

<body>

<!-- ================= ADMIN NAVBAR ================= -->
<?php include __DIR__ . '/partials/admin-navbar.php'; ?>
<!-- ================= DASHBOARD ================= -->
<main class="container admin-wrap">

  <h1 class="admin-title">Dashboard</h1>
  <p class="admin-sub">
    Welcome, <strong><?= htmlspecialchars($adminName) ?></strong> —
    Overview of valuation requests, reviews and business activity
  </p>

  <!-- STATS -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-label">Valuation Requests</div>
      <div class="stat-value">128</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Active Services</div>
      <div class="stat-value">14</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Client Reviews</div>
      <div class="stat-value">56</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Staff Members</div>
      <div class="stat-value">9</div>
    </div>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="quick-grid">

    <div class="quick-card" onclick="location.href='valuation-requests.html'">
      <h4>📑 New Valuation Requests</h4>
      <p>View and manage incoming valuation and site inspection requests.</p>
    </div>

    <div class="quick-card" onclick="location.href='clients.html'">
      <h4>⭐ Reviews & Clients</h4>
      <p>Manage customer reviews and companies you have worked with.</p>
    </div>

    <div class="quick-card" onclick="location.href='content.html'">
      <h4>📰 FAQ & Blog Content</h4>
      <p>Update FAQs and publish blog posts for SEO and client education.</p>
    </div>

  </div>

</main>

</body>
</html>
