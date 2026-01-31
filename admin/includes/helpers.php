<?php
if (!function_exists('adminBasePath')) {
    function adminBasePath(): string
    {
        static $basePath = null;
        if ($basePath !== null) {
            return $basePath;
        }
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/admin/index.php');
        $position = strpos($scriptName, '/admin/');
        $basePath = $position !== false ? substr($scriptName, 0, $position + 6) : '/admin';
        return rtrim($basePath, '/');
    }
}

if (!function_exists('adminUrl')) {
    function adminUrl(string $path = ''): string
    {
        $base = adminBasePath();
        $trimmed = ltrim($path, '/');
        return $trimmed === '' ? $base . '/' : $base . '/' . $trimmed;
    }
}

if (!function_exists('adminPublicBasePath')) {
    function adminPublicBasePath(): string
    {
        $base = adminBasePath();
        $position = strrpos($base, '/admin');
        if ($position === false) {
            return rtrim($base, '/');
        }
        $public = substr($base, 0, $position);
        return rtrim($public, '/');
    }
}

if (!function_exists('adminAssetUrl')) {
    function adminAssetUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }

        $trimmed = ltrim($path, '/');
        $publicBase = adminPublicBasePath();

        if ($publicBase === '') {
            return '/' . $trimmed;
        }

        return $publicBase . '/' . $trimmed;
    }
}
