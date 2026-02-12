<?php
// ============================================================
// reviews.php – Full dynamic reviews page with backend integration
// ============================================================
require_once __DIR__ . '/config/config.php';

/* ---------- FETCH ACTIVE REVIEWS ---------- */
$reviews = [];
$query = "SELECT id, name, request_about, rating, review_text, image_path, created_at, helpful_count
          FROM reviews
          WHERE status = 1
          ORDER BY created_at DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Reviews — BBC Engineering Consultantancy Pvt.Ltd</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Global CSS (same as index) -->
  <link rel="stylesheet" href="style.css">

  <style>
    /* =============================
       REVIEWS PAGE – ORIGINAL STYLES
       (from static version, kept intact)
    ============================= */
    .toppers-hero h1 { font-family: Poppins, sans-serif; font-weight: 700; }

    .reviews-controls {
      display:flex;
      gap:16px;
      align-items:center;
      justify-content:space-between;
      margin:28px 0 8px;
      flex-wrap:wrap;
    }
    .reviews-search {
      display:flex;
      gap:8px;
      align-items:center;
      flex:1 1 360px;
    }
    .reviews-search input, .reviews-search select {
      min-width: 180px;
      border-radius: 12px;
      padding: 10px 12px;
      border: 1px solid rgba(2,20,40,.08);
      background: #fff;
      box-shadow: 0 6px 18px rgba(2,8,20,.03);
      font-weight:700;
    }

    .reviews-actions { display:flex; gap:10px; align-items:center; }

    .rating-chips { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
    .rating-chip {
      padding:8px 12px;
      border-radius: 999px;
      background: rgba(2,20,40,.04);
      cursor:pointer;
      font-weight:700;
      color: #013a5a;
      border:1px solid rgba(2,20,40,.04);
      transition: all .18s ease;
    }
    .rating-chip.active {
      background: var(--cta);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,212,255,.12);
    }

    .reviews-grid {
      display: grid;
      gap: 22px;
      margin-top: 18px;
    }

    .review-card {
      background: #fff;
      border-radius: 14px;
      padding: 18px;
      box-shadow: 0 12px 30px rgba(2,8,20,.08);
      display:flex;
      gap:14px;
      align-items:flex-start;
      transition: transform .18s ease, box-shadow .18s ease;
      border:1px solid rgba(2,20,40,.02);
      min-height: 110px;
    }
    .review-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(2,8,20,.12);
    }
    .review-avatar {
      width:74px;
      height:74px;
      border-radius:50%;
      overflow:hidden;
      flex:0 0 74px;
      border:4px solid var(--accent-1);
      background:#eee;
    }
    .review-avatar img { width:100%; height:100%; object-fit:cover; display:block; }

    .review-body { flex:1; }
    .review-name { font-weight:800; margin-bottom:4px; color: #021428; }
    .review-meta { color: #6c757d; font-size:13px; margin-bottom:8px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
    .review-text {
      color: #374151;
      font-size:15px;
      line-height:1.5;
      max-height:3.6em;
      overflow:hidden;
      text-overflow:ellipsis;
    }
    .review-actions { margin-top:10px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .btn-inline {
      background: transparent;
      border: 1px solid rgba(2,20,40,.06);
      padding:6px 10px;
      border-radius:10px;
      font-weight:700;
      cursor:pointer;
    }

    .stars { display:inline-flex; gap:4px; vertical-align:middle; color:#f6a623; }
    .star { width:18px; height:18px; display:inline-block; }

    .no-results { text-align:center; padding:40px; color:#6c757d; }

    .review-modal-top {
      height:260px; overflow:hidden; border-top-left-radius: .5rem; border-top-right-radius: .5rem;
      background: #000;
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .review-modal-top img {
      width:100%;
      height:100%;
      object-fit: contain;
      object-position: center top;
      display:block;
      background:#000;
    }

    .reviews-pagination {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 28px 0 40px;
      flex-wrap: wrap;
    }
    .reviews-pagination button {
      padding: 8px 14px;
      border-radius: 10px;
      border: 1px solid rgba(2,20,40,.15);
      background: #fff;
      font-weight: 700;
      cursor: pointer;
    }
    .reviews-pagination button.active {
      background: var(--cta);
      color: #fff;
      border-color: var(--cta);
    }

    @media (max-width: 768px) {
      .reviews-grid { grid-template-columns: 1fr; gap: 16px; }
      .review-card { flex-direction: row; align-items:flex-start; padding:14px; min-height: 96px; }
      .review-avatar { width:60px; height:60px; flex:0 0 60px; border-width:3px; }
      .review-text { font-size:14px; max-height:3.8em; }
      .reviews-controls { gap:10px; align-items:flex-start; }
      .reviews-pagination button { padding:10px 16px; font-size:14px; }
    }

    @media (max-width: 420px) {
      .toppers-hero h1 { font-size:24px; }
      .reviews-search input { min-width:140px; }
      .reviews-actions { gap:8px; }
    }

    .helpful[disabled] { opacity:0.7; cursor:default; }

    .form-row { display:flex; gap:12px; flex-wrap:wrap; }
    .form-row > * { flex:1 1 200px; }
    .rating-select { width:110px; }
    .file-preview { width:60px; height:60px; border-radius:50%; overflow:hidden; border:3px solid var(--accent-1); background:#f2f4f7; display:inline-block; vertical-align:middle; }
    .file-preview img { width:100%; height:100%; object-fit:cover; display:block; }

    /* ===== STAR RATING INPUT ===== */
    .rating-input {
      display: flex;
      flex-direction: row-reverse;
      justify-content: flex-end;
      gap: 4px;
    }
    .rating-input input {
      display: none;
    }
    .rating-input label {
      font-size: 2rem;
      color: #ddd;
      cursor: pointer;
      transition: color 0.15s;
    }
    .rating-input label:before {
      content: '★';
    }
    .rating-input input:checked ~ label,
    .rating-input label:hover,
    .rating-input label:hover ~ label {
      color: #f6a623;
    }
    .rating-input input:checked + label {
      color: #f6a623;
    }

    /* ===== WHY REVIEWERS SECTION – REDESIGN ===== */
.why-reviewers {
  padding: 60px 0 40px;
  background: linear-gradient(145deg, #ffffff 0%, #fafcff 100%);
  border-radius: 48px 48px 48px 48px;
  margin: 48px auto;
  position: relative;
  overflow: hidden;
}

.why-reviewers .container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  position: relative;
  z-index: 2;
}

.why-reviewers .section-header {
  text-align: center;
  margin-bottom: 48px;
}

.why-reviewers .badge {
  display: inline-block;
  padding: 8px 20px;
  background: linear-gradient(90deg, rgba(0,212,255,0.12), rgba(99,91,255,0.12));
  border-radius: 40px;
  font-size: 0.9rem;
  font-weight: 700;
  color: #015383;
  letter-spacing: 0.5px;
  margin-bottom: 16px;
  border: 1px solid rgba(0,212,255,0.2);
}

.why-reviewers h2 {
  font-family: 'Poppins', sans-serif;
  font-size: 2.2rem;
  font-weight: 700;
  color: #021428;
  margin-bottom: 12px;
  line-height: 1.2;
}

.why-reviewers .subhead {
  font-size: 1.1rem;
  color: #4b6a7c;
  max-width: 600px;
  margin: 0 auto;
}

/* Pillars grid – 4 cards on desktop */
.pillars-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 28px;
}

.pillar-card {
  background: white;
  border-radius: 24px;
  padding: 32px 24px;
  text-align: center;
  box-shadow: 0 12px 32px rgba(2,20,40,0.04);
  transition: all 0.3s cubic-bezier(0.2,0.9,0.4,1);
  border: 1px solid rgba(255,255,255,0.6);
  backdrop-filter: blur(4px);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.pillar-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 28px 48px rgba(0,212,255,0.16);
  border-color: rgba(0,212,255,0.2);
}

.pillar-icon {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3.2rem;
  margin-bottom: 24px;
  transition: all 0.25s;
}

.pillar-card:hover .pillar-icon {
  transform: scale(1.05);
  box-shadow: 0 12px 28px rgba(0,212,255,0.2);
}

.pillar-card h4 {
  font-family: 'Poppins', sans-serif;
  font-size: 1.25rem;
  font-weight: 700;
  color: #021428;
  margin-bottom: 12px;
}

.pillar-card p {
  color: #3d5a6c;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 0;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .pillars-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
  }
  .why-reviewers h2 {
    font-size: 2rem;
  }
}

@media (max-width: 640px) {
  .why-reviewers {
    padding: 48px 0 32px;
    border-radius: 32px;
  }
  .why-reviewers h2 {
    font-size: 1.8rem;
  }
  .pillars-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  .pillar-card {
    padding: 28px 20px;
    flex-direction: row;
    text-align: left;
    gap: 20px;
  }
  .pillar-icon {
    width: 72px;
    height: 72px;
    font-size: 2.4rem;
    margin-bottom: 0;
    flex-shrink: 0;
  }
  .pillar-card h4 {
    margin-bottom: 6px;
  }
  .pillar-card p {
    font-size: 0.9rem;
  }
}

/* ===== RESPONSIVE – TABLET & SMALL LAPTOP ===== */
@media (max-width: 1024px) {
  .pillars-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
  }
  .why-reviewers h2 {
    font-size: 2rem;
  }
}

/* ===== RESPONSIVE – LARGE PHONES & SMALL TABLETS ===== */
@media (max-width: 640px) {
  .why-reviewers {
    padding: 48px 0 32px;
    border-radius: 32px;
    margin: 32px auto;
  }
  .why-reviewers .container {
    padding: 0 20px;
  }
  .why-reviewers h2 {
    font-size: 1.8rem;
    margin-bottom: 8px;
  }
  .why-reviewers .subhead {
    font-size: 1rem;
    padding: 0 10px;
  }
  .pillars-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  .pillar-card {
    padding: 28px 24px;
    flex-direction: row;
    text-align: left;
    gap: 20px;
    align-items: flex-start;
  }
  .pillar-icon {
    width: 72px;
    height: 72px;
    font-size: 2.4rem;
    margin-bottom: 0;
    flex-shrink: 0;
  }
  .pillar-card h4 {
    margin-bottom: 8px;
    font-size: 1.2rem;
  }
  .pillar-card p {
    font-size: 0.95rem;
    line-height: 1.5;
  }
}

/* ===== RESPONSIVE – SMALL PHONES (≤ 480px) ===== */
@media (max-width: 480px) {
  .why-reviewers {
    padding: 40px 0 28px;
    border-radius: 24px;
    margin: 24px auto;
  }
  .why-reviewers .container {
    padding: 0 16px;
  }
  .why-reviewers .badge {
    padding: 6px 16px;
    font-size: 0.8rem;
    margin-bottom: 12px;
  }
  .why-reviewers h2 {
    font-size: 1.5rem;
    line-height: 1.3;
  }
  .why-reviewers .subhead {
    font-size: 0.95rem;
    padding: 0 5px;
  }
  .pillar-card {
    padding: 20px 16px;
    gap: 16px;
    border-radius: 20px;
  }
  .pillar-icon {
    width: 56px;
    height: 56px;
    font-size: 2rem;
  }
  .pillar-card h4 {
    font-size: 1.1rem;
    margin-bottom: 6px;
  }
  .pillar-card p {
    font-size: 0.85rem;
    line-height: 1.5;
  }
}

/* ===== EXTRA TINY PHONES (≤ 360px) – OPTIONAL ===== */
@media (max-width: 360px) {
  .pillar-card {
    flex-direction: column;
    text-align: center;
    align-items: center;
  }
  .pillar-icon {
    margin-bottom: 8px;
  }
  .pillar-card h4 {
    font-size: 1rem;
  }
}
  </style>
</head>
<body>

<!-- NAVBAR (unchanged) -->
<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- MAIN -->
<main class="container">

  <!-- HERO BANNER -->
  <section class="toppers-hero">
    <div class="badge" style="background:#fff;color:#021428;font-weight:800"></div>
    <h1 class="mt-3">What our students & clients say</h1>
    <p>Verified testimonials and course reviews from past learners — read honest feedback about our programs and mentorship.</p>
  </section>

  <!-- AUTH OVERLAY (unchanged, hidden by default) -->
  <div class="auth-overlay" id="authOverlay" style="display:none;align-items:center;justify-content:center;">
    <div class="auth-card" role="dialog" aria-modal="true" style="background:#fff;padding:28px;border-radius:14px;max-width:360px;width:92%;">
      <h3>Account Access</h3>
      <p>Login or create a new account to continue</p>
      <button class="auth-main login">Login</button>
      <button class="auth-main signup">Sign Up</button>
      <div class="divider"><span>or continue with</span></div>
      <a href="#" class="forgot">Forgot password?</a>
    </div>
  </div>

  <!-- Controls: search, sort, rating filter -->
  <div class="reviews-controls" style="max-width:1200px;margin:32px auto 0;">
    <div style="flex:1 1 0;">
      <div class="reviews-search">
        <input id="reviewSearch" type="search" placeholder="Search reviews — reviewer, course, or keywords" aria-label="Search reviews">
        <select id="sortSelect" aria-label="Sort reviews" title="Sort reviews">
          <option value="latest">Newest</option>
          <option value="highest">Highest rating</option>
          <option value="lowest">Lowest rating</option>
        </select>
      </div>
      <div class="rating-chips mt-2" id="ratingChips" role="tablist" aria-label="Filter by rating">
        <div class="rating-chip active" data-rating="all" role="tab">All</div>
        <div class="rating-chip" data-rating="5" role="tab">5★</div>
        <div class="rating-chip" data-rating="4" role="tab">4★+</div>
        <div class="rating-chip" data-rating="3" role="tab">3★+</div>
      </div>
    </div>
    <div class="reviews-actions">
      <button id="writeReviewBtn" class="btn-inline" title="Add a review">Write a review</button>
    </div>
  </div>

  <!-- REVIEWS GRID – DYNAMIC FROM DATABASE -->
 <section style="max-width:1200px;margin:18px auto;">
  <div class="reviews-grid" id="reviewsGrid">
    <?php if (!empty($reviews)): ?>
      <?php foreach ($reviews as $r):
        $img = !empty($r['image_path']) ? htmlspecialchars($r['image_path']) : 'top1.webp';
        $date = date('M j, Y', strtotime($r['created_at']));
        $short_text = strlen($r['review_text']) > 280 ? substr($r['review_text'],0,277).'…' : $r['review_text'];
      ?>
        <article class="review-card"
          data-id="<?= $r['id'] ?>"
          data-name="<?= htmlspecialchars($r['name']) ?>"
          data-role="<?= htmlspecialchars($r['request_about']) ?>"
          data-rating="<?= (int)$r['rating'] ?>"
          data-date="<?= $r['created_at'] ?>"
          data-img="<?= $img ?>"
          data-text="<?= htmlspecialchars($r['review_text']) ?>">
          <div class="review-avatar"><img src="<?= $img ?>" alt="<?= htmlspecialchars($r['name']) ?>"></div>
          <div class="review-body">
            <div class="review-name"><?= htmlspecialchars($r['name']) ?></div>
            <div class="review-meta">
              <span class="stars" aria-hidden="true">
                <?php for($i=1;$i<=5;$i++): ?>
                  <span class="star"><?= $i <= $r['rating'] ? '★' : '☆' ?></span>
                <?php endfor; ?>
              </span>
              <span> • </span>
              <small><?= htmlspecialchars($r['request_about']) ?></small>
              <span> • </span>
              <small><?= $date ?></small>
            </div>
            <div class="review-text"><?= htmlspecialchars($short_text) ?></div>
            <div class="review-actions">
              <button class="btn-inline read-more" data-bs-toggle="modal" data-bs-target="#reviewModal">Read more</button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; padding:40px; color:#6c757d;">No reviews yet. Be the first to write one!</p>
    <?php endif; ?>
  </div>

  <div id="noResults" class="no-results" style="display:none;">No reviews found for your search.</div>

  <!-- Pagination (handled by JS) -->
  <div class="reviews-pagination" id="reviewsPagination" aria-label="Reviews pagination"></div>
</section>

  <!-- SUCCESS STRIP (unchanged) -->
  <section class="why-reviewers">
  <div class="container">
    <div class="section-header">
      <span class="badge">Why reviewers ❤️ us</span>
      <h2>Why reviewers rate us highly</h2>
      <p class="subhead">Four pillars that make BBC the trusted choice</p>
    </div>

    <div class="pillars-grid">
      <div class="pillar-card">
        <div class="pillar-icon" style="background: rgba(0,212,255,0.1); color: #00D4FF;">
          📚
        </div>
        <h4>Focused Study Plan</h4>
        <p>Structured curriculum designed for exam success and real‑world application.</p>
      </div>
      <div class="pillar-card">
        <div class="pillar-icon" style="background: rgba(99,91,255,0.1); color: #635BFF;">
          🧠
        </div>
        <h4>Live Doubt Clearing</h4>
        <p>Direct access to mentors – no question goes unanswered.</p>
      </div>
      <div class="pillar-card">
        <div class="pillar-icon" style="background: rgba(0,212,255,0.1); color: #00D4FF;">
          📊
        </div>
        <h4>Mock Test Analytics</h4>
        <p>Detailed performance reports to track progress and weak areas.</p>
      </div>
      <div class="pillar-card">
        <div class="pillar-icon" style="background: rgba(99,91,255,0.1); color: #635BFF;">
          🏆
        </div>
        <h4>Top Exam Performance</h4>
        <p>Consistently high scores – 98% of our learners achieve their goals.</p>
      </div>
    </div>
  </div>
</section>

  <!-- ACHIEVEMENT STATS (unchanged) -->
  <section style="padding: 10px 24px; background: linear-gradient(135deg, rgba(0, 212, 255, 0.05) 0%, rgba(37, 99, 235, 0.05) 100%); margin-top:28px;">
    <div style="max-width: 1200px; margin: 0 auto;">
      <div style="text-align: center; margin-bottom: 60px;">
        <h2 style="font-family: Poppins, sans-serif; font-size: 48px; color: #015383; margin: 0 0 16px; font-weight: 800;">Our Success Metrics</h2>
        <p style="font-size: 18px; color: #666; max-width: 600px; margin: 0 auto;">Real results from students who transformed their careers</p>
      </div>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 28px;">
        <div style="background: white; padding: 40px 28px; border-radius: 16px; text-align: center; box-shadow: 0 8px 24px rgba(0, 212, 255, 0.1);">
          <div style="font-size: 48px; font-weight: 900; color: #00D4FF; margin-bottom: 12px;">98%</div>
          <div style="font-size: 16px; font-weight: 700; color: #015383; text-transform: uppercase; letter-spacing: 0.5px;">Success Rate</div>
        </div>
        <div style="background: white; padding: 40px 28px; border-radius: 16px; text-align: center; box-shadow: 0 8px 24px rgba(0, 212, 255, 0.1);">
          <div style="font-size: 48px; font-weight: 900; color: #00D4FF; margin-bottom: 12px;">500+</div>
          <div style="font-size: 16px; font-weight: 700; color: #015383; text-transform: uppercase; letter-spacing: 0.5px;">Top Achievers</div>
        </div>
        <div style="background: white; padding: 40px 28px; border-radius: 16px; text-align: center; box-shadow: 0 8px 24px rgba(0, 212, 255, 0.1);">
          <div style="font-size: 48px; font-weight: 900; color: #00D4FF; margin-bottom: 12px;">92%</div>
          <div style="font-size: 16px; font-weight: 700; color: #015383; text-transform: uppercase; letter-spacing: 0.5px;">Avg Score</div>
        </div>
        <div style="background: white; padding: 40px 28px; border-radius: 16px; text-align: center; box-shadow: 0 8px 24px rgba(0, 212, 255, 0.1);">
          <div style="font-size: 48px; font-weight: 900; color: #00D4FF; margin-bottom: 12px;">12 Weeks</div>
          <div style="font-size: 16px; font-weight: 700; color: #015383; text-transform: uppercase; letter-spacing: 0.5px;">Avg Duration</div>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- REVIEW MODAL (read more) -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="review-modal-top">
        <img id="modalImg" src="top1.webp" alt="Reviewer image">
      </div>
      <div class="modal-body text-center">
        <h4 id="modalName" style="font-weight:800;"></h4>
        <div id="modalMeta" style="color:#6c757d;margin-bottom:10px;"></div>
        <div id="modalRating" style="margin-bottom:12px;color:#f6a623;"></div>
        <p id="modalText" style="color:#334155;"></p>
        <div style="margin-top:12px;">
          <button class="btn-inline" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- WRITE REVIEW MODAL – with star rating and "Your request about" -->
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Write a review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="writeReviewForm" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Your name</label>
            <input type="text" id="rvName" class="form-control" required placeholder="Full name">
          </div>
          <div class="mb-2">
            <label class="form-label">Your request about</label>
            <input type="text" id="rvRequestAbout" class="form-control" required placeholder="e.g. Property Valuation, Architectural Design, Site Supervision">
          </div>
          <div class="mb-2">
            <label class="form-label d-block">Rating</label>
            <div class="rating-input">
              <input type="radio" name="rating" id="star5" value="5" required><label for="star5"></label>
              <input type="radio" name="rating" id="star4" value="4"><label for="star4"></label>
              <input type="radio" name="rating" id="star3" value="3"><label for="star3"></label>
              <input type="radio" name="rating" id="star2" value="2"><label for="star2"></label>
              <input type="radio" name="rating" id="star1" value="1"><label for="star1"></label>
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label">Your photo (optional)</label>
            <input id="rvImage" type="file" accept="image/*" class="form-control" />
          </div>
          <div class="mb-2">
            <label class="form-label">Your review</label>
            <textarea id="rvText" class="form-control" rows="4" required placeholder="Tell others about your experience"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <div class="d-flex gap-2 justify-content-end w-100">
            <button type="button" class="btn-inline" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-inline" id="submitReviewBtn">Submit review</button>
          </div>
        </div>
      </form>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>

<script>
/* =======================================================
   reviews.js – Full dynamic functionality
   ======================================================= */
(function () {
  const grid = document.getElementById('reviewsGrid');
  const searchEl = document.getElementById('reviewSearch');
  const sortEl = document.getElementById('sortSelect');
  const chips = Array.from(document.querySelectorAll('.rating-chip'));
  const noResults = document.getElementById('noResults');
  const paginationEl = document.getElementById('reviewsPagination');
  const REVIEWS_PER_PAGE = 4;
  let currentPage = 1;

  // ----- Helper functions (keep all original) -----
  function allCards() {
    return Array.from(grid.querySelectorAll('.review-card'));
  }

  function renderStarsOnLoad() {
    allCards().forEach(card => {
      const starWrap = card.querySelector('.stars');
      const rating = Math.round(Number(card.dataset.rating || 0));
      if (starWrap) {
        starWrap.innerHTML = '';
        for (let i=1;i<=5;i++){
          const s = document.createElement('span');
          s.className = 'star';
          s.textContent = i <= rating ? '★' : '☆';
          starWrap.appendChild(s);
        }
      }
    });
  }

  function renderStars(container, rating) {
    container.innerHTML = '';
    const n = Math.round(Number(rating));
    for (let i=1;i<=5;i++){
      const span = document.createElement('span');
      span.className = 'star';
      span.textContent = i <= n ? '★' : '☆';
      container.appendChild(span);
    }
  }

  function applyFilters() {
    const q = (searchEl.value || '').trim().toLowerCase();
    const sortBy = sortEl.value;
    const activeChip = document.querySelector('.rating-chip.active');
    const ratingFilter = activeChip ? activeChip.dataset.rating : 'all';

    let cards = allCards();

    if (ratingFilter && ratingFilter !== 'all') {
      const min = Number(ratingFilter);
      cards = cards.filter(c => Number(c.dataset.rating) >= min);
    }

    if (q) {
      cards = cards.filter(c => {
        return (
          c.dataset.name.toLowerCase().includes(q) ||
          (c.dataset.role || '').toLowerCase().includes(q) ||
          (c.dataset.text || '').toLowerCase().includes(q)
        );
      });
    }

    cards.sort((a,b) => {
      if (sortBy === 'latest') return new Date(b.dataset.date) - new Date(a.dataset.date);
      if (sortBy === 'highest') return Number(b.dataset.rating) - Number(a.dataset.rating);
      if (sortBy === 'lowest') return Number(a.dataset.rating) - Number(b.dataset.rating);
      return 0;
    });

    currentPage = 1;
    grid.innerHTML = '';
    if (cards.length === 0) {
      noResults.style.display = '';
    } else {
      noResults.style.display = 'none';
      cards.forEach(c => grid.appendChild(c));
    }
    paginateReviews();
  }

  function paginateReviews() {
    const cards = allCards();
    const totalPages = Math.max(1, Math.ceil(cards.length / REVIEWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    cards.forEach((card, idx) => {
      const start = (currentPage - 1) * REVIEWS_PER_PAGE;
      const end = currentPage * REVIEWS_PER_PAGE;
      card.style.display = (idx >= start && idx < end) ? 'flex' : 'none';
    });

    paginationEl.innerHTML = '';
    if (totalPages <= 1) return;

    const prev = document.createElement('button');
    prev.textContent = '‹';
    prev.disabled = currentPage === 1;
    prev.onclick = () => { if (currentPage > 1) { currentPage--; paginateReviews(); window.scrollTo({ top: grid.offsetTop - 100, behavior:'smooth' }); } };
    paginationEl.appendChild(prev);

    const windowSize = 5;
    let startPage = Math.max(1, currentPage - Math.floor(windowSize/2));
    let endPage = Math.min(totalPages, startPage + windowSize - 1);
    if (endPage - startPage < windowSize - 1) {
      startPage = Math.max(1, endPage - windowSize + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      if (i === currentPage) btn.classList.add('active');
      btn.onclick = () => { currentPage = i; paginateReviews(); window.scrollTo({ top: grid.offsetTop - 100, behavior:'smooth' }); };
      paginationEl.appendChild(btn);
    }

    const next = document.createElement('button');
    next.textContent = '›';
    next.disabled = currentPage === totalPages;
    next.onclick = () => { if (currentPage < totalPages) { currentPage++; paginateReviews(); window.scrollTo({ top: grid.offsetTop - 100, behavior:'smooth' }); } };
    paginationEl.appendChild(next);
  }

  // ----- Event Listeners (filters, chips) -----
  chips.forEach(ch => {
    ch.addEventListener('click', function() {
      chips.forEach(x => x.classList.remove('active'));
      this.classList.add('active');
      applyFilters();
    });
  });

  searchEl.addEventListener('input', () => applyFilters());
  sortEl.addEventListener('change', () => applyFilters());

  // ----- Helpful button (AJAX) -----
  grid.addEventListener('click', function(e) {
    const btn = e.target.closest('.helpful');
    if (!btn || btn.disabled) return;
    const card = btn.closest('.review-card');
    const reviewId = card.dataset.id;
    if (!reviewId) return;

    fetch('helpful.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + encodeURIComponent(reviewId)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        btn.textContent = 'Marked ✓';
        btn.disabled = true;
      }
    })
    .catch(() => { /* silent fail */ });
  });

  // ----- Write review modal and AJAX submission -----
  const writeReviewBtn = document.getElementById('writeReviewBtn');
  const writeReviewModalEl = document.getElementById('writeReviewModal');
  const writeReviewForm = document.getElementById('writeReviewForm');
  const submitBtn = document.getElementById('submitReviewBtn');
  const bsWriteModal = new bootstrap.Modal(writeReviewModalEl, { backdrop: 'static', keyboard: false });

  writeReviewBtn.addEventListener('click', function() {
    writeReviewForm.reset();
    bsWriteModal.show();
    setTimeout(() => document.getElementById('rvName')?.focus(), 150);
  });

  writeReviewForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('name', document.getElementById('rvName').value.trim());
    formData.append('request_about', document.getElementById('rvRequestAbout').value.trim());
    const ratingEl = document.querySelector('input[name="rating"]:checked');
    if (ratingEl) formData.append('rating', ratingEl.value);
    formData.append('review_text', document.getElementById('rvText').value.trim());
    const imgFile = document.getElementById('rvImage').files[0];
    if (imgFile) formData.append('image', imgFile);

    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    fetch('submit_review.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        grid.insertAdjacentHTML('beforeend', data.card_html);
        renderStarsOnLoad();
        const totalPages = Math.max(1, Math.ceil(allCards().length / REVIEWS_PER_PAGE));
        currentPage = totalPages;
        paginateReviews();
        writeReviewForm.reset();
        bsWriteModal.hide();
        alert('Thank you! Your review has been posted.');
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(err => {
      console.error(err);
      alert('Something went wrong. Please try again.');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit review';
    });
  });

  // ----- Modal population (read more) -----
  const reviewModal = document.getElementById('reviewModal');
  if (reviewModal) {
    reviewModal.addEventListener('show.bs.modal', function (event) {
      const trigger = event.relatedTarget;
      const card = trigger?.closest('.review-card');
      if (!card) return;
      document.getElementById('modalName').textContent = card.dataset.name || '';
      document.getElementById('modalMeta').textContent = 
        (card.dataset.role || '') + (card.dataset.date ? ' • ' + formatDateReadable(card.dataset.date) : '');
      document.getElementById('modalImg').src = card.dataset.img || 'top1.webp';
      document.getElementById('modalText').textContent = card.dataset.text || '';
      renderStars(document.getElementById('modalRating'), card.dataset.rating || 0);
    });
  }

  // ----- Utility functions -----
  function formatDateReadable(iso) {
    try {
      return new Date(iso).toLocaleDateString(undefined, { year:'numeric', month:'short', day:'numeric' });
    } catch (e) { return iso; }
  }

  // ----- Initial load -----
  renderStarsOnLoad();
  applyFilters();
})();


(function () {
  const REVIEWS_PER_PAGE = 4; // fixed 4 items per page across all viewports
  const grid = document.getElementById('reviewsGrid');
  const paginationEl = document.getElementById('reviewsPagination');
  const noResultsEl = document.getElementById('noResults');

  // Optional controls (if present on page) — script will safely skip if they don't exist
  const searchEl = document.getElementById('reviewSearch'); // optional
  const sortEl = document.getElementById('sortSelect'); // optional
  const ratingChips = Array.from(document.querySelectorAll('.rating-chip')); // optional

  let currentPage = 1;

  // Helper to read all review cards (in DOM order)
  function allCards() {
    return Array.from(grid.querySelectorAll('.review-card'));
  }

  // Render star glyphs for cards based on data-rating (keeps server markup consistent)
  function renderStarsOnLoad() {
    allCards().forEach(card => {
      const wrap = card.querySelector('.stars');
      const rating = Math.round(Number(card.dataset.rating || 0));
      if (wrap) {
        // If server already printed stars, we leave them; but normalize to ensure correctness
        wrap.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
          const s = document.createElement('span');
          s.className = 'star';
          s.textContent = i <= rating ? '★' : '☆';
          wrap.appendChild(s);
        }
      }
    });
  }

  // Apply filters (search / rating / sort) and re-render grid content order
  function applyFiltersAndPaginate() {
    const q = (searchEl && searchEl.value) ? searchEl.value.trim().toLowerCase() : '';
    const sortBy = (sortEl && sortEl.value) ? sortEl.value : 'latest';
    const activeChip = ratingChips.find(ch => ch.classList.contains('active'));
    const ratingFilter = activeChip ? activeChip.dataset.rating : 'all';

    // Use the original DOM nodes as source
    let cards = allCards();

    // Filtering by rating
    if (ratingFilter && ratingFilter !== 'all') {
      const min = Number(ratingFilter);
      cards = cards.filter(c => Number(c.dataset.rating || 0) >= min);
    }

    // Filtering by search query (name, role, or text)
    if (q) {
      cards = cards.filter(c => {
        const name = (c.dataset.name || '').toLowerCase();
        const role = (c.dataset.role || '').toLowerCase();
        const text = (c.dataset.text || '').toLowerCase();
        return name.includes(q) || role.includes(q) || text.includes(q);
      });
    }

    // Sorting
    cards.sort((a, b) => {
      if (sortBy === 'highest') return Number(b.dataset.rating || 0) - Number(a.dataset.rating || 0);
      if (sortBy === 'lowest') return Number(a.dataset.rating || 0) - Number(b.dataset.rating || 0);
      // latest/default: by date desc
      return new Date(b.dataset.date || 0) - new Date(a.dataset.date || 0);
    });

    // Rebuild grid DOM with filtered+sorted nodes (keeps the same elements, moves them)
    grid.innerHTML = '';
    if (cards.length === 0) {
      noResultsEl.style.display = '';
    } else {
      noResultsEl.style.display = 'none';
      cards.forEach(c => grid.appendChild(c));
    }

    // Reset to first page and build pagination
    currentPage = 1;
    paginate();
  }

  // Paginate using fixed REVIEWS_PER_PAGE
  function paginate() {
    const cards = allCards();
    const totalPages = Math.max(1, Math.ceil(cards.length / REVIEWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    // Show/hide cards according to current page
    cards.forEach((card, idx) => {
      const start = (currentPage - 1) * REVIEWS_PER_PAGE;
      const end = currentPage * REVIEWS_PER_PAGE;
      card.style.display = (idx >= start && idx < end) ? 'flex' : 'none';
    });

    // Build pagination controls (prev, numeric window, next)
    paginationEl.innerHTML = '';
    if (totalPages <= 1) return;

    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-btn';
    prevBtn.textContent = '‹';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; paginate(); scrollToGrid(); } };
    paginationEl.appendChild(prevBtn);

    // numeric window (show up to 7 page numbers centered on current)
    const windowSize = 7;
    let startPage = Math.max(1, currentPage - Math.floor(windowSize / 2));
    let endPage = Math.min(totalPages, startPage + windowSize - 1);
    if (endPage - startPage < windowSize - 1) {
      startPage = Math.max(1, endPage - windowSize + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      const btn = document.createElement('button');
      btn.className = 'page-btn';
      btn.textContent = i;
      if (i === currentPage) btn.classList.add('active');
      btn.onclick = (function(p){ return function(){ currentPage = p; paginate(); scrollToGrid(); }; })(i);
      paginationEl.appendChild(btn);
    }

    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-btn';
    nextBtn.textContent = '›';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; paginate(); scrollToGrid(); } };
    paginationEl.appendChild(nextBtn);
  }

  function scrollToGrid() {
    const top = Math.max(0, grid.getBoundingClientRect().top + window.pageYOffset - 90);
    window.scrollTo({ top, behavior: 'smooth' });
  }

  // Wire up optional controls if present
  if (searchEl) {
    searchEl.addEventListener('input', function () {
      applyFiltersAndPaginate();
    });
  }

  if (sortEl) {
    sortEl.addEventListener('change', function () {
      applyFiltersAndPaginate();
    });
  }

  if (ratingChips && ratingChips.length) {
    ratingChips.forEach(ch => {
      ch.addEventListener('click', function () {
        ratingChips.forEach(x => x.classList.remove('active'));
        this.classList.add('active');
        applyFiltersAndPaginate();
      });
      ch.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
      });
    });
  }

  // If new review cards are appended later via AJAX and server returns 'card_html',
  // call renderStarsOnLoad(); applyFiltersAndPaginate(); to reflow and paginate.

  // Init
  renderStarsOnLoad();
  applyFiltersAndPaginate();

  // Expose small helper on window for manual refresh (optional)
  window.reviews_refresh = function() {
    renderStarsOnLoad();
    applyFiltersAndPaginate();
  };
})();
</script>

</body>
</html>