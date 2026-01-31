<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ../aliados');
    exit;
}

$pageTitle = 'Editar aliado | Inicio';
$pageHeader = 'Editar aliado estratégico';
$activeNav = 'home';
require_once __DIR__ . '/../../includes/page-top.php';

$errors = [];
$formData = null;

try {
    $stmt = $db->prepare('SELECT id, name, logo_path, is_primary, sort_order FROM home_allies WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        throw new RuntimeException('No se pudo preparar la consulta.');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($rowId, $name, $logoPath, $isPrimary, $sortOrder);
        $stmt->fetch();
        $formData = [
            'id' => $rowId,
            'name' => $name,
            'logo_path' => $logoPath,
            'is_primary' => (int) $isPrimary,
            'sort_order' => (int) $sortOrder
        ];
    }
    $stmt->close();
} catch (Throwable $exception) {
    $formData = null;
}

if (!$formData) {
    echo '<div class="empty-state">El aliado solicitado no existe.</div>';
    echo '<div style="margin-top:18px;"><a class="btn btn-outline" href="../aliados">Volver</a></div>';
    require_once __DIR__ . '/../../includes/page-bottom.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['logo_path'] = trim($_POST['logo_path'] ?? '');
    $formData['is_primary'] = isset($_POST['is_primary']) ? 1 : 0;
    $formData['sort_order'] = (int) ($_POST['sort_order'] ?? 0);

    if ($formData['name'] === '') {
        $errors[] = 'El nombre del aliado es obligatorio.';
    }
    if ($formData['logo_path'] === '') {
        $errors[] = 'La ruta o URL del logo es obligatoria.';
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare('UPDATE home_allies SET name = ?, logo_path = ?, is_primary = ?, sort_order = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar la actualización.');
            }
            $stmt->bind_param('ssiii', $formData['name'], $formData['logo_path'], $formData['is_primary'], $formData['sort_order'], $id);
            $stmt->execute();
            $stmt->close();
            header('Location: ../aliados');
            exit;
        } catch (Throwable $exception) {
            $errors[] = 'No se pudo actualizar el aliado.';
        }
    }
}
?>
<section>
    <?php if ($errors): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>
    <form method="post">
        <label for="name">Nombre comercial</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="logo_path">Logo (ruta o URL)</label>
        <input type="text" name="logo_path" id="logo_path" value="<?php echo htmlspecialchars($formData['logo_path'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="sort_order">Orden</label>
        <input type="number" name="sort_order" id="sort_order" value="<?php echo (int) $formData['sort_order']; ?>" min="0">

        <label style="display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="is_primary" value="1" <?php echo $formData['is_primary'] ? 'checked' : ''; ?>>
            <span>Marcar como logo principal (máximo 4)</span>
        </label>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Actualizar</button>
            <a class="btn btn-outline" href="../aliados">Cancelar</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/page-bottom.php'; ?>
