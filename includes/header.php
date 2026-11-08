<?php require_once __DIR__ . "/../config/app.php"; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? "Pharmacy Management System") ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark topbar">
 <div class="container-fluid">
  <a class="navbar-brand fw-bold" href="<?= url('dashboard.php') ?>">✚ PharmacyCare</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse" id="mainNav">
   <?php if (!empty($_SESSION["user_id"])): ?>
   <ul class="navbar-nav me-auto">
    <li class="nav-item"><a class="nav-link" href="<?= url('dashboard.php') ?>">Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('medicines/index.php') ?>">Medicines</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('customers/index.php') ?>">Customers</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('sales/index.php') ?>">Sales</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('prescriptions/index.php') ?>">Prescriptions</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('reports.php') ?>">Reports</a></li>
   </ul>
   <span class="navbar-text me-3"><?= e($_SESSION["fullname"] ?? "") ?></span>
   <a class="btn btn-light btn-sm" href="<?= url('logout.php') ?>">Logout</a>
   <?php endif; ?>
  </div>
 </div>
</nav>
<main class="container-fluid page-wrap">
<?php show_flash(); ?>
