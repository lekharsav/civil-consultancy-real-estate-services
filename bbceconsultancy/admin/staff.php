<?php
// admin/staff.php
require_once "../config/config.php";

// guard: require login
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  header("Location: login.php");
  exit;
}

// fetch staff from DB
$rows = [];
$stmt = $conn->prepare("SELECT id, name, category, designation, phone, email, image FROM staff ORDER BY name ASC");
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $rows[] = $r;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Staff Directory — Admin</title>

  <!-- global admin stylesheet (ensure navbar styling is present) -->
  <link rel="stylesheet" href="../style.css">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <style>
    :root{ --accent:#021428; --muted:#64748b; --bg:#f6f9ff; }
    *{box-sizing:border-box}

    /* Scope all page-specific visual rules under .staff-page to avoid collisions with navbar */
    .staff-page { font-family: Inter, system-ui; background: var(--bg); color: var(--accent); min-height: 100vh; }

    .staff-page .container{max-width:1100px;margin:18px auto;padding:12px;}

    /* Make existing header sticky without modifying include file */
    .staff-page .page-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin:18px 0;flex-wrap:wrap}
    .staff-page .title{font-family:Poppins, sans-serif;font-size:34px;font-weight:800}
    .staff-page .subtitle{color:var(--muted);font-size:15px}

    .staff-page .actions{display:flex;gap:12px;align-items:center}
    .staff-page .search{padding:9px 12px;border-radius:10px;border:1px solid #e6eefc;background:#fff;min-width:220px}

    .staff-page .btn{padding:10px 14px;border-radius:10px;border:none;font-weight:800;cursor:pointer}
    .staff-page .btn-primary{background:linear-gradient(90deg,#00D4FF,#635BFF); color:#021428}
    .staff-page .btn-ghost{background:#fff;border:1px solid rgba(2,8,20,0.06);color:var(--accent)}

    .staff-page .staff-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px}
    .staff-page .card{background:#fff;border-radius:12px;padding:14px;text-align:center;box-shadow:0 8px 30px rgba(2,8,20,.06);cursor:pointer;transition:transform .16s,box-shadow .16s}
    .staff-page .card:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(2,8,20,.08)}
    .staff-page .avatar{width:118px;height:118px;border-radius:50%;overflow:hidden;margin:0 auto;border:6px solid #fff;box-shadow:0 10px 28px rgba(2,8,20,.06)}
    .staff-page .avatar img{width:100%;height:100%;object-fit:cover}
    .staff-page .name{margin-top:12px;font-weight:800}
    .staff-page .category{margin-top:6px;font-size:12px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.5px}

    /* Modal (scoped) */
    .staff-page .modal{display:none;position:fixed;inset:0;background:rgba(2,8,20,.6);align-items:center;justify-content:center;z-index:9999;padding:18px}
    .staff-page .modal.active{display:flex}
    .staff-page .modal-card{background:#fff;border-radius:14px;padding:20px;width:100%;max-width:820px;box-shadow:0 30px 90px rgba(2,8,20,.12);max-height:86vh;overflow:auto}
    .staff-page .modal-grid{display:grid;grid-template-columns:260px 1fr;gap:18px}
    .staff-page .modal-avatar{width:100%;height:260px;border-radius:12px;overflow:hidden}
    .staff-page .modal-avatar img{width:100%;height:100%;object-fit:cover}
    .staff-page .h2{font-family:Poppins;font-size:20px;margin:0 0 6px}

    .staff-page .meta{display:flex;gap:8px;margin:8px 0;align-items:flex-start}
    .staff-page .label{min-width:120px;color:var(--muted);font-weight:700}
    .staff-page .value{font-weight:700;color:var(--accent)}

    .staff-page .modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:14px}
    .staff-page .btn-small{padding:8px 12px;border-radius:10px;font-weight:800}

    /* small screens */
    @media(max-width:880px){
      .staff-page .modal-grid{grid-template-columns:1fr}
      .staff-page .modal-avatar{height:220px}
    }
  </style>
</head>
<body>

  <?php include __DIR__ . '/partials/admin-navbar.php'; ?>

  <main class="staff-page">
    <div class="container">
      <div class="page-head">
        <div>
          <div class="title">Staff Directory</div>
          <div class="subtitle">Click a staff card to view, edit or delete details.</div>
        </div>

        <div class="actions">
          <input id="search" class="search" placeholder="Search staff by name or category">
          <button class="btn btn-primary" onclick="openAdd()">+ Add Staff</button>
        </div>
      </div>

      <section class="staff-grid" id="grid">
        <?php foreach ($rows as $r): ?>
          <?php
            $imgPath = $r['image'] ? "../uploads/staff/".htmlspecialchars($r['image']) : "../uploads/staff/default-staff.png";
          ?>
          <article class="card" tabindex="0"
                   data-id="<?= (int)$r['id'] ?>"
                   data-name="<?= htmlspecialchars($r['name']) ?>"
                   data-category="<?= htmlspecialchars($r['category']) ?>"
                   data-designation="<?= htmlspecialchars($r['designation']) ?>"
                   data-phone="<?= htmlspecialchars($r['phone']) ?>"
                   data-email="<?= htmlspecialchars($r['email']) ?>"
                   data-img="<?= htmlspecialchars($imgPath) ?>"
                   onclick="openDetail(this)">
            <div class="avatar"><img src="<?= $imgPath ?>" alt=""></div>
            <div class="name"><?= htmlspecialchars($r['name']) ?></div>
            <div class="category"><?= htmlspecialchars($r['category']) ?></div>
          </article>
        <?php endforeach; ?>
      </section>

      <!-- Detail modal (moved inside .staff-page to allow scoped CSS) -->
      <div id="detailModal" class="modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <div style="font-weight:800">Staff Details</div>
            <div>
              <button class="btn btn-ghost" onclick="closeDetail()">Close</button>
              <button class="btn btn-primary" id="editBtn" onclick="openEdit()" style="margin-left:10px">Edit</button>
            </div>
          </div>

          <div class="modal-grid">
            <div class="modal-avatar"><img id="mImg" src="" alt=""></div>
            <div>
              <h3 class="h2" id="mName"></h3>
              <div class="meta"><div class="label">Category</div><div class="value" id="mCategory">—</div></div>
              <div class="meta"><div class="label">Designation</div><div class="value" id="mDesignation">—</div></div>
              <div class="meta"><div class="label">Phone</div><div class="value" id="mPhone">—</div></div>
              <div class="meta"><div class="label">Email</div><div class="value" id="mEmail">—</div></div>

              <div class="modal-actions">
                <button class="btn btn-ghost btn-small" id="deleteBtn" onclick="deleteStaff()">Delete</button>
                <button class="btn btn-primary btn-small" id="messageBtn" onclick="sendMessage()">Send Message</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add/Edit Modal (moved inside .staff-page) -->
      <div id="addModal" class="modal" aria-hidden="true">
        <div class="modal-card">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <div style="font-weight:800" id="addTitle">Add Staff</div>
            <div>
              <button class="btn btn-ghost" onclick="closeAdd()">Close</button>
            </div>
          </div>

          <form id="staffForm" enctype="multipart/form-data" onsubmit="submitForm(event)">
            <input type="hidden" name="id" id="fId" value="">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
              <input name="name" id="fName" placeholder="Full name" required style="padding:10px;border-radius:8px;border:1px solid #e8f0ff">
              <input name="category" id="fCategory" placeholder="Category (e.g. Senior Engineer)" required style="padding:10px;border-radius:8px;border:1px solid #e8f0ff">
              <input name="designation" id="fDesignation" placeholder="Designation (optional)" style="padding:10px;border-radius:8px;border:1px solid #e8f0ff">
              <input name="phone" id="fPhone" placeholder="Phone" style="padding:10px;border-radius:8px;border:1px solid #e8f0ff">
              <input name="email" id="fEmail" placeholder="Email" style="padding:10px;border-radius:8px;border:1px solid #e8f0ff">
            </div>

            <div style="display:flex;gap:8px;align-items:center;margin-top:10px">
              <input type="file" name="image" id="fImage" accept="image/*">
              <small style="color:var(--muted)">Optional image — saved to uploads/staff/</small>
            </div>

            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
              <button type="button" class="btn btn-ghost" onclick="closeAdd()">Cancel</button>
              <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>

<script>
  let currentId = null;

  function openDetail(el){
    const id = el.dataset.id;
    currentId = id;
    document.getElementById('mImg').src = el.dataset.img || '../uploads/staff/default-staff.png';
    document.getElementById('mName').innerText = el.dataset.name || '—';
    document.getElementById('mCategory').innerText = el.dataset.category || '—';
    document.getElementById('mDesignation').innerText = el.dataset.designation || '—';
    document.getElementById('mPhone').innerText = el.dataset.phone || '—';
    document.getElementById('mEmail').innerText = el.dataset.email || '—';
    document.getElementById('detailModal').classList.add('active');
  }

  function closeDetail(){ document.getElementById('detailModal').classList.remove('active'); }

  function openAdd(){
    document.getElementById('addTitle').innerText = 'Add Staff';
    document.getElementById('fId').value = '';
    document.getElementById('fName').value = '';
    document.getElementById('fCategory').value = '';
    document.getElementById('fDesignation').value = '';
    document.getElementById('fPhone').value = '';
    document.getElementById('fEmail').value = '';
    document.getElementById('fImage').value = '';
    document.getElementById('addModal').classList.add('active');
  }

  function openEdit(){
    // populate form with current staff values for editing
    const name = document.getElementById('mName').innerText;
    document.getElementById('addTitle').innerText = 'Edit Staff';
    document.getElementById('fId').value = currentId;
    document.getElementById('fName').value = name;
    document.getElementById('fCategory').value = document.getElementById('mCategory').innerText;
    document.getElementById('fDesignation').value = document.getElementById('mDesignation').innerText === '—' ? '' : document.getElementById('mDesignation').innerText;
    document.getElementById('fPhone').value = document.getElementById('mPhone').innerText === '—' ? '' : document.getElementById('mPhone').innerText;
    document.getElementById('fEmail').value = document.getElementById('mEmail').innerText === '—' ? '' : document.getElementById('mEmail').innerText;
    document.getElementById('addModal').classList.add('active');
  }

  function closeAdd(){ document.getElementById('addModal').classList.remove('active'); }

  // submit add/edit via fetch to staff-save.php
  async function submitForm(e){
    e.preventDefault();
    const form = document.getElementById('staffForm');
    const fd = new FormData(form);

    const res = await fetch('staff-save.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success){
      // reload or update small parts — we'll reload for simplicity
      location.reload();
    } else {
      alert('Save failed: ' + (data.error || 'Unknown error'));
    }
  }

  // delete via fetch to staff-delete.php
  async function deleteStaff(){
    if(!currentId) return;
    if(!confirm('Delete this staff record? This will remove the image file as well.')) return;
    const res = await fetch('staff-delete.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ id: currentId })
    });
    const data = await res.json();
    if(data.success){
      location.reload();
    } else {
      alert('Delete failed: ' + (data.error || 'Unknown'));
    }
  }

  function sendMessage(){
    alert('Not implemented yet — integrate SMS / WhatsApp API.');
  }

  // search helper
  document.getElementById('search').addEventListener('input', function(){
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('.card').forEach(card=>{
      const name = card.querySelector('.name').innerText.toLowerCase();
      const cat = card.querySelector('.category').innerText.toLowerCase();
      card.style.display = (!q || name.includes(q) || cat.includes(q)) ? '' : 'none';
    });
  });

  // accessibility: open card on Enter
  document.querySelectorAll('.card').forEach(c=>{
    c.addEventListener('keydown', e=>{
      if(e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDetail(c); }
    });
  });

  // close modals on escape or outside click
  document.getElementById('detailModal').addEventListener('click', e=>{ if(e.target === e.currentTarget) closeDetail(); });
  document.getElementById('addModal').addEventListener('click', e=>{ if(e.target === e.currentTarget) closeAdd(); });
  window.addEventListener('keydown', e=>{ if(e.key === 'Escape'){ closeAdd(); closeDetail(); } });
</script>

</body>
</html>
