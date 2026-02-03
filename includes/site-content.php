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

if (!function_exists('normalizeFrontAssetPath')) {
    function normalizeFrontAssetPath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $trimmed) || str_starts_with($trimmed, 'data:') || preg_match('/^[a-z0-9.+-]+:/i', $trimmed)) {
            return $trimmed;
        }

        return '/' . ltrim($trimmed, '/');
    }
}

if (!function_exists('getContactPageSettings')) {
    function getContactPageSettings(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $defaults = [
            'hero_title' => 'Estamos listos para apoyar su próximo proyecto',
            'hero_kicker' => 'Atención 24/7',
            'hero_description' => 'Comparta los números de parte, modelo de equipo y requerimientos logísticos. Nuestro equipo responderá con disponibilidad, tiempos de entrega y alternativas compatibles.',
            'content_html' => '<p><strong>Venezuela</strong><br>Global Induprod RL<br>Urbanización Colinas del Yurubí, Calle Polígono de Tiro, diagonal Hospital Pediátrico "Niño Jesús". Centro Profesional Spasso, Planta Baja Oficinas 02 y 03.<br>San Felipe, Yaracuy - Venezuela.</p><p><strong>Teléfonos:</strong> 0412-762.25.47 / 0424-574.04.08 / 0424-312.7477 / 0412-268.2883</p><p><strong>Correo:</strong> <a href="mailto:mauro@induprod.com">mauro@induprod.com</a></p>',
            'phone_placeholder' => '+58 412-0000000',
            'map_embed' => '',
            'contact_email' => 'mauro@induprod.com',
            'contact_whatsapp' => '',
            'contact_whatsapp_link' => null,
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => '',
            'smtp_auth' => 1,
            'smtp_from_email' => '',
            'smtp_from_name' => 'Formulario de Contacto',
            'email_subject' => 'Nuevo mensaje desde el sitio web',
            'email_logo_path' => null,
            'email_logo_url' => null,
            'email_logo_absolute' => null,
            'hero_image_path' => null,
            'hero_image_url' => null,
            'hero_image_absolute' => null,
        ];

        try {
            $db = getAdminDb();
        } catch (Throwable $exception) {
            $cache = $defaults;
            return $cache;
        }

        try {
            $sql = 'SELECT hero_title, hero_kicker, hero_description, hero_image_path, content_html, phone_placeholder, map_embed, contact_email, contact_whatsapp, smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption, smtp_auth, smtp_from_email, smtp_from_name, email_subject, email_logo_path FROM contact_page_settings ORDER BY id ASC LIMIT 1';
            if ($result = $db->query($sql)) {
                if ($row = $result->fetch_assoc()) {
                    foreach (['hero_title','hero_kicker','hero_description','content_html','phone_placeholder','map_embed','contact_email','contact_whatsapp','smtp_host','smtp_username','smtp_password','smtp_encryption','smtp_from_email','smtp_from_name','email_subject'] as $field) {
                        if (isset($row[$field]) && trim((string) $row[$field]) !== '') {
                            $defaults[$field] = (string) $row[$field];
                        }
                    }
                                        $digitsOnly = preg_replace('/\D+/', '', $defaults['contact_whatsapp']);
                                        if ($digitsOnly !== '') {
                                            $defaults['contact_whatsapp_link'] = 'https://wa.me/' . $digitsOnly;
                                        }
                    if (isset($row['smtp_port'])) {
                        $defaults['smtp_port'] = (int) $row['smtp_port'];
                    }
                    if (isset($row['smtp_auth'])) {
                        $defaults['smtp_auth'] = (int) $row['smtp_auth'] === 1 ? 1 : 0;
                    }

                    if (!empty($row['email_logo_path'])) {
                        $normalized = normalizeFrontAssetPath($row['email_logo_path']);
                        if ($normalized) {
                            $defaults['email_logo_path'] = $row['email_logo_path'];
                            $defaults['email_logo_url'] = $normalized;
                            $absolute = dirname(__DIR__) . '/' . ltrim($row['email_logo_path'], '/');
                            if (is_file($absolute)) {
                                $defaults['email_logo_absolute'] = $absolute;
                            }
                        }
                    }

                    if (!empty($row['hero_image_path'])) {
                        $normalizedHero = normalizeFrontAssetPath($row['hero_image_path']);
                        if ($normalizedHero) {
                            $defaults['hero_image_path'] = $row['hero_image_path'];
                            $defaults['hero_image_url'] = $normalizedHero;
                            $heroAbsolute = dirname(__DIR__) . '/' . ltrim($row['hero_image_path'], '/');
                            if (is_file($heroAbsolute)) {
                                $defaults['hero_image_absolute'] = $heroAbsolute;
                            }
                        }
                    }
                }
                $result->free();
            }
        } catch (Throwable $exception) {
            // Se mantienen los valores por defecto si falla la consulta.
        }

        $cache = $defaults;
        return $cache;
    }
}

if (!function_exists('getAboutPageContent')) {
    function getAboutPageContent(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $defaultHero = [
            'message_small' => 'Somos Global Induprod',
            'title' => 'Logística industrial y outsourcing integral para su operación',
            'description' => 'Más de 15 años suministrando, manteniendo y modernizando activos críticos para petróleo, minería, energía, puertos y agroindustria con cobertura binacional desde Venezuela y Estados Unidos.',
            'image_path' => null,
        ];

        $defaultSections = [
            'about_overview' => [
                'title' => 'Quiénes somos',
                'body' => "Global Induprod RL es una asociación cooperativa fundada en 2010 por un equipo de profesionales yaracuyanos convencidos de que la logística integral es un motor para el desarrollo industrial. Desde nuestros inicios hemos acompañado operaciones petroleras, mineras, agroindustriales y de infraestructura con una propuesta que combina suministros críticos, logística 24/7 y servicios múltiples especializados.\n\nTrabajamos como un aliado outsourcing enfocándonos en resolver necesidades técnicas, de mantenimiento y de fabricación con personal propio en campo, soporte administrativo y gestión documental completa. La apertura de Global Induprod International LLC en Miami en 2016 nos permite consolidar cadenas de suministro binacionales, acelerar procesos de importación y ofrecer repuesta inmediata con estándares OEM.",
            ],
            'about_history' => [
                'title' => 'Historia y evolución',
                'body' => "La experiencia acumulada desde 2008 y la formalización en 2010 marcaron el inicio de un crecimiento sostenido: ampliamos capacidades de ingeniería, incorporamos cuadrillas certificadas y sumamos más de 300 aliados estratégicos en América y Europa. Esta red de expertos respalda proyectos de fabricación, modernización y seguridad industrial con cumplimiento de normas API, ASTM, AWS y NFPA.\n\n2010: registro oficial de Global Induprod RL en San Felipe, Yaracuy, orientado a servicios industriales integrales.\n2016: inicio de operaciones en Miami para garantizar disponibilidad inmediata de repuestos y equipos de alta tecnología.\n2021 en adelante: expansión de servicios binacionales en minería, logística y gestión de residuos con cobertura 24/7.",
            ],
            'about_presence' => [
                'title' => 'Presencia operativa y contacto',
                'body' => "Coordinamos operaciones desde nuestras oficinas en San Felipe y Miami, atendiendo solicitudes nacionales e internacionales con un centro logístico ágil y canales directos de atención.\n\nVenezuela: Urbanización Colinas del Yurubí, Calle Polígono de Tiro, Centro Profesional Spasso, Planta Baja, Oficinas 02-03. San Felipe, Yaracuy.\nEstados Unidos: 9858 NW 43rd Terrace, Doral, Florida 33178.\nContacto: mauro@induprod.com | +58 424-574-0408 | +58 412-762-2547 | +1 786-294-1719.",
            ],
            'about_values' => [
                'title' => 'Valores que nos definen',
                'body' => "Estandarización: construimos y seguimos modelos que garantizan mejora continua y replicabilidad.\nTecnología: aplicamos recursos y procesos de vanguardia para entregar soluciones de alto impacto.\nCalidad: trabajamos bajo normas estrictas y auditorías técnicas que aseguran resultados verificables.\nOportunidad: administramos proyectos para cumplir tiempos de entrega acordados y soportar operaciones críticas.",
            ],
            'about_services' => [
                'title' => 'Servicios principales',
                'body' => "Construcción, obra civil, montajes industriales y rehabilitación de infraestructura.\nMantenimiento predictivo, preventivo y correctivo de plantas, calderas, bombas, maquinaria pesada y flotas especiales.\nServicios portuarios y aeronáuticos: overhaul de grúas pórtico, defensas, luminarias, protección contra incendios y salvataje.\nAlquiler de equipos especializados, embarcaciones, grúas y vehículos industriales.\nImportación, distribución y comercialización de repuestos, equipos médicos, transformadores, herramientas y consumibles.\nGestión integral de residuos, transporte de cargas especiales y logística nacional e internacional.\nCapital humano, outsourcing operacional, capacitación y seguridad industrial.",
            ],
            'about_trust' => [
                'title' => 'Confianza respaldada',
                'body' => 'Atendemos a corporaciones como PDVSA, Petroquímica de Venezuela, Corpoelec, CVG y grandes agroindustrias. Contamos con más de 300 aliados estratégicos globales que nos brindan representación comercial, inventarios espejo y soporte técnico especializado en campo.',
            ],
        ];

        $defaultPresence = [
            'presence_logistics' => [
                'slug' => 'presence_logistics',
                'title' => 'Logística binacional 24/7',
                'description' => 'Inventarios espejo en Miami y San Felipe, coordinación terrestre, marítima y aérea, aduanas y documentación para entregar repuestos críticos con tiempos de tránsito controlados.',
                'image_path' => normalizeFrontAssetPath('/nosotros/resumen/resumen-operadores-industriales.jpg'),
            ],
            'presence_maintenance' => [
                'slug' => 'presence_maintenance',
                'title' => 'Mantenimiento y montaje industrial',
                'description' => 'Cuadrillas propias de soldadores, instrumentistas, electricistas y mecánicos para overhaul de plantas, calderas, bombas, maquinaria pesada y sistemas de carga en puertos y aeropuertos.',
                'image_path' => normalizeFrontAssetPath('/nosotros/resumen/resumen-ingenieria-colaborativa.jpg'),
            ],
            'presence_alliances' => [
                'slug' => 'presence_alliances',
                'title' => 'Alianzas certificadas',
                'description' => 'Más de 300 aliados técnicos en América y Europa respaldan proyectos de ingeniería, fabricación y seguridad industrial con cumplimiento de normas API, ASTM, AWS y NFPA.',
                'image_path' => normalizeFrontAssetPath('/nosotros/resumen/resumen-equipo-reunion.jpg'),
            ],
        ];

        $defaultHighlights = [
            'mission' => [
                'slug' => 'mission',
                'title' => 'Misión',
                'description' => 'Ofrecer suministros, servicios de montaje, fabricación y mantenimiento industrial, seguridad integral y soluciones logísticas que permitan a nuestros clientes concentrarse en la continuidad operativa con un aliado técnico, oportuno y confiable.',
            ],
            'objective' => [
                'slug' => 'objective',
                'title' => 'Objetivo organizacional',
                'description' => 'Prestar servicios de construcción, montaje, mantenimiento, importación, logística, capital humano y apoyo organizacional bajo estrictos patrones de estandarización, calidad y eficiencia para optimizar los procesos de nuestros clientes.',
            ],
            'coverage' => [
                'slug' => 'coverage',
                'title' => 'Cobertura y aliados',
                'description' => 'Respaldamos operaciones de empresas como PDVSA, Petroquímica de Venezuela, Corpoelec, CVG y grandes agroindustrias, apoyados por una red global de representantes y fabricantes que amplía nuestra capacidad de respuesta.',
            ],
        ];

        $defaultFeaturedServices = [
            [
                'slug' => 'construccion-montaje',
                'title' => 'Construcción y obra civil',
                'summary' => 'Ingeniería, inspección y ejecución de estructuras metálicas, obras civiles y rehabilitación de infraestructura crítica.',
                'image_path' => normalizeFrontAssetPath('/nosotros/servicios/servicio-construccion-industrial.jpg'),
                'kicker' => '',
            ],
            [
                'slug' => 'mantenimiento-industrial',
                'title' => 'Mantenimiento integral',
                'summary' => 'Planes predictivos, overhaul de calderas, turbinas y sistemas de proceso con cuadrillas certificadas.',
                'image_path' => normalizeFrontAssetPath('/nosotros/servicios/servicio-mantenimiento-integral.jpg'),
                'kicker' => '',
            ],
            [
                'slug' => 'logistica-portuaria',
                'title' => 'Logística y suministros',
                'summary' => 'Importación, nacionalización y distribución de repuestos y equipos con trazabilidad completa.',
                'image_path' => normalizeFrontAssetPath('/nosotros/servicios/servicio-logistica-portuaria.jpg'),
                'kicker' => '',
            ],
            [
                'slug' => 'outsourcing-seguridad',
                'title' => 'Outsourcing y seguridad',
                'summary' => 'Gestión de capital humano, seguridad industrial y servicios múltiples para entornos críticos.',
                'image_path' => normalizeFrontAssetPath('/nosotros/servicios/servicio-outsourcing-seguridad.jpg'),
                'kicker' => '',
            ],
            [
                'slug' => 'servicios-portuarios',
                'title' => 'Servicios portuarios y aeronáuticos',
                'summary' => 'Overhaul de grúas pórtico, sistemas contra incendios y luminarias aeronáuticas bajo normas internacionales.',
                'image_path' => normalizeFrontAssetPath('/nosotros/servicios/servicio-seguridad-industrial.jpg'),
                'kicker' => '',
            ],
            [
                'slug' => 'gestion-residuos',
                'title' => 'Gestión ambiental y residuos',
                'summary' => 'Recolección, transporte y tratamiento de desechos peligrosos con cumplimiento regulatorio.',
                'image_path' => normalizeFrontAssetPath('/nosotros/servicios/servicio-gestion-residuos.jpg'),
                'kicker' => '',
            ],
        ];

        $hero = $defaultHero;
        $sections = $defaultSections;
        $presenceCards = $defaultPresence;
        $highlightCards = $defaultHighlights;
        $featuredServices = [];

        try {
            $db = getAdminDb();
        } catch (Throwable $exception) {
            $cache = [
                'hero' => $hero,
                'sections' => $sections,
                'presence_cards' => array_values($presenceCards),
                'highlight_cards' => array_values($highlightCards),
                'featured_services' => $defaultFeaturedServices,
            ];
            return $cache;
        }

        if ($result = $db->query('SELECT message_small, title, description, image_path FROM about_hero ORDER BY id ASC LIMIT 1')) {
            if ($row = $result->fetch_assoc()) {
                $messageSmall = isset($row['message_small']) ? trim((string) $row['message_small']) : '';
                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $description = isset($row['description']) ? trim((string) $row['description']) : '';
                $imagePath = isset($row['image_path']) ? trim((string) $row['image_path']) : '';

                if ($messageSmall !== '') {
                    $hero['message_small'] = $messageSmall;
                }
                if ($title !== '') {
                    $hero['title'] = $title;
                }
                if ($description !== '') {
                    $hero['description'] = $description;
                }
                if ($imagePath !== '') {
                    $hero['image_path'] = normalizeFrontAssetPath($imagePath);
                }
            }
            $result->free();
        }

        if ($result = $db->query('SELECT slug, title, body FROM about_sections')) {
            while ($row = $result->fetch_assoc()) {
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                if ($slug === '' || !isset($sections[$slug])) {
                    continue;
                }

                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $body = isset($row['body']) ? (string) $row['body'] : '';

                if ($title !== '') {
                    $sections[$slug]['title'] = $title;
                }
                if ($body !== '') {
                    $sections[$slug]['body'] = $body;
                }
            }
            $result->free();
        }

        if ($result = $db->query('SELECT slug, title, description, image_path FROM about_presence_cards ORDER BY sort_order ASC, id ASC')) {
            while ($row = $result->fetch_assoc()) {
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                if ($slug === '' || !isset($presenceCards[$slug])) {
                    continue;
                }

                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $description = isset($row['description']) ? trim((string) $row['description']) : '';
                $imagePath = isset($row['image_path']) ? trim((string) $row['image_path']) : '';

                if ($title !== '') {
                    $presenceCards[$slug]['title'] = $title;
                }
                if ($description !== '') {
                    $presenceCards[$slug]['description'] = $description;
                }
                if ($imagePath !== '') {
                    $presenceCards[$slug]['image_path'] = normalizeFrontAssetPath($imagePath);
                }
            }
            $result->free();
        }

        if ($result = $db->query('SELECT slug, title, description FROM about_highlight_cards ORDER BY sort_order ASC, id ASC')) {
            while ($row = $result->fetch_assoc()) {
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                if ($slug === '' || !isset($highlightCards[$slug])) {
                    continue;
                }

                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $description = isset($row['description']) ? trim((string) $row['description']) : '';

                if ($title !== '') {
                    $highlightCards[$slug]['title'] = $title;
                }
                if ($description !== '') {
                    $highlightCards[$slug]['description'] = $description;
                }
            }
            $result->free();
        }

        $servicesSql = 'SELECT slug, title, summary, hero_image_path, kicker FROM services WHERE is_featured_about = 1 ORDER BY title ASC';
        if ($result = $db->query($servicesSql)) {
            while ($row = $result->fetch_assoc()) {
                $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
                $title = isset($row['title']) ? trim((string) $row['title']) : '';
                $summary = isset($row['summary']) ? trim((string) $row['summary']) : '';
                $imagePath = isset($row['hero_image_path']) ? trim((string) $row['hero_image_path']) : '';
                $kicker = isset($row['kicker']) ? trim((string) $row['kicker']) : '';

                if ($slug === '' || $title === '' || $summary === '') {
                    continue;
                }

                $featuredServices[] = [
                    'slug' => $slug,
                    'title' => $title,
                    'summary' => $summary,
                    'image_path' => $imagePath !== '' ? normalizeFrontAssetPath($imagePath) : null,
                    'kicker' => $kicker,
                ];
            }
            $result->free();
        }

        if (!$featuredServices) {
            $featuredServices = $defaultFeaturedServices;
        }

        $cache = [
            'hero' => $hero,
            'sections' => $sections,
            'presence_cards' => array_values($presenceCards),
            'highlight_cards' => array_values($highlightCards),
            'featured_services' => $featuredServices,
        ];

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

if (!function_exists('searchServices')) {
    function searchServices(string $query): array
    {
        $normalized = trim($query);
        if ($normalized === '') {
            return [];
        }

        $buildExcerpt = static function (string $source) use ($normalized): string {
            $clean = trim(preg_replace('/\s+/u', ' ', strip_tags($source)) ?? '');
            if ($clean === '') {
                return '';
            }

            $lowerSource = mb_strtolower($clean, 'UTF-8');
            $lowerQuery = mb_strtolower($normalized, 'UTF-8');
            $position = mb_strpos($lowerSource, $lowerQuery, 0, 'UTF-8');
            if ($position === false) {
                $position = 0;
            }

            $window = 220;
            $start = $position > 80 ? $position - 80 : 0;
            $excerpt = mb_substr($clean, $start, $window, 'UTF-8');

            if ($start > 0) {
                $excerpt = '…' . ltrim($excerpt);
            }

            if ($start + $window < mb_strlen($clean, 'UTF-8')) {
                $excerpt = rtrim($excerpt) . '…';
            }

            return $excerpt;
        };

        $normalizeRow = static function (array $row) use ($buildExcerpt, $normalized): ?array {
            $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
            $title = isset($row['title']) ? trim((string) $row['title']) : '';
            if ($slug === '' || $title === '') {
                return null;
            }

            $summary = isset($row['summary']) ? trim((string) $row['summary']) : '';
            $kicker = isset($row['kicker']) ? trim((string) $row['kicker']) : '';
            $imagePath = isset($row['hero_image_path']) ? trim((string) $row['hero_image_path']) : '';
            $contentHtml = isset($row['content_html']) ? (string) $row['content_html'] : '';

            $normalizedImage = null;
            if ($imagePath !== '') {
                $normalizedImage = preg_match('/^https?:\/\//i', $imagePath) || str_starts_with($imagePath, 'data:')
                    ? $imagePath
                    : '/' . ltrim($imagePath, '/');
            }

            $excerptSource = $summary !== '' ? $summary : $contentHtml;
            $excerpt = $buildExcerpt($excerptSource);
            if ($excerpt === '' && $kicker !== '') {
                $excerpt = $buildExcerpt($kicker);
            }

            return [
                'slug' => $slug,
                'title' => $title,
                'summary' => $summary,
                'image_path' => $normalizedImage,
                'kicker' => $kicker,
                'excerpt' => $excerpt,
            ];
        };

        $collectFallback = static function () use ($normalizeRow, $normalized): array {
            $matches = [];
            $services = getServicesListData();
            if (!$services) {
                return $matches;
            }

            foreach ($services as $service) {
                $haystack = implode(' ', [
                    $service['title'] ?? '',
                    $service['summary'] ?? '',
                    $service['kicker'] ?? '',
                ]);
                if (mb_stripos($haystack, $normalized, 0, 'UTF-8') === false) {
                    continue;
                }

                $matches[] = [
                    'slug' => $service['slug'],
                    'title' => $service['title'],
                    'summary' => $service['summary'],
                    'image_path' => $service['image_path'],
                    'kicker' => $service['kicker'],
                    'excerpt' => $service['summary'],
                ];
            }

            return $matches;
        };

        try {
            $db = getAdminDb();
        } catch (Throwable $exception) {
            return $collectFallback();
        }

        $sql = 'SELECT slug, title, summary, hero_image_path, kicker, content_html FROM services WHERE title LIKE ? OR summary LIKE ? OR content_html LIKE ? OR kicker LIKE ? ORDER BY title ASC';
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            return $collectFallback();
        }

        $like = '%' . $normalized . '%';
        $stmt->bind_param('ssss', $like, $like, $like, $like);

        $results = [];
        if ($stmt->execute()) {
            $queryResult = $stmt->get_result();
            if ($queryResult) {
                while ($row = $queryResult->fetch_assoc()) {
                    $normalizedRow = $normalizeRow($row);
                    if ($normalizedRow !== null) {
                        $results[] = $normalizedRow;
                    }
                }
                $queryResult->free();
            }
        }

        $stmt->close();

        if ($results) {
            return $results;
        }

        return $collectFallback();
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
