<?php
// admin/index.php (professional analytics dashboard)
// Replace your existing index.php with this file.

require_once "../config/config.php";

// don't call session_start() unconditionally if config.php already does
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// auth guard
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: login.php");
    exit;
}

$adminName = $_SESSION['admin_name'] ?? 'Admin';

/* --------------------
   Helper functions
   -------------------- */
function safe_prepare_get_count($conn, $sql, $types = null, $params = []) {
    $count = 0;
    if ($stmt = $conn->prepare($sql)) {
        if ($types !== null && !empty($params)) $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $count = (int)($row['cnt'] ?? 0);
        }
        $stmt->close();
    }
    return $count;
}

function safe_fetch_all($conn, $sql, $types = null, $params = []) {
    $rows = [];
    if ($stmt = $conn->prepare($sql)) {
        if ($types !== null && !empty($params)) $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) $rows[] = $r;
        }
        $stmt->close();
    }
    return $rows;
}

// format date/time
function fmt_dt($d) {
    if (!$d) return '—';
    $ts = strtotime($d);
    return date('M j, Y H:i', $ts);
}

// relative time (simple)
function rel_time($d) {
    $t = time() - strtotime($d);
    if ($t < 60) return $t . 's';
    if ($t < 3600) return floor($t/60) . 'm';
    if ($t < 86400) return floor($t/3600) . 'h';
    return floor($t/86400) . 'd';
}

// map numeric status to readable label (non-destructive: unknown statuses kept numeric)
function status_label($s) {
    $map = [
        '0' => 'Pending',
        '1' => 'Contacted',
        '2' => 'In Progress',
        '3' => 'Completed',
        '4' => 'Rejected'
    ];
    return isset($map[(string)$s]) ? $map[(string)$s] : "Status {$s}";
}

// simple star rendering
function star_text($rating) {
    $r = (int)$rating;
    $s = '';
    for ($i=1; $i<=5; $i++) $s .= ($i <= $r) ? '★' : '☆';
    return $s;
}

/* --------------------
   Aggregate Stats
   -------------------- */
// totals
$totalValRequests = safe_prepare_get_count($conn, "SELECT COUNT(*) AS cnt FROM valuation_requests");
$activeServices = safe_prepare_get_count($conn, "SELECT COUNT(*) AS cnt FROM services WHERE status = 1");
$totalReviews = safe_prepare_get_count($conn, "SELECT COUNT(*) AS cnt FROM reviews");
$totalStaff = safe_prepare_get_count($conn, "SELECT COUNT(*) AS cnt FROM staff");
$totalCompanies = safe_prepare_get_count($conn, "SELECT COUNT(*) AS cnt FROM companies");

// average rating + sum helpful_count
$avgRating = null;
$totalHelpful = 0;
if ($stmt = $conn->prepare("SELECT ROUND(AVG(rating),1) AS avg_rating, COALESCE(SUM(helpful_count),0) AS helpful_sum FROM reviews")) {
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) {
        $avgRating = $r['avg_rating'] !== null ? (float)$r['avg_rating'] : null;
        $totalHelpful = (int)$r['helpful_sum'];
    }
    $stmt->close();
}

/* --------------------
   Requests timeseries: last 60 days grouped by date
   We'll compute last 30 days vs previous 30-day window for accurate comparison.
   -------------------- */
$periodDays = 30;
$lookback = $periodDays * 2; // 60 days
$startDate = date('Y-m-d', strtotime("-" . ($lookback - 1) . " days")); // inclusive

$reqRows = [];
if ($stmt = $conn->prepare(
    "SELECT DATE(created_at) AS d, COUNT(*) AS cnt
     FROM valuation_requests
     WHERE DATE(created_at) >= ?
     GROUP BY d
     ORDER BY d ASC"
)) {
    $stmt->bind_param("s", $startDate);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $reqRows[$row['d']] = (int)$row['cnt'];
    }
    $stmt->close();
}

// build arrays for last 30 days and previous 30 days
$days = [];
$lastPeriod = [];  // last 30 days counts
$prevPeriod = [];  // previous 30 days counts
for ($i = $periodDays-1; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $days[] = $d;
    $lastPeriod[$d] = $reqRows[$d] ?? 0;
}
for ($i = 2*$periodDays-1; $i >= $periodDays; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $prevPeriod[$d] = $reqRows[$d] ?? 0;
}

$totalLast30 = array_sum($lastPeriod);
$totalPrev30 = array_sum($prevPeriod);

// percent change
if ($totalPrev30 === 0) {
    $pctChange = $totalLast30 === 0 ? 0 : 100;
} else {
    $pctChange = round((($totalLast30 - $totalPrev30) / $totalPrev30) * 100, 1);
}
$trendUp = $pctChange >= 0;

/* --------------------
   Recent lists (limit 10)
   -------------------- */
$recentValRequests = safe_fetch_all($conn,
    "SELECT id, client_name, contact_number, address, property_owner_name, property_address, plot_no, area_of_plot, notes, created_at, status
     FROM valuation_requests
     ORDER BY created_at DESC
     LIMIT 10"
);

$recentReviews = safe_fetch_all($conn,
    "SELECT id, name, request_about, rating, review_text, image_path, helpful_count, status, created_at
     FROM reviews
     ORDER BY created_at DESC
     LIMIT 8"
);

$recentServices = safe_fetch_all($conn,
    "SELECT id, title, status FROM services WHERE status = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 6"
);

/* --------------------
   Prepare chart arrays for JS
   -------------------- */
$chartLabels = $days; // last 30 days in order
$chartValues = array_map(function($d) use ($lastPeriod) { return (int)$lastPeriod[$d]; }, $chartLabels);
$chartMax = max(1, max($chartValues));

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard — BBC Engineering Consultancy</title>

  <!-- Fonts & stylesheet -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">

<style>
/* ===== GLOBAL ===== */
body {
  background:#f6f9ff;
  font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  color:#021428;
}

.admin-wrap {
  padding:36px 0;
  max-width:1200px;
  margin:0 auto;
}

.admin-title {
  font-family:Poppins, sans-serif;
  font-size:34px;
  font-weight:800;
  color:#021428;
  margin-bottom:6px;
}

.admin-sub {
  color:#475569;
  font-size:15px;
  margin-bottom:22px;
}

/* ===== STATS ===== */
.stat-grid {
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:18px;
  margin-bottom:18px;
}

.stat-card {
  background:#fff;
  border-radius:14px;
  padding:18px;
  box-shadow:0 10px 30px rgba(2,8,20,.06);
}

.stat-label {
  font-size:12px;
  font-weight:700;
  color:#64748b;
  text-transform:uppercase;
  letter-spacing:.4px;
}

.stat-value {
  font-size:28px;
  font-weight:800;
  margin-top:8px;
}

.stat-note {
  color:#64748b;
  font-size:13px;
  margin-top:8px;
}

.trend {
  display:inline-flex;
  gap:8px;
  align-items:center;
  padding:6px 10px;
  border-radius:999px;
  font-weight:800;
}

.trend.up { background:#dcfce7; color:#166534; }
.trend.down { background:#fee2e2; color:#991b1b; }

/* ===== MAIN LAYOUT ===== */
.main-split {
  display:grid;
  grid-template-columns:1fr 420px;
  gap:18px;
  margin-bottom:18px;
}

.panel {
  background:#fff;
  border-radius:12px;
  padding:18px;
  box-shadow:0 10px 30px rgba(2,8,20,.06);
}

/* ===== CHART ===== */
.chart-header {
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.chart-title {
  font-weight:800;
  font-size:16px;
}

.chart-sub {
  color:#64748b;
  font-size:13px;
}

.bar-chart {
  width:100%;
  height:160px;
  margin-top:12px;
}

.bar {
  cursor:pointer;
  transition:.2s;
}

.bar:hover {
  transform:translateY(-6px);
}

/* ===== HORIZONTAL RECENT REVIEWS ===== */
.recent-list {
  list-style:none;
  padding:0;
  margin-top:14px;
  display:flex;
  gap:18px;
  overflow-x:auto;
  scroll-snap-type:x mandatory;
}

.recent-list::-webkit-scrollbar {
  height:8px;
}

.recent-list::-webkit-scrollbar-thumb {
  background:#dbeafe;
  border-radius:10px;
}

.recent-item {
  flex:0 0 320px;
  background:#fff;
  border-radius:14px;
  padding:16px;
  box-shadow:0 8px 24px rgba(2,8,20,.06);
  border:1px solid #eef2ff;
  scroll-snap-align:start;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  transition:.25s;
}

.recent-item:hover {
  transform:translateY(-6px);
  box-shadow:0 14px 40px rgba(2,8,20,.12);
}

.recent-title {
  font-weight:800;
  font-size:15px;
}

.recent-meta {
  font-size:13px;
  color:#64748b;
  margin-top:6px;
}

.recent-actions {
  display:flex;
  gap:8px;
  margin-top:14px;
  flex-wrap:wrap;
}

/* ===== PILLS ===== */
.pill {
  display:inline-block;
  padding:6px 10px;
  border-radius:999px;
  font-weight:800;
  font-size:12px;
}

.pill.visible { background:#dcfce7; color:#166534; }
.pill.hidden { background:#fee2e2; color:#991b1b; }

/* ===== BUTTONS ===== */
.btn {
  padding:6px 10px;
  border-radius:8px;
  border:none;
  cursor:pointer;
  font-weight:700;
  font-size:13px;
}

.btn-primary {
  background:linear-gradient(90deg,#00D4FF,#635BFF);
  color:#021428;
}

.btn-ghost {
  background:#f1f5f9;
  color:#021428;
}

/* ===== RESPONSIVE ===== */
@media (max-width:1100px) {
  .stat-grid { grid-template-columns:repeat(2,1fr); }
  .main-split { grid-template-columns:1fr; }
}

@media (max-width:640px) {
  .stat-grid { grid-template-columns:1fr; }
  .recent-item { flex:0 0 85%; }
}
</style>

</head>
<body>

<?php include __DIR__ . '/partials/admin-navbar.php'; ?>

<main class="admin-wrap">

  <h1 class="admin-title">Dashboard</h1>
  <p class="admin-sub">Welcome, <strong><?= htmlspecialchars($adminName) ?></strong> — accurate analytics and operational overview</p>

  <!-- Top stats -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-label">Valuation Requests (30d)</div>
      <div style="display:flex;align-items:center;gap:12px;">
        <div class="stat-value"><?= number_format($totalLast30) ?></div>
        <div class="trend <?= $trendUp ? 'up' : 'down' ?>">
          <?= $trendUp ? '▲' : '▼' ?> <?= abs($pctChange) ?>%
        </div>
      </div>
      <div class="stat-note">Compared to previous 30 days (<?= number_format($totalPrev30) ?>)</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Active Services</div>
      <div class="stat-value"><?= number_format($activeServices) ?></div>
      <div class="stat-note">Visible services on the public site</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Client Reviews</div>
      <div style="display:flex;align-items:center;gap:12px;">
        <div class="stat-value"><?= number_format($totalReviews) ?></div>
        <div style="font-size:14px;color:#64748b;font-weight:700"><?= $avgRating !== null ? htmlspecialchars(number_format($avgRating,1)).'/5' : '—' ?></div>
      </div>
      <div class="stat-note"><?= number_format($totalHelpful) ?> helpful votes total</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Team & Partners</div>
      <div class="stat-value"><?= number_format($totalStaff + $totalCompanies) ?></div>
      <div class="stat-note"><?= number_format($totalStaff) ?> staff • <?= number_format($totalCompanies) ?> companies</div>
    </div>
  </div>

  <!-- Main area: left chart + recent lists -->
  <div class="main-split">
    <div class="panel">
      <div class="chart-header">
        <div>
          <div class="chart-title">Daily Valuation Requests — Last 30 days</div>
          <div class="chart-sub">Hover bars for date & exact count. Totals: <strong><?= number_format($totalLast30) ?></strong></div>
        </div>
        <div>
          <button class="btn btn-ghost small" onclick="exportCSV()">Export recent requests (CSV)</button>
          <button class="btn btn-primary small" onclick="location.href='valuation-requests.php'">Open Requests Manager</button>
        </div>
      </div>

      <!-- SVG bar chart -->
      <svg id="barChart" class="bar-chart" viewBox="0 0 800 160" preserveAspectRatio="none" aria-hidden="true">
        <!-- Bars drawn by JS based on data embedded below -->
      </svg>

      <div style="margin-top:10px; color:#64748b; font-size:13px;">
        Showing last 30 calendar days (<?= htmlspecialchars($chartLabels[0]) ?> → <?= htmlspecialchars(end($chartLabels)) ?>)
      </div>
    </div>

    <aside>
      <div class="panel" style="margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div><strong>Requests snapshot</strong><div class="chart-sub">By status</div></div>
          <div style="text-align:right">
            <div style="font-weight:800; font-size:18px"><?= number_format($totalValRequests) ?></div>
            <div class="chart-sub">Total requests</div>
          </div>
        </div>

        <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
          <?php
            if (empty($valByStatus)) {
                echo '<div class="chart-sub">No requests yet</div>';
            } else {
                foreach ($valByStatus as $st => $cnt) {
                    $lbl = status_label($st);
                    echo '<div class="pill" title="'.htmlspecialchars($lbl).'">'.htmlspecialchars($lbl).' • '.number_format($cnt).'</div>';
                }
            }
          ?>
        </div>

        <div style="margin-top:12px;">
          <div style="font-size:13px;color:#64748b">Recent (latest 10)</div>
          <ul class="recent-list" style="margin-top:8px;">
            <?php if (!empty($recentValRequests)): ?>
              <?php foreach ($recentValRequests as $r): ?>
                <li class="recent-item">
                  <div class="recent-left">
                    <div class="recent-title"><?= htmlspecialchars($r['client_name'] ?: '—') ?></div>
                    <div class="recent-meta"><?= htmlspecialchars($r['contact_number'] ?: '-') ?> • <?= htmlspecialchars($r['property_address'] ?? $r['address'] ?? '-') ?></div>
                    <div style="margin-top:6px" class="recent-meta"><?= htmlspecialchars(substr($r['notes'] ?? '',0,80)) ?><?= strlen($r['notes'] ?? '')>80 ? '...' : '' ?></div>
                  </div>
                  <div style="text-align:right">
                    <div class="recent-meta"><?= fmt_dt($r['created_at']) ?></div>
                    <div style="margin-top:6px">
                      <button class="btn small" onclick="location.href='valuation-requests.php?id=<?= (int)$r['id'] ?>'">Open</button>
                    </div>
                    <div style="margin-top:8px">
                      <span class="pill <?= (int)$r['status'] === 1 ? 'visible' : ((int)$r['status'] === 0 ? 'hidden' : '') ?>"><?= htmlspecialchars(status_label($r['status'])) ?></span>
                    </div>
                  </div>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li style="text-align:center;color:#64748b;padding:12px;">No recent valuation requests</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <div class="panel">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div><strong>Recent Reviews</strong><div class="chart-sub">Latest feedback from clients</div></div>
          <div style="font-size:13px;color:#64748b"><?= number_format($totalReviews) ?> total</div>
        </div>

        <ul class="recent-list" style="margin-top:10px;">
          <?php if (!empty($recentReviews)): ?>
            <?php foreach ($recentReviews as $rv): ?>
              <li class="recent-item">
                <div class="recent-left">
                  <div class="recent-title"><?= htmlspecialchars($rv['name'] ?: '—') ?> <span style="font-weight:600;color:#64748b">/ <?= htmlspecialchars($rv['request_about'] ?? '-') ?></span></div>
                  <div class="recent-meta"><?= star_text($rv['rating']) ?> • <?= htmlspecialchars(substr($rv['review_text'],0,110)) ?><?= strlen($rv['review_text'])>110 ? '...' : '' ?></div>
                </div>
                <div style="text-align:right">
                  <div class="recent-meta"><?= fmt_dt($rv['created_at']) ?></div>
                  <div style="margin-top:6px">
                    <button class="btn small" onclick="location.href='clients.php#reviewsTable'">Open</button>
                    <button class="btn small" onclick="toggleReview(<?= (int)$rv['id'] ?>)"><?= (int)$rv['status'] === 1 ? 'Hide' : 'Show' ?></button>
                    <button class="btn small" onclick="deleteReview(<?= (int)$rv['id'] ?>)">Delete</button>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li style="text-align:center;color:#64748b;padding:12px;">No recent reviews</li>
          <?php endif; ?>
        </ul>
      </div>
    </aside>
  </div>

  <!-- Active services preview -->
  <div class="panel" style="margin-bottom:18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div><strong>Active Services (preview)</strong><div class="chart-sub">Quick access to service editor</div></div>
      <div><button class="btn small" onclick="location.href='services.php'">Manage Services</button></div>
    </div>
    <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
      <?php if (!empty($recentServices)): ?>
        <?php foreach ($recentServices as $s): ?>
          <div style="background:#fff;padding:10px 12px;border-radius:10px;box-shadow:0 8px 30px rgba(2,8,20,.04); font-weight:700;"><?= htmlspecialchars($s['title']) ?></div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="chart-sub">No active services</div>
      <?php endif; ?>
    </div>
  </div>

</main>

<!-- Chart tooltip -->
<div id="chartTooltip" class="chart-tooltip" role="status" aria-hidden="true"></div>

<script>
/* --------------------------
   Chart rendering + interactivity
   Data is embedded via PHP-generated arrays
   -------------------------- */

const labels = <?= json_encode($chartLabels, JSON_UNESCAPED_SLASHES) ?>;
const values = <?= json_encode($chartValues) ?>;
const maxValue = <?= json_encode($chartMax) ?>;

// draw bar chart into SVG (#barChart)
(function renderBarChart() {
  const svg = document.getElementById('barChart');
  if (!svg) return;
  // responsive viewBox width 800 -> we'll map bars into width
  const viewW = 800, viewH = 160, padding = 20;
  const innerW = viewW - padding*2, innerH = viewH - padding*2;
  const count = values.length;
  const barGap = Math.max(2, Math.floor(innerW / count * 0.08));
  const barW = Math.max(2, Math.floor((innerW - barGap*(count-1)) / count));
  // Clear
  while (svg.firstChild) svg.removeChild(svg.firstChild);

  // background grid lines (optional)
  for (let i=0;i<=4;i++){
    const y = padding + (innerH * i / 4);
    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', padding);
    line.setAttribute('y1', y);
    line.setAttribute('x2', viewW - padding);
    line.setAttribute('y2', y);
    line.setAttribute('stroke', '#eef6ff');
    line.setAttribute('stroke-width', '1');
    svg.appendChild(line);
  }

  // bars group
  for (let i=0;i<count;i++){
    const v = values[i];
    const x = padding + i * (barW + barGap);
    const h = maxValue > 0 ? Math.round((v / maxValue) * innerH) : 0;
    const y = padding + (innerH - h);
    const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    rect.setAttribute('x', x);
    rect.setAttribute('y', y);
    rect.setAttribute('width', barW);
    rect.setAttribute('height', h);
    rect.setAttribute('rx', 4);
    rect.setAttribute('class', 'bar');
    rect.setAttribute('fill', '#00D4FF');
    rect.dataset.index = i;
    rect.dataset.date = labels[i];
    rect.dataset.value = v;
    svg.appendChild(rect);

    // attach mouse events for tooltip
    rect.addEventListener('mouseenter', (ev)=> showTooltip(ev, labels[i], v));
    rect.addEventListener('mouseleave', hideTooltip);
    rect.addEventListener('mousemove', (ev)=> moveTooltip(ev));
    rect.addEventListener('click', ()=> {
      // on click, open requests filtered by date (link to valuation-requests.php?date=YYYY-MM-DD)
      window.location.href = 'valuation-requests.php?date=' + encodeURIComponent(labels[i]);
    });
  }

  // x-axis labels (every 5 days to avoid clutter)
  const labelGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
  labelGroup.setAttribute('fill', '#64748b');
  for (let i=0;i<count;i+=5) {
    const x = padding + i * (barW + barGap) + barW/2;
    const txt = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    txt.setAttribute('x', x);
    txt.setAttribute('y', viewH - 4);
    txt.setAttribute('font-size', '10');
    txt.setAttribute('text-anchor', 'middle');
    txt.textContent = labels[i].slice(5); // "MM-DD" style
    labelGroup.appendChild(txt);
  }
  svg.appendChild(labelGroup);

})();

const tooltip = document.getElementById('chartTooltip');
function showTooltip(ev, date, value) {
  tooltip.style.display = 'block';
  tooltip.innerHTML = '<strong>' + date + '</strong><div style="font-size:13px;margin-top:4px">' + value + ' requests</div>';
  moveTooltip(ev);
}
function moveTooltip(ev) {
  const pad = 12;
  tooltip.style.left = (ev.clientX) + 'px';
  tooltip.style.top = (ev.clientY - pad) + 'px';
}
function hideTooltip() {
  tooltip.style.display = 'none';
}

/* --------------------------
   Actions: toggleReview, deleteReview (uses existing endpoints)
   -------------------------- */

async function postJSON(url, body) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(body)
  });
  return res.json();
}

async function toggleReview(id) {
  if (!confirm('Toggle review visibility?')) return;
  try {
    const r = await postJSON('review-toggle.php', { id });
    if (r.success) location.reload();
    else alert('Toggle failed: ' + (r.error || 'Unknown'));
  } catch (e) { alert('Toggle failed'); }
}

async function deleteReview(id) {
  if (!confirm('Delete review permanently?')) return;
  try {
    const r = await postJSON('review-delete.php', { id });
    if (r.success) location.reload();
    else alert('Delete failed: ' + (r.error || 'Unknown'));
  } catch (e) { alert('Delete failed'); }
}

/* --------------------------
   CSV Export for recentValRequests
   -------------------------- */
function exportCSV() {
  // Grab recent requests from DOM (PHP also embedded them server-side)
  const rows = <?= json_encode(array_map(function($r){
    return [
      'id' => (int)$r['id'],
      'client_name' => $r['client_name'] ?? '',
      'contact_number' => $r['contact_number'] ?? '',
      'property_address' => $r['property_address'] ?? ($r['address'] ?? ''),
      'plot_no' => $r['plot_no'] ?? '',
      'area_of_plot' => $r['area_of_plot'] ?? '',
      'created_at' => $r['created_at'] ?? '',
      'status' => isset($r['status']) ? $r['status'] : ''
    ];
  }, $recentValRequests), JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK) ?>;

  if (!rows || !rows.length) return alert('No recent requests to export');
  const header = Object.keys(rows[0]);
  const csv = [header.join(',')].concat(rows.map(r => header.map(h => '"' + (('' + (r[h] ?? '')).replace(/"/g,'""')) + '"').join(','))).join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'recent_requests.csv';
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}
</script>

</body>
</html>
