<?php
require_once __DIR__ . '/../admin/config.php';

if (!function_exists('getSiteHeaderSettings')) {
    function getSiteHeaderSettings(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [
            'logo_path' => null,
            'logo_label' => null
        ];

        try {
            $db = getAdminDb();
            $sql = 'SELECT logo_path, logo_label FROM site_header ORDER BY id ASC LIMIT 1';
            if ($result = $db->query($sql)) {
                if ($row = $result->fetch_assoc()) {
                    $logoPath = isset($row['logo_path']) ? trim((string) $row['logo_path']) : '';
                    $logoLabel = isset($row['logo_label']) ? trim((string) $row['logo_label']) : '';
                    $cache['logo_path'] = $logoPath !== '' ? $logoPath : null;
                    $cache['logo_label'] = $logoLabel !== '' ? $logoLabel : null;
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se mantiene el valor por defecto si la consulta falla.
        }

        return $cache;
    }
}

if (!function_exists('getSiteFooterSettings')) {
    function getSiteFooterSettings(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $defaultContact = "AGA Parts\n210 41st Street, #202\nBrooklyn, New York, 11232\nsales@aga-parts-demo.com\n+1 (347) 773-3247";
        $defaultRights = sprintf('© %s AGA Parts Demo. Todos los derechos reservados.', date('Y'));

        $cache = [
            'logo_path' => null,
            'contact_text' => $defaultContact,
            'rights_text' => $defaultRights
        ];

        try {
            $db = getAdminDb();
            $sql = 'SELECT logo_path, contact_text, rights_text FROM site_footer_settings ORDER BY id ASC LIMIT 1';
            if ($result = $db->query($sql)) {
                if ($row = $result->fetch_assoc()) {
                    $logoPath = isset($row['logo_path']) ? trim((string) $row['logo_path']) : '';
                    $contactText = isset($row['contact_text']) ? (string) $row['contact_text'] : '';
                    $rightsText = isset($row['rights_text']) ? trim((string) $row['rights_text']) : '';

                    if ($logoPath !== '') {
                        $cache['logo_path'] = $logoPath;
                    }
                    if ($contactText !== '') {
                        $cache['contact_text'] = $contactText;
                    }
                    if ($rightsText !== '') {
                        $cache['rights_text'] = $rightsText;
                    }
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se usan valores por defecto si falla la consulta.
        }

        return $cache;
    }
}

if (!function_exists('getSiteFooterSocials')) {
    function getSiteFooterSocials(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $defaults = [
            ['name' => 'Facebook', 'url' => '#', 'icon_key' => 'facebook', 'icon_path' => null],
            ['name' => 'Instagram', 'url' => '#', 'icon_key' => 'instagram', 'icon_path' => null],
            ['name' => 'YouTube', 'url' => '#', 'icon_key' => 'youtube', 'icon_path' => null],
            ['name' => 'TikTok', 'url' => '#', 'icon_key' => 'tiktok', 'icon_path' => null],
        ];

        $cache = $defaults;

        try {
            $db = getAdminDb();
            $rows = [];
            $sql = 'SELECT name, url, icon_key, icon_path FROM site_footer_socials ORDER BY sort_order ASC, id ASC';
            if ($result = $db->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $name = trim((string) ($row['name'] ?? ''));
                    $url = trim((string) ($row['url'] ?? ''));
                    $iconKey = isset($row['icon_key']) ? trim((string) $row['icon_key']) : '';
                    $iconPath = isset($row['icon_path']) ? trim((string) $row['icon_path']) : '';

                    if ($name === '' || $url === '' || $url === '#') {
                        continue;
                    }

                    $rows[] = [
                        'name' => $name,
                        'url' => $url,
                        'icon_key' => $iconKey !== '' ? $iconKey : null,
                        'icon_path' => $iconPath !== '' ? $iconPath : null,
                    ];
                }
                $result->free();
            }

            if ($rows) {
                $cache = $rows;
            }
        } catch (Throwable $exception) {
            // Se mantiene la lista por defecto si ocurre algún error.
        }

        return $cache;
    }
}
