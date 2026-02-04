<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Clients & Reviews — Admin | BBC Engineering Consultancy</title>

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

    .logo {
      width:46px;
      height:46px;
      border-radius:10px;
      object-fit:contain;
      background:#fff;
      border:1px solid #e5e7eb;
      padding:6px;
    }

    @media(max-width:768px){
      table, thead, tbody, th, td, tr { display:block; }
      thead { display:none; }

      tbody tr {
        padding:14px;
      }

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

<!-- ================= CONTENT ================= -->
<main class="container admin-wrap">

  <h1>Clients & Reviews</h1>
  <p class="page-sub">Manage client feedback and companies you have worked with</p>

  <!-- ================= REVIEWS ================= -->
  <section class="section-box">
    <div class="section-head">
      <h3>Client Reviews</h3>
      <button class="add-btn">+ Add Review</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Rating</th>
          <th>Review</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>

        <tr>
          <td data-label="Name">Bank Officer</td>
          <td data-label="Type">Bank</td>
          <td data-label="Rating">★★★★★</td>
          <td data-label="Review">Accurate NRB-compliant valuation reports.</td>
          <td data-label="Status"><span class="status active">Visible</span></td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

        <tr>
          <td data-label="Name">Corporate Client</td>
          <td data-label="Type">Corporate</td>
          <td data-label="Rating">★★★★☆</td>
          <td data-label="Review">Professional and timely service.</td>
          <td data-label="Status"><span class="status active">Visible</span></td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

      </tbody>
    </table>
  </section>

  <!-- ================= COMPANIES ================= -->
  <section class="section-box">
    <div class="section-head">
      <h3>Companies & Institutions</h3>
      <button class="add-btn">+ Add Company</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>Logo</th>
          <th>Name</th>
          <th>Type</th>
          <th>Description</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>

        <tr>
          <td data-label="Logo">
            <img src="../aa.jpg" class="logo" alt="">
          </td>
          <td data-label="Name">Rastriya Banijya Bank Ltd.</td>
          <td data-label="Type">Bank</td>
          <td data-label="Description">Loan & mortgage valuation partner</td>
          <td data-label="Status"><span class="status active">Visible</span></td>
          <td data-label="Actions">
            <button class="action-btn edit">Edit</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

        <tr>
          <td data-label="Logo">
            <img src="../ab.jpg" class="logo" alt="">
          </td>
          <td data-label="Name">CareTake Hospital Pvt. Ltd.</td>
          <td data-label="Type">Corporate</td>
          <td data-label="Description">Building design & supervision</td>
          <td data-label="Status"><span class="status inactive">Hidden</span></td>
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
