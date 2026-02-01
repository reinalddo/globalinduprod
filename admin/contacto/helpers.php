<?php
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        $db->query($sql);
    }
}

if (!function_exists('contactFetchSettings')) {
    function contactFetchSettings(mysqli $db): array
    {
        $defaults = [
            'id' => null,
            'hero_title' => '',
            'content_html' => '',
            'phone_placeholder' => '',
            'map_embed' => '',
            'contact_email' => ''
        ];

        if ($result = $db->query('SELECT id, hero_title, content_html, phone_placeholder, map_embed, contact_email FROM contact_page_settings ORDER BY id ASC LIMIT 1')) {
            if ($row = $result->fetch_assoc()) {
                $defaults = [
                    'id' => (int) $row['id'],
                    'hero_title' => (string) $row['hero_title'],
                    'content_html' => (string) $row['content_html'],
                    'phone_placeholder' => (string) $row['phone_placeholder'],
                    'map_embed' => (string) $row['map_embed'],
                    'contact_email' => (string) $row['contact_email']
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
        $contentHtml = (string) ($settings['content_html'] ?? '');
        $phonePlaceholder = (string) ($settings['phone_placeholder'] ?? '');
        $mapEmbed = (string) ($settings['map_embed'] ?? '');
        $contactEmail = (string) ($settings['contact_email'] ?? '');

        if ($id > 0) {
            $stmt = $db->prepare('UPDATE contact_page_settings SET hero_title = ?, content_html = ?, phone_placeholder = ?, map_embed = ?, contact_email = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar la actualización de la sección de contacto.');
            }
            $stmt->bind_param('sssssi', $heroTitle, $contentHtml, $phonePlaceholder, $mapEmbed, $contactEmail, $id);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudo actualizar la sección de contacto.');
            }
            $stmt->close();
        } else {
            $stmt = $db->prepare('INSERT INTO contact_page_settings (hero_title, content_html, phone_placeholder, map_embed, contact_email) VALUES (?, ?, ?, ?, ?)');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar el registro de la sección de contacto.');
            }
            $stmt->bind_param('sssss', $heroTitle, $contentHtml, $phonePlaceholder, $mapEmbed, $contactEmail);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudo registrar la sección de contacto.');
            }
            $stmt->close();
        }
    }
}
