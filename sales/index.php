<?php
require_once "../config/app.php"; require_login();
$page_title="Sales";
if($_SERVER["REQUEST_METHOD"]==="POST"){
 verify_csrf();$medicine_id=(int)($_POST["medicine_id"]??0);$qty=(int)($_POST["quantity"]??0);$customer_id=(int)($_POST["customer_id"]??0);$payment=$_POST["payment_method"]??"cash";
 if($qty<1||!in_array($payment,["cash","mobile_money","card"],true)){flash("danger","Enter a valid quantity and payment method.");header("Location: index.php");exit;}
 try{
  $conn->begin_transaction();
  $st=$conn->prepare("SELECT id,quantity,selling_price,expiry_date,medicine_name FROM medicines WHERE id=? FOR UPDATE");$st->bind_param("i",$medicine_id);$st->execute();$med=$st->get_result()->fetch_assoc();
  if(!$med||$med["quantity"]<$qty||($med["expiry_date"]&&$med["expiry_date"]<date("Y-m-d")))throw new Exception("Medicine unavailable, expired or insufficient stock.");
  $total=$qty*(float)$med["selling_price"];
  if($customer_id>0){$st=$conn->prepare("INSERT INTO sales(customer_id,user_id,total_amount,payment_method) VALUES(?,?,?,?)");$st->bind_param("iids",$customer_id,$_SESSION["user_id"],$total,$payment);}
  else{$st=$conn->prepare("INSERT INTO sales(customer_id,user_id,total_amount,payment_method) VALUES(NULL,?,?,?)");$st->bind_param("ids",$_SESSION["user_id"],$total,$payment);}
  $st->execute();$sale_id=$conn->insert_id;
  $st=$conn->prepare("INSERT INTO sale_items(sale_id,medicine_id,quantity,unit_price) VALUES(?,?,?,?)");$price=(float)$med["selling_price"];$st->bind_param("iiid",$sale_id,$medicine_id,$qty,$price);$st->execute();
  $st=$conn->prepare("UPDATE medicines SET quantity=quantity-? WHERE id=?");$st->bind_param("ii",$qty,$medicine_id);$st->execute();
  $conn->commit();flash("success","Sale completed. Receipt number: #".$sale_id);
 }catch(Throwable $ex){$conn->rollback();flash("danger",$ex->getMessage());}
 header("Location: index.php");exit;
}
$meds=$conn->query("SELECT id,medicine_name,quantity,selling_price FROM medicines WHERE quantity>0 AND (expiry_date IS NULL OR expiry_date>=CURDATE()) ORDER BY medicine_name");
$customers=$conn->query("SELECT id,fullname FROM customers ORDER BY fullname");
$history=$conn->query("SELECT s.*,c.fullname,u.fullname staff FROM sales s LEFT JOIN customers c ON c.id=s.customer_id JOIN users u ON u.id=s.user_id ORDER BY s.id DESC LIMIT 30");
require "../includes/header.php";
?>
<div class="row g-3"><div class="col-lg-4"><div class="card"><div class="card-header">Record a sale</div><div class="card-body"><form method="post" id="saleForm" action="index.php"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label">Medicine</label><select class="form-select" name="medicine_id" required><option value="">Choose medicine</option><?php while($m=$meds->fetch_assoc()): ?><option value="<?= (int)$m["id"] ?>"><?= e($m["medicine_name"]) ?> — <?= (int)$m["quantity"] ?> available — <?= money($m["selling_price"]) ?></option><?php endwhile; ?></select></div>
<div class="mb-3"><label class="form-label">Quantity</label><input type="number" min="1" class="form-control" name="quantity" required></div>
<div class="mb-3"><label class="form-label">Customer (optional)</label><select class="form-select" name="customer_id"><option value="0">Walk-in customer</option><?php while($c=$customers->fetch_assoc()): ?><option value="<?= (int)$c["id"] ?>"><?= e($c["fullname"]) ?></option><?php endwhile; ?></select></div>
<div class="mb-3"><label class="form-label">Payment method</label><select class="form-select" id="paymentMethod" name="payment_method"><option value="cash">Cash</option><option value="mobile_money">Mobile Money (record manually)</option><option value="card">Card (record manually)</option><option value="paystack">Paystack (Mobile Money / Card)</option></select></div><div class="mb-3 d-none" id="paystackEmailWrap"><label class="form-label">Payer email for Paystack</label><input type="email" class="form-control" name="payment_email" id="paymentEmail"><div class="form-text">Paystack will securely collect the payment details on its hosted checkout.</div></div><button class="btn btn-primary w-100" id="saleSubmit">Complete sale</button></form><script>const pm=document.getElementById("paymentMethod"),form=document.getElementById("saleForm"),wrap=document.getElementById("paystackEmailWrap"),em=document.getElementById("paymentEmail"),btn=document.getElementById("saleSubmit");function updatePaystack(){const on=pm.value==="paystack";wrap.classList.toggle("d-none",!on);em.required=on;form.action=on?"../paystack_initialize.php":"index.php";btn.textContent=on?"Continue to Paystack":"Complete sale";}pm.addEventListener("change",updatePaystack);updatePaystack();</script></div></div></div>
<div class="col-lg-8"><div class="card"><div class="card-header">Recent sales</div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Receipt</th><th>Date</th><th>Customer</th><th>Staff</th><th>Total</th></tr></thead><tbody><?php if(!$history->num_rows): ?><tr><td colspan="5">No sales recorded.</td></tr><?php endif; ?><?php while($s=$history->fetch_assoc()): ?><tr><td>#<?= (int)$s["id"] ?></td><td><?= e($s["sale_date"]) ?></td><td><?= e($s["fullname"]??"Walk-in") ?></td><td><?= e($s["staff"]) ?></td><td><?= money($s["total_amount"]) ?></td></tr><?php endwhile; ?></tbody></table></div></div></div></div>
<?php require "../includes/footer.php"; ?>
