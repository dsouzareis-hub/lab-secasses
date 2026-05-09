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

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido"]);
    exit;
}

$nome = trim($data["nome"] ?? "");
$email = trim($data["email"] ?? "");
$senha = $data["senha"] ?? "";

if (!$nome || !$email || !$senha) {
    http_response_code(400);
    echo json_encode(["error" => "Campos obrigatórios"]);
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["error" => "Email já existe"]);
    exit;
}

$user_id = bin2hex(random_bytes(16));
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO usuarios (user_id, nome, email, senha, data)
    VALUES (?, ?, ?, ?, NOW())
");

$stmt->bind_param("ssss", $user_id, $nome, $email, $senhaHash);

if ($stmt->execute()) {
    echo json_encode([
        "ok" => true,
        "user_id" => $user_id
    ]);
    exit;
}

http_response_code(500);
echo json_encode([
    "error" => "Erro ao cadastrar"
]);