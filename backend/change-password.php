<?php

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Não autenticado"]);
    exit;
}

$user_id = $_SESSION["user_id"];

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido"]);
    exit;
}

$senhaAtual = trim($data["senhaAtual"] ?? "");
$novaSenha = trim($data["novaSenha"] ?? "");

if (!$senhaAtual || !$novaSenha) {
    http_response_code(400);
    echo json_encode(["error" => "Campos obrigatórios"]);
    exit;
}

if (strlen($novaSenha) < 6) {
    http_response_code(400);
    echo json_encode(["error" => "Senha muito curta"]);
    exit;
}

$stmt = $conn->prepare("SELECT senha FROM usuarios WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($senhaAtual, $user["senha"])) {
    http_response_code(401);
    echo json_encode(["error" => "Senha atual incorreta"]);
    exit;
}

$hash = password_hash($novaSenha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE user_id = ?");
$stmt->bind_param("ss", $hash, $user_id);
$stmt->execute();

session_unset();
session_destroy();

setcookie(session_name(), '', time() - 3600, '/');

echo json_encode([
    "ok" => true,
    "force_logout" => true
]);