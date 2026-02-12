<?php
// admin/valuation-requests.php
require_once __DIR__ . "/../config/config.php";

// guard: require login
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  header("Location: login.php");
  exit;
}

// detect if 'status' column exists (we'll display it if present; otherwise default "New")
$has_status = false;
$colRes = $conn->query("SHOW COLUMNS FROM `valuation_requests` LIKE 'status'");
if ($colRes && $colRes->num_rows > 0) $has_status = true;

// fetch valuation requests
$rows = [];
$stmt = $conn->prepare("SELECT id, client_name, contact_number, address, property_owner_name, property_address, plot_no, area_of_plot, notes, created_at" . ($has_status ? ", status" : "") . " FROM valuation_requests ORDER BY created_at DESC");
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $rows[] = $r;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Valuation Requests — Admin | BBC Engineering Consultancy</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">

  <style>
    body { background:#f6f9ff; }

    .admin-wrap { padding:40px 0; }

    .page-title {
      font-family:Poppins,sans-serif;
      font-size:32px;
      font-weight:800;
      color:#021428;
      margin-bottom:6px;
    }

    .page-sub {
      font-size:14px;
      color:#475569;
      margin-bottom:30px;
    }

    /* TABLE */
    .table-box {
      background:#fff;
      border-radius:18px;
      box-shadow:0 12px 30px rgba(2,8,20,.08);
      overflow:hidden;
    }

    table {
      width:100%;
      border-collapse:collapse;
      font-size:14px;
    }

    thead {
      background:#021428;
      color:#fff;
    }

    th, td {
      padding:14px 16px;
      text-align:left;
      vertical-align:top;
    }

    tbody tr {
      border-bottom:1px solid #eef2ff;
    }

    tbody tr:hover {
      background:#f8fbff;
    }

    /* STATUS */
    .status {
      padding:6px 12px;
      border-radius:20px;
      font-size:12px;
      font-weight:700;
      display:inline-block;
      transition: background-color .35s ease, color .35s ease, transform .2s ease, box-shadow .2s ease;
    }

    .new { background:#e0f2fe; color:#0369a1; }
    .contacted { background:#fff7ed; color:#92400e; }
    .visited { background:#ecfeff; color:#155e75; }
    .completed { background:#dcfce7; color:#166534; }

    /* animate pulse */
    .status.animate {
      transform: scale(1.06);
      box-shadow: 0 8px 30px rgba(37,99,235,0.12);
    }

    /* ACTIONS */
    .action-btn {
      padding:6px 10px;
      font-size:12px;
      border-radius:8px;
      border:none;
      cursor:pointer;
      font-weight:700;
      margin-right:6px;
    }

    .view { background:#e0e7ff; color:#1e40af; }
    .edit { background:#fef3c7; color:#92400e; }
    .delete { background:#fee2e2; color:#991b1b; }

    /* ===== Enhanced Modal Design ===== */
    .vr-modal {
      display:none;
      position:fixed;
      inset:0;
      background:rgba(2,8,20,.65);
      backdrop-filter: blur(6px);
      align-items:center;
      justify-content:center;
      z-index:9999;
      padding:20px;
      animation: fadeIn .25s ease;
    }

    .vr-modal.active { display:flex; }

    .vr-card {
      background:#fff;
      border-radius:18px;
      padding:0;
      width:100%;
      max-width:940px;
      box-shadow:0 40px 100px rgba(2,8,20,.22);
      max-height:92vh;
      overflow:hidden;
      animation: slideUp .3s ease;
    }

    /* Gradient header strip */
    .vr-header {
      padding:16px 20px;
      background:linear-gradient(90deg,#021428,#015383,#2563eb);
      color:#fff;
      font-weight:800;
      font-family:Poppins;
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
    }

    .vr-header .vr-title { font-size:16px; letter-spacing:0.2px; }
    .vr-header .vr-subtle { font-size:13px; opacity:.9; font-weight:600; }

    .vr-body {
      padding:20px 24px 28px 24px;
    }

    .vr-grid {
      display:grid;
      grid-template-columns:1fr 320px;
      gap:24px;
    }

    .vr-left h3 {
      margin:0 0 6px 0;
      font-family:Poppins;
      font-size:20px;
      font-weight:800;
      color:#021428;
    }

    .vr-meta {
      margin:12px 0;
      font-size:14px;
      color:#334155;
    }

    .vr-meta strong {
      display:flex;
      align-items:center;
      gap:8px;
      font-size:12px;
      text-transform:uppercase;
      letter-spacing:.5px;
      color:#64748b;
      margin-bottom:6px;
    }

    .vr-right {
      border-left:1px solid #eef2ff;
      padding-left:18px;
      background:#f8fbff;
      border-radius:12px;
      padding:18px;
    }

    .vr-actions {
      display:flex;
      gap:10px;
      justify-content:flex-end;
      margin-top:20px;
    }

    /* Button hover premium */
    .vr-card .action-btn {
      transition:.25s ease;
    }

    .vr-card .action-btn:hover {
      transform:translateY(-2px);
      box-shadow:0 8px 20px rgba(2,8,20,.15);
    }

    /* Form styling (Update modal) */
    #updateForm label { display:block; }
    #updateForm label .lbl-title { font-size:12px; font-weight:700; text-transform:uppercase; color:#64748b; display:block; margin-bottom:6px; }
    #updateForm select,
    #updateForm textarea {
      margin-top:6px;
      background:#fff;
      transition:.25s ease;
      width:100%;
      padding:10px;
      border-radius:8px;
      border:1px solid #eef2ff;
      font-size:14px;
    }

    #updateForm select:focus,
    #updateForm textarea:focus {
      outline:none;
      border-color:#6366f1;
      box-shadow:0 0 0 4px rgba(99,102,241,.12);
    }

    /* Toast */
    .admin-toast {
      position:fixed;
      top:28px;
      right:-420px;
      background:linear-gradient(90deg,#0f172a,#021428);
      color:#fff;
      padding:14px 18px;
      border-radius:10px;
      font-size:14px;
      font-weight:700;
      box-shadow:0 15px 40px rgba(2,8,20,.24);
      transition:right .4s ease;
      z-index:10001;
      display:flex;
      align-items:center;
      gap:10px;
      min-width:260px;
    }

    .admin-toast.show { right:30px; }

    /* small screens */
    @media(max-width:900px){
      .vr-grid { grid-template-columns:1fr; }
      .vr-right { border-left:none; padding-left:0; }
    }

    @media(max-width:1024px){
      table { font-size:13px; }
    }

    @media(max-width:768px){
      table, thead, tbody, th, td, tr { display:block; }
      thead { display:none; }

      tbody tr {
        padding:14px;
        border-bottom:1px solid #e5e7eb;
      }

      td {
        padding:8px 0;
      }

      td::before {
        content:attr(data-label);
        font-weight:700;
        display:block;
        color:#64748b;
        font-size:12px;
        margin-bottom:4px;
      }
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity:0; }
      to { opacity:1; }
    }

    @keyframes slideUp {
      from { transform:translateY(20px); opacity:0; }
      to { transform:translateY(0); opacity:1; }
    }
  </style>
</head>

<body>

<!-- ================= ADMIN NAVBAR ================= -->
<?php include __DIR__ . '/partials/admin-navbar.php'; ?>

<!-- ================= CONTENT ================= -->
<main class="container admin-wrap">

  <h1 class="page-title">Valuation Requests</h1>
  <p class="page-sub">Manage incoming property valuation and site inspection requests</p>

  <div class="table-box">
    <table>
      <thead>
        <tr>
          <th>Client</th>
          <th>Contact</th>
          <th>Property</th>
          <th>Plot / Area</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:36px; color:#64748b">No valuation requests yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
              $id = (int)$r['id'];
              $client = htmlspecialchars($r['client_name'] ?: '—');
              $contact = htmlspecialchars($r['contact_number'] ?: '—');
              $addr   = htmlspecialchars($r['address'] ?: '—');
              $owner  = htmlspecialchars($r['property_owner_name'] ?: '—');
              $prop_addr = htmlspecialchars($r['property_address'] ?: '—');
              $plot   = htmlspecialchars($r['plot_no'] ?: '—');
              $area   = htmlspecialchars($r['area_of_plot'] ?: '—');
              $notes  = htmlspecialchars($r['notes'] ?: '—');
              $notes_br = nl2br($notes);
              $created = htmlspecialchars(date('Y-m-d H:i', strtotime($r['created_at'])));
              $status = $has_status ? htmlspecialchars($r['status'] ?? 'New') : 'New';

              // map status class (safe fallback)
              $sclass = 'new';
              $skey = strtolower($status);
              if (strpos($skey, 'contact') !== false) $sclass = 'contacted';
              elseif (strpos($skey, 'visit') !== false) $sclass = 'visited';
              elseif (strpos($skey, 'complete') !== false) $sclass = 'completed';
              else $sclass = 'new';
            ?>
            <tr id="vr-row-<?= $id ?>"
                data-id="<?= $id ?>"
                data-client="<?= $client ?>"
                data-contact="<?= $contact ?>"
                data-address="<?= $addr ?>"
                data-owner="<?= $owner ?>"
                data-property_address="<?= $prop_addr ?>"
                data-plot="<?= $plot ?>"
                data-area="<?= $area ?>"
                data-notes="<?= $notes ?>"
                data-created="<?= $created ?>"
                data-status="<?= htmlspecialchars($status) ?>">

              <td data-label="Client"><?= $client ?><br><small style="color:#64748b"><?= $created ?></small></td>

              <td data-label="Contact">📞 <?= $contact ?><br>📍 <?= $addr ?></td>

              <td data-label="Property"><?= $owner ?><br><small style="color:#64748b"><?= $prop_addr ?></small></td>

              <td data-label="Plot / Area"><?= $plot ?><br><?= $area ?></td>

              <td data-label="Status"><span class="status <?= $sclass ?>"><?= htmlspecialchars(ucfirst($status)) ?></span></td>

              <td data-label="Actions">
                <button class="action-btn view" data-action="view">View</button>
                <button class="action-btn edit" data-action="edit">Update</button>
                <button class="action-btn delete" data-action="delete">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</main>

<!-- VIEW Modal -->
<div id="viewModal" class="vr-modal" aria-hidden="true">
  <div class="vr-card" role="dialog" aria-modal="true">
    <div class="vr-header">
      <div>
        <div class="vr-title">Valuation Request Details</div>
        <div class="vr-subtle">View request information</div>
      </div>
      <div>
        <button onclick="closeView()" class="action-btn" style="background:#fff;color:#021428">Close</button>
      </div>
    </div>

    <div class="vr-body">
      <div class="vr-grid">
        <div class="vr-left">
          <h3 id="vClient">—</h3>

          <div class="vr-meta">
            <strong>📞 Contact</strong>
            <div id="vContact">—</div>
          </div>

          <div class="vr-meta">
            <strong>📍 Address</strong>
            <div id="vAddress">—</div>
          </div>

          <div class="vr-meta">
            <strong>👤 Property Owner</strong>
            <div id="vOwner">—</div>
          </div>

          <div class="vr-meta">
            <strong>🏷 Property Address</strong>
            <div id="vPropAddr">—</div>
          </div>

          <div class="vr-meta">
            <strong>📐 Plot / Area</strong>
            <div id="vPlotArea">—</div>
          </div>

          <div class="vr-meta">
            <strong>📝 Notes</strong>
            <div id="vNotes" style="margin-top:6px;color:#334155">—</div>
          </div>
        </div>

        <div class="vr-right">
          <div class="vr-meta">
            <strong>⏱ Submitted</strong>
            <div id="vCreated">—</div>
          </div>

          <div class="vr-meta">
            <strong>🔖 Status</strong>
            <div id="vStatus"><span class="status new">New</span></div>
          </div>

          <div class="vr-actions" style="margin-top:18px">
            <button class="action-btn edit" onclick="openUpdateFromView()">Update</button>
            <button class="action-btn delete" onclick="deleteFromView()">Delete</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- UPDATE Modal -->
<div id="updateModal" class="vr-modal" aria-hidden="true">
  <div class="vr-card" role="dialog" aria-modal="true">
    <div class="vr-header">
      <div>
        <div class="vr-title">Update Valuation Request</div>
        <div class="vr-subtle">Change status or add admin note</div>
      </div>
      <div>
        <button onclick="closeUpdate()" class="action-btn" style="background:#fff;color:#021428">Close</button>
      </div>
    </div>

    <div class="vr-body">
      <form id="updateForm">
        <input type="hidden" name="id" id="uId" value="">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <label>
            <span class="lbl-title">Status</span>
            <select name="status" id="uStatus">
              <option value="New">New</option>
              <option value="Contacted">Contacted</option>
              <option value="Site Visited">Site Visited</option>
              <option value="Completed">Completed</option>
            </select>
          </label>

          <label>
            <span class="lbl-title">Notes (admin)</span>
            <textarea name="admin_notes" id="uNotes" rows="3" placeholder="Optional admin note"></textarea>
          </label>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
          <button type="button" class="action-btn" onclick="closeUpdate()">Cancel</button>
          <button type="submit" class="action-btn edit">Save</button>
        </div>
      </form>
    </div>

  </div>
</div>

<!-- Admin success toast -->
<div id="adminToast" class="admin-toast" role="status" aria-live="polite" aria-atomic="true">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="opacity:.9">
    <path d="M20 6L9 17l-5-5" stroke="#10B981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <div id="adminToastMessage">Status updated successfully</div>
</div>

<script>
  // Helpers
  function qs(sel, parent=document) { return parent.querySelector(sel); }
  function qsa(sel, parent=document) { return Array.from(parent.querySelectorAll(sel)); }

  // map status to class (client-side)
  function mapStatusClass(status) {
    if (!status) return 'new';
    const s = String(status).toLowerCase();
    if (s.includes('contact')) return 'contacted';
    if (s.includes('visit')) return 'visited';
    if (s.includes('complete')) return 'completed';
    return 'new';
  }

  // show admin toast
  function showAdminToast(message) {
    const toast = qs('#adminToast');
    const msg = qs('#adminToastMessage');
    if (!toast) return;
    msg.textContent = message || 'Saved';
    toast.classList.add('show');
    // auto hide
    setTimeout(()=> toast.classList.remove('show'), 3000);
  }

  // Wire row buttons
  document.addEventListener('DOMContentLoaded', function(){
    qsa('tbody tr').forEach(tr => {
      const viewBtn = tr.querySelector('button[data-action="view"]');
      const editBtn = tr.querySelector('button[data-action="edit"]');
      const delBtn  = tr.querySelector('button[data-action="delete"]');

      if (viewBtn) viewBtn.addEventListener('click', () => openView(tr));
      if (editBtn) editBtn.addEventListener('click', () => openUpdate(tr));
      if (delBtn)  delBtn.addEventListener('click', () => confirmDelete(tr));
    });

    // update form submit
    const updateForm = qs('#updateForm');
    if (updateForm) {
      updateForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const id = qs('#uId').value;
        const status = qs('#uStatus').value;
        const adminNotes = qs('#uNotes').value;

        try {
          const res = await fetch('valuation-update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, status: status, admin_notes: adminNotes })
          });
          const data = await res.json();
          if (data.success) {
            // update UI row status text & class without full reload
            const row = document.getElementById('vr-row-' + id);
            if (row) {
              row.dataset.status = status;
              const statusEl = row.querySelector('td[data-label="Status"] .status');
              if (statusEl) {
                const cls = mapStatusClass(status);
                statusEl.textContent = status;
                statusEl.className = 'status ' + cls;
                // pulse animation
                statusEl.classList.add('animate');
                setTimeout(()=> statusEl.classList.remove('animate'), 450);
              }
            }

            // update view modal if open
            if (qs('#viewModal').classList.contains('active')) {
              const rowForView = document.getElementById('vr-row-' + id);
              if (rowForView) populateViewFromRow(rowForView);
            }

            closeUpdate();

            // show toast
            showAdminToast('Status updated successfully');
          } else {
            alert('Update failed: ' + (data.error || 'Unknown error'));
          }
        } catch (err) {
          console.error(err);
          alert('Server error');
        }
      });
    }
  });

  // Open view modal and populate
  function openView(tr) {
    populateViewFromRow(tr);
    qs('#viewModal').classList.add('active');
  }
  function populateViewFromRow(tr) {
    if (!tr) return;
    const client = tr.dataset.client || '—';
    const contact = tr.dataset.contact || '—';
    const address = tr.dataset.address || '—';
    const owner = tr.dataset.owner || '—';
    const propAddr = tr.dataset.property_address || '—';
    const plot = tr.dataset.plot || '—';
    const area = tr.dataset.area || '—';
    const notes = tr.dataset.notes || '—';
    const created = tr.dataset.created || '—';
    const status = tr.dataset.status || 'New';

    qs('#vClient').innerText = client;
    qs('#vContact').innerText = contact;
    qs('#vAddress').innerText = address;
    qs('#vOwner').innerText = owner;
    qs('#vPropAddr').innerText = propAddr;
    qs('#vPlotArea').innerHTML = (plot) + '<br>' + (area);
    qs('#vNotes').innerHTML = (notes || '—').replace(/\n/g,'<br>');
    qs('#vCreated').innerText = created;

    // status badge in view (with animation)
    const vStatusEl = qs('#vStatus');
    if (vStatusEl) {
      const cls = mapStatusClass(status);
      vStatusEl.innerHTML = '<span class="status ' + cls + '">' + status + '</span>';
      // small pulse to draw attention
      const b = vStatusEl.querySelector('.status');
      if (b) {
        b.classList.add('animate');
        setTimeout(()=> b.classList.remove('animate'), 450);
      }
    }
  }
  function closeView(){ qs('#viewModal').classList.remove('active'); }

  // Open update modal with row data
  function openUpdate(tr){
    qs('#uId').value = tr.dataset.id || '';
    qs('#uStatus').value = tr.dataset.status || 'New';
    qs('#uNotes').value = tr.dataset.notes || '';
    qs('#updateModal').classList.add('active');
  }
  function openUpdateFromView(){
    const client = qs('#vClient').innerText;
    const created = qs('#vCreated').innerText;
    const tr = Array.from(document.querySelectorAll('tbody tr')).find(r => r.dataset.client === client && r.dataset.created === created);
    if (tr) {
      closeView();
      openUpdate(tr);
    } else {
      alert('Unable to find the row to update.');
    }
  }
  function closeUpdate(){ qs('#updateModal').classList.remove('active'); }

  // Delete flow
  async function confirmDelete(tr){
    if(!confirm('Delete this valuation request? This action cannot be undone.')) return;
    const id = tr.dataset.id;
    await doDelete(id);
  }

  async function deleteFromView(){
    const client = qs('#vClient').innerText;
    const created = qs('#vCreated').innerText;
    const tr = Array.from(document.querySelectorAll('tbody tr')).find(r => r.dataset.client === client && r.dataset.created === created);
    if (!tr) { alert('Row not found'); return; }
    if (!confirm('Delete this valuation request?')) return;
    await doDelete(tr.dataset.id);
  }

  async function doDelete(id) {
    try {
      const res = await fetch('valuation-delete.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ id: id })
      });
      const data = await res.json();
      if (data.success) {
        // remove row
        const row = document.getElementById('vr-row-' + id);
        if (row) row.remove();
        closeView();
        showAdminToast('Request deleted');
      } else {
        alert('Delete failed: ' + (data.error || 'Unknown'));
      }
    } catch (err) {
      console.error(err);
      alert('Server error');
    }
  }

  // close modals on outside click or escape
  ['viewModal','updateModal'].forEach(id=>{
    const el = qs('#' + id);
    if (el) {
      el.addEventListener('click', function(e){ if (e.target === e.currentTarget) el.classList.remove('active'); });
    }
  });
  window.addEventListener('keydown', function(e){ if (e.key === 'Escape') { ['viewModal','updateModal'].forEach(id => { const el = qs('#'+id); if(el) el.classList.remove('active'); }); } });

</script>

</body>
</html>
