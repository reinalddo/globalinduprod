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
