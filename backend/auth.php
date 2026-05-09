<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

define("SESSION_TIMEOUT", 3600);

function requireAuth() {

    if (!isset($_SESSION["user_id"])) {
        http_response_code(401);
        echo json_encode([
            "data" => null,
            "error" => "Não autenticado"
        ]);
        exit;
    }

    if (!isset($_SESSION["last_activity"])) {
        $_SESSION["last_activity"] = time();
    }

    if (time() - $_SESSION["last_activity"] > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();

        http_response_code(401);
        echo json_encode([
            "data" => null,
            "error" => "Sessão expirada"
        ]);
        exit;
    }

    $_SESSION["last_activity"] = time();

    return $_SESSION["user_id"];
}