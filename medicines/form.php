<?php
require_once "../config/app.php"; require_login();
$id=(int)($_GET["id"]??0); $editing=$id>0;
$m=["medicine_name"=>"","category_id"=>"","batch_number"=>"","quantity"=>"0","reorder_level"=>"10","buying_price"=>"0.00","selling_price"=>"0.00","expiry_date"=>""];
if($editing){$st=$conn->prepare("SELECT * FROM medicines WHERE id=?");$st->bind_param("i",$id);$st->execute();$found=$st->get_result()->fetch_assoc();if(!$found){flash("danger","Medicine not found.");header("Location: index.php");exit;}$m=$found;}
$categories=$conn->query("SELECT id,category_name FROM categories ORDER BY category_name");
$error="";
if($_SERVER["REQUEST_METHOD"]==="POST"){
 verify_csrf();
 $name=trim($_POST["medicine_name"]??"");$cat=(int)($_POST["category_id"]??0);$batch=trim($_POST["batch_number"]??"");
 $qty=filter_var($_POST["quantity"]??null,FILTER_VALIDATE_INT);$reorder=filter_var($_POST["reorder_level"]??null,FILTER_VALIDATE_INT);
 $buy=(float)($_POST["buying_price"]??-1);$sell=(float)($_POST["selling_price"]??-1);$expiry=$_POST["expiry_date"]?:null;
 if($name===""||$qty===false||$qty<0||$reorder===false||$reorder<0||$buy<0||$sell<0){$error="Enter a medicine name and valid non-negative quantities and prices.";}
 else {
  if($editing){$st=$conn->prepare("UPDATE medicines SET medicine_name=?,category_id=NULLIF(?,0),batch_number=?,quantity=?,reorder_level=?,buying_price=?,selling_price=?,expiry_date=? WHERE id=?");$st->bind_param("sisidddsi",$name,$cat,$batch,$qty,$reorder,$buy,$sell,$expiry,$id);}
  else {$st=$conn->prepare("INSERT INTO medicines(medicine_name,category_id,batch_number,quantity,reorder_level,buying_price,selling_price,expiry_date) VALUES(?,NULLIF(?,0),?,?,?,?,?,?)");$st->bind_param("sisiidds",$name,$cat,$batch,$qty,$reorder,$buy,$sell,$expiry);}
  try{$st->execute();flash("success",$editing?"Medicine updated.":"Medicine added.");header("Location: index.php");exit;}
  catch(mysqli_sql_exception $ex){$error="Could not save medicine. Check the information and try again.";}
 }
 $m=array_merge($m,$_POST);
}
$page_title=$editing?"Edit medicine":"Add medicine";require "../includes/header.php";
?>
<div class="row justify-content-center"><div class="col-lg-8"><div class="card"><div class="card-header"><?= e($page_title) ?></div><div class="card-body p-4">
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Medicine name *</label><input class="form-control" name="medicine_name" value="<?= e($m["medicine_name"]) ?>" required></div>
<div class="col-md-4"><label class="form-label">Category</label><select class="form-select" name="category_id"><option value="0">Select category</option><?php while($c=$categories->fetch_assoc()): ?><option value="<?= (int)$c["id"] ?>" <?= (int)($m["category_id"]??0)===(int)$c["id"]?"selected":"" ?>><?= e($c["category_name"]) ?></option><?php endwhile; ?></select></div>
<div class="col-md-4"><label class="form-label">Batch number</label><input class="form-control" name="batch_number" value="<?= e($m["batch_number"]) ?>"></div>
<div class="col-md-4"><label class="form-label">Quantity *</label><input type="number" min="0" class="form-control" name="quantity" value="<?= e($m["quantity"]) ?>" required></div>
<div class="col-md-4"><label class="form-label">Reorder level</label><input type="number" min="0" class="form-control" name="reorder_level" value="<?= e($m["reorder_level"]) ?>"></div>
<div class="col-md-4"><label class="form-label">Buying price (GHS)</label><input type="number" min="0" step=".01" class="form-control" name="buying_price" value="<?= e($m["buying_price"]) ?>"></div>
<div class="col-md-4"><label class="form-label">Selling price (GHS)</label><input type="number" min="0" step=".01" class="form-control" name="selling_price" value="<?= e($m["selling_price"]) ?>"></div>
<div class="col-md-4"><label class="form-label">Expiry date</label><input type="date" class="form-control" name="expiry_date" value="<?= e($m["expiry_date"]??"") ?>"></div>
</div><div class="mt-4"><button class="btn btn-primary">Save medicine</button> <a class="btn btn-outline-secondary" href="index.php">Cancel</a></div></form>
</div></div></div></div>
<?php require "../includes/footer.php"; ?>
