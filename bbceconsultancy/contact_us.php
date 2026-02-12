<?php
require_once __DIR__ . '/config/config.php';
 ?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Contact Us — BBC Engineering Consultancy Pvt. Ltd</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">

<style>
/* ===== PAGE LAYOUT FIX (DESKTOP) ===== */
.page-banner {
  padding: 80px 20px;
  color:#fff;
  background-size:cover;
  background-position:center;
}

.page-banner h2 {
  font-family:Poppins,sans-serif;
  font-size:52px;
  font-weight:800;
}

.section-box {
  background:rgba(255,255,255,0.95);
  padding:40px;
  border-radius:14px;
  margin-top:-60px;
  box-shadow:0 20px 50px rgba(0,0,0,.08);
}

/* ===== CONTACT GRID ===== */
.contact-grid {
  display:grid;
  grid-template-columns: 1.2fr 1fr;
  gap:30px;
}

.contact-item {
  display:flex;
  gap:12px;
  margin-bottom:14px;
  font-size:14px;
}

/* ===== STAFF ===== */
.staff-grid {
  display:grid;
  grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
  gap:22px;
  margin-top:30px;
}

.staff-card {
  background:#fff;
  border-radius:14px;
  padding:20px;
  text-align:center;
  box-shadow:0 8px 24px rgba(0,0,0,.06);
}

.staff-card img {
  width:90px;
  height:90px;
  border-radius:50%;
  object-fit:cover;
  margin-bottom:12px;
}

/* ===== FORM ===== */
.request-form {
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:14px;
}

.request-form input,
.request-form textarea {
  padding:12px;
  border-radius:8px;
  border:1px solid #dfe7fa;
  font-size:14px;
}

.request-form textarea {
  grid-column:1/-1;
  resize:none;
}

@media(max-width:900px){
  .contact-grid { grid-template-columns:1fr; }
  .request-form { grid-template-columns:1fr; }
  .section-box { margin-top:0; }
}

/* ===== Toast Notification ===== */
.toast-notification {
  position: fixed;
  top: 30px;
  right: -400px;
  background: #015383;
  color: #fff;
  padding: 16px 22px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  transition: right 0.5s ease;
  z-index: 9999;
  max-width: 320px;
}

.toast-notification.show {
  right: 30px;
}

.toast-notification.error {
  background: #dc3545;
}

/* ===== Staff Carousel ===== */
.staff-carousel-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-top: 30px;
}

.staff-carousel {
  display: flex;
  overflow: hidden;
  scroll-behavior: smooth;
  gap: 22px;
  width: 100%;
}

.staff-card {
  min-width: 250px;
  flex: 0 0 auto;
  background: #fff;
  border-radius: 14px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 8px 24px rgba(0,0,0,.06);
}

.staff-card img {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 12px;
}

.staff-arrow {
  background: #015383;
  color: #fff;
  border: none;
  font-size: 22px;
  padding: 10px 16px;
  cursor: pointer;
  border-radius: 50%;
  transition: 0.3s;
}

.staff-arrow:hover {
  background: #013d61;
}

.staff-arrow.left {
  margin-right: 10px;
}

.staff-arrow.right {
  margin-left: 10px;
}


/* ===== Enhanced Staff Card ===== */
.staff-card {
  min-width: 250px;
  flex: 0 0 auto;
  background: #fff;
  border-radius: 16px;
  padding: 22px;
  text-align: center;
  box-shadow: 0 8px 24px rgba(0,0,0,.06);
  transition: all 0.35s ease;
  position: relative;
  overflow: hidden;
}

.staff-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 18px 40px rgba(0,0,0,.12);
}

/* subtle gradient glow on hover */
.staff-card::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(1,83,131,0.08), transparent);
  opacity: 0;
  transition: opacity 0.4s ease;
  border-radius: 16px;
}

.staff-card:hover::before {
  opacity: 1;
}

.staff-card img {
  width: 95px;
  height: 95px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 14px;
  transition: transform 0.4s ease, box-shadow 0.4s ease;
}

.staff-card:hover img {
  transform: scale(1.08);
  box-shadow: 0 8px 20px rgba(1,83,131,0.25);
}

.staff-card h5 {
  font-weight: 700;
  margin-bottom: 6px;
  transition: color 0.3s ease;
}

.staff-card:hover h5 {
  color: #015383;
}

.staff-card p {
  font-size: 14px;
  margin-bottom: 4px;
  color: #555;
}


</style>
</head>

<body>

<!-- NAVBAR (UNCHANGED) -->
 <?php include __DIR__ . '/includes/navbar.php'; ?>


<!-- HERO -->
<section class="page-banner" style="background-image:linear-gradient(rgba(2,8,20,.6),rgba(2,8,20,.3)),url('teach1.avif')">
  <div class="container text-center">
    <h2>Contact Us</h2>
    <p>Professional Building, Architecture & Valuation Consultancy</p>
  </div>
</section>

<!-- MAIN -->
<main class="container">

<?php if(isset($_GET['error'])): ?>
  <div class="alert alert-danger mt-3">Something went wrong. Please try again.</div>
<?php endif; ?>

<div class="section-box">

<!-- CONTACT + MAP -->
<div class="contact-grid">

<div>
<h3 style="color:#015383;font-weight:800">Office Information</h3>

<div class="contact-item">📍 Ward 5, Dhangadhi Sub-Metropolitan City</div>
<div class="contact-item">Near North Gate of Dhangadhi Stadium</div>
<div class="contact-item">✉️ bbcdhangadhi@gmail.com</div>
<div class="contact-item">✉️ bhandari.krishna01@gmail.com</div>
<div class="contact-item">📞 +977 9858422178</div>
<div class="contact-item">☎️ 091-525169 / 091-521069</div>

<h4 style="margin-top:24px;font-weight:700">Google Map</h4>
<iframe
  src="https://www.google.com/maps?q=Ward%205,%20Dhangadhi%20Sub-Metropolitan%20City,%20Kailali,%20Sudurpaschim%20Province,%20Nepal&output=embed"
  width="100%"
  height="260"
  style="border:0;border-radius:12px"
  loading="lazy"
  referrerpolicy="no-referrer-when-downgrade">
</iframe>

</div>

<!-- REQUEST FORM -->
<div>
<h3 style="color:#015383;font-weight:800">Valuation Request</h3>

<form class="request-form" method="post" action="contact_submit.php">
  <input name="client_name" placeholder="Client Name" required>
  <input name="contact_number" placeholder="Contact Number" required>
  <input name="address" placeholder="Address">
  <input name="property_owner_name" placeholder="Property Owner Name">
  <input name="property_address" placeholder="Property Address">
  <input name="plot_no" placeholder="Plot No">
  <input name="area_of_plot" placeholder="Area of Plot">
  <textarea name="notes" rows="4" placeholder="Additional Notes"></textarea>

  <button class="btn-primary" type="submit">Submit Request</button>
</form>

</div>

</div>

<!-- STAFF -->
<h3 style="margin-top:50px;color:#015383;font-weight:800">Our Staff</h3>

<?php

$result = $conn->query("SELECT * FROM staff ORDER BY created_at DESC");
?>

<div class="staff-carousel-wrapper">
  <button class="staff-arrow left">&#10094;</button>

  <div class="staff-carousel">
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="staff-card">
        <img src="uploads/staff/<?php echo htmlspecialchars($row['image']); ?>" alt="">
        <h5><?php echo htmlspecialchars($row['name']); ?></h5>
        <p><?php echo htmlspecialchars($row['designation']); ?></p>
        <p>📞 <?php echo htmlspecialchars($row['phone']); ?></p>
      </div>
    <?php endwhile; ?>
  </div>

  <button class="staff-arrow right">&#10095;</button>
</div>

<?php $conn->close(); ?>


</div>
</main>

<!-- FOOTER (UNCHANGED) -->
<footer>
    <div class="container">
        <div class="footer-content">
            <!-- Left Column: Brand Info -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <div class="logo-mark">BBC</div>
                    <h3>Engineering Consultantancy Pvt.Ltd</h3>
                </div>
                <p class="footer-tagline">Empowering learners with cutting-edge education</p>
                <div class="footer-copyright">&copy; <strong>BBC</strong> — 2025 • All rights reserved</div>
            </div>
            
            <!-- Right Column: Contact Info -->
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
            
            <!-- Social Media Column -->
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
<!-- Toast Notification -->
<div id="toastNotification" class="toast-notification">
  <span id="toastMessage"></span>
</div>

 <script>
 

        // Toast Logic
(function(){
  const params = new URLSearchParams(window.location.search);
  const toast = document.getElementById("toastNotification");
  const message = document.getElementById("toastMessage");

  if (params.has("success")) {
    message.textContent = "Your request has been submitted successfully. Our team will contact you soon.";
    toast.classList.add("show");
  }

  if (params.has("error")) {
    message.textContent = "Something went wrong. Please try again.";
    toast.classList.add("show", "error");
  }

  if (params.has("success") || params.has("error")) {
    setTimeout(() => {
      toast.classList.remove("show");
      window.history.replaceState({}, document.title, window.location.pathname);
    }, 4000);
  }
})();

// Staff Carousel Controls
document.addEventListener("DOMContentLoaded", function () {
  const carousel = document.querySelector(".staff-carousel");
  const leftBtn = document.querySelector(".staff-arrow.left");
  const rightBtn = document.querySelector(".staff-arrow.right");

  if (carousel && leftBtn && rightBtn) {
    leftBtn.addEventListener("click", () => {
      carousel.scrollBy({ left: -300, behavior: "smooth" });
    });

    rightBtn.addEventListener("click", () => {
      carousel.scrollBy({ left: 300, behavior: "smooth" });
    });
  }
});


      </script>
</body>
</html>
