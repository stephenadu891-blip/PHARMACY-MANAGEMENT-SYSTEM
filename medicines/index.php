<?php
require_once "../config/app.php"; require_login();
$page_title="Medicines";
if ($_SERVER["REQUEST_METHOD"]==="POST") {
 verify_csrf(); $action=$_POST["action"]??"";
 if ($action==="delete") {
  $id=(int)($_POST["id"]??0);
  try { $st=$conn->prepare("DELETE FROM medicines WHERE id=?"); $st->bind_param("i",$id); $st->execute(); flash("success","Medicine deleted."); }
  catch(mysqli_sql_exception $e){ flash("danger","This medicine is linked to existing records and cannot be deleted."); }
  header("Location: index.php"); exit;
 }
}
$q=trim($_GET["q"]??"");
if($q!==""){ $like="%".$q."%"; $st=$conn->prepare("SELECT m.*,c.category_name FROM medicines m LEFT JOIN categories c ON c.id=m.category_id WHERE m.medicine_name LIKE ? OR m.batch_number LIKE ? ORDER BY m.medicine_name"); $st->bind_param("ss",$like,$like); $st->execute(); $rows=$st->get_result(); }
else { $rows=$conn->query("SELECT m.*,c.category_name FROM medicines m LEFT JOIN categories c ON c.id=m.category_id ORDER BY m.medicine_name"); }
require "../includes/header.php";
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h2>Medicine inventory</h2><p class="text-muted mb-0">Manage medicine details, stock and expiry dates.</p></div><a class="btn btn-primary" href="form.php">+ Add medicine</a></div>
<div class="card"><div class="card-body">
<form class="row g-2 mb-3"><div class="col-md-5"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search medicine or batch number"></div><div class="col-auto"><button class="btn btn-outline-primary">Search</button> <a class="btn btn-outline-secondary" href="index.php">Reset</a></div></form>
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Medicine</th><th>Category</th><th>Batch</th><th>Quantity</th><th>Selling price</th><th>Expiry</th><th>Actions</th></tr></thead><tbody>
<?php if(!$rows->num_rows): ?><tr><td colspan="7" class="text-center text-muted py-4">No medicines found.</td></tr><?php endif; ?>
<?php while($m=$rows->fetch_assoc()): ?><tr><td class="fw-semibold"><?= e($m["medicine_name"]) ?></td><td><?= e($m["category_name"]??"—") ?></td><td><?= e($m["batch_number"]??"—") ?></td><td><?= (int)$m["quantity"] ?><?php if((int)$m["quantity"]<=(int)$m["reorder_level"]): ?> <span class="badge text-bg-danger">Low</span><?php endif; ?></td><td><?= money($m["selling_price"]) ?></td><td><?= e($m["expiry_date"]??"—") ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="form.php?id=<?= (int)$m["id"] ?>">Edit</a>
<form method="post" class="d-inline"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$m["id"] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Delete this medicine?">Delete</button></form></td></tr><?php endwhile; ?>
</tbody></table></div></div></div>
<?php require "../includes/footer.php"; ?>
