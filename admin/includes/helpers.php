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
