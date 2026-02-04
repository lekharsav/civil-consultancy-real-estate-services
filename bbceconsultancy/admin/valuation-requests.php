<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Valuation Requests — Admin | BBC Engineering Consultancy</title>

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

    /* TABLE */
    .table-box {
      background:#fff;
      border-radius:18px;
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
      vertical-align:top;
    }

    tbody tr {
      border-bottom:1px solid #eef2ff;
    }

    tbody tr:hover {
      background:#f8fbff;
    }

    /* STATUS */
    .status {
      padding:6px 12px;
      border-radius:20px;
      font-size:12px;
      font-weight:700;
      display:inline-block;
    }

    .new { background:#e0f2fe; color:#0369a1; }
    .contacted { background:#fff7ed; color:#9a3412; }
    .visited { background:#ecfeff; color:#155e75; }
    .completed { background:#dcfce7; color:#166534; }

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

    .view { background:#e0e7ff; color:#1e40af; }
    .edit { background:#fef3c7; color:#92400e; }
    .delete { background:#fee2e2; color:#991b1b; }

    @media(max-width:1024px){
      table { font-size:13px; }
    }

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

  <h1 class="page-title">Valuation Requests</h1>
  <p class="page-sub">Manage incoming property valuation and site inspection requests</p>

  <div class="table-box">
    <table>
      <thead>
        <tr>
          <th>Client</th>
          <th>Contact</th>
          <th>Property</th>
          <th>Plot / Area</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>

        <tr>
          <td data-label="Client">Ramesh Bhandari</td>
          <td data-label="Contact">📞 98XXXXXXXX<br>📍 Dhangadhi</td>
          <td data-label="Property">Residential House</td>
          <td data-label="Plot / Area">Plot 12A<br>1200 sq.ft</td>
          <td data-label="Status"><span class="status new">New</span></td>
          <td data-label="Actions">
            <button class="action-btn view">View</button>
            <button class="action-btn edit">Update</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

        <tr>
          <td data-label="Client">Global IME Bank</td>
          <td data-label="Contact">📞 Branch Office<br>📍 Kailali</td>
          <td data-label="Property">Commercial Building</td>
          <td data-label="Plot / Area">Plot 8B<br>3500 sq.ft</td>
          <td data-label="Status"><span class="status contacted">Contacted</span></td>
          <td data-label="Actions">
            <button class="action-btn view">View</button>
            <button class="action-btn edit">Update</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

        <tr>
          <td data-label="Client">Sita Chaudhary</td>
          <td data-label="Contact">📞 98XXXXXXXX<br>📍 Attariya</td>
          <td data-label="Property">Land Valuation</td>
          <td data-label="Plot / Area">Plot 22<br>5 Kattha</td>
          <td data-label="Status"><span class="status visited">Site Visited</span></td>
          <td data-label="Actions">
            <button class="action-btn view">View</button>
            <button class="action-btn edit">Update</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

        <tr>
          <td data-label="Client">Prabhu Bank Ltd.</td>
          <td data-label="Contact">📞 Head Office<br>📍 Kathmandu</td>
          <td data-label="Property">Under-Construction</td>
          <td data-label="Plot / Area">Loan Case<br>Stage 3</td>
          <td data-label="Status"><span class="status completed">Completed</span></td>
          <td data-label="Actions">
            <button class="action-btn view">View</button>
            <button class="action-btn edit">Update</button>
            <button class="action-btn delete">Delete</button>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

</main>

</body>
</html>
