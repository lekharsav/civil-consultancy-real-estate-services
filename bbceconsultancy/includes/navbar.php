<?php
// includes/navbar.php
// Reusable navbar - include this in pages with: include __DIR__ . '/includes/navbar.php';
?>
<header class="site-nav" role="banner">
  <div class="container nav-inner">
    <!-- Left: Brand/Logo -->
    <div style="display:flex;align-items:center;gap:14px">
      <div class="logo-mark" aria-hidden="true">BBC</div>
      <div class="brand">
        <div style="font-size:12px;color:#ffffff;margin-top:2px;font-weight:700">Engineering Consultantancy Pvt.Ltd.</div>
      </div>
    </div>

    <!-- Desktop Navigation -->
 <nav class="primary" role="navigation" aria-label="primary">
       <a href="index.php">Home</a>
      <a href="project.php">Projects</a>
      <a href="about_us.php">About us</a>
      <a href="reviews.php">Reviews</a>
      <a href="contact_us.php">Contact Us</a>
    </nav>

    <!-- Right: CTA + Profile + Hamburger -->
    <div class="nav-right">
      <!-- Desktop CTA Button -->
      <button class="btn-cta desktop-cta" onclick="document.getElementById('courses').scrollIntoView({behavior:'smooth'})">Browse Architecture</button>
      
      <!-- Profile Button -->
      <button class="profile-btn" id="profileBtn" aria-label="Account">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M12 12c2.761 0 5-2.462 5-5.5S14.761 1 12 1 7 3.462 7 6.5 9.239 12 12 12Zm0 2c-4.418 0-8 2.239-8 5v2h16v-2c0-2.761-3.582-5-8-5Z"
                fill="currentColor"/>
        </svg>
      </button>
      
      <!-- Mobile Hamburger Menu -->
      <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu" aria-expanded="false">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
    </div>

    <!-- Mobile Navigation Menu (Horizontal dropdown) -->
   
  </div>
   <div class="mobile-dropdown" id="mobileDropdown">
        <nav class="mobile-nav">
        <a href="index.php" class="mobile-nav-link">Home</a>
        <a href="project.php" class="mobile-nav-link">Projects</a>
        <a href="about_us.php" class="mobile-nav-link">about us</a>
        <a href="reviews.php" class="mobile-nav-link">Reviews</a>
        <a href="contact_us.php" class="mobile-nav-link">Contact Us</a>
        <button class="mobile-dropdown-cta" onclick="document.getElementById('courses').scrollIntoView({behavior:'smooth'})">Browse Architecture</button>
      </nav>
    </div>
    <script>
// Mobile Dropdown Functionality
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileDropdown = document.getElementById('mobileDropdown');
  const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
  
  // Toggle mobile dropdown
  mobileMenuBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    const isExpanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
    mobileMenuBtn.setAttribute('aria-expanded', !isExpanded);
    mobileDropdown.classList.toggle('active');
  });
  
  // Close dropdown when clicking on links
  mobileNavLinks.forEach(link => {
    link.addEventListener('click', function() {
      mobileDropdown.classList.remove('active');
      mobileMenuBtn.setAttribute('aria-expanded', 'false');
    });
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (!mobileDropdown.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
      mobileDropdown.classList.remove('active');
      mobileMenuBtn.setAttribute('aria-expanded', 'false');
    }
  });
  // Close dropdown on escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && mobileDropdown.classList.contains('active')) {
      mobileDropdown.classList.remove('active');
      mobileMenuBtn.setAttribute('aria-expanded', 'false');
    }
  });
});
</script>
</header>

