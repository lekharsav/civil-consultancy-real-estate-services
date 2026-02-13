<?php
// admin/clients.php
session_start();
require_once __DIR__ . "/../config/config.php";

// guard: require login (adjust according to your auth)
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  header("Location: login.php");
  exit;
}

// Pagination settings
$reviewsPerPage = 10;
$companiesPerPage = 10;

$reviewPage = isset($_GET['rpage']) && is_numeric($_GET['rpage']) && $_GET['rpage'] > 0 ? (int)$_GET['rpage'] : 1;
$companyPage = isset($_GET['cpage']) && is_numeric($_GET['cpage']) && $_GET['cpage'] > 0 ? (int)$_GET['cpage'] : 1;

$reviewOffset = ($reviewPage - 1) * $reviewsPerPage;
$companyOffset = ($companyPage - 1) * $companiesPerPage;

// total counts
$totalReviews = 0;
$res = $conn->query("SELECT COUNT(*) AS cnt FROM reviews");
if ($res) {
    $tmp = $res->fetch_assoc();
    $totalReviews = (int)$tmp['cnt'];
}

$totalCompanies = 0;
$res = $conn->query("SELECT COUNT(*) AS cnt FROM companies");
if ($res) {
    $tmp = $res->fetch_assoc();
    $totalCompanies = (int)$tmp['cnt'];
}

// fetch reviews (paged)
$reviews = [];
$stmt = $conn->prepare("SELECT * FROM reviews ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $reviewsPerPage, $reviewOffset);
$stmt->execute();
$rres = $stmt->get_result();
while ($row = $rres->fetch_assoc()) $reviews[] = $row;
$stmt->close();

// fetch companies (paged)
$companies = [];
$stmt2 = $conn->prepare("SELECT * FROM companies ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt2->bind_param("ii", $companiesPerPage, $companyOffset);
$stmt2->execute();
$cres = $stmt2->get_result();
while ($c = $cres->fetch_assoc()) $companies[] = $c;
$stmt2->close();

function render_stars($rating) {
    $rating = (int)$rating;
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= ($i <= $rating) ? '★' : '☆';
    }
    return $stars;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Clients & Reviews — Admin | BBC Engineering Consultancy</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">

  <style>
    /* original styles kept as-is */
    body { background:#f6f9ff; }

    .admin-wrap { padding:40px 0; }

    h1 {
      font-family:Poppins,sans-serif;
      font-size:32px;
      font-weight:800;
      color:#021428;
      margin-bottom:8px;
    }

    .page-sub {
      font-size:14px;
      color:#475569;
      margin-bottom:30px;
    }

    .section-box {
      background:#fff;
      border-radius:16px;
      box-shadow:0 12px 30px rgba(2,8,20,.08);
      margin-bottom:40px;
      overflow:hidden;
    }

    .section-head {
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:18px 22px;
      border-bottom:1px solid #eef2ff;
    }

    .section-head h3 {
      margin:0;
      font-size:20px;
      font-weight:800;
      color:#021428;
    }

    .add-btn {
      padding:8px 14px;
      border-radius:10px;
      background:#021428;
      color:#fff;
      font-size:13px;
      font-weight:700;
      border:none;
      cursor:pointer;
    }

    table {
      width:100%;
      border-collapse:collapse;
      font-size:14px;
    }

    th, td {
      padding:14px 16px;
      text-align:left;
    }

    thead {
      background:#021428;
      color:#fff;
    }

    tbody tr {
      border-bottom:1px solid #eef2ff;
    }

    tbody tr:hover {
      background:#f8fbff;
    }

    .status {
      padding:4px 10px;
      border-radius:999px;
      font-size:12px;
      font-weight:700;
    }

    .active { background:#dcfce7; color:#166534; }
    .inactive { background:#fee2e2; color:#991b1b; }

    .action-btn {
      padding:6px 10px;
      font-size:12px;
      border-radius:8px;
      border:none;
      cursor:pointer;
      font-weight:700;
      margin-right:6px;
    }

    .edit { background:#fef3c7; color:#92400e; }
    .delete { background:#fee2e2; color:#991b1b; }
    .small { padding:5px 8px; font-size:12px; border-radius:8px; }

    .logo {
      width:46px;
      height:46px;
      border-radius:10px;
      object-fit:contain;
      background:#fff;
      border:1px solid #e5e7eb;
      padding:6px;
    }

    .muted { color:#64748b; font-size:13px; }

    /* modal and form */
    .modal { display:none; position:fixed; inset:0; background:rgba(2,8,20,.6); align-items:center; justify-content:center; z-index:9999; padding:18px; }
    .modal.active { display:flex; }
    .modal-card { background:#fff; border-radius:14px; padding:18px; width:100%; max-width:900px; box-shadow:0 30px 90px rgba(2,8,20,.12); max-height:86vh; overflow:auto; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px; }
    .form-control { padding:10px; border-radius:8px; border:1px solid #e8f0ff; width:100%; }
    .form-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:12px; }

    .pager { display:flex; gap:8px; align-items:center; margin-top:12px; }
    .pager button { padding:8px 10px; border-radius:8px; border:none; background:#fff; cursor:pointer; box-shadow:0 6px 24px rgba(2,8,20,.04); }
    .pager .current { font-weight:800; padding:8px 12px; background:linear-gradient(90deg,#00D4FF,#635BFF); color:#021428; border:none; }

    @media(max-width:768px){
      table, thead, tbody, th, td, tr { display:block; }
      thead { display:none; }

      tbody tr {
        padding:14px;
      }

      td::before {
        content:attr(data-label);
        display:block;
        font-weight:700;
        font-size:12px;
        color:#64748b;
        margin-bottom:4px;
      }

      .form-row { grid-template-columns:1fr; }
    }

    /* tiny UX helpers */
    .btn-inline { display:inline-block; margin-right:8px; }
    .helpful { display:inline-flex; gap:8px; align-items:center; }
    .helpful button { padding:6px 8px; border-radius:8px; border:none; cursor:pointer; background:#fff; box-shadow:0 6px 20px rgba(2,8,20,.04); }
  </style>
</head>

<body>

<?php include __DIR__ . '/partials/admin-navbar.php'; ?>

<main class="container admin-wrap">

  <h1>Clients & Reviews</h1>
  <p class="page-sub">Manage client feedback and companies you have worked with</p>

  <!-- ================= REVIEWS ================= -->
  <section class="section-box">
    <div class="section-head">
      <h3>Client Reviews</h3>
      <button class="add-btn" id="openAddReview">+ Add Review</button>
    </div>

    <table id="reviewsTable">
      <thead>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Rating</th>
          <th>Review</th>
          <th>Status</th>
          <th>Helpful</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $r): ?>
            <tr>
              <td data-label="Name"><?= htmlspecialchars($r['name']) ?></td>
              <td data-label="Type"><?= htmlspecialchars($r['request_about']) ?></td>
              <td data-label="Rating"><?= render_stars($r['rating']) ?></td>
              <td data-label="Review"><?= htmlspecialchars($r['review_text']) ?></td>
              <td data-label="Status">
                <span class="status <?= $r['status'] == 1 ? 'active' : 'inactive' ?>" data-id="<?= (int)$r['id'] ?>">
                  <?= $r['status'] == 1 ? 'Visible' : 'Hidden' ?>
                </span>
              </td>
              <td data-label="Helpful">
                <div class="helpful">
                  <span id="help-count-<?= (int)$r['id'] ?>"><?= (int)$r['helpful_count'] ?></span>
                  <button class="small" onclick="markHelpful(<?= (int)$r['id'] ?>)">Helpful</button>
                </div>
              </td>
              <td data-label="Actions">
                <button class="action-btn edit" onclick="openEditReview(<?= (int)$r['id'] ?>)">Edit</button>
                <button class="action-btn delete" onclick="deleteReview(<?= (int)$r['id'] ?>)">Delete</button>
                <button class="action-btn small" onclick="toggleReview(<?= (int)$r['id'] ?>)"><?= $r['status'] == 1 ? 'Hide' : 'Show' ?></button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" style="text-align:center;padding:20px;">No reviews found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- review pagination -->
    <div class="pager" style="padding:12px 16px;">
      <?php
        $totalPages = (int)ceil($totalReviews / $reviewsPerPage);
        if ($totalPages <= 1) {
          echo '';
        } else {
          // render basic pager: prev, pages, next (limited to 7 page buttons)
          $start = max(1, $reviewPage - 3);
          $end = min($totalPages, $reviewPage + 3);
          if ($reviewPage > 1) {
            echo '<a href="?rpage='.($reviewPage-1).'#reviewsTable"><button>&laquo; Prev</button></a>';
          }
          for ($p = $start; $p <= $end; $p++) {
            if ($p == $reviewPage) {
              echo '<span class="current">'.$p.'</span>';
            } else {
              echo '<a href="?rpage='.$p.'#reviewsTable"><button>'.$p.'</button></a>';
            }
          }
          if ($reviewPage < $totalPages) {
            echo '<a href="?rpage='.($reviewPage+1).'#reviewsTable"><button>Next &raquo;</button></a>';
          }
        }
      ?>
    </div>

  </section>

  <!-- ================= COMPANIES ================= -->
  <section class="section-box">
    <div class="section-head">
      <h3>Companies & Institutions</h3>
      <button class="add-btn" id="openAddCompany">+ Add Company</button>
    </div>

    <table id="companiesTable">
      <thead>
        <tr>
          <th>Logo</th>
          <th>Name</th>
          <th>Type</th>
          <th>Description</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($companies)): ?>
          <?php foreach ($companies as $c): ?>
            <tr>
              <td data-label="Logo">
                <?php if (!empty($c['logo_path']) && file_exists(__DIR__ . '/../' . $c['logo_path'])): ?>
                  <img src="../<?= ltrim($c['logo_path'], '/'); ?>" class="logo" alt="">
                <?php else: ?>
                  <div class="logo muted">No logo</div>
                <?php endif; ?>
              </td>
              <td data-label="Name"><?= htmlspecialchars($c['name']) ?></td>
              <td data-label="Type"><?= htmlspecialchars($c['type']) ?></td>
              <td data-label="Description"><?= htmlspecialchars($c['description']) ?></td>
              <td data-label="Status">
                <span class="status <?= $c['status'] == 1 ? 'active' : 'inactive' ?>"><?= $c['status'] == 1 ? 'Visible' : 'Hidden' ?></span>
              </td>
              <td data-label="Actions">
                <button class="action-btn edit" onclick="openEditCompany(<?= (int)$c['id'] ?>)">Edit</button>
                <button class="action-btn delete" onclick="deleteCompany(<?= (int)$c['id'] ?>)">Delete</button>
                <button class="action-btn small" onclick="toggleCompany(<?= (int)$c['id'] ?>)"><?= $c['status'] == 1 ? 'Hide' : 'Show' ?></button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;padding:20px;">No companies found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="pager" style="padding:12px 16px;">
      <?php
        $totalCompanyPages = (int)ceil($totalCompanies / $companiesPerPage);
        if ($totalCompanyPages > 1) {
          if ($companyPage > 1) echo '<a href="?cpage='.($companyPage-1).'#companiesTable"><button>&laquo; Prev</button></a>';
          $start = max(1, $companyPage - 3);
          $end = min($totalCompanyPages, $companyPage + 3);
          for ($p = $start; $p <= $end; $p++) {
            if ($p == $companyPage) echo '<span class="current">'.$p.'</span>';
            else echo '<a href="?cpage='.$p.'#companiesTable"><button>'.$p.'</button></a>';
          }
          if ($companyPage < $totalCompanyPages) echo '<a href="?cpage='.($companyPage+1).'#companiesTable"><button>Next &raquo;</button></a>';
        }
      ?>
    </div>

  </section>

</main>

<!-- ================= MODALS ================= -->
<!-- Add/Edit Review Modal -->
<div id="reviewModal" class="modal" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <div style="font-weight:800" id="reviewModalTitle">Add Review</div>
      <div><button class="add-btn" onclick="closeReviewModal()">Close</button></div>
    </div>

    <form id="reviewForm" enctype="multipart/form-data" onsubmit="submitReviewForm(event)">
      <input type="hidden" name="id" id="reviewId" value="">
      <div class="form-row">
        <input name="name" id="rName" class="form-control" placeholder="Full name" required>
        <input name="request_about" id="rAbout" class="form-control" placeholder="Type (e.g. Bank, Corporate)" required>
      </div>

      <div class="form-row">
        <select name="rating" id="rRating" class="form-control" required>
          <option value="5">★★★★★ (5)</option>
          <option value="4">★★★★☆ (4)</option>
          <option value="3">★★★☆☆ (3)</option>
          <option value="2">★★☆☆☆ (2)</option>
          <option value="1">★☆☆☆☆ (1)</option>
        </select>
        <input name="image" id="rImage" type="file" accept="image/*" class="form-control">
      </div>

      <div style="margin-bottom:10px">
        <textarea name="review_text" id="rText" class="form-control" placeholder="Review" rows="4" required></textarea>
      </div>

      <div class="form-row">
        <label class="muted">Status</label>
        <select name="status" id="rStatus" class="form-control">
          <option value="1">Visible</option>
          <option value="0">Hidden</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="button" class="action-btn" onclick="closeReviewModal()">Cancel</button>
        <button type="submit" class="add-btn">Save Review</button>
      </div>
    </form>
  </div>
</div>

<!-- Add/Edit Company Modal -->
<div id="companyModal" class="modal" aria-hidden="true">
  <div class="modal-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <div style="font-weight:800" id="companyModalTitle">Add Company</div>
      <div><button class="add-btn" onclick="closeCompanyModal()">Close</button></div>
    </div>

    <form id="companyForm" enctype="multipart/form-data" onsubmit="submitCompanyForm(event)">
      <input type="hidden" name="id" id="companyId" value="">
      <div class="form-row">
        <input name="name" id="cName" class="form-control" placeholder="Company name" required>
        <input name="type" id="cType" class="form-control" placeholder="Type (Bank / Corporate)">
      </div>

      <div style="margin-bottom:10px">
        <textarea name="description" id="cDesc" class="form-control" placeholder="Description" rows="3"></textarea>
      </div>

      <div class="form-row">
        <input type="file" name="logo" id="cLogo" accept="image/*" class="form-control">
        <select name="status" id="cStatus" class="form-control">
          <option value="1">Visible</option>
          <option value="0">Hidden</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="button" class="action-btn" onclick="closeCompanyModal()">Cancel</button>
        <button type="submit" class="add-btn">Save Company</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Utility fetch wrapper
  async function postJSON(url, data) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    return res.json();
  }

  // ---------- Reviews: modal open/close ----------
  document.getElementById('openAddReview').addEventListener('click', ()=>{
    document.getElementById('reviewModalTitle').innerText = 'Add Review';
    document.getElementById('reviewId').value = '';
    document.getElementById('rName').value = '';
    document.getElementById('rAbout').value = '';
    document.getElementById('rRating').value = '5';
    document.getElementById('rText').value = '';
    document.getElementById('rImage').value = '';
    document.getElementById('rStatus').value = '1';
    document.getElementById('reviewModal').classList.add('active');
  });

  function closeReviewModal(){ document.getElementById('reviewModal').classList.remove('active'); }

  // Add or edit review via AJAX (multipart)
  async function submitReviewForm(e){
    e.preventDefault();
    const form = document.getElementById('reviewForm');
    const fd = new FormData(form);

    const res = await fetch('review-save.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      location.reload();
    } else {
      alert('Save failed: ' + (data.error || 'Unknown'));
    }
  }

  // open edit (fetch single review)
  async function openEditReview(id){
    const res = await fetch('review-get.php?id=' + encodeURIComponent(id), { method: 'GET' });
    const data = await res.json();
    if (!data.success) return alert('Load failed.');
    const r = data.review;
    document.getElementById('reviewModalTitle').innerText = 'Edit Review';
    document.getElementById('reviewId').value = r.id;
    document.getElementById('rName').value = r.name;
    document.getElementById('rAbout').value = r.request_about;
    document.getElementById('rRating').value = r.rating;
    document.getElementById('rText').value = r.review_text;
    document.getElementById('rStatus').value = r.status;
    document.getElementById('reviewModal').classList.add('active');
  }

  // delete review
  async function deleteReview(id){
    if(!confirm('Delete this review? This will remove its image too.')) return;
    const res = await postJSON('review-delete.php', { id });
    if (res.success) location.reload();
    else alert('Delete failed: ' + (res.error || 'Unknown'));
  }

  // toggle review status
  async function toggleReview(id){
    const res = await postJSON('review-toggle.php', { id });
    if (res.success) location.reload();
    else alert('Toggle failed: ' + (res.error || 'Unknown'));
  }

  // helpful vote
  async function markHelpful(id){
    const res = await postJSON('review-helpful.php', { id });
    if (res.success) {
      // update count in UI
      const el = document.getElementById('help-count-' + id);
      if (el) el.innerText = res.helpful_count;
    } else {
      alert('Could not mark helpful: ' + (res.error || 'You may have already voted.'));
    }
  }

  // ---------- Companies: modal open/close ----------
  document.getElementById('openAddCompany').addEventListener('click', ()=>{
    document.getElementById('companyModalTitle').innerText = 'Add Company';
    document.getElementById('companyId').value = '';
    document.getElementById('cName').value = '';
    document.getElementById('cType').value = '';
    document.getElementById('cDesc').value = '';
    document.getElementById('cLogo').value = '';
    document.getElementById('cStatus').value = '1';
    document.getElementById('companyModal').classList.add('active');
  });

  function closeCompanyModal(){ document.getElementById('companyModal').classList.remove('active'); }

  async function submitCompanyForm(e){
    e.preventDefault();
    const form = document.getElementById('companyForm');
    const fd = new FormData(form);
    const res = await fetch('company-save.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) location.reload();
    else alert('Save failed: ' + (data.error || 'Unknown'));
  }

  async function openEditCompany(id){
    const res = await fetch('company-get.php?id=' + encodeURIComponent(id));
    const data = await res.json();
    if (!data.success) return alert('Load failed.');
    const c = data.company;
    document.getElementById('companyModalTitle').innerText = 'Edit Company';
    document.getElementById('companyId').value = c.id;
    document.getElementById('cName').value = c.name;
    document.getElementById('cType').value = c.type;
    document.getElementById('cDesc').value = c.description;
    document.getElementById('cStatus').value = c.status;
    document.getElementById('companyModal').classList.add('active');
  }

  async function deleteCompany(id){
    if (!confirm('Delete this company?')) return;
    const res = await postJSON('company-delete.php', { id });
    if (res.success) location.reload();
    else alert('Delete failed: ' + (res.error || 'Unknown'));
  }

  async function toggleCompany(id){
    const res = await postJSON('company-toggle.php', { id });
    if (res.success) location.reload();
    else alert('Toggle failed: ' + (res.error || 'Unknown'));
  }

  // close modals on overlay click or Escape
  document.getElementById('reviewModal').addEventListener('click', (e)=>{ if (e.target === e.currentTarget) closeReviewModal(); });
  document.getElementById('companyModal').addEventListener('click', (e)=>{ if (e.target === e.currentTarget) closeCompanyModal(); });
  window.addEventListener('keydown', (e)=>{ if (e.key === 'Escape'){ closeReviewModal(); closeCompanyModal(); } });
</script>

</body>
</html>
