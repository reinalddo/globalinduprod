<?php
if (!function_exists('tenantNormalizeHost')) {
    function tenantNormalizeHost(string $host): string
    {
        $trimmed = strtolower(trim($host));
        if ($trimmed === '') {
            return '';
        }
        $parts = explode(':', $trimmed, 2);
        return $parts[0];
    }
}

if (!function_exists('tenantConfigData')) {
    function tenantConfigData(): array
    {
        static $config = null;
        if ($config === null) {
            $configFile = __DIR__ . '/tenants.php';
            $config = is_file($configFile) ? require $configFile : [];
        }
        return $config;
    }
}

if (!function_exists('tenantResolveCurrent')) {
    function tenantResolveCurrent(): array
    {
        static $resolved = null;
        if ($resolved !== null) {
            return $resolved;
        }

        $config = tenantConfigData();
        $tenants = $config['tenants'] ?? [];
        $defaultKey = $config['default'] ?? array_key_first($tenants);
        $host = tenantNormalizeHost($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''));

        $selectedKey = $defaultKey;
        if ($host !== '') {
            foreach ($tenants as $key => $tenant) {
                foreach ($tenant['domains'] ?? [] as $domain) {
                    if ($host === tenantNormalizeHost((string) $domain)) {
                        $selectedKey = $key;
                        break 2;
                    }
                }
            }
        }

        if ($selectedKey === null || !isset($tenants[$selectedKey])) {
            $selectedKey = array_key_first($tenants);
        }

        $resolved = $tenants[$selectedKey] ?? [];
        $resolved['_key'] = $selectedKey;
        return $resolved;
    }
}

if (!function_exists('tenantInfo')) {
    function tenantInfo(?string $key = null, mixed $default = null): mixed
    {
        $tenant = tenantResolveCurrent();
        if ($key === null) {
            return $tenant;
        }
        if (array_key_exists($key, $tenant)) {
            return $tenant[$key];
        }
        return $default;
    }
}

if (!function_exists('tenantLabels')) {
    function tenantLabels(): array
    {
        $labels = tenantInfo('labels');
        if (!is_array($labels)) {
            return [];
        }
        return $labels;
    }
}

if (!function_exists('tenantText')) {
    function tenantText(string $key, ?string $default = null): string
    {
        $labels = tenantLabels();
        if (array_key_exists($key, $labels)) {
            return (string) $labels[$key];
        }
        if ($default !== null) {
            return $default;
        }
        return $key;
    }
}

if (!function_exists('tenantLanguageCode')) {
    function tenantLanguageCode(): string
    {
        $code = tenantInfo('language_code');
        if (is_string($code) && $code !== '') {
            return $code;
        }
        return 'es';
    }
}

if (!function_exists('tenantLocale')) {
    function tenantLocale(): string
    {
        $locale = tenantInfo('locale');
        if (is_string($locale) && $locale !== '') {
            return $locale;
        }
        return 'es_VE';
    }
}

if (!function_exists('tenantLang')) {
    function tenantLang(string $spanish, string $english): string
    {
        return tenantLanguageCode() === 'en' ? $english : $spanish;
    }
}

if (!function_exists('tenantHtml')) {
    function tenantHtml(string $spanish, string $english): string
    {
        return htmlspecialchars(tenantLang($spanish, $english), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tenantProjectRoot')) {
    function tenantProjectRoot(): string
    {
        static $root = null;
        if ($root === null) {
            $root = dirname(__DIR__);
        }
        return $root;
    }
}

if (!function_exists('tenantStorageConfig')) {
    function tenantStorageConfig(): array
    {
        $storage = tenantInfo('storage');
        if (!is_array($storage)) {
            $storage = [];
        }
        return $storage;
    }
}

if (!function_exists('tenantUploadsBaseDir')) {
    function tenantUploadsBaseDir(): string
    {
        $storage = tenantStorageConfig();
        $base = isset($storage['relative_root']) ? (string) $storage['relative_root'] : 'uploads';
        $base = trim($base);
        if ($base === '') {
            $base = 'uploads';
        }
        return trim($base, '/');
    }
}

if (!function_exists('tenantUploadsRelative')) {
    function tenantUploadsRelative(string $path = ''): string
    {
        $base = tenantUploadsBaseDir();
        if ($path === '') {
            return $base;
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('tenantUploadsAbsolute')) {
    function tenantUploadsAbsolute(string $path = ''): string
    {
        $relative = tenantUploadsRelative($path);
        return tenantProjectRoot() . '/' . $relative;
    }
}

if (!function_exists('tenantUploadsPair')) {
    function tenantUploadsPair(string $subdirectory = ''): array
    {
        $relative = tenantUploadsRelative($subdirectory);
        $absolute = tenantProjectRoot() . '/' . $relative;
        return [$relative, $absolute];
    }
}

if (!function_exists('tenantEnsureUploadsDirectory')) {
    function tenantEnsureUploadsDirectory(string $subdirectory): array
    {
        [$relative, $absolute] = tenantUploadsPair($subdirectory);
        if (!is_dir($absolute)) {
            if (!mkdir($absolute, 0775, true) && !is_dir($absolute)) {
                throw new RuntimeException('No se pudo preparar la carpeta de archivos.');
            }
        }
        return [$relative, $absolute];
    }
}

if (!function_exists('tenantUploadsPublic')) {
    function tenantUploadsPublic(string $path = ''): string
    {
        $relative = tenantUploadsRelative($path);
        return '/' . ltrim($relative, '/');
    }
}

if (!function_exists('tenantUploadsIsWithin')) {
    function tenantUploadsIsWithin(string $relativePath, string $subdirectory): bool
    {
        $normalized = ltrim($relativePath, '/');
        [$relative] = tenantUploadsPair($subdirectory);
        $prefix = rtrim($relative, '/') . '/';
        if ($prefix === '/') {
            return true;
        }
        return str_starts_with($normalized, $prefix);
    }
}

if (!function_exists('tenantUploadsAbsolutePath')) {
    function tenantUploadsAbsolutePath(string $relativePath): string
    {
        $normalized = ltrim($relativePath, '/');
        return tenantProjectRoot() . '/' . $normalized;
    }
}
