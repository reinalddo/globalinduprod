<?php
    require_once __DIR__ . '/../includes/auth.php';

    $pageTitle = 'Nosotros | Administración';
    $pageHeader = 'Contenido de la página Nosotros';
    $activeNav = 'about';

    $formErrors = [
        'hero' => [],
        'sections' => [],
        'presence' => [],
        'highlights' => []
    ];
    $successMessage = '';

    if (isset($_SESSION['about_flash'])) {
        $successMessage = $_SESSION['about_flash'];
        unset($_SESSION['about_flash']);
    }

    $sectionDefinitions = [
        'about_overview' => ['label' => 'Quiénes somos'],
        'about_history' => ['label' => 'Historia y evolución'],
        'about_presence' => ['label' => 'Presencia operativa y contacto'],
        'about_values' => ['label' => 'Valores que nos definen'],
        'about_services' => ['label' => 'Servicios principales'],
        'about_trust' => ['label' => 'Confianza respaldada']
    ];

    $presenceDefinitions = [
        'presence_logistics' => ['label' => 'Tarjeta 1', 'sort_order' => 1],
        'presence_maintenance' => ['label' => 'Tarjeta 2', 'sort_order' => 2],
        'presence_alliances' => ['label' => 'Tarjeta 3', 'sort_order' => 3]
    ];

    $highlightDefinitions = [
        'mission' => ['label' => 'Tarjeta 1'],
        'objective' => ['label' => 'Tarjeta 2'],
        'coverage' => ['label' => 'Tarjeta 3']
    ];

    function isAjaxRequest(): bool
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        if (!empty($_SERVER['HTTP_ACCEPT']) && stripos((string) $_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }
        return false;
    }

    function fetchAboutHero(mysqli $db): array
    {
        $hero = [
            'id' => null,
            'image_path' => '',
            'message_small' => '',
            'title' => '',
            'description' => ''
        ];

        if ($result = $db->query('SELECT id, image_path, message_small, title, description FROM about_hero ORDER BY id ASC LIMIT 1')) {
            if ($row = $result->fetch_assoc()) {
                $hero = [
                    'id' => (int) $row['id'],
                    'image_path' => (string) $row['image_path'],
                    'message_small' => (string) $row['message_small'],
                    'title' => (string) $row['title'],
                    'description' => (string) $row['description']
                ];
            }
            $result->free();
        }

        return $hero;
    }

    function fetchAboutSections(mysqli $db, array $definitions): array
    {
        $sections = [];
        foreach ($definitions as $slug => $meta) {
            $sections[$slug] = [
                'slug' => $slug,
                'title' => '',
                'body' => ''
            ];
        }

        if ($result = $db->query('SELECT slug, title, body FROM about_sections')) {
            while ($row = $result->fetch_assoc()) {
                $slug = (string) $row['slug'];
                if (isset($sections[$slug])) {
                    $sections[$slug]['title'] = (string) $row['title'];
                    $sections[$slug]['body'] = (string) $row['body'];
                }
            }
            $result->free();
        }

        return $sections;
    }

    function fetchPresenceCards(mysqli $db, array $definitions): array
    {
        $cards = [];
        foreach ($definitions as $slug => $meta) {
            $cards[$slug] = [
                'id' => null,
                'slug' => $slug,
                'title' => '',
                'description' => '',
                'image_path' => '',
                'sort_order' => (int) ($meta['sort_order'] ?? 0)
            ];
        }

        if ($result = $db->query('SELECT id, slug, title, description, image_path, sort_order FROM about_presence_cards')) {
            while ($row = $result->fetch_assoc()) {
                $slug = (string) $row['slug'];
                if (isset($cards[$slug])) {
                    $cards[$slug] = [
                        'id' => (int) $row['id'],
                        'slug' => $slug,
                        'title' => (string) $row['title'],
                        'description' => (string) $row['description'],
                        'image_path' => (string) $row['image_path'],
                        'sort_order' => (int) $row['sort_order']
                    ];
                }
            }
            $result->free();
        }

        return $cards;
    }

    function fetchHighlightCards(mysqli $db, array $definitions): array
    {
        $cards = [];
        $position = 1;
        foreach ($definitions as $slug => $meta) {
            $cards[$slug] = [
                'id' => null,
                'slug' => $slug,
                'title' => '',
                'description' => '',
                'sort_order' => $position
            ];
            $position++;
        }

        if ($result = $db->query('SELECT id, slug, title, description, sort_order FROM about_highlight_cards')) {
            while ($row = $result->fetch_assoc()) {
                $slug = (string) $row['slug'];
                if (isset($cards[$slug])) {
                    $cards[$slug] = [
                        'id' => (int) $row['id'],
                        'slug' => $slug,
                        'title' => (string) $row['title'],
                        'description' => (string) $row['description'],
                        'sort_order' => (int) $row['sort_order']
                    ];
                }
            }
            $result->free();
        }

        return $cards;
    }

    function saveOptimizedAboutImage(array $file, string $relativeDir, string $prefix, int $maxWidth, int $maxHeight): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo procesar la imagen cargada.');
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
            throw new RuntimeException('La imagen debe ser JPG, PNG o WebP.');
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

        $projectRoot = dirname(__DIR__, 2);
        $absoluteDir = $projectRoot . '/' . $relativeDir;

        if (!is_dir($absoluteDir)) {
            if (!mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
                throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
            }
        }

        try {
            $filename = $prefix . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
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

    function deleteStoredImage(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }
        $fullPath = dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    $heroData = fetchAboutHero($db);
    $sectionData = fetchAboutSections($db, $sectionDefinitions);
    $presenceCards = fetchPresenceCards($db, $presenceDefinitions);
    $highlightCards = fetchHighlightCards($db, $highlightDefinitions);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formType = $_POST['form_type'] ?? '';

        if ($formType === 'hero') {
            $heroData['message_small'] = trim($_POST['message_small'] ?? '');
            $heroData['title'] = trim($_POST['title'] ?? '');
            $heroData['description'] = trim($_POST['description'] ?? '');

            $uploadedImage = $_FILES['hero_image'] ?? null;
            $replacingImage = $uploadedImage && $uploadedImage['error'] !== UPLOAD_ERR_NO_FILE;

            if ($heroData['title'] === '') {
                $formErrors['hero'][] = 'El título del hero es obligatorio.';
            }
            if ($heroData['description'] === '') {
                $formErrors['hero'][] = 'El párrafo del hero es obligatorio.';
            }
            if (!$heroData['image_path'] && !$replacingImage) {
                $formErrors['hero'][] = 'Debes cargar una imagen para el hero.';
            }
            if ($replacingImage && $uploadedImage['error'] !== UPLOAD_ERR_OK) {
                $formErrors['hero'][] = 'No se pudo cargar la imagen seleccionada.';
            }

            $newHeroImage = $heroData['image_path'];
            $temporaryPath = null;

            if ($replacingImage && !$formErrors['hero']) {
                try {
                    $temporaryPath = saveOptimizedAboutImage($uploadedImage, 'uploads/about/hero', 'hero', 1600, 900);
                    $newHeroImage = $temporaryPath;
                } catch (Throwable $exception) {
                    $formErrors['hero'][] = $exception->getMessage();
                }
            }

            if (!$formErrors['hero']) {
                try {
                    if ($heroData['id']) {
                        $stmt = $db->prepare('UPDATE about_hero SET image_path = ?, message_small = ?, title = ?, description = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                        if ($stmt === false) {
                            throw new RuntimeException('No se pudo preparar la actualización del hero.');
                        }
                        $stmt->bind_param('ssssi', $newHeroImage, $heroData['message_small'], $heroData['title'], $heroData['description'], $heroData['id']);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        $stmt = $db->prepare('INSERT INTO about_hero (image_path, message_small, title, description) VALUES (?, ?, ?, ?)');
                        if ($stmt === false) {
                            throw new RuntimeException('No se pudo preparar el registro del hero.');
                        }
                        $stmt->bind_param('ssss', $newHeroImage, $heroData['message_small'], $heroData['title'], $heroData['description']);
                        $stmt->execute();
                        $heroData['id'] = (int) $stmt->insert_id;
                        $stmt->close();
                    }

                    if ($replacingImage && $heroData['image_path'] && $heroData['image_path'] !== $newHeroImage) {
                        deleteStoredImage($heroData['image_path']);
                    }

                    $heroData['image_path'] = $newHeroImage;
                    $_SESSION['about_flash'] = 'Hero actualizado correctamente.';
                    header('Location: ' . adminUrl('nosotros') . '#hero');
                    exit;
                } catch (Throwable $exception) {
                    $formErrors['hero'][] = 'No se pudo guardar la información del hero.';
                    if ($temporaryPath) {
                        deleteStoredImage($temporaryPath);
                    }
                }
            } elseif ($temporaryPath) {
                deleteStoredImage($temporaryPath);
            }
        } elseif ($formType === 'sections') {
            $postedSections = $_POST['sections'] ?? [];
            $payload = [];

            foreach ($sectionDefinitions as $slug => $meta) {
                $title = isset($postedSections[$slug]['title']) ? trim((string) $postedSections[$slug]['title']) : '';
                $body = isset($postedSections[$slug]['body']) ? trim((string) $postedSections[$slug]['body']) : '';
                if ($title === '' || $body === '') {
                    $formErrors['sections'][] = 'Completa título y texto de todas las secciones.';
                    break;
                }
                $payload[$slug] = ['title' => $title, 'body' => $body];
            }

            if (!$formErrors['sections']) {
                try {
                    $stmt = $db->prepare('INSERT INTO about_sections (slug, title, body) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body), updated_at = NOW()');
                    if ($stmt === false) {
                        throw new RuntimeException('No se pudo preparar la actualización de secciones.');
                    }
                    foreach ($payload as $slug => $values) {
                        $stmt->bind_param('sss', $slug, $values['title'], $values['body']);
                        $stmt->execute();
                        $sectionData[$slug]['title'] = $values['title'];
                        $sectionData[$slug]['body'] = $values['body'];
                    }
                    $stmt->close();
                    $_SESSION['about_flash'] = 'Secciones de texto actualizadas correctamente.';
                    header('Location: ' . adminUrl('nosotros') . '#sections');
                    exit;
                } catch (Throwable $exception) {
                    $formErrors['sections'][] = 'No se pudieron guardar las secciones.';
                }
            } else {
                foreach ($payload as $slug => $values) {
                    $sectionData[$slug]['title'] = $values['title'];
                    $sectionData[$slug]['body'] = $values['body'];
                }
            }
        } elseif ($formType === 'presence_card') {
            $cardSlug = isset($_POST['card_slug']) ? trim((string) $_POST['card_slug']) : '';
            $isAjax = isAjaxRequest();
            if (!isset($presenceDefinitions[$cardSlug])) {
                $errorList = ['Tarjeta desconocida.'];
                if ($isAjax) {
                    http_response_code(422);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['ok' => false, 'errors' => $errorList]);
                    exit;
                }
                $formErrors['presence'][$cardSlug] = $errorList;
            } else {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $existing = $presenceCards[$cardSlug];
                $sortOrder = (int) ($presenceDefinitions[$cardSlug]['sort_order'] ?? 0);

                $errors = [];
                $uploadedImage = $_FILES['image_file'] ?? null;
                $replacingImage = $uploadedImage && $uploadedImage['error'] !== UPLOAD_ERR_NO_FILE;

                if ($title === '') {
                    $errors[] = 'El título de la tarjeta es obligatorio.';
                }
                if ($description === '') {
                    $errors[] = 'El texto de la tarjeta es obligatorio.';
                }
                if (!$existing['image_path'] && !$replacingImage) {
                    $errors[] = 'Debes subir una imagen para la tarjeta.';
                }
                if ($replacingImage && $uploadedImage['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'No se pudo cargar la imagen de la tarjeta.';
                }

                $newImagePath = $existing['image_path'];
                $temporaryPath = null;

                if ($replacingImage && !$errors) {
                    try {
                        $temporaryPath = saveOptimizedAboutImage($uploadedImage, 'uploads/about/presence', 'presence', 1200, 900);
                        $newImagePath = $temporaryPath;
                    } catch (Throwable $exception) {
                        $errors[] = $exception->getMessage();
                    }
                }

                if (!$errors) {
                    try {
                        $stmt = $db->prepare('INSERT INTO about_presence_cards (slug, title, description, image_path, sort_order) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), image_path = VALUES(image_path), sort_order = VALUES(sort_order), updated_at = NOW()');
                        if ($stmt === false) {
                            throw new RuntimeException('No se pudo preparar la actualización de la tarjeta.');
                        }
                        $stmt->bind_param('ssssi', $cardSlug, $title, $description, $newImagePath, $sortOrder);
                        $stmt->execute();
                        if (!$existing['id'] && $stmt->insert_id) {
                            $presenceCards[$cardSlug]['id'] = (int) $stmt->insert_id;
                        }
                        $stmt->close();

                        if ($replacingImage && $existing['image_path'] && $existing['image_path'] !== $newImagePath) {
                            deleteStoredImage($existing['image_path']);
                        }

                        $presenceCards[$cardSlug]['title'] = $title;
                        $presenceCards[$cardSlug]['description'] = $description;
                        $presenceCards[$cardSlug]['image_path'] = $newImagePath;
                        $presenceCards[$cardSlug]['sort_order'] = $sortOrder;

                        $successMessageCard = 'Tarjeta de presencia actualizada correctamente.';
                        if ($isAjax) {
                            $imageUrl = $newImagePath ? adminAssetUrl($newImagePath) : '';
                            header('Content-Type: application/json; charset=UTF-8');
                            echo json_encode([
                                'ok' => true,
                                'message' => $successMessageCard,
                                'slug' => $cardSlug,
                                'title' => $title,
                                'description' => $description,
                                'image_path' => $newImagePath,
                                'image_url' => $imageUrl
                            ]);
                            exit;
                        }

                        $_SESSION['about_flash'] = $successMessageCard;
                        header('Location: ' . adminUrl('nosotros') . '#presence');
                        exit;
                    } catch (Throwable $exception) {
                        $errors[] = 'No se pudo actualizar la tarjeta.';
                        if ($temporaryPath) {
                            deleteStoredImage($temporaryPath);
                        }
                    }
                } else {
                    if ($temporaryPath) {
                        deleteStoredImage($temporaryPath);
                    }
                }

                if ($errors) {
                    if ($isAjax) {
                        http_response_code(422);
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode(['ok' => false, 'errors' => $errors]);
                        exit;
                    }
                    $formErrors['presence'][$cardSlug] = $errors;
                    $presenceCards[$cardSlug]['title'] = $title;
                    $presenceCards[$cardSlug]['description'] = $description;
                }
            }
        } elseif ($formType === 'highlight_card') {
            $cardSlug = isset($_POST['card_slug']) ? trim((string) $_POST['card_slug']) : '';
            $isAjax = isAjaxRequest();
            if (!isset($highlightDefinitions[$cardSlug])) {
                $errorList = ['Tarjeta desconocida.'];
                if ($isAjax) {
                    http_response_code(422);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['ok' => false, 'errors' => $errorList]);
                    exit;
                }
                $formErrors['highlights'][$cardSlug] = $errorList;
            } else {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $existing = $highlightCards[$cardSlug];
                $sortOrder = (int) ($existing['sort_order'] ?? 0);

                $errors = [];
                if ($title === '') {
                    $errors[] = 'El título de la tarjeta es obligatorio.';
                }
                if ($description === '') {
                    $errors[] = 'El texto de la tarjeta es obligatorio.';
                }

                if (!$errors) {
                    try {
                        $stmt = $db->prepare('INSERT INTO about_highlight_cards (slug, title, description, sort_order) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), sort_order = VALUES(sort_order), updated_at = NOW()');
                        if ($stmt === false) {
                            throw new RuntimeException('No se pudo preparar la actualización de la tarjeta.');
                        }
                        $stmt->bind_param('sssi', $cardSlug, $title, $description, $sortOrder);
                        $stmt->execute();
                        if (!$existing['id'] && $stmt->insert_id) {
                            $highlightCards[$cardSlug]['id'] = (int) $stmt->insert_id;
                        }
                        $stmt->close();

                        $highlightCards[$cardSlug]['title'] = $title;
                        $highlightCards[$cardSlug]['description'] = $description;
                        $highlightCards[$cardSlug]['sort_order'] = $sortOrder;

                        $successMessageCard = 'Tarjeta actualizada correctamente.';
                        if ($isAjax) {
                            header('Content-Type: application/json; charset=UTF-8');
                            echo json_encode([
                                'ok' => true,
                                'message' => $successMessageCard,
                                'slug' => $cardSlug,
                                'title' => $title,
                                'description' => $description
                            ]);
                            exit;
                        }

                        $_SESSION['about_flash'] = 'Tarjetas inferiores actualizadas correctamente.';
                        header('Location: ' . adminUrl('nosotros') . '#highlights');
                        exit;
                    } catch (Throwable $exception) {
                        $errors[] = 'No se pudo actualizar la tarjeta.';
                    }
                }

                if ($errors) {
                    if ($isAjax) {
                        http_response_code(422);
                        header('Content-Type: application/json; charset=UTF-8');
                        echo json_encode(['ok' => false, 'errors' => $errors]);
                        exit;
                    }
                    $formErrors['highlights'][$cardSlug] = $errors;
                    $highlightCards[$cardSlug]['title'] = $title;
                    $highlightCards[$cardSlug]['description'] = $description;
                }
            }
        }
    }

    require_once __DIR__ . '/../includes/page-top.php';
    ?>
    <section>
        <?php if ($successMessage): ?>
            <div class="empty-state" style="background:#ecfdf5;color:#065f46;border:1px solid #34d399;margin-bottom:20px;">
                <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div style="display:grid;gap:32px;">
            <div id="hero">
                <h2 style="margin:0 0 16px;">Hero de Nosotros</h2>
                <p style="margin:0 0 18px;color:#6b7280;font-size:14px;">Carga una sola imagen optimizada y edita el texto principal de la cabecera.</p>
                <?php if ($formErrors['hero']): ?>
                    <div class="empty-state" style="background:#fee2e2;color:#b91c1c;margin-bottom:16px;text-align:left;">
                        <ul style="margin:0;padding-left:18px;">
                            <?php foreach ($formErrors['hero'] as $error): ?>
                                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="hero">

                    <label for="message_small">Mensaje pequeño</label>
                    <input type="text" name="message_small" id="message_small" value="<?php echo htmlspecialchars($heroData['message_small'], ENT_QUOTES, 'UTF-8'); ?>">

                    <label for="title">Título</label>
                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($heroData['title'], ENT_QUOTES, 'UTF-8'); ?>" required>

                    <label for="description">Párrafo</label>
                    <textarea name="description" id="description" required><?php echo htmlspecialchars($heroData['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                    <label for="hero_image">Imagen</label>
                    <input type="file" name="hero_image" id="hero_image" accept="image/*">
                    <?php if ($heroData['image_path']): ?>
                        <?php $heroUrl = adminAssetUrl($heroData['image_path']); ?>
                        <?php if ($heroUrl): ?>
                            <img src="<?php echo htmlspecialchars($heroUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Hero actual" style="width:100%;max-width:320px;height:180px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;margin:12px 0;display:block;">
                        <?php endif; ?>
                        <span style="display:block;font-size:12px;color:#6b7280;word-break:break-all;margin-bottom:18px;">Ruta actual: <?php echo htmlspecialchars($heroData['image_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Guardar hero</button>
                    </div>
                </form>
            </div>

            <div id="sections">
                <h2 style="margin:0 0 16px;">Bloques de texto</h2>
                <p style="margin:0 0 18px;color:#6b7280;font-size:14px;">Actualiza los títulos y párrafos de cada bloque de la página Nosotros.</p>
                <?php if ($formErrors['sections']): ?>
                    <div class="empty-state" style="background:#fee2e2;color:#b91c1c;margin-bottom:16px;text-align:left;">
                        <ul style="margin:0;padding-left:18px;">
                            <?php foreach ($formErrors['sections'] as $error): ?>
                                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="form_type" value="sections">
                    <?php $sectionIndex = 1; ?>
                    <?php foreach ($sectionDefinitions as $slug => $meta): ?>
                        <?php $legendLabel = 'Encabezado ' . $sectionIndex; ?>
                        <fieldset style="border:1px solid #e5e7eb;border-radius:10px;padding:18px;margin-bottom:20px;">
                            <legend style="padding:0 8px;font-weight:600;color:#111827;"><?php echo htmlspecialchars($legendLabel, ENT_QUOTES, 'UTF-8'); ?></legend>
                            <label for="section-title-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">Título</label>
                            <input type="text" name="sections[<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>][title]" id="section-title-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($sectionData[$slug]['title'], ENT_QUOTES, 'UTF-8'); ?>" required>

                            <label for="section-body-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">Texto</label>
                            <textarea name="sections[<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>][body]" id="section-body-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" required><?php echo htmlspecialchars($sectionData[$slug]['body'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </fieldset>
                        <?php $sectionIndex++; ?>
                    <?php endforeach; ?>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Guardar bloques</button>
                    </div>
                </form>
            </div>

            <div id="presence">
                <h2 style="margin:0 0 16px;">Tarjetas Sección Nosotros</h2>
                <p style="margin:0 0 18px;color:#6b7280;font-size:14px;">Cada tarjeta requiere imagen optimizada, título y texto.</p>
                <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
                    <?php foreach ($presenceDefinitions as $slug => $meta): ?>
                        <?php $card = $presenceCards[$slug]; ?>
                        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
                            <h3 style="margin:0 0 12px;font-size:16px;color:#111827;"><?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <?php if (!empty($formErrors['presence'][$slug])): ?>
                                <div class="empty-state" style="background:#fee2e2;color:#b91c1c;margin-bottom:14px;text-align:left;">
                                    <ul style="margin:0;padding-left:18px;">
                                        <?php foreach ($formErrors['presence'][$slug] as $error): ?>
                                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <div class="form-status js-card-status" aria-live="polite" style="display:none;margin-bottom:12px;"></div>
                            <form method="post" enctype="multipart/form-data" class="js-ajax-card-form" data-card-type="presence" data-card-slug="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="form_type" value="presence_card">
                                <input type="hidden" name="card_slug" value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">

                                <label for="presence-title-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">Título</label>
                                <input type="text" name="title" id="presence-title-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?>" required>

                                <label for="presence-description-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">Texto</label>
                                <textarea name="description" id="presence-description-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" required><?php echo htmlspecialchars($card['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                                <label for="presence-image-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">Imagen</label>
                                <input type="file" name="image_file" id="presence-image-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" accept="image/*">
                                <?php if ($card['image_path']): ?>
                                    <?php $cardUrl = adminAssetUrl($card['image_path']); ?>
                                    <?php if ($cardUrl): ?>
                                        <img src="<?php echo htmlspecialchars($cardUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen actual" class="js-card-preview" style="width:100%;max-width:220px;height:140px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;margin:12px 0;display:block;">
                                    <?php endif; ?>
                                    <span class="js-card-path" style="display:block;font-size:12px;color:#6b7280;word-break:break-all;margin-bottom:12px;">Ruta actual: <?php echo htmlspecialchars($card['image_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>

                                <div class="form-actions">
                                    <button class="btn btn-primary" type="submit">Guardar tarjeta</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="highlights">
                <h2 style="margin:0 0 16px;">Tarjetas inferiores</h2>
                <p style="margin:0 0 18px;color:#6b7280;font-size:14px;">Misión, objetivo y cobertura se editan por separado.</p>
                <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
                    <?php foreach ($highlightDefinitions as $slug => $meta): ?>
                        <?php $card = $highlightCards[$slug]; ?>
                        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;">
                            <h3 style="margin:0 0 12px;font-size:16px;color:#111827;"><?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <?php if (!empty($formErrors['highlights'][$slug])): ?>
                                <div class="empty-state" style="background:#fee2e2;color:#b91c1c;margin-bottom:14px;text-align:left;">
                                    <ul style="margin:0;padding-left:18px;">
                                        <?php foreach ($formErrors['highlights'][$slug] as $error): ?>
                                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <div class="form-status js-card-status" aria-live="polite" style="display:none;margin-bottom:12px;"></div>
                            <form method="post" class="js-ajax-card-form" data-card-type="highlight" data-card-slug="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="form_type" value="highlight_card">
                                <input type="hidden" name="card_slug" value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">

                                <label for="highlight-title-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">Título</label>
                                <input type="text" name="title" id="highlight-title-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?>" required>

                                <label for="highlight-description-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">Texto</label>
                                <textarea name="description" id="highlight-description-<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" required><?php echo htmlspecialchars($card['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                                <div class="form-actions">
                                    <button class="btn btn-primary" type="submit">Guardar tarjeta</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <p style="margin:0;color:#6b7280;font-size:13px;">Los servicios destacados se mostrarán con un indicador cuando se implemente su propio CRUD.</p>
            </div>
        </div>
    </section>
    <script>
    (function () {
        const forms = document.querySelectorAll('.js-ajax-card-form');
        if (!forms.length || !window.fetch || !window.FormData) {
            return;
        }

        function showStatus(element, message, isError, extraList) {
            element.style.display = 'block';
            element.textContent = '';
            element.style.background = isError ? '#fee2e2' : '#ecfdf5';
            element.style.color = isError ? '#b91c1c' : '#065f46';
            element.style.border = '1px solid ' + (isError ? '#fca5a5' : '#34d399');
            element.style.padding = '12px 16px';
            element.style.borderRadius = '6px';
            if (Array.isArray(extraList) && extraList.length > 1) {
                const list = document.createElement('ul');
                extraList.forEach(function (item) {
                    const li = document.createElement('li');
                    li.textContent = item;
                    list.appendChild(li);
                });
                element.appendChild(list);
            } else {
                element.textContent = extraList && extraList.length ? extraList[0] : message;
            }
        }

        function resetStatus(element) {
            element.style.display = 'none';
            element.textContent = '';
            element.style.background = '';
            element.style.color = '';
            element.style.border = '';
            element.style.padding = '';
            element.style.borderRadius = '';
        }

        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const statusBox = form.previousElementSibling && form.previousElementSibling.classList.contains('js-card-status') ? form.previousElementSibling : null;
                if (!statusBox) {
                    return;
                }

                resetStatus(statusBox);

                const submitButton = form.querySelector('button[type="submit"]');
                const originalText = submitButton ? submitButton.textContent : '';
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Guardando...';
                }

                const formData = new FormData(form);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                }).then(function (payload) {
                    if (!payload.ok) {
                        const messages = Array.isArray(payload.data.errors) ? payload.data.errors : ['No se pudo guardar la tarjeta.'];
                        showStatus(statusBox, '', true, messages);
                        return;
                    }

                    const successMessage = payload.data.message || 'Guardado correctamente.';
                    showStatus(statusBox, successMessage, false, [successMessage]);

                    if (form.dataset.cardType === 'presence') {
                        let pathBox = form.querySelector('.js-card-path');
                        if (!pathBox && payload.data.image_path) {
                            pathBox = document.createElement('span');
                            pathBox.className = 'js-card-path';
                            pathBox.style.display = 'block';
                            pathBox.style.fontSize = '12px';
                            pathBox.style.color = '#6b7280';
                            pathBox.style.wordBreak = 'break-all';
                            pathBox.style.marginBottom = '12px';
                            const referenceNode = form.querySelector('input[type="file"]');
                            if (referenceNode && referenceNode.parentNode) {
                                referenceNode.parentNode.insertBefore(pathBox, referenceNode.nextSibling);
                            }
                        }
                        if (pathBox && payload.data.image_path) {
                            pathBox.textContent = 'Ruta actual: ' + payload.data.image_path;
                            pathBox.style.display = 'block';
                        }

                        const preview = form.querySelector('.js-card-preview');
                        if (payload.data.image_url) {
                            const cacheBuster = '?v=' + Date.now();
                            if (preview) {
                                preview.src = payload.data.image_url + cacheBuster;
                                preview.style.display = 'block';
                            } else {
                                const newImg = document.createElement('img');
                                newImg.src = payload.data.image_url + cacheBuster;
                                newImg.alt = 'Imagen actual';
                                newImg.className = 'js-card-preview';
                                newImg.style.width = '100%';
                                newImg.style.maxWidth = '220px';
                                newImg.style.height = '140px';
                                newImg.style.objectFit = 'cover';
                                newImg.style.borderRadius = '8px';
                                newImg.style.border = '1px solid #e5e7eb';
                                newImg.style.margin = '12px 0';
                                newImg.style.display = 'block';
                                const referenceNode = form.querySelector('input[type="file"]');
                                if (referenceNode && referenceNode.parentNode) {
                                    referenceNode.parentNode.insertBefore(newImg, referenceNode.nextSibling);
                                }
                            }
                        }
                    }

                    const fileInput = form.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.value = '';
                    }
                }).catch(function () {
                    showStatus(statusBox, 'No se pudo guardar la tarjeta.', true, ['No se pudo guardar la tarjeta.']);
                }).finally(function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                });
            });
        });
    })();
    </script>
    <?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
