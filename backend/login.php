<?php

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
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

$MAX_TENTATIVAS = 3;
$BLOQUEIO_SEGUNDOS = 30;

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$senha = trim($data["senha"] ?? "");

if (!$email || !$senha) {
    http_response_code(400);
    echo json_encode(["error" => "Email e senha são obrigatórios"]);
    exit;
}

$ip = $_SERVER["REMOTE_ADDR"];
$key = "login_" . $ip;

if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = [
        "tentativas" => 0,
        "bloqueado_ate" => 0
    ];
}

$controle = &$_SESSION[$key];

if (time() < $controle["bloqueado_ate"]) {
    $restante = $controle["bloqueado_ate"] - time();

    http_response_code(429);
    echo json_encode([
        "error" => "Muitas tentativas. Aguarde.",
        "retry_after" => $restante
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($senha, $user["senha"])) {

    $controle["tentativas"]++;

    $restantes = $MAX_TENTATIVAS - $controle["tentativas"];

    if ($controle["tentativas"] >= $MAX_TENTATIVAS) {
        $controle["bloqueado_ate"] = time() + $BLOQUEIO_SEGUNDOS;
        $controle["tentativas"] = 0;

        http_response_code(429);
        echo json_encode([
            "error" => "Muitas tentativas. Aguarde.",
            "retry_after" => $BLOQUEIO_SEGUNDOS,
            "tentativas_restantes" => 0
        ]);
        exit;
    }

    http_response_code(401);
    echo json_encode([
        "error" => "Credenciais inválidas",
        "tentativas_restantes" => $restantes
    ]);
    exit;
}

unset($_SESSION[$key]);

session_regenerate_id(true);

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["last_activity"] = time();

echo json_encode([
    "ok" => true
]);