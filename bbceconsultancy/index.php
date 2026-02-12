<?php
// ============================================================
// DYNAMIC SERVICES FOR INDEX.PHP (with full fixes)
// ============================================================
require_once __DIR__ . '/config/config.php';

/* ---------- Helpers ---------- */
function slugify($s) {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^\p{L}\p{N}]+/u', '-', $s);
    $s = trim($s,'-');
    return $s === '' ? 'uncategorized' : $s;
}

function resolve_image($filename) {
    $candidates = [];
    if ($filename) {
        $candidates[] = 'uploads/services/' . $filename;
        $candidates[] = 'uploads/projects/' . $filename;
        $candidates[] = 'assets/images/' . $filename;
        $candidates[] = $filename;
    }
    $candidates[] = 'aa.jpg';
    foreach ($candidates as $p) {
        if (!$p) continue;
        if (file_exists(__DIR__ . '/' . $p)) return $p;
    }
    return 'aa.jpg';
}

/* ---------- Fetch categories (active) ---------- */
$categories = [];
$cat_q = $conn->prepare("SELECT id, name, icon, image, status, sort_order FROM service_categories WHERE status = 1 ORDER BY sort_order ASC, id ASC");
$cat_q->execute();
$cat_res = $cat_q->get_result();
while ($c = $cat_res->fetch_assoc()) {
    $c['slug'] = slugify($c['name']);
    $categories[$c['id']] = $c;
}
$cat_q->close();

/* ---------- Fetch services (active) ---------- */
$services = [];
$svc_q = $conn->prepare("
    SELECT s.id, s.category_id, s.title, s.description, s.scope, s.image, s.status, s.sort_order, s.created_at
    FROM services s
    JOIN service_categories c ON s.category_id = c.id
    WHERE s.status = 1 AND c.status = 1
    ORDER BY c.sort_order ASC, s.sort_order ASC, s.created_at DESC
");
$svc_q->execute();
$svc_res = $svc_q->get_result();
while ($s = $svc_res->fetch_assoc()) {
    $cat = isset($categories[$s['category_id']]) ? $categories[$s['category_id']] : null;
    $s['category_name'] = $cat ? $cat['name'] : 'Uncategorized';
    $s['category_slug'] = $cat ? $cat['slug'] : slugify($s['category_name']);
    $s['category_icon'] = $cat ? $cat['icon'] : '';
    $s['image_path'] = resolve_image($s['image']);
    $services[] = $s;
}
$svc_q->close();

// ========== MAP DB CATEGORIES TO STATIC TAB VALUES ==========
function get_tab_cat($category_name) {
    $name = strtolower($category_name);
    if (strpos($name, 'valuation') !== false) return 'full';
    if (strpos($name, 'design') !== false || strpos($name, 'drawing') !== false) return 'combo';
    if (strpos($name, 'supervision') !== false) return 'mock';
    return 'full'; // fallback
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title> BBC — Engineering Consultantancy Pvt.Ltd</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome (fixes icons in modal and close button) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="style.css"> 
  <link rel="script" href="script.js">

  <!-- ========== MODAL CSS (professional, responsive) ========== -->
  <style>
    /* ==================== */
    /*   MODAL — PERFECT    */
    /*   PROFESSIONAL       */
    /* ==================== */

    .svc-modal {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 12000;
      background: rgba(2, 20, 40, 0.8);
      backdrop-filter: blur(10px);
      padding: 20px;
      box-sizing: border-box;
    }
    .svc-modal.active {
      display: flex;
    }

    .svc-modal {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 12000;
      background: rgba(2, 20, 40, 0.75);
      backdrop-filter: blur(8px);
      padding: 24px;
    }

    .svc-modal.active {
      display: flex;
    }

    .svc-card {
      width: 100%;
      max-width: 1100px;
      background: #ffffff;
      border-radius: 24px;
      box-shadow: 0 40px 80px -12px rgba(0,0,0,0.35), 0 18px 36px -18px rgba(0,0,0,0.2);
      animation: modalSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    @keyframes modalSlideIn {
      from { opacity: 0.8; transform: scale(0.98) translateY(12px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* ----- HEADER ----- */
    .svc-header {
      padding: 20px 28px;
      background: linear-gradient(98deg, #e6f2ff, #dceeff);
      border-bottom: 1px solid rgba(2, 20, 40, 0.06);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    /* override inline style for icon container */
    .svc-header div[style*="display:flex"] {
      /* width: 54px !important; */
      height: 54px !important;
      /* background: rgba(255,255,255,0.9) !important; */
      border-radius: 16px !important;
      /* box-shadow: 0 6px 18px rgba(0,108,255,0.12) !important; */
      color: #021428;
      font-size: 24px;
    }

    .svc-title {
      font-family: 'Poppins', sans-serif;
      font-size: 22px;
      font-weight: 800;
      line-height: 1.2;
      color: #021428;
      margin-bottom: 4px;
    }

    #svcCategory {
      font-size: 14px !important;
      font-weight: 600;
      color: #2c4b66 !important;
      background: rgba(44,75,102,0.06);
      display: inline-block;
      padding: 4px 14px;
      border-radius: 40px;
      letter-spacing: 0.3px;
      margin-top: 6px !important;
    }

    /* close button */
    #svcCloseBtn {
      width: 44px;
      height: 44px;
      border-radius: 14px;
      border: none;
      background: rgba(255,255,255,0.8);
      backdrop-filter: blur(4px);
      color: #1e3b5a;
      font-size: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.15s;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(2,8,20,0.04);
    }
    #svcCloseBtn:hover {
      background: white;
      color: #021428;
      box-shadow: 0 10px 22px rgba(2,108,255,0.16);
      transform: scale(0.96);
    }
    #svcCloseBtn i {
      font-size: 20px;
    }

    /* ----- BODY (GRID) ----- */
    .svc-body {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 28px;
      padding: 28px;
      background: #ffffff;
    }

    /* left column */
    .svc-left {
      display: flex;
      flex-direction: column;
    }

    .svc-image {
      width: 100%;
      height: 280px;
      border-radius: 20px;
      overflow: hidden;
      background: #f0f6fd;
      box-shadow: 0 8px 22px rgba(2,20,40,0.06);
      border: 1px solid rgba(255,255,255,0.4);
    }
    .svc-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.3s ease;
    }
    .svc-image:hover img {
      transform: scale(1.02);
    }

    /* description area */
    .svc-desc {
      margin-top: 22px;
      font-size: 16px;
      line-height: 1.7;
      color: #1f384e;
      background: #fafcff;
      padding: 20px;
      border-radius: 18px;
      border-left: 5px solid #5f9ef0;
      font-weight: 450;
    }

    /* ----- RIGHT PANEL (aside) ----- */
    .svc-right {
      background: #f8fcff;
      border-radius: 24px;
      padding: 26px 20px;
      border: 1px solid rgba(100,140,200,0.12);
      box-shadow: inset 0 1px 4px rgba(255,255,255,0.8), 0 8px 18px rgba(2,40,80,0.04);
    }

    .svc-meta {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      padding: 16px 0;
      border-bottom: 1px solid rgba(2,20,40,0.06);
    }
    .svc-meta:last-of-type {
      border-bottom: none;
    }

    .svc-meta .label {
      font-size: 15px;
      font-weight: 600;
      color: #2f5670;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .svc-meta .label i {
      width: 20px;
      color: #3d7eb9;
      font-size: 16px;
    }

    .svc-meta .value {
      font-weight: 700;
      color: #021428;
      background: white;
      padding: 6px 14px;
      border-radius: 60px;
      font-size: 14px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.02);
      max-width: 180px;
      text-align: right;
      word-break: break-word;
    }
    .svc-meta .value a {
      color: #021428;
      text-decoration: none;
      font-weight: 600;
    }
    .svc-meta .value a:hover {
      text-decoration: underline;
      color: #0b4b7a;
    }

    /* ----- ACTION BUTTONS (right panel) ----- */
    .svc-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 28px;
    }

    /* button styles within modal */
    .svc-modal .btn-ghost,
    .svc-modal .btn-primary {
      flex: 1 1 auto;
      padding: 12px 16px;
      border-radius: 14px;
      font-weight: 700;
      font-size: 15px;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: 0.16s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .svc-modal .btn-ghost {
      background: white;
      border: 1.5px solid #e1ecf5;
      color: #1e3f5c;
    }
    .svc-modal .btn-ghost:hover {
      background: #f2f9ff;
      border-color: #98b9d6;
      transform: translateY(-2px);
      box-shadow: 0 12px 22px rgba(66,153,225,0.12);
    }

    .svc-modal .btn-primary {
      background: linear-gradient(105deg, #00c6ff, #5f7eff);
      color: #021428;
      border: none;
      font-weight: 800;
    }
    .svc-modal .btn-primary:hover {
      background: linear-gradient(105deg, #0099ff, #4c6ef0);
      transform: translateY(-2px);
      box-shadow: 0 16px 28px rgba(79,131,255,0.28);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 900px) {
      .svc-body {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      .svc-image {
        height: 240px;
      }
      .svc-card {
        max-width: 92vw;
      }
      .svc-header {
        padding: 18px 22px;
      }
      .svc-title {
        font-size: 20px;
      }
    }

    @media (max-width: 480px) {
      .svc-modal { padding: 16px; }
      .svc-body { padding: 20px; }
      .svc-image { height: 190px; }
      .svc-desc { padding: 16px; font-size: 15px; }
      .svc-right { padding: 20px 16px; }
      .svc-meta .value { max-width: 140px; }
      .svc-actions { flex-direction: column; }
    }

    /* --------------------
   Mobile-friendly modal patch for .svc-card
   Add this at the end of your stylesheet
   -------------------- */

/* Make modal limited by viewport and scrollable on small devices */
.svc-card {
  width: 100%;
  max-width: 1100px;                /* keep desktop cap */
  max-height: calc(100vh - 48px);   /* leave breathing room for status bar */
  margin: 0 auto;
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 40px 80px -12px rgba(0,0,0,0.35), 0 18px 36px -18px rgba(0,0,0,0.2);
  animation: modalSlideIn 0.3s cubic-bezier(0.16,1,0.3,1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
}

/* ensure inner content can scroll when modal exceeds viewport */
.svc-card .svc-body,
.svc-card > * {
  -webkit-overflow-scrolling: touch;
}

.svc-modal.active {
  /* allow body-level scrolling inside modal when modal content is tall */
  align-items: center;
  justify-content: center;
  padding: 12px;
}

/* Small / medium phones: stack layout and make things tappable */
@media (max-width: 720px) {
  .svc-card {
    max-width: 100%;
    border-radius: 18px;
    max-height: calc(100vh - 24px);
  }

  /* header adjustments */
  .svc-header {
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .svc-title { font-size: 18px; margin-bottom: 0; }
  #svcCategory { font-size: 13px; padding: 3px 12px; }

  /* place close button in top-right inside header for easier reach */
  #svcCloseBtn {
    position: absolute;
    right: 10px;
    top: 10px;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    z-index: 3;
  }

  /* Stack main + aside vertically */
  .svc-body {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
    padding: 18px;
  }

  .svc-image { height: 190px; border-radius: 14px; }
  .svc-desc { margin-top: 12px; padding: 14px; font-size: 15px; border-radius: 14px; }

  /* make right panel full width under left block */
  .svc-right {
    width: 100%;
    padding: 18px 14px;
    border-radius: 14px;
  }

  .svc-meta { padding: 12px 0; }
  .svc-meta .value { max-width: 160px; font-size: 14px; padding: 8px 12px; }

  /* actions take full width stacked */
  .svc-actions { flex-direction: column; gap: 12px; margin-top: 18px; }
  .svc-modal .btn-ghost,
  .svc-modal .btn-primary { width: 100%; padding: 12px 14px; border-radius: 12px; }

  /* ensure modal content scrolls internally rather than pushing viewport */
  .svc-card { overflow: hidden; }
  .svc-card > .svc-body { overflow-y: auto; max-height: calc(100vh - 180px); padding-right: 6px; }
}

/* Very small phones */
@media (max-width: 400px) {
  .svc-header { padding: 12px 12px; }
  .svc-title { font-size: 16px; }
  .svc-image { height: 150px; }
  .svc-desc { font-size: 14px; padding: 12px; }
  .svc-meta .value { max-width: 130px; font-size: 13px; padding: 7px 10px; }
  .svc-card { border-radius: 12px; }
  #svcCloseBtn { right: 8px; top: 8px; width: 40px; height: 40px; border-radius: 10px; }
}

/* Accessibility: ensure focusable controls remain visible */
.svc-card :focus {
  outline: 3px solid rgba(90,160,255,0.14);
  outline-offset: 2px;
}


    /* ========== FIX: Our Circle Toppers mobile visibility ========== */
    .toppers-carousel {
      position: relative;
      width: 100%;
      overflow: hidden;
      margin-top: 20px;
    }
    .carousel-wrapper {
      width: 100%;
    }
    .carousel-track {
      display: flex;
      transition: transform 0.5s ease;
    }
    .carousel-slide {
      flex: 0 0 100%;
      max-width: 100%;
      padding: 20px 0;
      box-sizing: border-box;
    }
    @media (min-width: 821px) {
      .carousel-slide {
        flex: 0 0 auto;
        margin-right: 30px;
      }
    }
    /* Ensure slides are visible on mobile */
    @media (max-width: 820px) {
      .carousel-track {
        display: flex;
        width: 100%;
      }
      .carousel-slide {
        flex: 0 0 100%;
        opacity: 1;
        position: relative;
        transition: opacity 0.5s ease;
      }
      .carousel-slide.active {
        display: block;
      }
      .carousel-slide:not(.active) {
        display: none;
      }
    }

    /* Mobile-only carousel behaviour (max-width:820px) */
/* Mobile Sliding Animation */
@media (max-width: 820px) {

  .carousel-wrapper {
    overflow: hidden;
    position: relative;
  }

  .carousel-track {
    display: flex;
    transition: transform 0.6s cubic-bezier(.4,0,.2,1);
    width: 100%;
  }

  .carousel-slide {
    flex: 0 0 100%;
    max-width: 100%;
  }

}

/* ===== OUR CIRCLE TOPPERS – MOBILE ENHANCEMENT ===== */
@media (max-width: 820px) {
  /* 1. Make the avatar images LARGER */
  .carousel-slide .avatar {
    width: 240px !important;
    height: 240px !important;
    margin-top: 5px !important;
    margin-bottom: 5px !important;
  }

  /* 2. Reduce top & bottom gaps around the whole section */
  #toppers {
    margin-top: 0;
    margin-bottom: 0;
    padding-top: 0;
    padding-bottom: 10px;
  }

  /* 3. Tighter spacing for badge and heading */
  #toppers .badge {
    margin-bottom: 4px;
  }
  #toppers h3 {
    margin-top: 6px !important;
    margin-bottom: 4px;
  }

  /* 4. Reduce the heavy bottom shadow */
  .carousel-slide .avatar {
    box-shadow: 0 0 0 2px #ffffff, 0 8px 4px rgba(2,8,20,0.5) !important;
  }
}

  </style>
</head>
<body>

  <!-- NAV (unchanged) -->
   <?php include __DIR__ . '/includes/navbar.php'; ?>

  <!-- HERO (unchanged) -->
  <main class="container">
    <section class="hero" id="home" aria-label="Hero">
      <div class="hero-left">
        <div class="eyebrow">Trusted by postal & tech pros • BBC verified</div>
        <h2 class="hero-title">
          Trusted <span class="highlight">Engineering & Property Valuation</span><br>
          Services Across Nepal
        </h2>
        <p class="lead">
          BBC Engineering Consultancy Pvt. Ltd. is a registered engineering and property valuation firm providing
          accurate, transparent, and NRB-compliant valuation services across Nepal.
          We specialize in residential, commercial, land, and bank valuation, along with architectural design,
          structural design, and professional site supervision services. Our team supports clients, banks, and
          institutions from documentation and inspection to final valuation and execution guidance.
        </p>
        <div class="hero-ctas" style="align-items:center">
          <a href="contact_us.php"class="btn-primary" id="ctaEnroll">Request Valuation</a>
          <a href="contact_us.php" class="btn-outline" id="ctaDemo">View Our Services</a>
          <div style="margin-left:12px" class="kpi-pill">Bank & Legal Approved</div>
        </div>

        <div class="order-track" aria-hidden="true" style="margin-top:26px">
          <svg class="track-svg" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
            <defs>
              <linearGradient id="g1" x1="0" x2="1">
                <stop offset="0" stop-color="#00D4FF" stop-opacity="0.9"/>
                <stop offset="1" stop-color="#635BFF" stop-opacity="0.9"/>
              </linearGradient>
            </defs>
            <path id="trackPath" d="M20 80 C 200 10, 400 140, 600 80 C 760 30, 900 140, 1180 60" stroke="url(#g1)" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M20 80 C 200 10, 400 140, 600 80 C 760 30, 900 140, 1180 60" stroke="rgba(255,255,255,0.03)" stroke-width="36" fill="none" stroke-linecap="round"/>
            <g id="moving" transform="translate(0,0)">
              <circle cx="0" cy="0" r="10" fill="#00D4FF" />
              <circle cx="0" cy="0" r="16" fill="rgba(0,212,255,0.08)" />
            </g>
          </svg>
          <div class="track-steps">
            <div class="step active">Browse</div>
            <div class="step">Enroll</div>
            <div class="step">Learn</div>
            <div class="step">Test</div>
            <div class="step">Promoted</div>
          </div>
        </div>
      </div>
      <div class="hero-right" aria-hidden="true">
        <div class="glow-ring"></div>
        <div class="device-card" aria-hidden="true">
          <img src="unnamed.jpg" alt="hero illustration" />
          <div class="floating-icons" style="position:absolute;right:8px;top:12px;">
            <img class="float-a" src="WhatsApp Image 2025-12-11 at 18.41.35_af569130.jpg" alt="">
            <img class="float-b" src="WhatsApp Image 2025-12-11 at 18.44.09_70ffb918.jpg" alt="">
            <img class="float-c" src="WhatsApp Image 2025-12-11 at 18.43.06_b9ac1ef2.jpg" alt="">
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Auth overlay (unchanged) -->
  <div class="auth-overlay" id="authOverlay">
    <div class="auth-card">
      <h3>Account Access</h3>
      <p>Login or create a new account to continue</p>
      <button class="auth-main login">Login</button>
      <button class="auth-main signup">Sign Up</button>
      <div class="divider"><span>or continue with</span></div>
      <a href="#" class="forgot">Forgot password?</a>
    </div>
  </div>

  <!-- ACHIEVEMENTS BAND (unchanged) -->
  <section class="band">
    <div class="container" style="text-align:center">
      <div class="reach-card">
        <div class="badge">Our Reach</div>
        <h3>Trusted by Banks, Institutions & Individuals Across Nepal</h3>
        <p class="reach-desc">
          BBC Engineering Consultancy Pvt. Ltd. is trusted by leading banks, financial institutions,
          government offices, corporate firms, and individual clients for accurate valuation,
          professional design, and reliable supervision services.
        </p>
      </div>
    </div>
  </section>

  <!-- TOPPERS (Carousel fixed for mobile) -->
  <section id="toppers">
  <div class="container" style="text-align:center;margin-bottom:20px;">
    <div class="badge" style="background:linear-gradient(90deg,var(--accent-2),var(--accent-1));display:inline-block;padding:8px 16px;border-radius:18px;color:#021428;font-weight:800">Top Achievers</div>
    <h3 style="margin-top:12px;font-family:Poppins">Our Circle Toppers</h3>

    <!-- Carousel Container - Mobile single slide -->
    <!-- Carousel Container - Mobile single slide -->
<div class="toppers-carousel">
  <div class="carousel-wrapper">
    <div class="carousel-track">
      <!-- Original slides -->
      <div class="carousel-slide">
        <div class="avatar" style="width:200px;height:200px;border-radius:50%;border:12px solid var(--accent-1);overflow:hidden;box-shadow:0 0 0 2px #ffffff, 0 18px 4px rgba(2,8,20,0.7);margin:0 auto">
          <img src="top1.webp" alt="Topper 1">
        </div>
      </div>
      
      <div class="carousel-slide">
        <div class="avatar" style="width:200px;height:200px;border-radius:50%;border:12px solid var(--accent-2);overflow:hidden;box-shadow:0 0 0 2px #ffffff,0 18px 4px rgba(2,8,20,0.7);margin:0 auto">
          <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=800&auto=format&fit=crop&ixlib=rb-4.0.3&s=abcd" alt="Topper 2">
        </div>
      </div>
      
      <div class="carousel-slide">
        <div class="avatar" style="width:200px;height:200px;border-radius:50%;border:12px solid var(--accent-1);overflow:hidden;box-shadow:0 0 0 2px #ffffff,0 18px 4px rgba(2,8,20,0.7);margin:0 auto">
          <img src="top3.webp" alt="Topper 3">
        </div>
      </div>
      
      <!-- DUPLICATE SLIDES FOR CONTINUOUS EFFECT ON PC -->
      <div class="carousel-slide">
        <div class="avatar" style="width:200px;height:200px;border-radius:50%;border:12px solid var(--accent-1);overflow:hidden;box-shadow:0 0 0 2px #ffffff, 0 18px 4px rgba(2,8,20,0.7);margin:0 auto">
          <img src="top1.webp" alt="Topper 1">
        </div>
      </div>
      
      <div class="carousel-slide">
        <div class="avatar" style="width:200px;height:200px;border-radius:50%;border:12px solid var(--accent-2);overflow:hidden;box-shadow:0 0 0 2px #ffffff,0 18px 4px rgba(2,8,20,0.7);margin:0 auto">
          <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=800&auto=format&fit=crop&ixlib=rb-4.0.3&s=abcd" alt="Topper 2">
        </div>
      </div>
    </div>
  </div>
</div>
  </div>
</section>

  <!-- ========== DYNAMIC SERVICES SECTION (courses) ========== -->
  <section id="courses" class="courses">
    <div class="container">
      <div class="tabs tabs-heading">
        <div class="tabs-card">
          <h3 class="tabs-title">Our Professional Services</h3>
          <p class="tabs-subtitle">
            Comprehensive property valuation, engineering design, and site supervision services
            delivered with accuracy, compliance, and professional integrity.
          </p>
        </div>
      </div>


      <div class="course-grid" style="margin-top:24px" id="courseGrid">
        <?php if (!empty($services)): ?>
          <?php foreach ($services as $svc): 
            $title = htmlspecialchars(trim($svc['title'] ?: 'Untitled Service'));
            $desc = trim($svc['description'] ?: ($svc['scope'] ?: 'Professional service'));
            $desc_short = htmlspecialchars(mb_substr($desc, 0, 60) . (strlen($desc) > 60 ? '…' : ''));
            $img = htmlspecialchars($svc['image_path']);
            $cat_slug = get_tab_cat($svc['category_name']); // map to static tab values
          ?>
            <article class="course-card" 
                     data-id="<?php echo (int)$svc['id']; ?>" 
                     data-cat="<?php echo $cat_slug; ?>" 
                     tabindex="0">
              <div class="course-thumb"><img src="<?php echo $img; ?>" alt="<?php echo $title; ?>"></div>
              <h4 class="course-title"><?php echo $title; ?></h4>
              <div class="course-meta">
                <div style="color:#021428"><?php echo $desc_short ?: '&nbsp;'; ?></div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Fallback static cards if no services in DB -->
          <article class="course-card" data-id="0" data-cat="full" tabindex="0">
            <div class="course-thumb"><img src="pasa.jpg" alt=""></div>
            <h4 class="course-title">🏠 Residential Property Valuation</h4>
            <div class="course-meta"><div style="color:#021428">Complete valuation reports</div></div>
          </article>
          <article class="course-card" data-id="0" data-cat="full" tabindex="0">
            <div class="course-thumb"><img src="aa.jpg" alt=""></div>
            <h4 class="course-title">🏢 Commercial Property Valuation</h4>
            <div class="course-meta"><div style="color:#021428">Shops, offices & complexes</div></div>
          </article>
          <article class="course-card" data-id="0" data-cat="combo" tabindex="0">
            <div class="course-thumb"><img src="ca.jpg" alt=""></div>
            <h4 class="course-title">🏢 Architectural Design</h4>
            <div class="course-meta"><div style="color:#021428">Concept to approval drawings</div></div>
          </article>
          <article class="course-card" data-id="0" data-cat="mock" tabindex="0">
            <div class="course-thumb"><img src="se.jpg" alt=""></div>
            <h4 class="course-title">🏗 House & Building Construction Supervision</h4>
            <div class="course-meta"><div style="color:#021428">Quality & progress control</div></div>
          </article>
        <?php endif; ?>
      </div>
    </div>

    <div class="pagination-wrap">
      <button class="page-btn" id="prevPage">Prev</button>
      <span class="page-info" id="pageInfo"></span>
      <button class="page-btn" id="nextPage">Next</button>
    </div>
  </section>

  <!-- WHY CHOOSE / FEATURES (unchanged) -->
  <section class="why">
    <div class="container">
      <div class="why-head">
        <span class="badge">Why BBC</span>
        <h3>Why Choose BBC Engineering Consultancy</h3>
        <p>
          We provide independent, accurate, and NRB-compliant valuation and
          engineering services trusted by banks, government offices, and
          individual clients across Nepal.
        </p>
      </div>
      <div class="why-grid">
        <div class="why-card">
          <div class="why-icon">📜</div>
          <h4>Registered & Compliant</h4>
          <p>Valuation reports prepared in accordance with Nepal Rastra Bank (NRB) and bank-specific guidelines.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">🏗</div>
          <h4>Technical Expertise</h4>
          <p>Experienced engineers and valuators with strong knowledge of Nepalese construction and property markets.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">⏱</div>
          <h4>Timely & Reliable Service</h4>
          <p>Prompt site inspection, documentation review, and delivery of professional reports without delay.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS (unchanged) -->
  <section class="testimonials">
    <div class="container">
      <div style="text-align:center">
        <div class="badge" style="background:linear-gradient(90deg,var(--accent-1),var(--accent-2));display:inline-block;padding:6px 12px;border-radius:12px;color:#021428;font-weight:800">
          Client Feedback
        </div>
        <h3 style="margin-top:12px">Trusted by Banks, Institutions & Individuals</h3>
      </div>
      <div class="test-grid" style="margin-top:20px">
        <div class="testimonial">
          <div style="display:flex;gap:12px;align-items:center">
            <img src="https://via.placeholder.com/64" style="width:64px;height:64px;border-radius:50%;border:3px solid rgba(255,255,255,0.02)" alt="">
            <div><div style="font-weight:800">Bank Officer</div><div style="color:#ffb400">★★★★★</div></div>
          </div>
          <p style="color:#021428;margin-top:12px">“BBC Engineering Consultancy provides accurate, NRB-compliant valuation reports that we regularly rely on for loan and mortgage processing.”</p>
        </div>
        <div class="testimonial">
          <div style="display:flex;gap:12px;align-items:center">
            <img src="https://via.placeholder.com/64" style="width:64px;height:64px;border-radius:50%;border:3px solid rgba(255,255,255,0.02)" alt="">
            <div><div style="font-weight:800">Corporate Client</div><div style="color:#ffb400">★★★★★</div></div>
          </div>
          <p style="color:#021428;margin-top:12px">“Their professional approach, timely site inspection, and clear documentation made the valuation process smooth and reliable.”</p>
        </div>
        <div class="testimonial">
          <div style="display:flex;gap:12px;align-items:center">
            <img src="https://via.placeholder.com/64" style="width:64px;height:64px;border-radius:50%;border:3px solid rgba(255,255,255,0.02)" alt="">
            <div><div style="font-weight:800">Individual Property Owner</div><div style="color:#ffb400">★★★★★</div></div>
          </div>
          <p style="color:#021428;margin-top:12px">“BBC explained the valuation process clearly and delivered the report on time. Highly professional and trustworthy service.”</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ SECTION (unchanged) -->
  <section id="faq" class="faq-section">
    <div class="container">
      <h2 class="faq-title">Frequently Asked Questions</h2>
      <div class="faq-grid">
        <div class="faq-card" onclick="openFaqModal('Is the valuation report NRB compliant?','Yes. All valuation reports prepared by BBC Engineering Consultancy Pvt. Ltd. follow Nepal Rastra Bank (NRB) guidelines and are accepted by banks and financial institutions across Nepal.')">
          <h4>Is the valuation report NRB compliant?</h4>
          <p>Tap to read answer</p>
        </div>
        <div class="faq-card" onclick="openFaqModal('How long does the valuation process take?','The valuation process generally takes 2 to 5 working days, depending on property type, document availability, and site inspection requirements.')">
          <h4>How long does the valuation process take?</h4>
          <p>Tap to read answer</p>
        </div>
        <div class="faq-card" onclick="openFaqModal('Is site inspection mandatory for valuation?','Yes. Physical site inspection is mandatory to verify property details, construction status, location factors, and market conditions before issuing a valid valuation report.')">
          <h4>Is site inspection mandatory for valuation?</h4>
          <p>Tap to read answer</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ MODAL (unchanged) -->
  <div class="modal fade" id="faqModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content faq-modal-content">
        <div class="modal-header">
          <h5 id="faqModalTitle"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="faqModalBody"></div>
      </div>
    </div>
  </div>

  <!-- ========== SERVICE DETAIL MODAL (same as project.php) ========== -->
  <div id="svcModal" class="svc-modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="svc-card" role="document" aria-labelledby="svcTitle">
      <div class="svc-header">
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:46px;height:46px;border-radius:10px;background:rgba(255,255,255,0.7);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px rgba(2,8,20,.08)">
            <i class="fas fa-building" style="font-size:20px"></i>
          </div>
          <div>
            <div id="svcTitle" class="svc-title">Service Title</div>
            <div style="font-size:13px;color:#022032;margin-top:4px" id="svcCategory">Category</div>
          </div>
        </div>
        <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
          <button class="btn-ghost" id="svcCloseBtn" aria-label="Close details"><i class="fas fa-times"></i></button>
        </div>
      </div>

      <div class="svc-body">
        <div class="svc-left">
          <div class="svc-image" id="svcImageWrap">
            <img id="svcImage" src="" alt="">
          </div>
          <div style="margin-top:8px">
            <div style="font-weight:700;color:#64748b;font-size:13px;margin-bottom:6px">
              <i class="fas fa-info-circle" style="margin-right:8px"></i>Details
            </div>
            <div id="svcDescription" class="svc-desc">—</div>
          </div>
        </div>

        <aside class="svc-right">
          <div class="svc-meta">
            <div class="label"><i class="fas fa-calendar-alt"></i> Submitted</div>
            <div class="value" id="svcCreated">—</div>
          </div>
          <div class="svc-meta">
            <div class="label"><i class="fas fa-layer-group"></i> Scope</div>
            <div class="value" id="svcScope">—</div>
          </div>
          <div class="svc-meta">
            <div class="label"><i class="fas fa-envelope"></i> Email</div>
            <div class="value"><a id="svcEmail" href="mailto:bhandari.krishna01@gmail.com">bhandari.krishna01@gmail.com</a></div>
          </div>
          <div class="svc-meta">
            <div class="label"><i class="fas fa-phone"></i> Phone</div>
            <div class="value"><a id="svcPhone" href="tel:+9779858422178">+977 9858422178</a></div>
          </div>

          <div class="svc-actions">
            <button class="btn-ghost" id="svcMailBtn"><i class="fas fa-envelope"></i> Email</button>
            <button class="btn-ghost" id="svcCallBtn"><i class="fas fa-phone"></i> Call</button>
            <button class="btn-primary" id="svcContactBtn"><i class="fas fa-paper-plane" style="margin-right:8px"></i> Contact Us</button>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <!-- FOOTER (unchanged) -->
  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-brand">
          <div class="footer-logo">
            <div class="logo-mark">BBC</div>
            <h3>Engineering Consultantancy Pvt.Ltd</h3>
          </div>
          <p class="footer-tagline">Empowering learners with cutting-edge education</p>
          <div class="footer-copyright">&copy; <strong>BBC</strong> — 2025 • All rights reserved</div>
        </div>
        <div class="footer-contact">
          <h4>Contact Information</h4>
          <div class="contact-item">
            <div class="contact-icon">📍</div>
            <div class="contact-details">
              <p>Ward 5, Dhangadhi sub-metropolitan city</p>
              <p>Kailali, Sudurpaschim pradesh, Nepal</p>
              <p class="address-note">Near North Gate of Dhangadhi stadium</p>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">✉️</div>
            <div class="contact-details">
              <a href="mailto:bhandari.krishna01@gmail.com">bhandari.krishna01@gmail.com</a>
            </div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">📞</div>
            <div class="contact-details">
              <a href="tel:+9779858422178">+977 9858422178</a>
            </div>
          </div>
        </div>
        <div class="footer-social">
          <h4>Follow Us</h4>
          <div class="social-icons">
            <a href="#" class="social-icon facebook">f</a>
            <a href="#" class="social-icon twitter">t</a>
            <a href="#" class="social-icon instagram">ig</a>
            <a href="#" class="social-icon linkedin">in</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Floating WhatsApp CTA (unchanged) -->
  <a href="https://wa.me/9779858422178" target="_blank" style="position:fixed;right:16px;bottom:18px;background:#25D366;width:64px;height:64px;border-radius:999px;display:grid;place-items:center;box-shadow:0 3px 2px rgba(1, 4, 12, 1);border: 2px solid rgba(255, 255, 255, 1);z-index:80">
    <svg width="34" height="34" viewBox="0 0 24 24"><path fill="#fff" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.272-.099-.47-.149-.67.15-.198.297-.767.967-.94 1.165-.173.198-.347.223-.644.074-1.758-.867-2.905-1.54-4.07-3.313-.308-.53.308-.495.89-1.64.099-.198.05-.372-.025-.522-.075-.149-.67-1.611-.92-2.207-.242-.579-.487-.5-.67-.51-.173-.009-.372-.01-.57-.01-.198 0-.52.074-.793.372s-1.04 1.017-1.04 2.479 1.065 2.876 1.213 3.074c.148.198 2.095 3.2 5.076 4.487 3 .8 3.003.534 3.545.5.543-.034 1.758-.72 2.006-1.413.248-.693.248-1.287.173-1.413-.074-.125-.272-.198-.57-.347z"/></svg>
  </a>

  <!-- ========== SCRIPTS ========== -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- SERVICES DATA (JSON) -->
  <script>
    const SERVICES = <?php echo json_encode(array_map(function($s){
        return [
          'id' => (int)$s['id'],
          'title' => $s['title'],
          'description' => $s['description'] ?? '',
          'scope' => $s['scope'] ?? '',
          'image_path' => $s['image_path'],
          'category_name' => $s['category_name'],
          'category_slug' => $s['category_slug'],
          'created_at' => $s['created_at'],
        ];
    }, $services), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

    const COMPANY_EMAIL = "bhandari.krishna01@gmail.com";
    const COMPANY_PHONE = "+977 9858422178";
  </script>

  <!-- MODAL & DYNAMIC BEHAVIOR -->
  <script>
    // Utilities: escape HTML
    function escapeHtml(unsafe) {
      if (unsafe === null || unsafe === undefined) return '';
      return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    // Modal handling
    (function(){
      const modal = document.getElementById('svcModal');
      const svcTitle = document.getElementById('svcTitle');
      const svcCategory = document.getElementById('svcCategory');
      const svcImage = document.getElementById('svcImage');
      const svcDescription = document.getElementById('svcDescription');
      const svcScope = document.getElementById('svcScope');
      const svcCreated = document.getElementById('svcCreated');
      const svcEmail = document.getElementById('svcEmail');
      const svcPhone = document.getElementById('svcPhone');
      const svcMailBtn = document.getElementById('svcMailBtn');
      const svcCallBtn = document.getElementById('svcCallBtn');
      const svcContactBtn = document.getElementById('svcContactBtn');
      const svcCloseBtn = document.getElementById('svcCloseBtn');

      function openModalFor(service) {
        svcTitle.textContent = service.title || 'Service';
        svcCategory.textContent = service.category_name || '';
        svcImage.src = service.image_path || 'aa.jpg';
        svcImage.alt = service.title || 'Service image';
        svcDescription.innerHTML = escapeHtml(service.description || '').replace(/\n/g, '<br>');
        svcScope.textContent = (service.scope && service.scope.length) ? service.scope : '—';
        svcCreated.textContent = service.created_at ? service.created_at : '—';
        svcEmail.href = 'mailto:' + COMPANY_EMAIL;
        svcEmail.textContent = COMPANY_EMAIL;
        svcPhone.href = 'tel:' + COMPANY_PHONE;
        svcPhone.textContent = COMPANY_PHONE;

        svcMailBtn.onclick = function(){
          window.location.href = 'mailto:' + COMPANY_EMAIL + '?subject=' + encodeURIComponent('Inquiry: ' + (service.title || 'Service'));
        };
        svcCallBtn.onclick = function(){
          window.location.href = 'tel:' + COMPANY_PHONE;
        };
        svcContactBtn.onclick = function(){
          const url = 'contact_us.php' + (service.id ? '?service_id=' + encodeURIComponent(service.id) : '');
          window.location.href = url;
        };

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        svcContactBtn.focus();
      }

      function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
      }

      // Click on service card
      document.getElementById('courseGrid').addEventListener('click', function(e){
        const card = e.target.closest('.course-card');
        if (!card) return;
        const sid = parseInt(card.getAttribute('data-id') || 0, 10);
        if (!sid) return;
        const svc = SERVICES.find(s => s.id === sid);
        if (!svc) return;
        openModalFor(svc);
      });

      svcCloseBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', function(e){
        if (e.target === modal) closeModal();
      });
      window.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
      });

      // Focus trap
      modal.addEventListener('keydown', function(e){
        if (e.key === 'Tab') {
          const focusable = modal.querySelectorAll('button, a[href], input, textarea, select');
          if (!focusable.length) return;
          const first = focusable[0];
          const last = focusable[focusable.length - 1];
          if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
          } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
          }
        }
      });
    })();
  </script>

  <!-- Existing scripts (pagination, tabs, animations, etc.) - all preserved -->
  <script>
    // 1) ORDER TRACK PATH: animate the "moving" group along SVG path
    (function () {
      const path = document.getElementById('trackPath');
      const moving = document.getElementById('moving');
      if (path && moving) {
        const pathLen = path.getTotalLength();
        let start = null;
        let duration = 7000;
        let direction = 1;
        function step(ts) {
          if (!start) start = ts;
          let t = (ts - start) % duration;
          let progress = t / duration;
          let p = direction === 1 ? progress : 1 - progress;
          let pointAt = path.getPointAtLength(p * pathLen);
          moving.setAttribute('transform', `translate(${pointAt.x}, ${pointAt.y - 10})`);
          const steps = document.querySelectorAll('.track-steps .step');
          steps.forEach((s, i) => s.classList.remove('active'));
          let idx = Math.min(steps.length - 1, Math.floor(p * steps.length));
          steps[idx].classList.add('active');
          if ((progress >= 0.999 && direction === 1) || (progress >= 0.999 && direction === -1)) {
            direction *= -1;
          }
          requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      }
    })();

    // 2) Floating shapes: small parallax on mouse move
    (function () {
      const shapes = document.querySelectorAll('.floating-shapes .shape');
      if (!shapes.length) return;
      document.addEventListener('mousemove', (e) => {
        const cx = window.innerWidth / 2;
        const cy = window.innerHeight / 2;
        const dx = (e.clientX - cx) / cx;
        const dy = (e.clientY - cy) / cy;
        shapes.forEach((el, i) => {
          const depth = (i + 1) * 6;
          el.style.transform = `translate3d(${dx * depth}px, ${dy * depth}px, 0) rotate(${dx * depth}deg)`;
        });
      });
    })();

    // 3) Tabs filter for courses (dynamic)
    (function () {
      const tabs = document.querySelectorAll('.courses .tab');
      const cards = () => document.querySelectorAll('.course-card');
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('active'));
          tab.classList.add('active');
          const cat = tab.dataset.cat;
          cards().forEach(c => {
            if (cat === 'all' || c.dataset.cat === cat) {
              c.style.display = 'block';
            } else {
              c.style.display = 'none';
            }
          });
          setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
        });
      });
    })();

    // 4) Pagination
    (function () {
      const allCards = Array.from(document.querySelectorAll('.course-card'));
      const prevBtn = document.getElementById('prevPage');
      const nextBtn = document.getElementById('nextPage');
      const pageInfo = document.getElementById('pageInfo');
      let currentPage = 1;
      let activeCards = [...allCards];

      function cardsPerPage() {
        return window.innerWidth <= 480 ? 3 : 8;
      }

      function render() {
        const perPage = cardsPerPage();
        const totalPages = Math.ceil(activeCards.length / perPage) || 1;
        activeCards.forEach((card, index) => {
          card.style.display =
            index >= (currentPage - 1) * perPage &&
            index < currentPage * perPage
              ? 'block'
              : 'none';
        });
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
      }

      function applyFilter(category) {
        activeCards = allCards.filter(card =>
          category === 'all' || card.dataset.cat === category
        );
        currentPage = 1;
        render();
      }

      prevBtn.addEventListener('click', () => {
        if (currentPage > 1) { currentPage--; render(); }
      });
      nextBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(activeCards.length / cardsPerPage());
        if (currentPage < totalPages) { currentPage++; render(); }
      });

      document.querySelectorAll('.courses .tab').forEach(tab => {
        tab.addEventListener('click', () => applyFilter(tab.dataset.cat));
      });

      window.addEventListener('resize', () => { currentPage = 1; render(); });
      render();
    })();

    // 5) reveal animations
    (function () {
      const sections = document.querySelectorAll('section, .course-card, .testimonial');
      const io = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.transform = 'translateY(0)'; entry.target.style.opacity = 1;
            entry.target.style.transition = 'all 700ms cubic-bezier(.2,.9,.2,1)';
          } else {
            entry.target.style.transform = 'translateY(6px)'; entry.target.style.opacity = 0.98;
          }
        });
      }, {threshold: 0.08});
      sections.forEach(s => { s.style.transform = 'translateY(6px)'; s.style.opacity = 0.98; io.observe(s); });
    })();

    // 6) keyboard focus
    (function () {
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') document.documentElement.classList.add('show-focus');
      });
      document.addEventListener('mousedown', () => document.documentElement.classList.remove('show-focus'));
    })();

    // 7) CTA scroll
    document.getElementById('ctaEnroll').addEventListener('click', function () {
      document.getElementById('courses').scrollIntoView({behavior: 'smooth'});
    });

    // 8) Auth overlay
    const profileBtn = document.getElementById('profileBtn');
    const authOverlay = document.getElementById('authOverlay');
    if (profileBtn) {
      profileBtn.addEventListener('click', () => {
        authOverlay.classList.add('active');
      });
      authOverlay.addEventListener('click', (e) => {
        if (e.target === authOverlay) {
          authOverlay.classList.remove('active');
        }
      });
    }

    // 9) Hero entry animation
    (function () {
      const hero = document.querySelector('.hero');
      if (hero) requestAnimationFrame(() => hero.classList.add('animate'));
    })();

    // 10) Mobile dropdown
    

    // 11) FAQ modal
    window.openFaqModal = function(title, body) {
      const modalEl = document.getElementById('faqModal');
      const modalTitle = document.getElementById('faqModalTitle');
      const modalBody = document.getElementById('faqModalBody');
      if (modalTitle && modalBody && modalEl) {
        modalTitle.textContent = title;
        modalBody.textContent = body;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      }
    };

    // 12) Toppers Carousel for mobile
document.addEventListener('DOMContentLoaded', function() {
  const carouselTrack = document.querySelector('.carousel-track');
  const slides = document.querySelectorAll('.carousel-slide');
  let currentSlide = 0;
  let slideInterval;
  
  function setupCarousel() {
    if (window.innerWidth <= 820) {
      // MOBILE: Single slide transition
      clearInterval(slideInterval);
      
      // Set all slides to absolute positioning
      slides.forEach(slide => {
        slide.style.position = 'absolute';
        slide.classList.remove('active', 'exiting');
      });
      
      // Show first slide
      slides[0].classList.add('active');
      currentSlide = 0;
      
      // Auto slide every 3 seconds
      slideInterval = setInterval(() => {
        // Mark current slide as exiting
        slides[currentSlide].classList.remove('active');
        slides[currentSlide].classList.add('exiting');
        
        // Move to next slide
        currentSlide = (currentSlide + 1) % 3; // Only cycle through first 3 slides
        
        // Show new slide
        setTimeout(() => {
          slides.forEach(slide => {
            slide.classList.remove('exiting');
          });
          slides[currentSlide].classList.add('active');
        }, 800);
      }, 3000);
      
    } else {
      // DESKTOP/TABLET: Continuous animation
      clearInterval(slideInterval);
      
      // Set all slides to relative positioning for flexbox
      slides.forEach(slide => {
        slide.style.position = 'relative';
        slide.classList.remove('active', 'exiting');
      });
      
      // Enable CSS animation
      carouselTrack.style.animationPlayState = 'running';
    }
  }
  
  // Initial setup
  setupCarousel();
  
  // Handle window resize
  window.addEventListener('resize', setupCarousel);
});

  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>