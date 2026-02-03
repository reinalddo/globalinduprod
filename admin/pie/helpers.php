<?php
if (!function_exists('footerEnsureSchema')) {
    function footerEnsureSchema(mysqli $db): void
    {
        $settingsSql = <<<SQL
CREATE TABLE IF NOT EXISTS site_footer_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logo_path VARCHAR(255) NOT NULL DEFAULT '',
    contact_text TEXT NULL,
    rights_text VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $db->query($settingsSql);

        $socialSql = <<<SQL
CREATE TABLE IF NOT EXISTS site_footer_socials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon_key VARCHAR(40) DEFAULT NULL,
    icon_path VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_icon_key (icon_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $db->query($socialSql);
    }
}

if (!function_exists('footerEnsureDefaultSocials')) {
    function footerEnsureDefaultSocials(mysqli $db): void
    {
        $defaults = [
            'facebook' => ['name' => 'Facebook', 'sort_order' => 10],
            'instagram' => ['name' => 'Instagram', 'sort_order' => 20],
            'youtube' => ['name' => 'YouTube', 'sort_order' => 30],
            'tiktok' => ['name' => 'TikTok', 'sort_order' => 40],
        ];

        foreach ($defaults as $key => $data) {
            $stmt = $db->prepare('SELECT id FROM site_footer_socials WHERE icon_key = ? LIMIT 1');
            if ($stmt === false) {
                continue;
            }
            $stmt->bind_param('s', $key);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows === 0) {
                    $insert = $db->prepare('INSERT INTO site_footer_socials (name, url, icon_key, sort_order) VALUES (?, ?, ?, ?)');
                    if ($insert !== false) {
                        $defaultUrl = '#';
                        $insert->bind_param('sssi', $data['name'], $defaultUrl, $key, $data['sort_order']);
                        $insert->execute();
                        $insert->close();
                    }
                }
            }
            $stmt->close();
        }
    }
}

if (!function_exists('footerFetchSettings')) {
    function footerFetchSettings(mysqli $db): array
    {
        $settings = [
            'id' => null,
            'logo_path' => '',
            'contact_text' => '',
            'rights_text' => '',
            'updated_at' => ''
        ];

        if ($result = $db->query('SELECT id, logo_path, contact_text, rights_text, updated_at FROM site_footer_settings ORDER BY id ASC LIMIT 1')) {
            if ($row = $result->fetch_assoc()) {
                $settings = [
                    'id' => (int) $row['id'],
                    'logo_path' => (string) $row['logo_path'],
                    'contact_text' => isset($row['contact_text']) ? (string) $row['contact_text'] : '',
                    'rights_text' => (string) $row['rights_text'],
                    'updated_at' => isset($row['updated_at']) ? (string) $row['updated_at'] : ''
                ];
            }
            $result->free();
        }

        return $settings;
    }
}

if (!function_exists('footerSaveLogoImage')) {
    function footerSaveLogoImage(array $file): string
    {
        if (!isset($file['error'], $file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo procesar el logo enviado.');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('El archivo de logo no es válido.');
        }

        $allowedMimes = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        if (!$mime || !isset($allowedMimes[$mime])) {
            throw new RuntimeException('El logo debe ser PNG, JPG o WebP.');
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
        $maxWidth = 420;
        $maxHeight = 240;
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
            $resampled = imagecreatetruecolor($targetWidth, $targetHeight);
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($resampled, false);
                imagesavealpha($resampled, true);
            }
            imagecopyresampled($resampled, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            imagedestroy($image);
            $image = $resampled;
        }

        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        try {
            [$relativeDir, $absoluteDir] = tenantEnsureUploadsDirectory('site/footer');
        } catch (Throwable $exception) {
            imagedestroy($image);
            throw $exception;
        }

        try {
            $filename = 'footer-logo-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
        } catch (Throwable $exception) {
            imagedestroy($image);
            throw new RuntimeException('No se pudo generar el nombre del archivo.');
        }

        $targetPath = $absoluteDir . '/' . $filename;

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($image, $targetPath, 85);
                break;
            case 'image/png':
                imagepng($image, $targetPath, 7);
                break;
            case 'image/webp':
                if (!function_exists('imagewebp')) {
                    imagedestroy($image);
                    throw new RuntimeException('El servidor no soporta guardar logos WebP.');
                }
                imagewebp($image, $targetPath, 85);
                break;
        }

        imagedestroy($image);

        if (!is_file($targetPath)) {
            throw new RuntimeException('No se pudo guardar el archivo del logo.');
        }

        return $relativeDir . '/' . $filename;
    }
}

if (!function_exists('footerSaveCustomIcon')) {
    function footerSaveCustomIcon(array $file): string
    {
        if (!isset($file['error'], $file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo procesar el icono enviado.');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('El archivo del icono no es válido.');
        }

        $allowedMimes = [
            'image/png' => 'png',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp',
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        if (!$mime || !isset($allowedMimes[$mime])) {
            throw new RuntimeException('El icono debe ser PNG, SVG o WebP.');
        }

        [$relativeDir, $absoluteDir] = tenantEnsureUploadsDirectory('site/footer');

        try {
            $filename = 'social-icon-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo generar el nombre del icono.');
        }

        $targetPath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('No se pudo guardar el icono enviado.');
        }

        return $relativeDir . '/' . $filename;
    }
}

if (!function_exists('footerDeleteStoredLogo')) {
    function footerDeleteStoredLogo(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }
        $normalized = ltrim($relativePath, '/');
        if (!tenantUploadsIsWithin($normalized, 'site/footer')) {
            return;
        }
        $absolute = tenantUploadsAbsolutePath($normalized);
        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }
}

if (!function_exists('footerFetchSocials')) {
    function footerFetchSocials(mysqli $db): array
    {
        $socials = [];
        if ($result = $db->query('SELECT id, name, url, icon_key, icon_path, sort_order FROM site_footer_socials ORDER BY sort_order ASC, id ASC')) {
            while ($row = $result->fetch_assoc()) {
                $socials[] = [
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                    'url' => (string) $row['url'],
                    'icon_key' => isset($row['icon_key']) ? (string) $row['icon_key'] : null,
                    'icon_path' => isset($row['icon_path']) ? (string) $row['icon_path'] : null,
                    'sort_order' => (int) $row['sort_order'],
                    'is_default' => !empty($row['icon_key'])
                ];
            }
            $result->free();
        }
        return $socials;
    }
}

if (!function_exists('footerFindSocialById')) {
    function footerFindSocialById(mysqli $db, int $id): ?array
    {
        $stmt = $db->prepare('SELECT id, name, url, icon_key, icon_path, sort_order FROM site_footer_socials WHERE id = ? LIMIT 1');
        if ($stmt === false) {
            return null;
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }
        $stmt->bind_result($socialId, $name, $url, $iconKey, $iconPath, $sortOrder);
        $social = null;
        if ($stmt->fetch()) {
            $social = [
                'id' => (int) $socialId,
                'name' => (string) $name,
                'url' => (string) $url,
                'icon_key' => $iconKey !== null ? (string) $iconKey : null,
                'icon_path' => $iconPath !== null ? (string) $iconPath : null,
                'sort_order' => (int) $sortOrder,
                'is_default' => $iconKey !== null
            ];
        }
        $stmt->close();
        return $social;
    }
}

if (!function_exists('footerDeleteSocial')) {
    function footerDeleteSocial(mysqli $db, int $id): bool
    {
        $social = footerFindSocialById($db, $id);
        if (!$social || $social['is_default']) {
            return false;
        }

        if ($social['icon_path']) {
            footerDeleteStoredLogo($social['icon_path']);
        }

        $stmt = $db->prepare('DELETE FROM site_footer_socials WHERE id = ? LIMIT 1');
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
