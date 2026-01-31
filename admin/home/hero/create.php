<?php
require_once __DIR__ . '/../../includes/auth.php';

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
            $formData['image_path'] = saveOptimizedHeroImage($uploadedImage);
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

function saveOptimizedHeroImage(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo procesar la imagen enviada.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('La carga de la imagen no es válida.');
    }

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!$mime || !isset($allowedMimes[$mime])) {
        throw new RuntimeException('El archivo seleccionado debe ser una imagen JPG, PNG o WebP.');
    }

    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $image = imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) {
                throw new RuntimeException('El servidor no soporta imágenes WebP.');
            }
            $image = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            $image = false;
    }

    if (!$image) {
        throw new RuntimeException('No se pudo leer la imagen enviada.');
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $maxWidth = 1600;
    $maxHeight = 900;

    $targetWidth = $width;
    $targetHeight = $height;

    if ($targetWidth > $maxWidth) {
        $targetHeight = (int) ceil($targetHeight * ($maxWidth / $targetWidth));
        $targetWidth = $maxWidth;
    }

    if ($targetHeight > $maxHeight) {
        $targetWidth = (int) ceil($targetWidth * ($maxHeight / $targetHeight));
        $targetHeight = $maxHeight;
    }

    if ($targetWidth !== $width || $targetHeight !== $height) {
        $optimized = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($optimized, false);
            imagesavealpha($optimized, true);
        }
        imagecopyresampled($optimized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);
        $image = $optimized;
    }

    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    $projectRoot = dirname(__DIR__, 3);
    $relativeDir = 'uploads/home/hero';
    $absoluteDir = $projectRoot . '/' . $relativeDir;

    if (!is_dir($absoluteDir)) {
        if (!mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
        }
    }

    try {
        $filename = 'hero-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
    } catch (Throwable $exception) {
        imagedestroy($image);
        throw new RuntimeException('No se pudo generar el nombre del archivo.');
    }

    $targetPath = $absoluteDir . '/' . $filename;

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($image, $targetPath, 82);
            break;
        case 'image/png':
            imagepng($image, $targetPath, 7);
            break;
        case 'image/webp':
            if (!function_exists('imagewebp')) {
                imagedestroy($image);
                throw new RuntimeException('El servidor no soporta optimizar imágenes WebP.');
            }
            imagewebp($image, $targetPath, 80);
            break;
    }

    imagedestroy($image);

    if (!file_exists($targetPath)) {
        throw new RuntimeException('No se pudo guardar la imagen optimizada.');
    }

    return $relativeDir . '/' . $filename;
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
