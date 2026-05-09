<?php

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["ok" => false]);
    exit;
}

require_once "db.php";

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT id, nome, email, user_id FROM usuarios WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

echo json_encode([
    "ok" => true,
    "user" => $user
]);