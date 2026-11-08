<?php
require_once "config/app.php"; require_login();
$page_title="Reports";
$daily=$conn->query("SELECT COUNT(*) n,COALESCE(SUM(total_amount),0) total FROM sales WHERE DATE(sale_date)=CURDATE()")->fetch_assoc();
$monthly=$conn->query("SELECT COUNT(*) n,COALESCE(SUM(total_amount),0) total FROM sales WHERE YEAR(sale_date)=YEAR(CURDATE()) AND MONTH(sale_date)=MONTH(CURDATE())")->fetch_assoc();
$low=$conn->query("SELECT medicine_name,quantity,reorder_level FROM medicines WHERE quantity<=reorder_level ORDER BY quantity");
$expired=$conn->query("SELECT medicine_name,expiry_date FROM medicines WHERE expiry_date<CURDATE() ORDER BY expiry_date");
require "includes/header.php";
?>
<h2 class="mb-3">Reports</h2><div class="row g-3 mb-4">
<div class="col-md-6"><div class="card"><div class="card-header">Today's sales</div><div class="card-body"><div class="stat-number"><?= money($daily["total"]) ?></div><?= (int)$daily["n"] ?> transactions</div></div></div>
<div class="col-md-6"><div class="card"><div class="card-header">This month's sales</div><div class="card-body"><div class="stat-number"><?= money($monthly["total"]) ?></div><?= (int)$monthly["n"] ?> transactions</div></div></div></div>
<div class="row g-3"><div class="col-lg-6"><div class="card"><div class="card-header">Low stock report</div><div class="card-body table-responsive"><table class="table"><tr><th>Medicine</th><th>Stock</th><th>Reorder level</th></tr><?php while($r=$low->fetch_assoc()): ?><tr><td><?= e($r["medicine_name"]) ?></td><td><?= (int)$r["quantity"] ?></td><td><?= (int)$r["reorder_level"] ?></td></tr><?php endwhile; ?></table></div></div></div>
<div class="col-lg-6"><div class="card"><div class="card-header">Expired medicines — do not dispense</div><div class="card-body table-responsive"><table class="table"><tr><th>Medicine</th><th>Expiry</th></tr><?php while($r=$expired->fetch_assoc()): ?><tr><td><?= e($r["medicine_name"]) ?></td><td><?= e($r["expiry_date"]) ?></td></tr><?php endwhile; ?></table></div></div></div></div>
<?php require "includes/footer.php"; ?>
