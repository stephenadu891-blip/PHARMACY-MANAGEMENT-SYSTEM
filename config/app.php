<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/database.php";

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
function require_login(): void {
    if (empty($_SESSION["user_id"])) {
        header("Location: " . url("login.php"));
        exit;
    }
}
function url(string $path = ""): string {
    return "/pharmacy_management/" . ltrim($path, "/");
}
function flash(string $type, string $message): void {
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}
function show_flash(): void {
    if (!empty($_SESSION["flash"])) {
        $f = $_SESSION["flash"];
        echo '<div class="alert alert-' . e($f["type"]) . ' alert-dismissible fade show" role="alert">'
            . e($f["message"]) .
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION["flash"]);
    }
}
function csrf_token(): string {
    if (empty($_SESSION["csrf"])) $_SESSION["csrf"] = bin2hex(random_bytes(32));
    return $_SESSION["csrf"];
}
function verify_csrf(): void {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $token = $_POST["csrf"] ?? "";
        if (!hash_equals($_SESSION["csrf"] ?? "", $token)) {
            http_response_code(419);
            exit("Invalid form token. Please go back and try again.");
        }
    }
}
function money($amount): string {
    return "GHS " . number_format((float)$amount, 2);
}
