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
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "auth.php";
$user_id = requireAuth();

require_once "db.php";

/* =========================
   GET
========================= */
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (isset($_GET["id"])) {
        $id = intval($_GET["id"]);

        $stmt = $conn->prepare("
            SELECT i.id, i.nome, i.descricao, u.nome AS autor
            FROM itens i
            LEFT JOIN usuarios u ON u.user_id = i.user_id
            WHERE i.id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        echo json_encode([
            "data" => $result->fetch_assoc() ?: null,
            "error" => null
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT i.id, i.nome, i.descricao, u.nome AS autor
        FROM itens i
        LEFT JOIN usuarios u ON u.user_id = i.user_id
    ");

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


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    $nome = trim($data["nome"] ?? "");
    $descricao = trim($data["descricao"] ?? "");

    if (!$nome || !$descricao) {
        http_response_code(400);
        echo json_encode(["error" => "Campos obrigatórios"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO itens (nome, descricao, user_id)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("sss", $nome, $descricao, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["ok" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Erro ao criar item"]);
    }

    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "PUT") {

    $data = json_decode(file_get_contents("php://input"), true);

    $id = intval($data["id"] ?? 0);
    $nome = trim($data["nome"] ?? "");
    $descricao = trim($data["descricao"] ?? "");

    if (!$id || !$nome || !$descricao) {
        http_response_code(400);
        echo json_encode(["error" => "Dados inválidos"]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE itens 
        SET nome = ?, descricao = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ssi", $nome, $descricao, $id);

    $stmt->execute();

    echo json_encode(["ok" => true]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {

    $data = json_decode(file_get_contents("php://input"), true);

    $id = intval($data["id"] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID inválido"]);
        exit;
    }

    $stmt = $conn->prepare("
        DELETE FROM itens WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    $stmt->execute();

    echo json_encode(["ok" => true]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Método não permitido"]);