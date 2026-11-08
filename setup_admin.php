<?php
require_once "config/database.php";
$username = "admin";
$fullname = "System Administrator";
$password = password_hash("ChangeMe123!", PASSWORD_DEFAULT);
$role = "admin";

$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows) {
    exit("Admin already exists. Delete this setup file.");
}
$stmt = $conn->prepare("INSERT INTO users(fullname,username,password,role) VALUES(?,?,?,?)");
$stmt->bind_param("ssss", $fullname, $username, $password, $role);
$stmt->execute();
echo "Admin created. Username: admin | Temporary password: ChangeMe123!<br>Delete setup_admin.php now and change the password after login.";
