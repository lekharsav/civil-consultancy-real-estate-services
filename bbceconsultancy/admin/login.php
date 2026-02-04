<?php
require_once "../config/config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($email === "" || $password === "") {
    $error = "Invalid user id or password";
  } else {

    $stmt = $conn->prepare(
      "SELECT id, name, password FROM admins WHERE email = ? LIMIT 1"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $admin = $result->fetch_assoc();

      // PLAIN PASSWORD CHECK
      if ($password === $admin['password']) {

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];

        header("Location: index.php");
        exit;
      }
    }

    $error = "Invalid user id or password";
  }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Admin Login — BBC Engineering Consultancy</title>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../style.css">

<style>
body{
  min-height:100vh;
  background:
    linear-gradient(rgba(2,8,20,.75),rgba(2,8,20,.65)),
    url('../aa.jpg') center/cover no-repeat;
  display:flex;
  align-items:center;
  justify-content:center;
  font-family:Inter,sans-serif;
}

.login-card{
  background:#fff;
  width:100%;
  max-width:420px;
  padding:40px;
  border-radius:20px;
  box-shadow:0 30px 80px rgba(0,0,0,.35);
}

.login-brand{
  display:flex;
  align-items:center;
  gap:14px;
  margin-bottom:30px;
}

.logo-mark{
  background:#021428;
  color:#fff;
  width:48px;
  height:48px;
  display:grid;
  place-items:center;
  font-weight:900;
  border-radius:12px;
}

.login-brand h2{
  font-family:Poppins,sans-serif;
  font-size:22px;
  font-weight:800;
  margin:0;
  color:#021428;
}

.login-brand span{
  font-size:12px;
  color:#64748b;
  font-weight:600;
}

.login-card h3{
  font-size:26px;
  font-weight:800;
  margin-bottom:6px;
  color:#021428;
}

.login-card p{
  font-size:14px;
  color:#475569;
  margin-bottom:26px;
}

.login-form{
  position:relative;
}

.login-form input{
  width:100%;
  padding:14px 44px 14px 14px;
  border-radius:10px;
  border:1px solid #dfe7fa;
  font-size:14px;
  margin-bottom:14px;
}

.login-form input:focus{
  outline:none;
  border-color:#2563eb;
  box-shadow:0 0 0 2px rgba(37,99,235,.15);
}

.password-wrap{
  position:relative;
}

.eye-btn{
  position:absolute;
  right:14px;
  top:40%;
  transform:translateY(-50%);
  cursor:pointer;
  font-size:18px;
  color:#64748b;
}

.login-btn{
  width:100%;
  padding:14px;
  border-radius:10px;
  border:none;
  background:#021428;
  color:#fff;
  font-weight:800;
  font-size:15px;
  cursor:pointer;
  transition:.3s;
}

.login-btn:hover{
  background:#031c3a;
  transform:translateY(-2px);
}

.login-error{
  background:#fee2e2;
  color:#991b1b;
  padding:10px;
  border-radius:8px;
  font-size:13px;
  margin-bottom:14px;
  text-align:center;
}
</style>
</head>

<body>

<div class="login-card">

  <!-- BRAND -->
  <div class="login-brand">
    <div class="logo-mark">BBC</div>
    <div>
      <h2>Admin Panel</h2>
      <span>Engineering Consultancy Pvt. Ltd.</span>
    </div>
  </div>

  <h3>Administrator Login</h3>
  <p>Authorized access only</p>

  <?php if ($error): ?>
    <div class="login-error"><?= $error ?></div>
  <?php endif; ?>

  <form class="login-form" method="POST">

    <input
      type="email"
      name="email"
      placeholder="Admin Email"
      required
    >

    <div class="password-wrap">
      <input
        type="password"
        name="password"
        id="password"
        placeholder="Password"
        required
      >
      <span class="eye-btn" onclick="togglePassword()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
</svg></span>
    </div>

    <button class="login-btn" type="submit">
      Sign In
    </button>

  </form>

</div>

<script>
function togglePassword(){
  const pass = document.getElementById('password');
  pass.type = pass.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
