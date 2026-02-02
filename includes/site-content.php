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

if (!function_exists('getServicesPageHeroContent')) {
    function getServicesPageHeroContent(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [
            'kicker' => 'Portafolio integral',
            'title' => 'Soluciones industriales para cada etapa de su operación',
            'description' => 'Coordinamos logística binacional y ejecutamos servicios técnicos certificados para petróleo, minería, energía, puertos y agroindustria.',
            'listing_title' => 'Nuestros pilares de servicio',
            'listing_description' => 'Seleccionamos nueve líneas estratégicas que integran construcción, mantenimiento, logística, seguridad y talento humano para garantizar operaciones continuas en entornos críticos.',
            'image_path' => null,
        ];

        try {
            $db = getAdminDb();
            $sql = 'SELECT kicker, title, description, listing_title, listing_description, image_path FROM services_page_hero ORDER BY id ASC LIMIT 1';
            if ($result = $db->query($sql)) {
                if ($row = $result->fetch_assoc()) {
                    $kicker = isset($row['kicker']) ? trim((string) $row['kicker']) : '';
                    $title = isset($row['title']) ? trim((string) $row['title']) : '';
                    $description = isset($row['description']) ? trim((string) $row['description']) : '';
                    $listingTitle = isset($row['listing_title']) ? trim((string) $row['listing_title']) : '';
                    $listingDescription = isset($row['listing_description']) ? trim((string) $row['listing_description']) : '';
                    $imagePath = isset($row['image_path']) ? trim((string) $row['image_path']) : '';

                    if ($kicker !== '') {
                        $cache['kicker'] = $kicker;
                    }
                    if ($title !== '') {
                        $cache['title'] = $title;
                    }
                    if ($description !== '') {
                        $cache['description'] = $description;
                    }
                    if ($listingTitle !== '') {
                        $cache['listing_title'] = $listingTitle;
                    }
                    if ($listingDescription !== '') {
                        $cache['listing_description'] = $listingDescription;
                    }
                    if ($imagePath !== '') {
                        $cache['image_path'] = preg_match('/^https?:\/\//i', $imagePath) || str_starts_with($imagePath, 'data:')
                            ? $imagePath
                            : '/' . ltrim($imagePath, '/');
                    }
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se mantienen los valores predeterminados si ocurre un error.
        }

        return $cache;
    }
}

if (!function_exists('getServicesListData')) {
    function getServicesListData(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [];

        try {
            $db = getAdminDb();
            $sql = 'SELECT slug, title, summary, hero_image_path, kicker FROM services ORDER BY title ASC';
            if ($result = $db->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                    $title = isset($row['title']) ? trim((string) $row['title']) : '';
                    $summary = isset($row['summary']) ? trim((string) $row['summary']) : '';
                    $imagePath = isset($row['hero_image_path']) ? trim((string) $row['hero_image_path']) : '';

                    if ($slug === '' || $title === '' || $summary === '' || $imagePath === '') {
                        continue;
                    }

                    $normalizedImage = preg_match('/^https?:\/\//i', $imagePath) || str_starts_with($imagePath, 'data:')
                        ? $imagePath
                        : '/' . ltrim($imagePath, '/');

                    $cache[] = [
                        'slug' => $slug,
                        'title' => $title,
                        'summary' => $summary,
                        'image_path' => $normalizedImage,
                        'kicker' => isset($row['kicker']) ? trim((string) $row['kicker']) : '',
                    ];
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se devuelve la lista vacía si ocurre un error.
        }

        return $cache;
    }
}

if (!function_exists('getServiceBySlug')) {
    function getServiceBySlug(string $slug): ?array
    {
        $normalizedSlug = trim($slug);
        if ($normalizedSlug === '') {
            return null;
        }

        try {
            $db = getAdminDb();
        } catch (Throwable $exception) {
            return null;
        }

        $stmt = $db->prepare('SELECT id, slug, kicker, title, summary, hero_image_path, content_html FROM services WHERE slug = ? LIMIT 1');
        if ($stmt === false) {
            return null;
        }

        $stmt->bind_param('s', $normalizedSlug);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $stmt->bind_result($id, $storedSlug, $kicker, $title, $summary, $heroImage, $contentHtml);
        $service = null;
        if ($stmt->fetch()) {
            $path = trim((string) $heroImage);
            $heroPath = null;
            if ($path !== '') {
                $heroPath = preg_match('/^https?:\/\//i', $path) || str_starts_with($path, 'data:')
                    ? $path
                    : '/' . ltrim($path, '/');
            }

            $service = [
                'id' => (int) $id,
                'slug' => (string) $storedSlug,
                'kicker' => (string) ($kicker ?? ''),
                'title' => (string) ($title ?? ''),
                'summary' => (string) ($summary ?? ''),
                'hero_image_path' => $heroPath,
                'content_html' => (string) ($contentHtml ?? '')
            ];
        }

        $stmt->close();
        return $service;
    }
}

if (!function_exists('getServiceGalleryImages')) {
    function getServiceGalleryImages(int $serviceId): array
    {
        if ($serviceId <= 0) {
            return [];
        }

        try {
            $db = getAdminDb();
        } catch (Throwable $exception) {
            return [];
        }

        $stmt = $db->prepare('SELECT image_path FROM service_gallery_images WHERE service_id = ? ORDER BY sort_order ASC, id ASC');
        if ($stmt === false) {
            return [];
        }

        $stmt->bind_param('i', $serviceId);
        $gallery = [];
        if ($stmt->execute()) {
            $stmt->bind_result($imagePath);
            while ($stmt->fetch()) {
                $stored = trim((string) $imagePath);
                if ($stored === '') {
                    continue;
                }
                $gallery[] = preg_match('/^https?:\/\//i', $stored) || str_starts_with($stored, 'data:')
                    ? $stored
                    : '/' . ltrim($stored, '/');
            }
        }
        $stmt->close();

        return $gallery;
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

if (!function_exists('getSiteBrandAssets')) {
    function getSiteBrandAssets(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [
            'favicon_path' => null,
            'updated_at' => null
        ];

        try {
            $db = getAdminDb();
            $sql = 'SELECT favicon_path, updated_at FROM site_brand_assets ORDER BY id ASC LIMIT 1';
            if ($result = $db->query($sql)) {
                if ($row = $result->fetch_assoc()) {
                    $faviconPath = isset($row['favicon_path']) ? trim((string) $row['favicon_path']) : '';
                    $cache['favicon_path'] = $faviconPath !== '' ? $faviconPath : null;
                    if (isset($row['updated_at'])) {
                        $cache['updated_at'] = (string) $row['updated_at'];
                    }
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se mantiene el valor por defecto si ocurre un error.
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

if (!function_exists('getHomeHighlightCopy')) {
    function getHomeHighlightCopy(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $defaults = [
            'allies' => [
                'title' => 'Nuestros Aliados Estratégicos',
                'body' => 'Contamos con más de 300 aliados estratégicos comerciales alrededor del mundo, los cuales nos han dado su representación y nos brindan respaldo total para nuestros clientes, a su vez, damos mayor capacidad de respuesta a nivel nacional e internacional.'
            ],
            'services' => [
                'title' => 'Servicios para operaciones industriales críticas',
                'body' => 'Integramos construcción, logística, mantenimiento, talento humano y seguridad industrial para garantizar continuidad operativa en refinerías, puertos, minería, agroindustria y proyectos de energía.'
            ],
            'clients' => [
                'title' => 'Clientes que confían en Global Induprod',
                'body' => 'Respaldamos operaciones públicas y privadas con suministros, ingeniería y soporte técnico integral para mantener en marcha proyectos energéticos, industriales y de infraestructura en toda Venezuela.'
            ],
        ];

        $cache = $defaults;

        try {
            $db = getAdminDb();
            $sql = 'SELECT section_key, title, body FROM home_section_copy';
            if ($result = $db->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $key = isset($row['section_key']) ? trim((string) $row['section_key']) : '';
                    if ($key === '' || !isset($cache[$key])) {
                        continue;
                    }

                    $title = isset($row['title']) ? trim((string) $row['title']) : '';
                    $body = isset($row['body']) ? trim((string) $row['body']) : '';

                    if ($title !== '') {
                        $cache[$key]['title'] = $title;
                    }
                    if ($body !== '') {
                        $cache[$key]['body'] = $body;
                    }
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se mantienen los valores por defecto si ocurre un error.
        }

        return $cache;
    }
}

if (!function_exists('getHomeAllies')) {
    function getHomeAllies(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [];

        try {
            $db = getAdminDb();
            $sql = 'SELECT name, logo_path, is_primary, sort_order FROM home_allies ORDER BY is_primary DESC, sort_order ASC, id ASC';
            if ($result = $db->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $name = isset($row['name']) ? trim((string) $row['name']) : '';
                    $logoPath = isset($row['logo_path']) ? trim((string) $row['logo_path']) : '';

                    if ($name === '' || $logoPath === '') {
                        continue;
                    }

                    $cache[] = [
                        'name' => $name,
                        'logo_path' => $logoPath,
                        'is_primary' => (int) ($row['is_primary'] ?? 0) === 1,
                        'sort_order' => (int) ($row['sort_order'] ?? 0),
                    ];
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se devuelve la lista vacía si la consulta falla.
        }

        return $cache;
    }
}

if (!function_exists('getHomeClients')) {
    function getHomeClients(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [];

        try {
            $db = getAdminDb();
            $sql = 'SELECT name, logo_path, sort_order FROM home_clients ORDER BY sort_order ASC, id ASC';
            if ($result = $db->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $name = isset($row['name']) ? trim((string) $row['name']) : '';
                    $logoPath = isset($row['logo_path']) ? trim((string) $row['logo_path']) : '';

                    if ($name === '' || $logoPath === '') {
                        continue;
                    }

                    $cache[] = [
                        'name' => $name,
                        'logo_path' => $logoPath,
                        'sort_order' => (int) ($row['sort_order'] ?? 0),
                    ];
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se devuelve la lista vacía si la consulta falla.
        }

        return $cache;
    }
}

if (!function_exists('getHomeServices')) {
    function getHomeServices(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [];

        try {
            $db = getAdminDb();
            $sql = 'SELECT slug, title, summary, hero_image_path, kicker FROM services WHERE is_featured_home = 1 ORDER BY updated_at DESC, title ASC';
            if ($result = $db->query($sql)) {
                while ($row = $result->fetch_assoc()) {
                    $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                    $title = isset($row['title']) ? trim((string) $row['title']) : '';
                    $summary = isset($row['summary']) ? trim((string) $row['summary']) : '';
                    $imagePath = isset($row['hero_image_path']) ? trim((string) $row['hero_image_path']) : '';

                    if ($slug === '' || $title === '' || $summary === '' || $imagePath === '') {
                        continue;
                    }

                    $cache[] = [
                        'slug' => $slug,
                        'title' => $title,
                        'summary' => $summary,
                        'hero_image_path' => $imagePath,
                        'kicker' => isset($row['kicker']) ? trim((string) $row['kicker']) : '',
                    ];
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se devuelve la lista vacía si ocurre algún error.
        }

        return $cache;
    }
}

if (!function_exists('getHomeHeroSlides')) {
    function getHomeHeroSlides(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $defaults = [
            [
                'image' => 'inicio/hero/hero.png',
                'tagline' => 'Proveedor global',
                'title' => 'Repuestos y soluciones para maquinaria pesada',
                'copy' => 'Distribuimos componentes originales y alternativos para flotas de minería, construcción y agricultura. Cobertura mundial, logística ágil y asesoría técnica especializada.',
                'cta_label' => null,
                'cta_url' => null,
            ],
            [
                'image' => 'inicio/hero/hero-02.svg',
                'tagline' => 'Integración industrial',
                'title' => 'Ingeniería, mantenimiento y talento técnico 360°',
                'copy' => 'Coordinamos proyectos EPC, planes predictivos y formación especializada para maximizar la disponibilidad operativa en refinerías, puertos y plantas de proceso.',
                'cta_label' => null,
                'cta_url' => null,
            ],
            [
                'image' => 'inicio/hero/hero-03.svg',
                'tagline' => 'Cobertura latinoamericana',
                'title' => 'Logística binacional y respuesta inmediata',
                'copy' => 'Centros de distribución en América y alianzas estratégicas que aseguran inventarios críticos, embarques puerta a puerta y soporte en sitio 24/7.',
                'cta_label' => null,
                'cta_url' => null,
            ],
        ];

        $cache = $defaults;

        try {
            $db = getAdminDb();
            $sql = 'SELECT image_path, message_small, title, description, cta_label, cta_url FROM home_hero_slides WHERE is_active = 1 ORDER BY sort_order ASC, id ASC';
            if ($result = $db->query($sql)) {
                $rows = [];
                while ($row = $result->fetch_assoc()) {
                    $image = isset($row['image_path']) ? trim((string) $row['image_path']) : '';
                    $title = isset($row['title']) ? trim((string) $row['title']) : '';
                    if ($image === '' || $title === '') {
                        continue;
                    }

                    $tagline = isset($row['message_small']) ? trim((string) $row['message_small']) : '';
                    $description = isset($row['description']) ? trim((string) $row['description']) : '';
                    $ctaLabel = isset($row['cta_label']) ? trim((string) $row['cta_label']) : '';
                    $ctaUrl = isset($row['cta_url']) ? trim((string) $row['cta_url']) : '';

                    if (!preg_match('/^https?:\/\//i', $image) && !str_starts_with($image, 'data:')) {
                        $image = '/' . ltrim($image, '/');
                    }

                    $rows[] = [
                        'image' => $image,
                        'tagline' => $tagline !== '' ? $tagline : null,
                        'title' => $title,
                        'copy' => $description !== '' ? $description : null,
                        'cta_label' => $ctaLabel !== '' ? $ctaLabel : null,
                        'cta_url' => $ctaUrl !== '' ? $ctaUrl : null,
                    ];
                }
                $result->free();

                if ($rows) {
                    $cache = $rows;
                }
            }
        } catch (Throwable $exception) {
            // Se usan los valores predeterminados si falla la consulta.
        }

        return $cache;
    }
}
