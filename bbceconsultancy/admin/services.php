<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Services Management — Admin | BBC Engineering Consultancy</title>

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

    /* CATEGORY BLOCK */
    .service-category {
      margin-bottom:40px;
    }

    .category-head {
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom:16px;
    }

    .category-title {
      font-size:22px;
      font-weight:800;
      color:#015383;
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

    /* TABLE */
    .table-box {
      background:#fff;
      border-radius:16px;
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
    }

    tbody tr {
      border-bottom:1px solid #eef2ff;
    }

    tbody tr:hover {
      background:#f8fbff;
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

    .edit { background:#fef3c7; color:#92400e; }
    .delete { background:#fee2e2; color:#991b1b; }

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
  </style>
</head>

<body>

<!-- ================= ADMIN NAVBAR ================= -->
<?php include __DIR__ . '/partials/admin-navbar.php'; ?>

<!-- ================= CONTENT ================= -->
<main class="container admin-wrap">

  <h1 class="page-title">Services Management</h1>
  <p class="page-sub">Manage valuation, design, and supervision services shown on the website</p>

  <!-- ================= PROPERTY VALUATION ================= -->
  <div class="service-category">
    <div class="category-head">
      <div class="category-title">🏠 Property Valuation</div>
      <button class="add-btn">+ Add Service</button>
    </div>

    <div class="table-box">
      <table>
        <thead>
          <tr>
            <th>Service Name</th>
            <th>Description</th>
            <th>Used For</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td data-label="Service Name">Residential Property Valuation</td>
            <td data-label="Description">House & apartment valuation as per NRB guidelines</td>
            <td data-label="Used For">Bank Loan / Legal</td>
            <td data-label="Actions">
              <button class="action-btn edit">Edit</button>
              <button class="action-btn delete">Delete</button>
            </td>
          </tr>
          <tr>
            <td data-label="Service Name">Land Valuation</td>
            <td data-label="Description">Market & location-based land valuation</td>
            <td data-label="Used For">Sale / Mortgage</td>
            <td data-label="Actions">
              <button class="action-btn edit">Edit</button>
              <button class="action-btn delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ================= DESIGN & DRAWING ================= -->
  <div class="service-category">
    <div class="category-head">
      <div class="category-title">🏢 Design & Drawing</div>
      <button class="add-btn">+ Add Service</button>
    </div>

    <div class="table-box">
      <table>
        <thead>
          <tr>
            <th>Service Name</th>
            <th>Description</th>
            <th>Project Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td data-label="Service Name">Architectural Design</td>
            <td data-label="Description">Complete building planning & drawing approval</td>
            <td data-label="Project Type">Residential / Commercial</td>
            <td data-label="Actions">
              <button class="action-btn edit">Edit</button>
              <button class="action-btn delete">Delete</button>
            </td>
          </tr>
          <tr>
            <td data-label="Service Name">3D Design & Animation</td>
            <td data-label="Description">Interior & exterior 3D visualization</td>
            <td data-label="Project Type">All Projects</td>
            <td data-label="Actions">
              <button class="action-btn edit">Edit</button>
              <button class="action-btn delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ================= SITE SUPERVISION ================= -->
  <div class="service-category">
    <div class="category-head">
      <div class="category-title">🏗 Site Supervision</div>
      <button class="add-btn">+ Add Service</button>
    </div>

    <div class="table-box">
      <table>
        <thead>
          <tr>
            <th>Service Name</th>
            <th>Description</th>
            <th>Scope</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td data-label="Service Name">House Construction Supervision</td>
            <td data-label="Description">Quality & drawing compliance supervision</td>
            <td data-label="Scope">Residential</td>
            <td data-label="Actions">
              <button class="action-btn edit">Edit</button>
              <button class="action-btn delete">Delete</button>
            </td>
          </tr>
          <tr>
            <td data-label="Service Name">Infrastructure Supervision</td>
            <td data-label="Description">Road, drainage & infrastructure projects</td>
            <td data-label="Scope">Government / Private</td>
            <td data-label="Actions">
              <button class="action-btn edit">Edit</button>
              <button class="action-btn delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</main>

</body>
</html>
