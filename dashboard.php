<?php
require_once "config/app.php"; require_login();
$page_title="Dashboard";
$meds=(int)$conn->query("SELECT COUNT(*) n FROM medicines")->fetch_assoc()["n"];
$customers=(int)$conn->query("SELECT COUNT(*) n FROM customers")->fetch_assoc()["n"];
$sales=(int)$conn->query("SELECT COUNT(*) n FROM sales")->fetch_assoc()["n"];
$revenue=(float)$conn->query("SELECT COALESCE(SUM(total_amount),0) n FROM sales")->fetch_assoc()["n"];
$low=$conn->query("SELECT medicine_name,quantity,reorder_level FROM medicines WHERE quantity<=reorder_level ORDER BY quantity ASC LIMIT 8");
$expiry=$conn->query("SELECT medicine_name,expiry_date FROM medicines WHERE expiry_date IS NOT NULL AND expiry_date<=DATE_ADD(CURDATE(),INTERVAL 90 DAY) ORDER BY expiry_date LIMIT 8");
require "includes/header.php";
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><h2 class="fw-bold mb-1">Dashboard</h2><div class="text-muted">Pharmacy operations overview</div></div><span class="badge badge-soft p-2">Welcome, <?= e($_SESSION["fullname"]) ?></span></div>
<div class="row g-3 mb-4">
<?php foreach([["Medicines",$meds,"bi-capsule"],["Customers",$customers,"bi-people"],["Sales",$sales,"bi-receipt"],["Total revenue",money($revenue),"bi-cash"]] as $s): ?>
<div class="col-12 col-sm-6 col-xl-3"><div class="card stat-card h-100"><div class="card-body"><div class="text-muted"><?= e($s[0]) ?></div><div class="stat-number"><?= e($s[1]) ?></div></div></div></div>
<?php endforeach; ?>
</div>
<div class="row g-3">
<div class="col-lg-6"><div class="card"><div class="card-header">Low-stock medicines</div><div class="card-body table-responsive"><table class="table align-middle"><thead><tr><th>Medicine</th><th>Quantity</th><th>Reorder level</th></tr></thead><tbody>
<?php if(!$low->num_rows): ?><tr><td colspan="3" class="text-muted">No low-stock medicines.</td></tr><?php endif; ?>
<?php while($r=$low->fetch_assoc()): ?><tr><td><?= e($r["medicine_name"]) ?></td><td><span class="badge text-bg-danger"><?= (int)$r["quantity"] ?></span></td><td><?= (int)$r["reorder_level"] ?></td></tr><?php endwhile; ?>
</tbody></table></div></div></div>
<div class="col-lg-6"><div class="card"><div class="card-header">Expiring within 90 days</div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Medicine</th><th>Expiry date</th></tr></thead><tbody>
<?php if(!$expiry->num_rows): ?><tr><td colspan="2" class="text-muted">No medicines approaching expiry.</td></tr><?php endif; ?>
<?php while($r=$expiry->fetch_assoc()): ?><tr><td><?= e($r["medicine_name"]) ?></td><td><?= e($r["expiry_date"]) ?></td></tr><?php endwhile; ?>
</tbody></table></div></div></div></div>
<?php require "includes/footer.php"; ?>
