<?php
require_once "../config/config.php";
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: login.php");
  exit;
}

/* FETCH FAQS */
$faqs = $conn->query("SELECT * FROM faqs ORDER BY id DESC");

/* FETCH BLOGS */
$blogs = $conn->query("SELECT * FROM blogs ORDER BY publish_date DESC");
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>FAQ & Blog — Admin | BBC Engineering Consultancy</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">

  <style>
    body { background:#f6f9ff; }
    .admin-wrap { padding:40px 0; }
    h1 { font-family:Poppins,sans-serif;font-size:32px;font-weight:800;color:#021428;margin-bottom:8px; }
    .page-sub { font-size:14px;color:#475569;margin-bottom:30px; }
    .section-box { background:#fff;border-radius:16px;box-shadow:0 12px 30px rgba(2,8,20,.08);margin-bottom:40px;overflow:hidden; }
    .section-head { display:flex;justify-content:space-between;align-items:center;padding:18px 22px;border-bottom:1px solid #eef2ff; }
    .section-head h3 { margin:0;font-size:20px;font-weight:800;color:#021428; }
    .add-btn { padding:8px 14px;border-radius:10px;background:#021428;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer; }
    table { width:100%;border-collapse:collapse;font-size:14px; }
    th, td { padding:14px 16px;text-align:left; }
    thead { background:#021428;color:#fff; }
    tbody tr { border-bottom:1px solid #eef2ff; }
    tbody tr:hover { background:#f8fbff; }
    .status { padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700; }
    .active { background:#dcfce7;color:#166534; }
    .inactive { background:#fee2e2;color:#991b1b; }
    .draft { background:#e0e7ff;color:#3730a3; }
    .action-btn { padding:6px 10px;font-size:12px;border-radius:8px;border:none;cursor:pointer;font-weight:700;margin-right:6px; }
    .edit { background:#fef3c7;color:#92400e; }
    .delete { background:#fee2e2;color:#991b1b; }

    @media(max-width:768px){
      table, thead, tbody, th, td, tr { display:block; }
      thead { display:none; }
      tbody tr { padding:14px; }
      td::before {
        content:attr(data-label);
        display:block;
        font-weight:700;
        font-size:12px;
        color:#64748b;
        margin-bottom:4px;
      }
    }
  </style>
</head>

<body>

<?php include __DIR__ . '/partials/admin-navbar.php'; ?>

<main class="container admin-wrap">

  <h1>FAQ & Blog Content</h1>
  <p class="page-sub">Manage frequently asked questions and blog insights</p>

  <!-- ================= FAQ ================= -->
  <section class="section-box">
    <div class="section-head">
      <h3>FAQs</h3>
      <button class="add-btn">+ Add FAQ</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>Question</th>
          <th>Category</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>

      <?php if($faqs && $faqs->num_rows): ?>
        <?php while($f = $faqs->fetch_assoc()): ?>
        <tr>
          <td data-label="Question"><?= htmlspecialchars($f['question']) ?></td>
          <td data-label="Category"><?= htmlspecialchars($f['category']) ?></td>
          <td data-label="Status">
            <span class="status <?= $f['status'] === 'visible' ? 'active' : 'inactive' ?>">
              <?= ucfirst($f['status']) ?>
            </span>
          </td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">No FAQs found.</td></tr>
      <?php endif; ?>

      </tbody>
    </table>
  </section>

  <!-- ================= BLOG ================= -->
  <section class="section-box">
    <div class="section-head">
      <h3>Blog / Insights</h3>
      <button class="add-btn">+ Add Blog</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>

      <?php if($blogs && $blogs->num_rows): ?>
        <?php while($b = $blogs->fetch_assoc()): ?>
        <tr>
          <td data-label="Title"><?= htmlspecialchars($b['title']) ?></td>
          <td data-label="Date"><?= htmlspecialchars($b['publish_date']) ?></td>
          <td data-label="Status">
            <span class="status <?= $b['status'] === 'published' ? 'active' : 'draft' ?>">
              <?= ucfirst($b['status']) ?>
            </span>
          </td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">No blog posts found.</td></tr>
      <?php endif; ?>

      </tbody>
    </table>
  </section>

</main>

</body>
</html>
