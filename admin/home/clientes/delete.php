<?php
require_once __DIR__ . '/../../includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . adminUrl('home/clientes'));
    exit;
}

try {
    $stmt = $db->prepare('DELETE FROM home_clients WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        throw new RuntimeException('No se pudo preparar la eliminación.');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
} catch (Throwable $exception) {
    // Registrar si es necesario
}

header('Location: ' . adminUrl('home/clientes'));
exit;
