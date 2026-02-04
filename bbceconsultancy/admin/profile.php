<?php
// admin/profile.php
require_once "../config/config.php"; // config opens DB + session

if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: login.php");
  exit;
}

$adminId = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT id, name, email, role, phone, avatar FROM admins WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

$avatar = $admin['avatar'] ?: 'default.png';
$avatarPath = "../uploads/admins/" . $avatar;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Profile — BBC</title>
<link rel="stylesheet" href="../style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Poppins:wght@700&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* Layout */
.profile-wrap{max-width:980px;margin:28px auto;padding:12px}
.profile-card{background:#fff;border-radius:16px;padding:22px 22px 28px 22px;box-shadow:0 18px 40px rgba(2,8,20,.06);display:flex;gap:20px;align-items:flex-start}

/* Make avatar large and centered in its column */
.avatar-col{flex:0 0 260px;display:flex;flex-direction:column;align-items:center;gap:12px;padding:6px}
.avatar-frame{width:220px;height:220px;border-radius:18px;display:grid;place-items:center;background:linear-gradient(180deg,#f8fbff,#eef6ff);border:1px solid #eef6ff}
.avatar-frame img{width:100%;height:100%;object-fit:cover;border-radius:12px}

/* pencil icon — no heavy background; 2D simple icon */
.avatar-edit-label{position:relative;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:50%;background:transparent;border:none}
.avatar-edit-label svg{width:20px;height:20px;fill:#021428;}

/* Form column */
.form-col{flex:1;min-width:0}
.profile-header{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:6px}
.profile-header h2{margin:0;font-weight:800;color:#021428}
.profile-sub{color:#64748b;font-size:14px;margin-top:6px}

/* compact form */
.profile-form{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.profile-form label{font-size:13px;font-weight:700;color:#475569;margin-bottom:6px;display:block}
.form-control{width:100%;padding:10px 12px;border-radius:10px;border:1px solid #e6eefc;background:#fff;font-size:14px}

/* password row uses single column */
.password-row{grid-column:1/-1;position:relative;display:flex;gap:8px;align-items:center}
.password-row input{flex:1;padding-right:44px}
.password-ico{right:12px;top:14px;cursor:pointer;color:#64748b;font-size:18px;user-select:none}

/* actions smaller */
.profile-actions{grid-column:1/-1;display:flex;gap:10px;justify-content:flex-start;margin-top:10px}
.btn-primary{padding:10px 18px;border:none;border-radius:10px;background:#021428;color:#fff;font-weight:800;cursor:pointer;font-size:14px}
.btn-outline{padding:10px 18px;border-radius:10px;border:2px solid #021428;background:#fff;color:#021428;font-weight:800;cursor:pointer;font-size:14px}

/* modal (centered) */
.modal{display:none;position:fixed;inset:0;background:rgba(2,8,20,.6);align-items:center;justify-content:center;z-index:3000}
.modal.active{display:flex}
.modal-box{background:#fff;padding:18px;border-radius:12px;width:100%;max-width:420px}
.modal-box h3{margin:0 0 10px;font-weight:800}
.modal-box input{width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefc}

/* small screens */
@media(max-width:880px){
  .profile-card{flex-direction:column;align-items:stretch}
  .avatar-col{flex:0 0 auto;order:0}
  .form-col{order:1}
  .profile-form{grid-template-columns:1fr}
  .avatar-frame{width:180px;height:180px}
}
</style>
</head>
<body>

<?php include __DIR__ . "/partials/admin-navbar.php"; ?>

<main class="profile-wrap">
  <div class="profile-card">

    <!-- Avatar column -->
    <div class="avatar-col">
      <div class="avatar-frame">
        <img id="avatarPreview" src="<?= htmlspecialchars($avatarPath) ?>" alt="avatar">
      </div>

      <!-- pencil-only label (no heavy bg) -->
      <label class="avatar-edit-label" title="Change profile image">
        <!-- SVG pencil icon -->
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM21.71 6.04a1 1 0 0 0 0-1.41L19.37 2.29a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
        </svg>
        <input id="avatarInput" form="profileForm" type="file" name="avatar" accept="image/*" style="display:none" onchange="previewAvatar(this)">
      </label>

      <div style="text-align:center;">
        <div style="font-weight:800;color:#021428"><?= htmlspecialchars($admin['name']) ?></div>
        <div style="color:#64748b;font-size:13px"><?= htmlspecialchars($admin['role']) ?></div>
      </div>
    </div>

    <!-- Form column -->
    <div class="form-col">
      <div class="profile-header">
        <div>
          <h2>Profile</h2>
          <div class="profile-sub">Edit your public name, phone and avatar</div>
        </div>
      </div>

      <form id="profileForm" class="profile-form" action="profile-update.php" method="post" enctype="multipart/form-data">
        <div>
          <label for="name">Name</label>
          <input id="name" class="form-control" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required>
        </div>

        <div>
          <label for="role">Role</label>
          <input id="role" class="form-control" name="role" value="<?= htmlspecialchars($admin['role']) ?>">
        </div>

        <div>
          <label>Email (locked)</label>
          <input class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" readonly>
        </div>

        <div>
          <label for="phone">Phone</label>
          <input id="phone" class="form-control" name="phone" value="<?= htmlspecialchars($admin['phone']) ?>">
        </div>

        <div class="password-row">
          <div style="flex:1">
            <label>Password</label>
            <input id="passwordField" class="form-control" type="password" value="********" readonly>
          </div>
          <!-- eye icon inside password box (right aligned) -->
          <div class="password-ico" title="View password" onclick="openVerifyModal()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
</svg></div>
        </div>

        <div class="profile-actions">
          <button type="submit" class="btn-primary">Update</button>
          <button type="button" class="btn-outline" onclick="openOtpModal()">Change password</button>
        </div>
      </form>
    </div>

  </div>
</main>

<!-- Verify password modal -->
<div id="verifyModal" class="modal" aria-hidden="true">
  <div class="modal-box" role="dialog" aria-modal="true">
    <h3>Confirm to View Password</h3>
    <p style="color:#64748b;margin:6px 0 12px">Enter your current password to reveal it.</p>
    <input id="verifyInput" type="password" placeholder="Enter current password">
    <div id="verifyError" style="color:#dc2626;margin-top:8px;font-size:13px"></div>
    <div style="display:flex;gap:10px;margin-top:12px;">
      <button class="btn-primary" onclick="submitVerify()">Verify</button>
      <button class="btn-outline" onclick="closeVerify()">Cancel</button>
    </div>
  </div>
</div>

<!-- OTP modal (stub) -->
<div id="otpModal" class="modal" aria-hidden="true">
  <div class="modal-box">
    <h3>Change password</h3>
    <p style="color:#64748b">We'll send an OTP to <strong><?= htmlspecialchars($admin['email']) ?></strong></p>
    <div style="margin-top:10px">
      <button class="btn-primary" onclick="closeOtp()">Send OTP</button>
      <button class="btn-outline" onclick="closeOtp()">Cancel</button>
    </div>
  </div>
</div>

<script>
/* Avatar preview */
function previewAvatar(input){
  const f = input.files[0];
  if (!f) return;
  document.getElementById('avatarPreview').src = URL.createObjectURL(f);
}

/* Verify modal handling */
function openVerifyModal(){
  document.getElementById('verifyModal').classList.add('active');
  document.getElementById('verifyError').innerText = '';
  document.getElementById('verifyInput').value = '';
}
function closeVerify(){
  document.getElementById('verifyModal').classList.remove('active');
}

/* OTP modal */
function openOtpModal(){
  document.getElementById('otpModal').classList.add('active');
}
function closeOtp(){
  document.getElementById('otpModal').classList.remove('active');
}

/* AJAX verify: posts the entered password to profile-verify.php */
function submitVerify(){
  const val = document.getElementById('verifyInput').value.trim();
  const err = document.getElementById('verifyError');
  err.innerText = '';
  if (!val) { err.innerText = 'Password is required'; return; }

  const data = new FormData();
  data.append('attempt', val);

  fetch('profile-verify.php', { method: 'POST', body: data })
    .then(r => r.json())
    .then(resp => {
      if (resp.ok){
        // show the real password in field
        const pf = document.getElementById('passwordField');
        pf.type = 'text';
        pf.value = resp.password;
        closeVerify();
        // replace eye icon behavior to hide
        document.querySelector('.password-ico').innerText = '🙈';
        document.querySelector('.password-ico').onclick = function(){ hidePassword(); };
      } else {
        err.innerText = resp.message || 'Incorrect password';
      }
    })
    .catch(e => {
      err.innerText = 'Server error';
      console.error(e);
    });
}

function hidePassword(){
  const pf = document.getElementById('passwordField');
  pf.type = 'password';
  pf.value = '********';
  document.querySelector('.password-ico').innerText = '👁';
  document.querySelector('.password-ico').onclick = openVerifyModal;
}

/* small enhancement: clicking pencil label triggers file input */
document.querySelector('.avatar-edit-label').addEventListener('click', function(){
  document.getElementById('avatarInput').click();
});
</script>
</body>
</html>
