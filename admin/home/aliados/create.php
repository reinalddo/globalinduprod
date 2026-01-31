<?php
$pageTitle = 'Nuevo aliado | Inicio';
$pageHeader = 'Agregar aliado estratégico';
$activeNav = 'home';
require_once __DIR__ . '/../../includes/auth.php';

if (!function_exists('saveOptimizedAllyLogo')) {
    function saveOptimizedAllyLogo(array $file): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo procesar el logo subido.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('La carga del logo no es válida.');
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
            throw new RuntimeException('El logo debe ser una imagen JPG, PNG o WebP.');
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
            throw new RuntimeException('No se pudo leer el logo enviado.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 600;
        $maxHeight = 400;

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
        $relativeDir = 'uploads/home/aliados';
        $absoluteDir = $projectRoot . '/' . $relativeDir;

        if (!is_dir($absoluteDir)) {
            if (!mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
                throw new RuntimeException('No se pudo preparar la carpeta para logos.');
            }
        }

        try {
            $filename = 'ally-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
        } catch (Throwable $exception) {
            imagedestroy($image);
            throw new RuntimeException('No se pudo generar el nombre del logo.');
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
                    throw new RuntimeException('El servidor no soporta optimizar logos WebP.');
                }
                imagewebp($image, $targetPath, 80);
                break;
        }

        imagedestroy($image);

        if (!file_exists($targetPath)) {
            throw new RuntimeException('No se pudo guardar el logo optimizado.');
        }

        return $relativeDir . '/' . $filename;
    }
}

$errors = [];
$formData = [
    'name' => '',
    'logo_path' => '',
    'is_primary' => 0,
    'sort_order' => 0
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['is_primary'] = isset($_POST['is_primary']) ? 1 : 0;
    $formData['sort_order'] = (int) ($_POST['sort_order'] ?? 0);

    $uploadedLogo = $_FILES['logo_file'] ?? null;

    if ($formData['name'] === '') {
        $errors[] = 'El nombre del aliado es obligatorio.';
    }
    if (!$uploadedLogo || $uploadedLogo['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Debes seleccionar un logo en formato de imagen.';
    }
    if ($uploadedLogo && $uploadedLogo['error'] !== UPLOAD_ERR_OK && $uploadedLogo['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'No se pudo cargar el logo. Intenta nuevamente.';
    }

    if (empty($errors)) {
        try {
            $formData['logo_path'] = saveOptimizedAllyLogo($uploadedLogo);
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare('INSERT INTO home_allies (name, logo_path, is_primary, sort_order) VALUES (?, ?, ?, ?)');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar la inserción.');
            }
            $stmt->bind_param('ssii', $formData['name'], $formData['logo_path'], $formData['is_primary'], $formData['sort_order']);
            $stmt->execute();
            $stmt->close();
            header('Location: ' . adminUrl('home/aliados'));
            exit;
        } catch (Throwable $exception) {
            if ($formData['logo_path'] !== '') {
                $storedPath = dirname(__DIR__, 3) . '/' . $formData['logo_path'];
                if (is_file($storedPath)) {
                    @unlink($storedPath);
                }
                $formData['logo_path'] = '';
            }
            $errors[] = 'No se pudo guardar el aliado.';
        }
    }
}


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
        <label for="name">Nombre comercial</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <label for="logo_file">Logo</label>
        <input type="file" name="logo_file" id="logo_file" accept="image/*" required>

        <label for="sort_order">Orden</label>
        <input type="number" name="sort_order" id="sort_order" value="<?php echo (int) $formData['sort_order']; ?>" min="0">

        <label style="display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="is_primary" value="1" <?php echo $formData['is_primary'] ? 'checked' : ''; ?>>
            <span>Marcar como logo principal (máximo 4)</span>
        </label>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn btn-outline" href="<?php echo adminUrl('home/aliados'); ?>">Cancelar</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/page-bottom.php'; ?>
