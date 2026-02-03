<?php
if (!function_exists('headerEnsureSchema')) {
    function headerEnsureSchema(mysqli $db): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS site_header (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logo_path VARCHAR(255) NOT NULL,
    logo_label VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $db->query($sql);
    }
}

if (!function_exists('headerFetchSettings')) {
    function headerFetchSettings(mysqli $db): array
    {
        $settings = [
            'id' => null,
            'logo_path' => '',
            'logo_label' => '',
            'updated_at' => ''
        ];

        if ($result = $db->query('SELECT id, logo_path, logo_label, updated_at FROM site_header ORDER BY id ASC LIMIT 1')) {
            if ($row = $result->fetch_assoc()) {
                $settings = [
                    'id' => (int) $row['id'],
                    'logo_path' => (string) $row['logo_path'],
                    'logo_label' => isset($row['logo_label']) ? (string) $row['logo_label'] : '',
                    'updated_at' => isset($row['updated_at']) ? (string) $row['updated_at'] : ''
                ];
            }
            $result->free();
        }

        return $settings;
    }
}

if (!function_exists('headerSaveLogoImage')) {
    function headerSaveLogoImage(array $file): string
    {
        if (!isset($file['error'], $file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo procesar el logo cargado.');
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
            throw new RuntimeException('El logo debe estar en formato PNG, JPG o WebP.');
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
            throw new RuntimeException('No se pudo leer el archivo de logo enviado.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 600;
        $maxHeight = 260;

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

        [$relativeDir, $absoluteDir] = tenantEnsureUploadsDirectory('site/header');

        try {
            $filename = 'logo-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
        } catch (Throwable $exception) {
            imagedestroy($image);
            throw new RuntimeException('No se pudo generar el nombre del archivo de logo.');
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

if (!function_exists('headerDeleteStoredLogo')) {
    function headerDeleteStoredLogo(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $normalized = ltrim($relativePath, '/');
        if (!tenantUploadsIsWithin($normalized, 'site/header')) {
            return;
        }
        $absolutePath = tenantUploadsAbsolutePath($normalized);
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}
