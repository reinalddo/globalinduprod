<?php
if (!function_exists('brandEnsureSchema')) {
    function brandEnsureSchema(mysqli $db): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS site_brand_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    favicon_path VARCHAR(255) NOT NULL DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $db->query($sql);
    }
}

if (!function_exists('brandFetchSettings')) {
    function brandFetchSettings(mysqli $db): array
    {
        $settings = [
            'id' => null,
            'favicon_path' => '',
            'updated_at' => ''
        ];

        if ($result = $db->query('SELECT id, favicon_path, updated_at FROM site_brand_assets ORDER BY id ASC LIMIT 1')) {
            if ($row = $result->fetch_assoc()) {
                $settings = [
                    'id' => (int) $row['id'],
                    'favicon_path' => (string) $row['favicon_path'],
                    'updated_at' => isset($row['updated_at']) ? (string) $row['updated_at'] : ''
                ];
            }
            $result->free();
        }

        return $settings;
    }
}

if (!function_exists('brandSaveFaviconFile')) {
    function brandSaveFaviconFile(array $file): string
    {
        if (!isset($file['error'], $file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo procesar el favicon enviado.');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('El archivo recibido no es válido.');
        }

        $allowedMimes = [
            'image/png' => 'png',
            'image/x-icon' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        if (!$mime || !isset($allowedMimes[$mime])) {
            throw new RuntimeException('El favicon debe ser PNG, WEBP, SVG o ICO.');
        }

        $extension = $allowedMimes[$mime];
        if ($extension === 'png' || $extension === 'webp') {
            $size = getimagesize($file['tmp_name']);
            if (!$size || $size[0] > 1024 || $size[1] > 1024) {
                throw new RuntimeException('El favicon no puede superar los 1024px de ancho o alto.');
            }
        }
        $maxFileSize = 1024 * 500;
        if (($file['size'] ?? 0) > $maxFileSize) {
            throw new RuntimeException('El favicon no debe superar los 500KB.');
        }

        $projectRoot = dirname(__DIR__, 2);
        $relativeDir = 'uploads/site/brand';
        $absoluteDir = $projectRoot . '/' . $relativeDir;

        if (!is_dir($absoluteDir)) {
            if (!mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
                throw new RuntimeException('No se pudo preparar la carpeta para los favicons.');
            }
        }

        try {
            $filename = 'favicon-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        } catch (Throwable $exception) {
            throw new RuntimeException('No se pudo generar el nombre del favicon.');
        }

        $targetPath = $absoluteDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('No se pudo guardar el favicon enviado.');
        }

        return $relativeDir . '/' . $filename;
    }
}

if (!function_exists('brandDeleteStoredFavicon')) {
    function brandDeleteStoredFavicon(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $projectRoot = dirname(__DIR__, 2);
        $absolute = realpath($projectRoot . '/' . ltrim($relativePath, '/'));
        $uploads = realpath($projectRoot . '/uploads/site/brand');
        if (!$absolute || !$uploads) {
            return;
        }

        $absoluteNorm = strtolower(str_replace('\\', '/', $absolute));
        $uploadsNorm = strtolower(str_replace('\\', '/', $uploads));
        if (!str_starts_with($absoluteNorm, $uploadsNorm)) {
            return;
        }

        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }
}
