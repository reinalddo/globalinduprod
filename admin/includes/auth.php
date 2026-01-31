<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: login');
    exit;
}

try {
    $db = getAdminDb();
} catch (Throwable $exception) {
    http_response_code(500);
    echo 'Error de conexión con la base de datos del panel.';
    exit;
}
