<?php
// admin/partials/admin-navbar.php
// Assumes session is already started and (optionally) config.php included earlier.
// If you sometimes include this partial before session_start(), uncomment the line below.
// if (session_status() === PHP_SESSION_NONE) session_start();

$currentPage = basename($_SERVER['PHP_SELF']);
$adminName   = $_SESSION['admin_name'] ?? 'Admin';
$adminAvatar = $_SESSION['admin_avatar'] ?? null;

// if avatar not in session, attempt to use known $_SESSION['admin_id'] and fetch from DB (safe fallback)
if (!$adminAvatar && isset($_SESSION['admin_id'])) {
    // Attempt to fetch avatar only if $conn exists (some pages include config before partial)
    if (isset($conn)) {
        $aid = (int) $_SESSION['admin_id'];
        $q = $conn->prepare("SELECT avatar FROM admins WHERE id = ? LIMIT 1");
        if ($q) {
            $q->bind_param("i", $aid);
            $q->execute();
            $res = $q->get_result();
            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();
                $adminAvatar = $row['avatar'];
                // optionally cache in session
                $_SESSION['admin_avatar'] = $adminAvatar;
            }
        }
    }
}

$avatarPath = '../uploads/admins/' . ($adminAvatar ? htmlspecialchars($adminAvatar) : 'default.png');
?>
<style>
/* ===== ADMIN NAVBAR — MATCH INDEX STYLE ===== */
.site-nav{
  position:sticky;
  top:0;
  z-index:3000;
  background: linear-gradient(180deg, #052033 0%, #0a2233 100%);
  box-shadow: 0 14px 28px rgba(2,8,20,0.28);
  border-bottom: 1px solid rgba(255,255,255,0.02);
  backdrop-filter: blur(6px);
}

.site-nav .nav-inner{
  height:72px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:18px;
}

/* Logo pill */
.logo-mark{
  width:46px;
  height:46px;
  border-radius:12px;
  background: linear-gradient(180deg,#0f2a4a,#021428);
  color:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight:900;
  font-size:16px;
  box-shadow: 0 6px 20px rgba(11,24,40,0.6), inset 0 -6px 12px rgba(255,255,255,0.02);
}

/* Primary nav */
.site-nav .primary{
  display:flex;
  gap:22px;
  align-items:center;
  padding-left:8px;
}

.site-nav .primary a{
  color:#cbd8ea;
  font-weight:700;
  font-size:14px;
  text-decoration:none;
  padding:10px 14px;
  border-radius:10px;
  transition: all 180ms ease;
}

.site-nav .primary a:hover{
  color:#ffffff;
  background: rgba(255,255,255,0.03);
  transform: translateY(-1px);
}

.site-nav .primary a.active{
  color:#021428;
  background: linear-gradient(90deg,#1b8fe6,#7b5bff);
  box-shadow: 0 8px 24px rgba(27,143,230,0.12);
  border-radius:12px;
}

/* Right side */
.nav-right{
  display:flex;
  align-items:center;
  gap:12px;
}

/* Profile avatar */
.profile-btn{
  border: none;
  background: transparent;
  padding: 0;
  cursor: pointer;
}

.profile-avatar{
  width:44px;
  height:44px;
  border-radius:50%;
  object-fit:cover;
  border: 3px solid rgba(255,255,255,0.08);
  box-shadow: 0 6px 18px rgba(2,8,20,0.35);
}

/* ===== PROFILE HOVER DROPDOWN (ADD-ON ONLY) ===== */
.profile-hover {
  position: relative;
}

.profile-dropdown {
  position: absolute;
  top: 58px;
  right: 0;
  width: 240px;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 20px 40px rgba(2,8,20,.18);
  padding: 16px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(8px);
  transition: all .2s ease;
  z-index: 5000;
}

.profile-hover:hover .profile-dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.pd-name {
  font-weight: 800;
  color: #021428;
}

.pd-role {
  font-size: 13px;
  color: #64748b;
  margin-bottom: 4px;
}

.pd-email {
  font-size: 13px;
  color: #334155;
  margin-bottom: 10px;
}

.pd-last {
  font-size: 12px;
  color: #64748b;
  margin-bottom: 12px;
}

.pd-actions a {
  display: block;
  padding: 8px 10px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 14px;
  color: #021428;
  text-decoration: none;
}

.pd-actions a:hover {
  background: #f1f5ff;
}

.pd-actions .logout {
  color: #dc2626;
}

/* Responsive small screens: collapse nav (simple) */
@media (max-width: 900px) {
  .site-nav .primary{
    display: none;
  }
}
</style>

<header class="site-nav">
  <div class="container nav-inner">

    <!-- Left: logo + label -->
    <div style="display:flex;align-items:center;gap:14px">
      <div class="logo-mark" aria-hidden="true">BBC</div>
      <div style="color:#fff;font-weight:700;font-size:13px;letter-spacing:0.1px">
        Admin Panel
      </div>
    </div>

    <!-- Center nav -->
    <nav class="primary" role="navigation" aria-label="admin">
      <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Dashboard</a>
      <a href="valuation-requests.php" class="<?= $currentPage === 'valuation-requests.php' ? 'active' : '' ?>">Valuation Requests</a>
      <a href="services.php" class="<?= $currentPage === 'services.php' ? 'active' : '' ?>">Services</a>
      <a href="staff.php" class="<?= $currentPage === 'staff.php' ? 'active' : '' ?>">Staff</a>
      <a href="clients.php" class="<?= $currentPage === 'clients.php' ? 'active' : '' ?>">Reviews & Clients</a>
      <a href="content.php" class="<?= $currentPage === 'content.php' ? 'active' : '' ?>">FAQ & Blog</a>
    </nav>

    <!-- Right: avatar -->
    <div class="nav-right profile-hover">

  <button class="profile-btn" title="<?= htmlspecialchars($adminName) ?>">
    <img src="<?= $avatarPath ?>" class="profile-avatar" alt="Admin">
  </button>

  <div class="profile-dropdown">
    <div class="pd-name"><?= htmlspecialchars($adminName) ?></div>
    <div class="pd-role"><?= htmlspecialchars($_SESSION['admin_role'] ?? 'Administrator') ?></div>
    <div class="pd-email"><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></div>

    <?php if (!empty($_SESSION['admin_last_active'])): ?>
      <div class="pd-last">
        Last active:
        <?= date('d M Y, h:i A', strtotime($_SESSION['admin_last_active'])) ?>
      </div>
    <?php endif; ?>

    <div class="pd-actions">
      <a href="profile.php">View Profile</a>
      <a href="logout.php" class="logout">Logout</a>
    </div>
  </div>

</div>


  </div>
</header>
