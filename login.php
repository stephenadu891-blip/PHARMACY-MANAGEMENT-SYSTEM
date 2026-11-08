<?php
require_once __DIR__ . "/config/app.php";

if (!empty($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter both your username and password.";
    } else {
        try {
            $stmt = $conn->prepare(
                "SELECT id, fullname, username, password, role
                 FROM users
                 WHERE username = ?
                 LIMIT 1"
            );
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($id, $fullname, $dbUsername, $hashedPassword, $role);

            if ($stmt->fetch() && password_verify($password, $hashedPassword)) {
                $stmt->close();
                session_regenerate_id(true);

                $_SESSION["user_id"] = (int)$id;
                $_SESSION["fullname"] = $fullname;
                $_SESSION["username"] = $dbUsername;
                $_SESSION["role"] = $role;

                header("Location: dashboard.php");
                exit;
            }
            $stmt->close();
            $error = "Incorrect username or password. Check your details or contact the system administrator.";
        } catch (mysqli_sql_exception $ex) {
            error_log("Login error: " . $ex->getMessage());
            $error = "Unable to sign in right now. Please check the database connection.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Sign In | PharmacyCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body.login-bg{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,#397fbd,#d9edff)}
        .login-card{width:100%;max-width:430px;border:0;border-radius:14px;overflow:hidden;box-shadow:0 12px 35px rgba(0,35,80,.16)}
        .login-brand{color:#0756a5;font-size:27px;font-weight:700}
        .login-card .card-header{background:#fff;border-bottom:1px solid #e8eef5}
        .login-card .form-control{min-height:44px;background:#f5f9ff}
        .login-card .btn-primary{background:#0756a5;border-color:#0756a5;min-height:43px}
        .login-links a{color:#0756a5;text-decoration:none}
    </style>
</head>
<body class="login-bg">
<div class="card login-card">
    <div class="card-header text-center py-4">
        <div class="login-brand">✚ PharmacyCare</div>
        <div class="text-muted">Staff sign in</div>
    </div>
    <div class="card-body p-4">
        <?php if ($error !== ""): ?>
            <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" autocomplete="on">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" class="form-control" name="username"
                       value="<?= e($username) ?>" autocomplete="username"
                       required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" class="form-control" type="password"
                       name="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign in</button>
        </form>

        <div class="text-center mt-3 login-links">
            New staff member? <a href="signup.php">Create an account</a>
        </div>
    </div>
</div>
</body>
</html>
