<?php

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
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
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

$maxSize = 1 * 1024 * 1024;
$contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;

if ($contentLength > $maxSize) {
    http_response_code(413);
    echo json_encode([
        "data" => null,
        "error" => "Payload muito grande"
    ]);
    exit;
}

require_once "auth.php";
$user_id = requireAuth();

$ip = $_SERVER["REMOTE_ADDR"];
$method = $_SERVER["REQUEST_METHOD"];

$limite = ($method === "GET") ? 20 : 5;

$key = "rate_" . $ip . "_" . $user_id . "_" . $method;

if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = [
        "count" => 0,
        "start" => microtime(true)
    ];
}

$controle = &$_SESSION[$key];
$agora = microtime(true);

if (($agora - $controle["start"]) >= 1) {
    $controle["count"] = 0;
    $controle["start"] = $agora;
}

$controle["count"]++;

if ($controle["count"] > $limite) {
    http_response_code(429);
    echo json_encode([
        "data" => null,
        "error" => "Muitas requisições",
        "retry_after" => 1
    ]);
    exit;
}

require_once "db.php";

if ($method === "GET") {

    if (isset($_GET["id"])) {
        $id = intval($_GET["id"]);

        $stmt = $conn->prepare("
            SELECT id, nome, descricao
            FROM itens
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param("is", $id, $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $item = $result->fetch_assoc();

        echo json_encode([
            "data" => $item ?: null,
            "error" => null
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT id, nome, descricao
        FROM itens
        WHERE user_id = ?
    ");

    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $itens = [];
    while ($row = $result->fetch_assoc()) {
        $itens[] = $row;
    }

    echo json_encode([
        "data" => $itens,
        "error" => null
    ]);
    exit;
}

if ($method === "POST") {

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "data" => null,
            "error" => "JSON inválido"
        ]);
        exit;
    }

    $nome = trim($data["nome"] ?? "");
    $descricao = trim($data["descricao"] ?? "");

    if (!$nome || !$descricao) {
        http_response_code(400);
        echo json_encode([
            "data" => null,
            "error" => "Campos obrigatórios"
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO itens (nome, descricao, user_id)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("sss", $nome, $descricao, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            "data" => ["ok" => true],
            "error" => null
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "data" => null,
            "error" => "Erro ao criar item"
        ]);
    }

    exit;
}

if ($method === "PUT") {

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "data" => null,
            "error" => "JSON inválido"
        ]);
        exit;
    }

    $id = intval($data["id"] ?? 0);
    $nome = trim($data["nome"] ?? "");
    $descricao = trim($data["descricao"] ?? "");

    if (!$id || !$nome || !$descricao) {
        http_response_code(400);
        echo json_encode([
            "data" => null,
            "error" => "Dados inválidos"
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE itens 
        SET nome = ?, descricao = ?
        WHERE id = ? AND user_id = ?
    ");

    $stmt->bind_param("ssis", $nome, $descricao, $id, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            "data" => ["ok" => true],
            "error" => null
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "data" => null,
            "error" => "Erro ao atualizar"
        ]);
    }

    exit;
}

if ($method === "DELETE") {

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "data" => null,
            "error" => "JSON inválido"
        ]);
        exit;
    }

    $id = intval($data["id"] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode([
            "data" => null,
            "error" => "ID inválido"
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        DELETE FROM itens 
        WHERE id = ? AND user_id = ?
    ");

    $stmt->bind_param("is", $id, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            "data" => ["ok" => true],
            "error" => null
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "data" => null,
            "error" => "Erro ao deletar"
        ]);
    }

    exit;
}

http_response_code(405);
echo json_encode([
    "data" => null,
    "error" => "Método não permitido"
]);