<?php
require_once __DIR__ . "/config/app.php";

if (!empty($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";
$fullname = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $fullname = trim($_POST["fullname"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($fullname === "" || $username === "" || $password === "" || $confirmPassword === "") {
        $error = "Please complete all fields.";
    } elseif (mb_strlen($fullname) > 120 || mb_strlen($username) > 60) {
        $error = "Your name or username is too long.";
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $username)) {
        $error = "Username must be 3–60 characters and may contain letters, numbers, dots, underscores or hyphens.";
    } elseif (strlen($password) < 8) {
        $error = "Choose a password with at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $error = "The passwords do not match.";
    } else {
        try {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "That username is already in use. Please choose another.";
                $check->close();
            } else {
                $check->close();
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                // Public registration is restricted to the least-privileged staff role.
                $role = "attendant";

                $stmt = $conn->prepare(
                    "INSERT INTO users (fullname, username, password, role)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param("ssss", $fullname, $username, $hashedPassword, $role);
                $stmt->execute();
                $stmt->close();

                $success = "Your staff account has been created. You can now sign in.";
                $fullname = "";
                $username = "";
            }
        } catch (mysqli_sql_exception $ex) {
            error_log("Signup error: " . $ex->getMessage());
            $error = "Account creation failed. Please check the database connection and try again.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Sign Up | PharmacyCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body.login-bg{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,#397fbd,#d9edff)}
        .login-card{width:100%;max-width:470px;border:0;border-radius:14px;overflow:hidden;box-shadow:0 12px 35px rgba(0,35,80,.16)}
        .login-brand{color:#0756a5;font-size:27px;font-weight:700}
        .login-card .card-header{background:#fff;border-bottom:1px solid #e8eef5}
        .login-card .form-control{min-height:43px;background:#f5f9ff}
        .login-card .btn-primary{background:#0756a5;border-color:#0756a5;min-height:43px}
        .login-links a{color:#0756a5;text-decoration:none}
    </style>
</head>
<body class="login-bg">
<div class="card login-card">
    <div class="card-header text-center py-4">
        <div class="login-brand">✚ PharmacyCare</div>
        <div class="text-muted">Create a staff account</div>
    </div>
    <div class="card-body p-4">
        <?php if ($error !== ""): ?>
            <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success !== ""): ?>
            <div class="alert alert-success" role="alert">
                <?= e($success) ?> <a href="login.php" class="alert-link">Sign in here</a>.
            </div>
        <?php endif; ?>

        <form method="post" action="signup.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="mb-3">
                <label for="fullname" class="form-label">Full name</label>
                <input id="fullname" class="form-control" name="fullname"
                       value="<?= e($fullname) ?>" maxlength="120" autocomplete="name" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Choose username</label>
                <input id="username" class="form-control" name="username"
                       value="<?= e($username) ?>" minlength="3" maxlength="60"
                       autocomplete="username" required>
                <div class="form-text">3–60 characters: letters, numbers, dot, underscore or hyphen.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" class="form-control" type="password" name="password"
                       minlength="8" autocomplete="new-password" required>
                <div class="form-text">Use at least 8 characters.</div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm password</label>
                <input id="confirm_password" class="form-control" type="password"
                       name="confirm_password" minlength="8" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Create account</button>
        </form>

        <div class="text-center mt-3 login-links">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
        <p class="small text-muted text-center mt-3 mb-0">
            New registrations receive the attendant role. Only an administrator should assign elevated access.
        </p>
    </div>
</div>
</body>
</html>
