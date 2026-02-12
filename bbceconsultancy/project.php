<?php
// project.php — DB-driven services page with "details" modal and contact CTA
// Requires config/config.php and includes/navbar.php
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

/* ---------- Fetch categories ---------- */
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

$total_services = count($services);

// Company contact details (used in modal)
$company_email = 'bhandari.krishna01@gmail.com';
$company_phone = '+977 9858422178';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BBC Engineering - Professional Services</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* --- Layout fixes for consistent cards (no color override) --- */
    .course-grid {
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 26px;
      align-items: stretch;
      margin-top: 22px;
    }

    .course-card {
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      min-height: 380px;       /* consistent height so rows align even when few cards */
      border-radius:14px;
      padding:18px;
      box-shadow:0 12px 30px rgba(2,8,20,0.06);
      transition: transform .18s ease, box-shadow .18s ease;
      overflow:hidden;
      /* preserve card color from style.css — do not set background here */
    }
    .course-card:hover {
      transform: translateY(-6px);
      box-shadow:0 24px 60px rgba(2,8,20,0.10);
    }

    .course-thumb {
      height:200px;
      padding:10px;
      border-radius:12px;
      margin-bottom:14px;
      display:flex;
      align-items:center;
      justify-content:center;
      background: transparent;
    }
    .course-thumb img {
      width:100%;
      height:100%;
      object-fit:cover;
      border-radius:10px;
      display:block;
    }

    .course-title { font-family:Poppins,sans-serif; font-size:18px; font-weight:800; margin:6px 0 10px 0; color:#021428; }
    .course-meta { display:flex; flex-direction:column; margin-top:8px; border-top:1px solid rgba(2,8,20,0.04); padding-top:14px; color:#334155; }
    .course-desc { min-height:44px; color:#02324a; line-height:1.45; }
    .course-scope { margin-top:8px; color:#64748b; font-size:13px; font-weight:600; }

    @media(max-width:900px){
      .course-thumb { height:160px; }
      .course-card { min-height:340px; }
    }
    @media(max-width:520px){
      .course-grid { grid-template-columns:1fr; gap:18px; }
      .course-thumb { height:220px; }
      .course-card { min-height:auto; padding:16px; }
    }

    /* Category tabs */
    .category-tabs { display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-top:12px; }
    .category-tab { padding:10px 14px; border-radius:999px; background:rgba(255,255,255,0.9); box-shadow:0 6px 14px rgba(2,8,20,.04); cursor:pointer; font-weight:700; color:#043856; }
    .category-tab.active { background:linear-gradient(90deg,#58f2d1,#7a8bff); color:#022032; box-shadow:0 22px 40px rgba(122,139,255,.16); transform:translateY(-2px); }

    /* ========== Modal styles ========== */
 /* ===========================
   SERVICE MODAL — CLEAN VERSION
=========================== */

.svc-modal {
  position: fixed;
  inset: 0;
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 12000;
  background: rgba(10, 20, 35, 0.65);
  backdrop-filter: blur(6px);
  padding: 20px;
}

.svc-modal.active {
  display: flex;
}

.svc-card {
  width: 100%;
  max-width: 1000px;
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 50px 120px rgba(0, 0, 0, 0.25);
  animation: modalIn .25s ease;
  display: flex;
  flex-direction: column;
}

@keyframes modalIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Header */
.svc-header {
  padding: 20px 24px;
  background: linear-gradient(90deg,#00c6ff,#7f7fff);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.svc-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.svc-header-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background: rgba(255,255,255,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  color: #021428;
}

.svc-title {
  font-family: Poppins, sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: #021428;
}

.svc-category {
  font-size: 13px;
  color: #102a43;
  margin-top: 4px;
}

.svc-close {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: none;
  background: rgba(255,255,255,0.9);
  cursor: pointer;
  font-weight: bold;
  font-size: 16px;
}

/* Body Layout */
.svc-body {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 30px;
  padding: 28px;
}

/* Image */
.svc-image {
  width: 100%;
  height: 300px;
  border-radius: 14px;
  overflow: hidden;
  background: #f4f8ff;
}

.svc-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Description */
.svc-description {
  margin-top: 20px;
  line-height: 1.6;
  color: #1f3a47;
}

/* Info Panel */
.svc-info {
  background: #f7faff;
  border-radius: 14px;
  padding: 20px;
}

.svc-info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 14px;
  font-size: 14px;
}

.svc-info-label {
  color: #64748b;
  font-weight: 600;
}

.svc-info-value {
  color: #021428;
  font-weight: 700;
  text-align: right;
}

/* CTA Buttons */
.svc-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.svc-btn {
  flex: 1;
  padding: 10px;
  border-radius: 10px;
  font-weight: 700;
  border: none;
  cursor: pointer;
}

.svc-btn-light {
  background: #ffffff;
  border: 1px solid #e2e8f0;
}

.svc-btn-primary {
  background: linear-gradient(90deg,#00D4FF,#635BFF);
  color: #021428;
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .svc-body {
    grid-template-columns: 1fr;
  }

  .svc-image {
    height: 220px;
  }
}

@media (max-width: 480px) {
  .svc-card {
    border-radius: 12px;
  }

  .svc-body {
    padding: 18px;
  }

  .svc-image {
    height: 180px;
  }
}

  </style>
</head>
<body>

  <!-- include navbar -->
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <!-- HERO -->
  <section class="courses-hero">
    <div class="container">
      <div class="hero-badge">Our Professional Services</div>
      <h1>Trusted <span class="highlight">Engineering & Valuation</span> Services</h1>
      <p>BBC Engineering Consultancy Pvt. Ltd. provides professional property valuation, engineering design, and site supervision services in compliance with Nepal Rastra Bank guidelines and local regulations.</p>

      <div class="hero-stats">
        <div class="hero-stat"><div class="number">15+</div><div class="label">Years Experience</div></div>
        <div class="hero-stat"><div class="number">1000+</div><div class="label">Valuation Reports</div></div>
        <div class="hero-stat"><div class="number">20+</div><div class="label">IBank Clients</div></div>
        <div class="hero-stat"><div class="number">100%</div><div class="label">NRB Compliance</div></div>
      </div>
    </div>
  </section>

  <!-- CATEGORIES -->
  <section class="courses-categories">
    <div class="container">
      <div class="section-header">
        <div class="badge">Our Services</div>
        <h2>Professional Engineering & Valuation Services</h2>
        <p>BBC Engineering Consultancy provides reliable property valuation, engineering design, and site supervision services trusted by banks, institutions and individual clients across Nepal.</p>
      </div>

      <div class="category-tabs" id="categoryTabs">
        <div class="category-tab active" data-category="all">All Services</div>
        <?php
          if (!empty($categories)) {
              foreach ($categories as $cat) {
                  $cat_name = htmlspecialchars($cat['name']);
                  $cat_slug = htmlspecialchars($cat['slug']);
                  $icon = $cat['icon'] ? htmlspecialchars($cat['icon']) . ' ' : '';
                  echo '<div class="category-tab" data-category="' . $cat_slug . '">' . $icon . $cat_name . '</div>';
              }
          } else {
              echo '<div class="category-tab" data-category="valuation">Property Valuation</div>';
              echo '<div class="category-tab" data-category="design">Design & Drawing</div>';
              echo '<div class="category-tab" data-category="supervision">Site Supervision</div>';
              echo '<div class="category-tab" data-category="consultancy">Civil Consultancy</div>';
              echo '<div class="category-tab" data-category="special">Specialized Services</div>';
          }
        ?>
      </div>

      <!-- SERVICES GRID -->
      <div class="course-grid" id="courseGrid">
        <?php if (!empty($services)): ?>
          <?php foreach ($services as $svc): 
            $title = htmlspecialchars(trim($svc['title'] ?: 'Untitled Service'));
            $short = trim($svc['description'] ?: '');
            $scope = trim($svc['scope'] ?: '');
            if ($short === '') $short = $scope;
            $short = htmlspecialchars($short);
            $cat_slug = htmlspecialchars($svc['category_slug']);
            $img = htmlspecialchars($svc['image_path']);
          ?>
            <article class="course-card" data-id="<?php echo (int)$svc['id']; ?>" data-cat="<?php echo $cat_slug; ?>">
              <div class="course-thumb"><img src="<?php echo $img; ?>" alt="<?php echo $title; ?>"></div>
              <div>
                <h4 class="course-title"><?php echo $title; ?></h4>
                <div class="course-meta">
                  <div class="course-desc"><?php echo $short ?: '&nbsp;'; ?></div>
                  <?php if ($scope !== ''): ?>
                    <div class="course-scope">Scope: <?php echo htmlspecialchars($scope); ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <article class="course-card" data-id="0" data-cat="valuation">
            <div class="course-thumb"><img src="aa.jpg" alt=""></div>
            <div>
              <h4 class="course-title">🏠 Residential & Commercial Property Valuation</h4>
              <div class="course-meta">
                <div class="course-desc">NRB-compliant valuation for houses, apartments, offices & complexes</div>
                <div class="course-scope">Scope: Bank, embassy, and legal valuations</div>
              </div>
            </div>
          </article>
        <?php endif; ?>
      </div>

      <!-- Pagination & info -->
      <div class="pagination-container" style="margin-top:26px">
        <div class="pagination" style="display:flex;justify-content:center;gap:14px;align-items:center">
          <button class="pagination-btn prev-btn" disabled><i class="fas fa-chevron-left"></i> Previous</button>
          <div class="page-numbers" style="min-width:200px;text-align:center"></div>
          <button class="pagination-btn next-btn">Next <i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="page-info" style="text-align:center;margin-top:10px">
          Showing <span class="current-range">1-8</span> of <span class="total-courses"><?php echo $total_services ?: '0'; ?></span> services
        </div>
      </div>
    </div>
  </section>

  <!-- Highlights (unchanged) -->
  <section class="course-highlights">
    <div class="container">
      <div class="section-header">
        <div class="badge">Why Choose BBC</div>
        <h2>Our Professional Advantage</h2>
        <p>Why banks, financial institutions, and individual clients across Nepal trust BBC Engineering Consultancy Pvt. Ltd.</p>
      </div>

      <div class="highlights-grid">
        <div class="highlight-card"><div class="highlight-icon"><i class="fas fa-user-tie"></i></div><h3>Registered & Experienced Valuator</h3><p>Valuation services provided by qualified professionals with extensive experience in the Nepalese property and construction market.</p></div>

        <div class="highlight-card"><div class="highlight-icon"><i class="fas fa-laptop-code"></i></div><h3>NRB & Bank Compliant Reports</h3><p>All valuation reports strictly follow Nepal Rastra Bank (NRB) guidelines and are accepted by banks and financial institutions.</p></div>

        <div class="highlight-card"><div class="highlight-icon"><i class="fas fa-briefcase"></i></div><h3>End-to-End Engineering Solutions</h3><p>From property valuation and architectural design to site supervision and documentation, we provide complete project support.</p></div>

        <div class="highlight-card"><div class="highlight-icon"><i class="fas fa-certificate"></i></div><h3>Fast Turnaround & Legal Reliability</h3><p>Timely service delivery with accurate documentation suitable for banking, legal, embassy, and official purposes.</p></div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="courses-cta">
    <div class="container">
      <h2>Need a Professional Valuation or Engineering Service?</h2>
      <p>Contact BBC Engineering Consultancy Pvt. Ltd. for accurate valuation, professional design, and reliable supervision services.</p>
      <div class="cta-buttons">
        <button class="btn-primary" id="browseAllBtn">View All Services</button>
        <button class="btn-outline" id="consultBtn">Request Valuation</button>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-brand"><div class="footer-logo"><div class="logo-mark">BBC</div><h3>Engineering Consultantancy Pvt.Ltd</h3></div><p class="footer-tagline">Trusted Engineering & Valuation Consultancy in Nepal</p><div class="footer-copyright">&copy; <strong>BBC</strong> — 2025 • All rights reserved</div></div>

        <div class="footer-contact">
          <h4>Contact Information</h4>
          <div class="contact-item"><div class="contact-icon">📍</div><div class="contact-details"><p>Ward 5, Dhangadhi sub-metropolitan city</p><p>Kailali, Sudurpaschim pradesh, Nepal</p><p class="address-note">Near North Gate of Dhangadhi stadium</p></div></div>

          <div class="contact-item"><div class="contact-icon">✉️</div><div class="contact-details"><a href="mailto:<?php echo htmlspecialchars($company_email); ?>"><?php echo htmlspecialchars($company_email); ?></a></div></div>

          <div class="contact-item"><div class="contact-icon">📞</div><div class="contact-details"><a href="tel:<?php echo htmlspecialchars($company_phone); ?>"><?php echo htmlspecialchars($company_phone); ?></a></div></div>
        </div>

        <div class="footer-social"><h4>Follow Us</h4><div class="social-icons"><a href="#" class="social-icon facebook">f</a><a href="#" class="social-icon twitter">t</a><a href="#" class="social-icon instagram">ig</a><a href="#" class="social-icon linkedin">in</a></div></div>

      </div>
    </div>
  </footer>

  <!-- Modal markup (hidden initially) -->
  <div id="svcModal" class="svc-modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="svc-card" role="document" aria-labelledby="svcTitle">
      <div class="svc-header">
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:46px;height:46px;border-radius:10px;background:rgba(255,255,255,0.7);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px rgba(2,8,20,.08)"><i class="fas fa-building" style="font-size:20px"></i></div>
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
            <div style="font-weight:700;color:#64748b;font-size:13px;margin-bottom:6px"><i class="fas fa-info-circle" style="margin-right:8px"></i>Details</div>
            <div id="svcDescription" class="svc-desc">—</div>
          </div>
        </div>

        <aside class="svc-right">
          <div class="svc-meta"><div class="label"><i class="fas fa-calendar-alt"></i> Submitted</div><div class="value" id="svcCreated">—</div></div>
          <div class="svc-meta"><div class="label"><i class="fas fa-layer-group"></i> Scope</div><div class="value" id="svcScope">—</div></div>
          <div class="svc-meta"><div class="label"><i class="fas fa-envelope"></i> Email</div><div class="value"><a id="svcEmail" href="mailto:<?php echo htmlspecialchars($company_email); ?>"><?php echo htmlspecialchars($company_email); ?></a></div></div>
          <div class="svc-meta"><div class="label"><i class="fas fa-phone"></i> Phone</div><div class="value"><a id="svcPhone" href="tel:<?php echo htmlspecialchars($company_phone); ?>"><?php echo htmlspecialchars($company_phone); ?></a></div></div>

          <div class="svc-actions">
            <button class="btn-ghost" id="svcMailBtn"><i class="fas fa-envelope"></i> Email</button>
            <button class="btn-ghost" id="svcCallBtn"><i class="fas fa-phone"></i> Call</button>
            <button class="btn-primary" id="svcContactBtn"><i class="fas fa-paper-plane" style="margin-right:8px"></i> Contact Us</button>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <!-- Build a JS map of services for safe client-side access -->
  <script>
    // SERVER -> CLIENT: SERVICES object
    const SERVICES = <?php echo json_encode(array_map(function($s){
        // sanitize/format values for JSON output
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

    // Company contact details (same as server)
    const COMPANY_EMAIL = "<?php echo addslashes($company_email); ?>";
    const COMPANY_PHONE = "<?php echo addslashes($company_phone); ?>";
  </script>

  <script>
    // Utilities: escape HTML to safely inject text into innerHTML when needed
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
      const modalCard = modal.querySelector('.svc-card');
      const svcTitle = document.getElementById('svcTitle');
      const svcCategory = document.getElementById('svcCategory');
      const svcImage = document.getElementById('svcImage');
      const svcImageWrap = document.getElementById('svcImageWrap');
      const svcDescription = document.getElementById('svcDescription');
      const svcScope = document.getElementById('svcScope');
      const svcCreated = document.getElementById('svcCreated');
      const svcEmail = document.getElementById('svcEmail');
      const svcPhone = document.getElementById('svcPhone');

      const svcMailBtn = document.getElementById('svcMailBtn');
      const svcCallBtn = document.getElementById('svcCallBtn');
      const svcContactBtn = document.getElementById('svcContactBtn');
      const svcCloseBtn = document.getElementById('svcCloseBtn');

      // open modal with service object
      function openModalFor(service) {
        svcTitle.textContent = service.title || 'Service';
        svcCategory.textContent = service.category_name || '';
        // image
        svcImage.src = service.image_path || 'aa.jpg';
        svcImage.alt = service.title || 'Service image';
        // description (preserve newlines). Use escape then replace newline -> <br>
        svcDescription.innerHTML = escapeHtml(service.description || '').replace(/\n/g, '<br>');
        svcScope.textContent = (service.scope && service.scope.length) ? service.scope : '—';
        svcCreated.textContent = service.created_at ? service.created_at : '—';
        svcEmail.href = 'mailto:' + COMPANY_EMAIL;
        svcEmail.textContent = COMPANY_EMAIL;
        svcPhone.href = 'tel:' + COMPANY_PHONE;
        svcPhone.textContent = COMPANY_PHONE;

        // Mail button opens mail client with prefilled subject referencing service
        svcMailBtn.onclick = function(){
          window.location.href = 'mailto:' + COMPANY_EMAIL + '?subject=' + encodeURIComponent('Inquiry: ' + (service.title || 'Service'));
        };
        // Call button: tel link
        svcCallBtn.onclick = function(){
          window.location.href = 'tel:' + COMPANY_PHONE;
        };
        // Contact Us button: send to contact_us.php with service_id query param
        svcContactBtn.onclick = function(){
          // preserve service id if available
          const url = 'contact_us.php' + (service.id ? '?service_id=' + encodeURIComponent(service.id) : '');
          window.location.href = url;
        };

        // show modal
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');

        // focus for accessibility
        svcContactBtn.focus();
      }

      function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
      }

      // click on card to open modal: use event delegation
      document.getElementById('courseGrid').addEventListener('click', function(e){
        // find nearest .course-card
        const card = e.target.closest('.course-card');
        if (!card) return;
        const sid = parseInt(card.getAttribute('data-id') || card.dataset.id || 0, 10);
        if (!sid) return;
        // find service by id in SERVICES
        const svc = SERVICES.find(s => s.id === sid);
        if (!svc) return;
        openModalFor(svc);
      });

      // close modal actions
      svcCloseBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', function(e){
        if (e.target === modal) closeModal();
      });
      window.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
      });

      // make modal keyboard-friendly: trap focus (basic)
      modal.addEventListener('keydown', function(e){
        if (e.key === 'Tab') {
          // simple trap: if focus leaves modal-card, force back to contact button
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

  <!-- Tabs + pagination (unchanged behavior) -->
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const tabs = Array.from(document.querySelectorAll('.category-tab'));
      const allCards = () => Array.from(document.querySelectorAll('.course-card'));
      let currentPage = 1;
      let activeCategory = 'all';

      function itemsPerPage() { return window.innerWidth <= 768 ? 4 : 8; }
      function filteredCards() {
        return allCards().filter(card => activeCategory === 'all' || card.dataset.cat === activeCategory);
      }

      function updateRange(filtered, perPage) {
        const total = filtered.length;
        const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
        const end = Math.min(currentPage * perPage, total);
        document.querySelector('.current-range').innerText = (total === 0 ? '0-0' : (start + '-' + end));
        document.querySelector('.total-courses').innerText = total;
      }

      function render() {
        const perPage = itemsPerPage();
        const filtered = filteredCards();
        const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        allCards().forEach(c => c.style.display = 'none');
        filtered.forEach((card, i) => {
          if (i >= (currentPage - 1) * perPage && i < currentPage * perPage) {
            card.style.display = 'block';
          }
        });
        document.querySelector('.prev-btn').disabled = currentPage === 1;
        document.querySelector('.next-btn').disabled = currentPage === totalPages;
        updateRange(filtered, perPage);
      }

      tabs.forEach(t => t.addEventListener('click', function(){
        tabs.forEach(x => x.classList.remove('active'));
        this.classList.add('active');
        activeCategory = this.dataset.category;
        currentPage = 1;
        render();
      }));

      document.querySelector('.prev-btn').addEventListener('click', function(){
        if (currentPage > 1) { currentPage--; render(); }
      });
      document.querySelector('.next-btn').addEventListener('click', function(){
        const total = Math.ceil(filteredCards().length / itemsPerPage());
        if (currentPage < total) { currentPage++; render(); }
      });

      window.addEventListener('resize', function(){ currentPage = 1; render(); });
      render();
    });
  </script>
</body>
</html>
