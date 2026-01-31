<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ../hero');
    exit;
}

$pageTitle = 'Editar diapositiva | Inicio';
$pageHeader = 'Editar diapositiva';
$activeNav = 'home';
require_once __DIR__ . '/../../includes/page-top.php';

$errors = [];
$formData = null;

try {
    $stmt = $db->prepare('SELECT id, image_path, message_small, title, description, cta_label, cta_url, sort_order, is_active FROM home_hero_slides WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        throw new RuntimeException('No se pudo preparar la consulta.');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($rowId, $imagePath, $messageSmall, $title, $description, $ctaLabel, $ctaUrl, $sortOrder, $isActive);
        $stmt->fetch();
        $formData = [
            'id' => $rowId,
            'image_path' => $imagePath,
            'message_small' => $messageSmall,
            'title' => $title,
            'description' => $description,
            'cta_label' => $ctaLabel,
            'cta_url' => $ctaUrl,
            'sort_order' => $sortOrder,
            'is_active' => $isActive
        ];
    }
    $stmt->close();
} catch (Throwable $exception) {
    $formData = null;
}

if (!$formData) {
    echo '<div class="empty-state">La diapositiva solicitada no existe.</div>';
    echo '<div style="margin-top:18px;"><a class="btn btn-outline" href="../hero">Volver</a></div>';
    require_once __DIR__ . '/../../includes/page-bottom.php';
    exit;
}

$formData['is_active'] = (int) $formData['is_active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['image_path'] = trim($_POST['image_path'] ?? '');
    $formData['message_small'] = trim($_POST['message_small'] ?? '');
    $formData['title'] = trim($_POST['title'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['cta_label'] = trim($_POST['cta_label'] ?? '');
    $formData['cta_url'] = trim($_POST['cta_url'] ?? '');
    $formData['sort_order'] = (int) ($_POST['sort_order'] ?? 0);
    $formData['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    if ($formData['image_path'] === '') {
        $errors[] = 'La ruta de la imagen es obligatoria.';
    }
    if ($formData['title'] === '') {
        $errors[] = 'El título es obligatorio.';
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare('UPDATE home_hero_slides SET image_path = ?, message_small = ?, title = ?, description = ?, cta_label = ?, cta_url = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar la actualización.');
            }
            $stmt->bind_param(
                'ssssssiii',
                $formData['image_path'],
                $formData['message_small'],
                $formData['title'],
                $formData['description'],
                $formData['cta_label'],
                $formData['cta_url'],
                $formData['sort_order'],
                $formData['is_active'],
                $id
            );
            $stmt->execute();
            $stmt->close();
            header('Location: ../hero');
            exit;
        } catch (Throwable $exception) {
            $errors[] = 'No se pudo actualizar la diapositiva.';
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
        <label for="image_path">Imagen (ruta o URL)</label>
        <input type="text" name="image_path" id="image_path" value="<?php echo htmlspecialchars($formData['image_path'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="message_small">Mensaje pequeño</label>
        <input type="text" name="message_small" id="message_small" value="<?php echo htmlspecialchars($formData['message_small'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="title">Título principal</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($formData['title'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="description">Texto explicativo</label>
        <textarea name="description" id="description"><?php echo htmlspecialchars($formData['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="cta_label">Texto del botón (opcional)</label>
        <input type="text" name="cta_label" id="cta_label" value="<?php echo htmlspecialchars($formData['cta_label'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="cta_url">URL del botón (opcional)</label>
        <input type="url" name="cta_url" id="cta_url" value="<?php echo htmlspecialchars($formData['cta_url'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="sort_order">Orden</label>
        <input type="number" name="sort_order" id="sort_order" value="<?php echo (int) $formData['sort_order']; ?>" min="0">

        <label style="display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="is_active" value="1" <?php echo $formData['is_active'] ? 'checked' : ''; ?>>
            <span>Mostrar en el sitio</span>
        </label>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Actualizar</button>
            <a class="btn btn-outline" href="../hero">Cancelar</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/page-bottom.php'; ?>
