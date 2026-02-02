<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/helpers.php';

$errors = [];
$formData = [
    'image_path' => '',
    'message_small' => '',
    'title' => '',
    'description' => '',
    'cta_label' => '',
    'cta_url' => '',
    'sort_order' => 0,
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['message_small'] = trim($_POST['message_small'] ?? '');
    $formData['title'] = trim($_POST['title'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['cta_label'] = trim($_POST['cta_label'] ?? '');
    $formData['cta_url'] = trim($_POST['cta_url'] ?? '');
    $formData['sort_order'] = (int) ($_POST['sort_order'] ?? 0);
    $formData['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    $uploadedImage = $_FILES['image_file'] ?? null;

    if (!$uploadedImage || $uploadedImage['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Debes seleccionar una imagen.';
    }
    if ($uploadedImage && $uploadedImage['error'] !== UPLOAD_ERR_OK && $uploadedImage['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'No se pudo cargar la imagen. Intenta nuevamente.';
    }

    if ($formData['title'] === '') {
        $errors[] = 'El título es obligatorio.';
    }

    if (empty($errors)) {
        try {
            $formData['image_path'] = saveHeroSlideImage($uploadedImage);
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare('INSERT INTO home_hero_slides (image_path, message_small, title, description, cta_label, cta_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar la inserción.');
            }
            $stmt->bind_param(
                'ssssssii',
                $formData['image_path'],
                $formData['message_small'],
                $formData['title'],
                $formData['description'],
                $formData['cta_label'],
                $formData['cta_url'],
                $formData['sort_order'],
                $formData['is_active']
            );
            $stmt->execute();
            $stmt->close();
            header('Location: ' . adminUrl('home/hero'));
            exit;
        } catch (Throwable $exception) {
            $errors[] = 'No se pudo guardar la diapositiva. Intenta nuevamente.';
        }
    }
}

$pageTitle = 'Nueva diapositiva | Inicio';
$pageHeader = 'Crear diapositiva';
$activeNav = 'home';
require_once __DIR__ . '/../../includes/page-top.php';
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
    <form method="post" enctype="multipart/form-data">
        <label for="image_file">Imagen</label>
        <input type="file" name="image_file" id="image_file" accept="image/*" required>

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
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn btn-outline" href="<?php echo adminUrl('home/hero'); ?>">Cancelar</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/page-bottom.php'; ?>
