<?php
if (!function_exists('contactEnsureIdentifier')) {
    function contactEnsureIdentifier(string $value): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_]+$/', $value);
    }
}

if (!function_exists('contactEnsureColumn')) {
    function contactEnsureColumn(mysqli $db, string $table, string $column, string $definition): void
    {
        if (!contactEnsureIdentifier($table) || !contactEnsureIdentifier($column)) {
            throw new InvalidArgumentException('Identificador no válido para el esquema de contacto.');
        }

        $stmt = $db->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1');
        if ($stmt === false) {
            return;
        }

        $stmt->bind_param('ss', $table, $column);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->close();
                return;
            }
        }
        $stmt->close();

        $sql = sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition);
        try {
            $db->query($sql);
        } catch (Throwable $exception) {
            // Si falla (por ejemplo, columna ya creada en otra ejecución), se ignora.
        }
    }
}

if (!function_exists('contactDeleteStoredAsset')) {
    function contactDeleteStoredAsset(?string $relativePath): void
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

if (!function_exists('contactSaveMailLogo')) {
    function contactSaveMailLogo(array $file): string
    {
        if (!isset($file['error'], $file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo procesar el logo del correo.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('La carga del logo no es válida.');
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        if (!$mime || !isset($allowed[$mime])) {
            throw new RuntimeException('El logo debe ser JPG, PNG o WebP.');
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
            throw new RuntimeException('No se pudo leer el logo cargado.');
        }

        $maxWidth = 600;
        $maxHeight = 300;
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
        $relativeDir = 'uploads/contact/email';
        $absoluteDir = $projectRoot . '/' . $relativeDir;

        if (!is_dir($absoluteDir)) {
            if (!mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
                imagedestroy($image);
                throw new RuntimeException('No se pudo preparar la carpeta para logos.');
            }
        }

        try {
            $filename = 'contact-logo-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
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
                    throw new RuntimeException('El servidor no puede optimizar imágenes WebP.');
                }
                imagewebp($image, $targetPath, 80);
                break;
        }

        imagedestroy($image);

        if (!is_file($targetPath)) {
            throw new RuntimeException('No se pudo guardar el logo optimizado.');
        }

        return $relativeDir . '/' . $filename;
    }
}

if (!function_exists('contactEnsureSchema')) {
    function contactEnsureSchema(mysqli $db): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS contact_page_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_title VARCHAR(200) NOT NULL,
    content_html MEDIUMTEXT NOT NULL,
    phone_placeholder VARCHAR(120) NOT NULL DEFAULT '',
    map_embed TEXT NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    hero_kicker VARCHAR(150) NOT NULL DEFAULT '',
    hero_description TEXT NOT NULL,
    smtp_host VARCHAR(255) NOT NULL DEFAULT '',
    smtp_port INT NOT NULL DEFAULT 587,
    smtp_username VARCHAR(255) NOT NULL DEFAULT '',
    smtp_password VARCHAR(255) NOT NULL DEFAULT '',
    smtp_encryption VARCHAR(20) NOT NULL DEFAULT '',
    smtp_auth TINYINT(1) NOT NULL DEFAULT 1,
    smtp_from_email VARCHAR(255) NOT NULL DEFAULT '',
    smtp_from_name VARCHAR(255) NOT NULL DEFAULT '',
    email_subject VARCHAR(255) NOT NULL DEFAULT '',
    email_logo_path VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $db->query($sql);

        contactEnsureColumn($db, 'contact_page_settings', 'hero_kicker', "VARCHAR(150) NOT NULL DEFAULT ''");
        contactEnsureColumn($db, 'contact_page_settings', 'hero_description', 'TEXT NOT NULL');
        contactEnsureColumn($db, 'contact_page_settings', 'smtp_host', "VARCHAR(255) NOT NULL DEFAULT ''");
        contactEnsureColumn($db, 'contact_page_settings', 'smtp_port', 'INT NOT NULL DEFAULT 587');
        contactEnsureColumn($db, 'contact_page_settings', 'smtp_username', "VARCHAR(255) NOT NULL DEFAULT ''");
        contactEnsureColumn($db, 'contact_page_settings', 'smtp_password', "VARCHAR(255) NOT NULL DEFAULT ''");
        contactEnsureColumn($db, 'contact_page_settings', 'smtp_encryption', "VARCHAR(20) NOT NULL DEFAULT ''");
        contactEnsureColumn($db, 'contact_page_settings', 'smtp_auth', 'TINYINT(1) NOT NULL DEFAULT 1');
        contactEnsureColumn($db, 'contact_page_settings', 'smtp_from_email', "VARCHAR(255) NOT NULL DEFAULT ''");
        contactEnsureColumn($db, 'contact_page_settings', 'smtp_from_name', "VARCHAR(255) NOT NULL DEFAULT ''");
        contactEnsureColumn($db, 'contact_page_settings', 'email_subject', "VARCHAR(255) NOT NULL DEFAULT ''");
        contactEnsureColumn($db, 'contact_page_settings', 'email_logo_path', "VARCHAR(255) NOT NULL DEFAULT ''");

        try {
            $db->query("UPDATE contact_page_settings SET hero_description = '' WHERE hero_description IS NULL");
        } catch (Throwable $exception) {
            // Ignorado si el motor no admite la instrucción.
        }
    }
}

if (!function_exists('contactFetchSettings')) {
    function contactFetchSettings(mysqli $db): array
    {
        $defaults = [
            'id' => null,
            'hero_title' => '',
            'hero_kicker' => '',
            'hero_description' => '',
            'content_html' => '',
            'phone_placeholder' => '',
            'map_embed' => '',
            'contact_email' => '',
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => '',
            'smtp_auth' => 1,
            'smtp_from_email' => '',
            'smtp_from_name' => '',
            'email_subject' => '',
            'email_logo_path' => ''
        ];

        $sql = 'SELECT id, hero_title, hero_kicker, hero_description, content_html, phone_placeholder, map_embed, contact_email, smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption, smtp_auth, smtp_from_email, smtp_from_name, email_subject, email_logo_path FROM contact_page_settings ORDER BY id ASC LIMIT 1';
        if ($result = $db->query($sql)) {
            if ($row = $result->fetch_assoc()) {
                $defaults = [
                    'id' => (int) $row['id'],
                    'hero_title' => (string) $row['hero_title'],
                    'hero_kicker' => isset($row['hero_kicker']) ? (string) $row['hero_kicker'] : '',
                    'hero_description' => isset($row['hero_description']) ? (string) $row['hero_description'] : '',
                    'content_html' => (string) $row['content_html'],
                    'phone_placeholder' => (string) $row['phone_placeholder'],
                    'map_embed' => (string) $row['map_embed'],
                    'contact_email' => (string) $row['contact_email'],
                    'smtp_host' => isset($row['smtp_host']) ? (string) $row['smtp_host'] : '',
                    'smtp_port' => isset($row['smtp_port']) ? (int) $row['smtp_port'] : 587,
                    'smtp_username' => isset($row['smtp_username']) ? (string) $row['smtp_username'] : '',
                    'smtp_password' => isset($row['smtp_password']) ? (string) $row['smtp_password'] : '',
                    'smtp_encryption' => isset($row['smtp_encryption']) ? (string) $row['smtp_encryption'] : '',
                    'smtp_auth' => isset($row['smtp_auth']) ? (int) $row['smtp_auth'] : 1,
                    'smtp_from_email' => isset($row['smtp_from_email']) ? (string) $row['smtp_from_email'] : '',
                    'smtp_from_name' => isset($row['smtp_from_name']) ? (string) $row['smtp_from_name'] : '',
                    'email_subject' => isset($row['email_subject']) ? (string) $row['email_subject'] : '',
                    'email_logo_path' => isset($row['email_logo_path']) ? (string) $row['email_logo_path'] : ''
                ];
            }
            $result->free();
        }

        return $defaults;
    }
}

if (!function_exists('contactSaveSettings')) {
    function contactSaveSettings(mysqli $db, array $settings): void
    {
        $id = isset($settings['id']) ? (int) $settings['id'] : 0;
        $heroTitle = (string) ($settings['hero_title'] ?? '');
        $heroKicker = (string) ($settings['hero_kicker'] ?? '');
        $heroDescription = (string) ($settings['hero_description'] ?? '');
        $contentHtml = (string) ($settings['content_html'] ?? '');
        $phonePlaceholder = (string) ($settings['phone_placeholder'] ?? '');
        $mapEmbed = (string) ($settings['map_embed'] ?? '');
        $contactEmail = (string) ($settings['contact_email'] ?? '');
        $smtpHost = (string) ($settings['smtp_host'] ?? '');
        $smtpPort = isset($settings['smtp_port']) ? (int) $settings['smtp_port'] : 587;
        $smtpUsername = (string) ($settings['smtp_username'] ?? '');
        $smtpPassword = (string) ($settings['smtp_password'] ?? '');
        $smtpEncryption = (string) ($settings['smtp_encryption'] ?? '');
        $smtpAuth = isset($settings['smtp_auth']) ? (int) $settings['smtp_auth'] : 1;
        $smtpFromEmail = (string) ($settings['smtp_from_email'] ?? '');
        $smtpFromName = (string) ($settings['smtp_from_name'] ?? '');
        $emailSubject = (string) ($settings['email_subject'] ?? '');
        $emailLogoPath = (string) ($settings['email_logo_path'] ?? '');

        if ($id > 0) {
            $stmt = $db->prepare('UPDATE contact_page_settings SET hero_title = ?, hero_kicker = ?, hero_description = ?, content_html = ?, phone_placeholder = ?, map_embed = ?, contact_email = ?, smtp_host = ?, smtp_port = ?, smtp_username = ?, smtp_password = ?, smtp_encryption = ?, smtp_auth = ?, smtp_from_email = ?, smtp_from_name = ?, email_subject = ?, email_logo_path = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar la actualización de la sección de contacto.');
            }
            $stmt->bind_param('ssssssssisssissssi', $heroTitle, $heroKicker, $heroDescription, $contentHtml, $phonePlaceholder, $mapEmbed, $contactEmail, $smtpHost, $smtpPort, $smtpUsername, $smtpPassword, $smtpEncryption, $smtpAuth, $smtpFromEmail, $smtpFromName, $emailSubject, $emailLogoPath, $id);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudo actualizar la sección de contacto.');
            }
            $stmt->close();
        } else {
            $stmt = $db->prepare('INSERT INTO contact_page_settings (hero_title, hero_kicker, hero_description, content_html, phone_placeholder, map_embed, contact_email, smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption, smtp_auth, smtp_from_email, smtp_from_name, email_subject, email_logo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar el registro de la sección de contacto.');
            }
            $stmt->bind_param('ssssssssisssissss', $heroTitle, $heroKicker, $heroDescription, $contentHtml, $phonePlaceholder, $mapEmbed, $contactEmail, $smtpHost, $smtpPort, $smtpUsername, $smtpPassword, $smtpEncryption, $smtpAuth, $smtpFromEmail, $smtpFromName, $emailSubject, $emailLogoPath);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudo registrar la sección de contacto.');
            }
            $stmt->close();
        }
    }
}
