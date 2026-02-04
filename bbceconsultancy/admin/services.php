<?php
require_once "../config/config.php";
if (!isset($_SESSION['admin_logged_in'])) {
  header("Location: login.php"); exit;
}

$icons = ['🏠','🏢','🏗','🌾','⚡','🕌','🏨','🏥'];

if(isset($_POST['save_category'])){
  $id=$_POST['cat_id']??null;
  $name=$_POST['name'];
  $icon=$_POST['icon'];
  $img=$_POST['old_image']??null;

  if(!empty($_FILES['image']['name'])){
    $img=time().'_'.$_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/services/".$img);
  }

  if($id){
    $stmt=$conn->prepare("UPDATE service_categories SET name=?,icon=?,image=? WHERE id=?");
    $stmt->bind_param("sssi",$name,$icon,$img,$id);
  }else{
    $stmt=$conn->prepare("INSERT INTO service_categories(name,icon,image) VALUES(?,?,?)");
    $stmt->bind_param("sss",$name,$icon,$img);
  }
  $stmt->execute();
  header("Location: services.php"); exit;
}

if(isset($_GET['del_cat'])){
  $conn->query("DELETE FROM service_categories WHERE id=".(int)$_GET['del_cat']);
  header("Location: services.php"); exit;
}

if(isset($_POST['save_service'])){
  $sid=$_POST['service_id']??null;
  $cid=$_POST['category_id'];
  $t=$_POST['title'];
  $d=$_POST['description'];
  $s=$_POST['scope'];
  $img=$_POST['old_image']??null;

  if(!empty($_FILES['image']['name'])){
    $img=time().'_'.$_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/services/".$img);
  }

  if($sid){
    $stmt=$conn->prepare("UPDATE services SET title=?,description=?,scope=?,image=? WHERE id=?");
    $stmt->bind_param("ssssi",$t,$d,$s,$img,$sid);
  }else{
    $stmt=$conn->prepare("INSERT INTO services(category_id,title,description,scope,image) VALUES(?,?,?,?,?)");
    $stmt->bind_param("issss",$cid,$t,$d,$s,$img);
  }
  $stmt->execute();
  header("Location: services.php"); exit;
}

if(isset($_GET['del_srv'])){
  $conn->query("DELETE FROM services WHERE id=".(int)$_GET['del_srv']);
  header("Location: services.php"); exit;
}

$cats=$conn->query("SELECT * FROM service_categories ORDER BY sort_order ASC");
?>

<!doctype html>
<html>
<head>
<title>Services — Admin</title>
<link rel="stylesheet" href="../style.css">

<style>/* ===== BASE ===== */
body{
  background:#f4f7fb;
  font-family:system-ui,-apple-system,BlinkMacSystemFont;
  color:#000;
  -webkit-font-smoothing:antialiased;
}
h1{font-weight:800;letter-spacing:-0.02em}

/* ===== PAGE HEADER ===== */
.page-head{
  display:grid;
  grid-template-columns:1fr auto;
  grid-template-rows:auto auto;
  align-items:start;
  column-gap:20px;
}

/* TITLE */
.page-head .page-title{
  grid-column:1;
  grid-row:1;
  margin:0;
}

/* SUBTITLE */
.page-head .admin-sub{
  grid-column:1;
  grid-row:2;
  margin-top:6px;
  margin-bottom:0;
  font-size:14px;
  opacity:.85;
}

/* BUTTON (right, pushed down intentionally) */
/* PAGE HEAD PRIMARY BUTTON */
.page-head .btn.primary{
  justify-self:end;
  align-self:flex-start;
  margin-bottom:6px;
  transform: translateY(40px);
  transition: box-shadow .25s ease, filter .25s ease;
}

/* HOVER — DO NOT TOUCH TRANSFORM */
.page-head .btn.primary:hover{
  filter: brightness(1.08);
  box-shadow:0 14px 32px rgba(59,130,246,.45);
}


.section-gap{height:24px}

/* ===== BUTTONS ===== */
.btn{
  padding:11px 22px;
  border-radius:14px;
  font-weight:600;
  border:none;
  cursor:pointer;
  line-height:1;
  transition:background .25s ease, transform .25s ease, box-shadow .25s ease;
}

.btn.primary{
  background:linear-gradient(135deg,#00c6ff,#3b82f6);
  color:#fff;
  box-shadow:0 10px 24px rgba(59,130,246,.35);
}


.btn.light{
  background:#eef2ff;
  color:#000;
}
.btn.light:hover{
  background:#dbeafe;
}

/* ===== ICON BUTTON ===== */
.icon-btn{
  width:38px;
  height:38px;
  border-radius:12px;
  border:none;
  background:#eef2ff;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  transition:background .2s ease, transform .2s ease;
}
.icon-btn:hover{
  background:#dbeafe;
  transform:translateY(-1px);
}

/* ===== CATEGORY CARD ===== */
.category-card{
  background:#fff;
  border-radius:22px;
  padding:24px;
  margin-bottom:32px;
  box-shadow:0 18px 45px rgba(0,0,0,.08);
  transition:transform .25s ease, box-shadow .25s ease;
}
.category-card:hover{
  transform:translateY(-4px);
  box-shadow:0 30px 70px rgba(0,0,0,.12);
}

.category-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  border-bottom:1px solid #eef2f7;
  padding-bottom:14px;
}
.cat-left{display:flex;align-items:center;gap:14px}
.cat-title{font-size:22px;font-weight:800}

/* ===== DROPDOWN ===== */
.dropdown{position:relative}
.dropdown-box{
  display:none;
  position:absolute;
  top:44px;
  right:0;
  width:260px;
  background:#fff;
  border-radius:16px;
  padding:14px;
  box-shadow:0 30px 70px rgba(0,0,0,.22);
  z-index:999;
}
.dropdown-box img{
  width:100%;
  height:150px;
  object-fit:cover;
  border-radius:12px;
  margin-bottom:10px;
}
.dropdown.active .dropdown-box{display:block}

/* ===== SERVICE ITEM ===== */
.service-item{
  margin-top:16px;
  padding:16px 18px;
  border-radius:16px;
  background:#f8fbff;
  display:flex;
  justify-content:space-between;
  align-items:center;
  border:1px solid #e6eef8;
  transition:background .25s ease, border-color .25s ease, transform .2s ease;
}
.service-item:hover{
  background:#eef6ff;
  border-color:#c7e0ff;
  transform:translateY(-1px);
}

/* ===== MODAL ===== */
.modal{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.55);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:5000;
}
.modal.active{display:flex}
.modal-box{
  background:#fff;
  border-radius:24px;
  padding:34px;
  width:100%;
  max-width:580px;
  box-shadow:0 40px 90px rgba(0,0,0,.25);
}
.modal-box h3{
  font-weight:800;
  margin-bottom:16px;
}

/* ===== FORM ===== */
input,textarea,select{
  width:100%;
  padding:11px 12px;
  margin-bottom:12px;
  border-radius:12px;
  border:1px solid #dbe3ef;
  transition:border-color .2s ease, box-shadow .2s ease;
}
input:focus,textarea:focus,select:focus{
  outline:none;
  border-color:#60a5fa;
  box-shadow:0 0 0 3px rgba(96,165,250,.2);
}
textarea{min-height:90px}

/* ===== IMAGE PREVIEW ===== */
.preview{
  height:220px;
  border-radius:16px;
  background:#eef2f7;
  overflow:hidden;
  margin-bottom:12px;
}
.preview img{
  width:100%;
  height:100%;
  object-fit:cover;
}

/* ===== RESPONSIVE ===== */
@media(max-width:768px){
  .modal-box{max-width:94%}
  .service-item{
    flex-direction:column;
    align-items:flex-start;
    gap:10px;
  }
}
</style>
</head>

<body>
<?php include __DIR__.'/partials/admin-navbar.php'; ?>

<main class="container admin-wrap">

<div class="page-head">
  <h1 class="page-title">Services</h1>
  <p class="admin-sub">Manage service categories and services offered</p>
  <button class="btn primary" onclick="openCat()">+ Add Category</button>
</div>
<div class="section-gap"></div>

<?php while($c=$cats->fetch_assoc()): ?>
<div class="category-card">
  <div class="category-head">
    <div class="cat-left">
      <div><?= $c['icon'] ?></div>
      <div class="cat-title"><?= htmlspecialchars($c['name']) ?></div>

      <div class="dropdown">
        <button class="icon-btn" onclick="toggle(this)">ℹ️</button>
        <div class="dropdown-box">
          <img src="../uploads/services/<?= $c['image'] ?>">
          <button class="btn light" onclick="editCat(<?= $c['id'] ?>,'<?= htmlspecialchars($c['name']) ?>','<?= $c['icon'] ?>','<?= $c['image'] ?>')">Edit</button>
          <a class="btn light" href="?del_cat=<?= $c['id'] ?>" onclick="return confirm('Delete category?')">Delete</a>
        </div>
      </div>
    </div>
    <button class="btn light" onclick="openSrv(<?= $c['id'] ?>)">+ Add Service</button>
  </div>

<?php
$sv=$conn->query("SELECT * FROM services WHERE category_id=".$c['id']);
while($s=$sv->fetch_assoc()):
?>
<div class="service-item">
  <div>
    <strong><?= $s['title'] ?></strong><br>
    <small><?= $s['scope'] ?></small>
  </div>
  <div>
    <!-- <button class="icon-btn" onclick="viewSrv('<?= $s['title'] ?>','<?= $s['description'] ?>','<?= $s['scope'] ?>')"></button> -->
    <button class="icon-btn" onclick="editSrv(<?= $s['id'] ?>,<?= $c['id'] ?>,'<?= $s['title'] ?>','<?= $s['description'] ?>','<?= $s['scope'] ?>','<?= $s['image'] ?>')">🔍</button>
    <a class="icon-btn" href="?del_srv=<?= $s['id'] ?>" onclick="return confirm('Delete service?')">🗑</a>
  </div>
</div>
<?php endwhile; ?>
</div>
<?php endwhile; ?>

</main>

<!-- MODALS (LOGIC UNCHANGED) -->
<div class="modal" id="catModal"><div class="modal-box">
<form method="post" enctype="multipart/form-data">
<h3>Category</h3>
<input type="hidden" name="cat_id" id="cat_id">
<input type="hidden" name="old_image" id="cat_old">
<input name="name" id="cat_name" required>
<select name="icon"><?php foreach($icons as $i): ?><option><?= $i ?></option><?php endforeach ?></select>
<div class="preview"><img id="cat_prev"></div>
<input type="file" name="image" onchange="prev(this,'cat_prev')">
<button class="btn primary" name="save_category">Save</button>
<button type="button" class="btn light" onclick="closeM('catModal')">Cancel</button>
</form>
</div></div>

<div class="modal" id="srvModal"><div class="modal-box">
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="service_id" id="srv_id">
<input type="hidden" name="category_id" id="srv_cat">
<input type="hidden" name="old_image" id="srv_old">
<input name="title" id="srv_title" required>
<textarea name="description" id="srv_desc"></textarea>
<input name="scope" id="srv_scope">
<div class="preview"><img id="srv_prev"></div>
<input type="file" name="image" onchange="prev(this,'srv_prev')">
<button class="btn primary" name="save_service">Save</button>
<button type="button" class="btn light" onclick="closeM('srvModal')">Cancel</button>
</form>
</div></div>

<script>
function toggle(b){b.parentElement.classList.toggle('active')}
function prev(i,id){document.getElementById(id).src=URL.createObjectURL(i.files[0])}
function openCat(){catModal.classList.add('active')}
function openSrv(cid){srv_cat.value=cid;srvModal.classList.add('active')}
function closeM(id){document.getElementById(id).classList.remove('active')}
function editCat(id,n,i,img){cat_id.value=id;cat_name.value=n;cat_old.value=img;cat_prev.src='../uploads/services/'+img;openCat()}
function editSrv(id,c,t,d,s,img){srv_id.value=id;srv_cat.value=c;srv_title.value=t;srv_desc.value=d;srv_scope.value=s;srv_old.value=img;srv_prev.src='../uploads/services/'+img;srvModal.classList.add('active')}
function viewSrv(t,d,s){alert(t+"\n\n"+d+"\n\n"+s)}
</script>

</body>
</html>
