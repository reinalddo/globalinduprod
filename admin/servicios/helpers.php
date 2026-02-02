<?php
if (!function_exists('servicesEnsureColumn')) {
    function servicesEnsureColumn(mysqli $db, string $table, string $column, string $definition): void
    {
        // Prevent injection by only allowing expected characters in identifiers
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new InvalidArgumentException('Identificador de tabla o columna no válido.');
        }

        $stmt = $db->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1');
        if ($stmt === false) {
            return;
        }

        $stmt->bind_param('ss', $table, $column);
        if ($stmt->execute()) {
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();
            if ($exists) {
                return;
            }
        } else {
            $stmt->close();
            return;
        }

        $sql = sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition);

        try {
            $db->query($sql);
        } catch (Throwable $exception) {
            // Resultado ignorado: la columna podría haber sido creada en una carrera concurrente.
        }
    }
}

if (!function_exists('servicesEnsureSchema')) {
    function servicesEnsureSchema(mysqli $db): void
    {
        $pageHeroSql = <<<SQL
    CREATE TABLE IF NOT EXISTS services_page_hero (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kicker VARCHAR(120) NOT NULL DEFAULT '',
        title VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        listing_title VARCHAR(200) NOT NULL DEFAULT '',
        listing_description TEXT NOT NULL,
        image_path VARCHAR(255) NOT NULL DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL;
        $db->query($pageHeroSql);

        servicesEnsureColumn($db, 'services_page_hero', 'listing_title', "VARCHAR(200) NOT NULL DEFAULT ''");
        servicesEnsureColumn($db, 'services_page_hero', 'listing_description', 'TEXT');
        try {
            $db->query("UPDATE services_page_hero SET listing_description = '' WHERE listing_description IS NULL");
            $db->query('ALTER TABLE `services_page_hero` MODIFY COLUMN `listing_description` TEXT NOT NULL');
        } catch (Throwable $exception) {
            // Si falla (p.ej. MySQL antiguo), se mantiene como nullable y se maneja en PHP.
        }

        $serviceSql = <<<SQL
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    kicker VARCHAR(120) NOT NULL DEFAULT '',
    title VARCHAR(200) NOT NULL,
    summary TEXT NOT NULL,
    hero_image_path VARCHAR(255) NOT NULL DEFAULT '',
    content_html MEDIUMTEXT NOT NULL,
    is_featured_home TINYINT(1) NOT NULL DEFAULT 0,
    is_featured_about TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $db->query($serviceSql);

        $gallerySql = <<<SQL
CREATE TABLE IF NOT EXISTS service_gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gallery_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service_sort (service_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $db->query($gallerySql);
    }
}

if (!function_exists('servicesFetchHero')) {
    function servicesFetchHero(mysqli $db): array
    {
        $hero = [
            'id' => null,
            'kicker' => '',
            'title' => '',
            'description' => '',
            'listing_title' => '',
            'listing_description' => '',
            'image_path' => ''
        ];

        $sql = 'SELECT id, kicker, title, description, listing_title, listing_description, image_path FROM services_page_hero ORDER BY id ASC LIMIT 1';

        if ($result = $db->query($sql)) {
            if ($row = $result->fetch_assoc()) {
                $hero = [
                    'id' => (int) $row['id'],
                    'kicker' => (string) $row['kicker'],
                    'title' => (string) $row['title'],
                    'description' => (string) $row['description'],
                    'listing_title' => (string) ($row['listing_title'] ?? ''),
                    'listing_description' => (string) ($row['listing_description'] ?? ''),
                    'image_path' => (string) $row['image_path']
                ];
            }
            $result->free();
        }

        return $hero;
    }
}

if (!function_exists('servicesFetchAll')) {
    function servicesFetchAll(mysqli $db): array
    {
        $services = [];
        if ($result = $db->query('SELECT id, slug, kicker, title, summary, hero_image_path, is_featured_home, is_featured_about, updated_at FROM services ORDER BY title ASC')) {
            while ($row = $result->fetch_assoc()) {
                $services[] = [
                    'id' => (int) $row['id'],
                    'slug' => (string) $row['slug'],
                    'kicker' => (string) $row['kicker'],
                    'title' => (string) $row['title'],
                    'summary' => (string) $row['summary'],
                    'hero_image_path' => (string) $row['hero_image_path'],
                    'is_featured_home' => (int) $row['is_featured_home'] === 1,
                    'is_featured_about' => (int) $row['is_featured_about'] === 1,
                    'updated_at' => (string) $row['updated_at']
                ];
            }
            $result->free();
        }
        return $services;
    }
}

if (!function_exists('servicesFetchById')) {
    function servicesFetchById(mysqli $db, int $id): ?array
    {
        $stmt = $db->prepare('SELECT id, slug, kicker, title, summary, hero_image_path, content_html, is_featured_home, is_featured_about FROM services WHERE id = ? LIMIT 1');
        if ($stmt === false) {
            return null;
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }
        $stmt->bind_result($serviceId, $slug, $kicker, $title, $summary, $heroImage, $contentHtml, $featuredHome, $featuredAbout);
        $service = null;
        if ($stmt->fetch()) {
            $service = [
                'id' => (int) $serviceId,
                'slug' => (string) $slug,
                'kicker' => (string) $kicker,
                'title' => (string) $title,
                'summary' => (string) $summary,
                'hero_image_path' => (string) $heroImage,
                'content_html' => (string) $contentHtml,
                'is_featured_home' => (int) $featuredHome === 1,
                'is_featured_about' => (int) $featuredAbout === 1
            ];
        }
        $stmt->close();
        return $service;
    }
}

if (!function_exists('servicesFetchGallery')) {
    function servicesFetchGallery(mysqli $db, int $serviceId): array
    {
        $gallery = [];
        $stmt = $db->prepare('SELECT id, image_path, sort_order FROM service_gallery_images WHERE service_id = ? ORDER BY sort_order ASC, id ASC');
        if ($stmt === false) {
            return $gallery;
        }
        $stmt->bind_param('i', $serviceId);
        if ($stmt->execute()) {
            $stmt->bind_result($imageId, $imagePath, $sortOrder);
            while ($stmt->fetch()) {
                $gallery[] = [
                    'id' => (int) $imageId,
                    'image_path' => (string) $imagePath,
                    'sort_order' => (int) $sortOrder
                ];
            }
        }
        $stmt->close();
        return $gallery;
    }
}

if (!function_exists('servicesDeleteStoredImage')) {
    function servicesDeleteStoredImage(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }
        $absolute = dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }
}

if (!function_exists('servicesSaveOptimizedImage')) {
    function servicesSaveOptimizedImage(array $file, string $relativeDir, string $prefix, int $maxWidth, int $maxHeight): string
    {
        if (!isset($file['error'], $file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
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
        $absoluteDir = $projectRoot . '/' . trim($relativeDir, '/');
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

        if (!is_file($targetPath)) {
            throw new RuntimeException('No se pudo guardar la imagen optimizada.');
        }

        return trim($relativeDir, '/') . '/' . $filename;
    }
}

if (!function_exists('servicesSlugify')) {
    function servicesSlugify(string $text): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($normalized === false) {
            $normalized = $text;
        }
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $normalized);
        $slug = strtolower(trim((string) $slug, '-'));
        return $slug === '' ? 'servicio-' . bin2hex(random_bytes(3)) : $slug;
    }
}

if (!function_exists('servicesNextGalleryOrder')) {
    function servicesNextGalleryOrder(mysqli $db, int $serviceId): int
    {
        $stmt = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM service_gallery_images WHERE service_id = ?');
        if ($stmt === false) {
            return 0;
        }
        $stmt->bind_param('i', $serviceId);
        $result = 0;
        if ($stmt->execute()) {
            $stmt->bind_result($maxOrder);
            if ($stmt->fetch()) {
                $result = (int) $maxOrder;
            }
        }
        $stmt->close();
        return $result;
    }
}
