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
    .draft { background:#e0e7ff; color:#3730a3; }

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

<!-- ================= ADMIN NAVBAR ================= -->
<?php include __DIR__ . '/partials/admin-navbar.php'; ?>

  </div>
</header>

<!-- ================= CONTENT ================= -->
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

        <tr>
          <td data-label="Question">Is the valuation NRB compliant?</td>
          <td data-label="Category">Valuation</td>
          <td data-label="Status"><span class="status active">Visible</span></td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

        <tr>
          <td data-label="Question">Is site inspection mandatory?</td>
          <td data-label="Category">Legal</td>
          <td data-label="Status"><span class="status active">Visible</span></td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

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

        <tr>
          <td data-label="Title">NRB Valuation Guidelines Explained</td>
          <td data-label="Date">2025-01-12</td>
          <td data-label="Status"><span class="status active">Published</span></td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

        <tr>
          <td data-label="Title">Property Valuation Process in Nepal</td>
          <td data-label="Date">2025-01-05</td>
          <td data-label="Status"><span class="status draft">Draft</span></td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

      </tbody>
    </table>
  </section>

</main>

</body>
</html>
