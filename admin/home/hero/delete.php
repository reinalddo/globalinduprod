<?php
require_once __DIR__ . '/../../includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../hero');
    exit;
}

try {
    $stmt = $db->prepare('DELETE FROM home_hero_slides WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        throw new RuntimeException('No se pudo preparar la eliminación.');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
} catch (Throwable $exception) {
    // Opcional: registrar el error con error_log
}

header('Location: ../hero');
exit;
