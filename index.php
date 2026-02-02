<?php
$pageTitle = 'Inicio';
$rootPath = '.';
$assetPath = $rootPath . '/assets';
$activeNav = 'home';
$bodyClass = 'page-home';
$brands = require __DIR__ . '/data/brands.php';
$brandNames = array_column($brands, 'name');
$brandHighlights = implode(', ', array_slice($brandNames, 0, min(8, count($brandNames))));
$alliesImages = glob(__DIR__ . '/inicio/aliados/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
if ($alliesImages === false) {
  $alliesImages = [];
} else {
  natsort($alliesImages);
  $alliesImages = array_values($alliesImages);
}

if (!function_exists('resolveFrontAssetPath')) {
  function resolveFrontAssetPath(string $path, string $rootPath): string
  {
    if ($path === '') {
      return '';
    }

    if (preg_match('/^https?:\/\//i', $path) || str_starts_with($path, 'data:') || preg_match('/^[a-z0-9.+-]+:/i', $path)) {
      return $path;
    }

    $trimmed = ltrim($path, '/');

    if (!empty($_SERVER['HTTP_HOST'])) {
      $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
      $scheme = $isHttps ? 'https://' : 'http://';
      $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '') ?: '';
      $scriptDir = str_replace('\\', '/', $scriptDir);
      $scriptDir = $scriptDir === '/' ? '' : rtrim($scriptDir, '/');
      $baseUrl = rtrim($scheme . $_SERVER['HTTP_HOST'] . $scriptDir, '/');
      return $baseUrl . '/' . $trimmed;
    }

    if ($rootPath === '.' || $rootPath === './') {
      return '/' . $trimmed;
    }

    return rtrim($rootPath, '/') . '/' . $trimmed;
  }
}

$featuredServices = [
  [
    'title' => 'Logística y Suministros',
    'excerpt' => 'Inventarios espejo binacionales, nacionalización express y entregas puerta a puerta para repuestos críticos.',
    'image' => 'logistica.jpg',
    'link' => $rootPath . '/servicios/servicio/',
  ],
  [
    'title' => 'Construcción y Montaje',
    'excerpt' => 'Obras civiles, estructuras metálicas, subestaciones y puesta en marcha siguiendo estándares industriales.',
    'image' => 'construccion.jpg',
    'link' => $rootPath . '/servicios/construccion-montaje/',
  ],
  [
    'title' => 'Mantenimiento Industrial',
    'excerpt' => 'Planes predictivos, overhaul de calderas, turbinas y sistemas de proceso con soporte técnico permanente.',
    'image' => 'mantenimiento.jpg',
    'link' => $rootPath . '/servicios/mantenimiento-industrial/',
  ],
  [
    'title' => 'Servicios Portuarios',
    'excerpt' => 'Overhaul de grúas, sistemas contra incendio y adecuaciones integrales para terminales marítimos.',
    'image' => 'portuario.jpg',
    'link' => $rootPath . '/servicios/servicios-portuarios/',
  ],
  [
    'title' => 'Alquiler de Equipos Pesados',
    'excerpt' => 'Flotas de grúas, gabarras, vehículos especiales y maquinaria pesada disponibles 24/7.',
    'image' => 'alquiler.jpg',
    'link' => $rootPath . '/servicios/alquiler-equipos/',
  ],
  [
    'title' => 'Seguridad Industrial',
    'excerpt' => 'Sistemas de protección, dotación de EPP y vigilancia especializada para operaciones críticas.',
    'image' => 'seguridad.jpg',
    'link' => $rootPath . '/servicios/seguridad-industrial/',
  ],
];
$clientsList = [
  [
    'name' => 'Petroquímica de Venezuela, C.A.',
    'logo' => 'petroquimica-de-venezuela-ca.svg',
  ],
  [
    'name' => 'Recuvensa',
    'logo' => 'recuvensa.svg',
  ],
  [
    'name' => 'PDVSA Minera',
    'logo' => 'pdvsa-minera.svg',
  ],
  [
    'name' => 'PDVSA Industrial',
    'logo' => 'pdvsa-industrial.svg',
  ],
  [
    'name' => 'CORPOELEC',
    'logo' => 'corpoelec.svg',
  ],
  [
    'name' => 'CVG',
    'logo' => 'cvg.svg',
  ],
  [
    'name' => 'CVM',
    'logo' => 'cvm.svg',
  ],
  [
    'name' => 'Transporte Cerealero APC, C.A.',
    'logo' => 'transporte-cerealeros-apc-ca.svg',
  ],
  [
    'name' => 'Cartón de Venezuela, S.A.',
    'logo' => 'carton-de-venezuela-sa.svg',
  ],
  [
    'name' => 'Centro de Almacenes Congelados, C.A. CEALCO',
    'logo' => 'centro-de-almacenes-congelados-ca-cealco.svg',
  ],
  [
    'name' => 'Apphire Jewelry, C.A.',
    'logo' => 'apphire-jewelry-ca.svg',
  ],
  [
    'name' => 'Cemento Cerro Azul, C.A.',
    'logo' => 'cemento-cerro-azul-ca.svg',
  ],
  [
    'name' => 'Minerven, C.A.',
    'logo' => 'minerven-ca.svg',
  ],
  [
    'name' => 'Industria Azucarera Santa Clara, C.A.',
    'logo' => 'industria-azucarera-santa-clara-ca.svg',
  ],
  [
    'name' => 'PDVSA Gas, S.A.',
    'logo' => 'pdvsa-gas-sa.svg',
  ],
  [
    'name' => 'Concejo Municipal de Peña Ministerio del Poder Popular para la Juventud y el Deporte',
    'logo' => 'concejo-municipal-de-pena-ministerio-del-poder-popular-para-la-juventud-y-el-deporte.svg',
  ],
  [
    'name' => 'Ministerio Público',
    'logo' => 'ministerio-publico.svg',
  ],
  [
    'name' => 'Fábrica para Procesamiento de Sábila de Venezuela, C.A.',
    'logo' => 'fabrica-para-procesamiento-de-sabila-de-venezuela-ca.svg',
  ],
  [
    'name' => 'Asociación Civil de Copropietarios del Conjunto Residencial Caña Dulce',
    'logo' => 'asociacion-civil-de-copropietarios-del-conjunto-residencial-cana-dulce.svg',
  ],
  [
    'name' => 'Industria Azucarera Santa Elena, C.A.',
    'logo' => 'industria-azucarera-santa-elena-ca.svg',
  ],
  [
    'name' => 'Global Furniture, C.A.',
    'logo' => 'global-furniture-ca.svg',
  ],
];
require_once __DIR__ . '/includes/site-content.php';
$homeAllies = getHomeAllies();
$alliesForDisplay = [];

// Prefer the allies configured in the admin; fall back to the bundled brand list if empty.
if (!empty($homeAllies)) {
  foreach ($homeAllies as $ally) {
    $logoUrl = resolveFrontAssetPath($ally['logo_path'], $rootPath);
    if ($logoUrl === '') {
      continue;
    }

    $alliesForDisplay[] = [
      'name' => $ally['name'],
      'image' => $logoUrl,
      'featured' => !empty($ally['is_primary']),
    ];
  }
}

if (empty($alliesForDisplay)) {
  foreach ($brands as $index => $brand) {
    $imagePath = $assetPath . '/img/brands/' . $brand['image'];
    if (!empty($alliesImages[$index])) {
      $imagePath = $rootPath . '/inicio/aliados/' . rawurlencode(basename($alliesImages[$index]));
    }

    $alliesForDisplay[] = [
      'name' => $brand['name'],
      'image' => $imagePath,
      'featured' => !empty($brand['featured']),
    ];
  }
}
$homeHeroSlides = getHomeHeroSlides();
$homeHighlightCopy = getHomeHighlightCopy();
include __DIR__ . '/includes/header.php';
?>
  <main>
    <section class="hero hero-slider" data-hero-slider data-aos="fade-up" data-aos-duration="900" data-aos-easing="ease-out-cubic">
      <div class="hero-slider__track" data-hero-track>
        <?php foreach ($homeHeroSlides as $index => $slide):
          $rawImage = isset($slide['image']) ? trim((string) $slide['image']) : '';
          $imageSource = resolveFrontAssetPath($rawImage, $rootPath);
          if ($imageSource === '') {
            continue;
          }
          $isActive = $index === 0;
          $delayBase = $index * 120;
          $ctaHref = '';
          $ctaAttributes = '';
          if (!empty($slide['cta_url'])) {
            $rawCta = trim((string) $slide['cta_url']);
            if ($rawCta !== '' && $rawCta[0] !== '#') {
              $ctaHref = resolveFrontAssetPath($rawCta, $rootPath);
              $ctaAttributes = ' target="_blank" rel="noopener noreferrer"';
            } else {
              $ctaHref = $rawCta;
            }
          }
        ?>
          <article class="hero-slide<?php echo $isActive ? ' is-active' : ''; ?>" data-hero-slide style="--hero-image: url('<?php echo htmlspecialchars($imageSource, ENT_QUOTES, 'UTF-8'); ?>');">
            <div class="hero-slide__media" aria-hidden="true"></div>
            <div class="hero-content">
              <?php if (!empty($slide['tagline'])): ?>
                <p data-aos="fade-up" data-aos-delay="<?php echo $delayBase; ?>" class="hero-content__tagline"><?php echo htmlspecialchars($slide['tagline'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
              <h1 data-aos="fade-up" data-aos-delay="<?php echo $delayBase + 120; ?>"><?php echo htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
              <?php if (!empty($slide['copy'])): ?>
                <p data-aos="fade-up" data-aos-delay="<?php echo $delayBase + 240; ?>"><?php echo htmlspecialchars($slide['copy'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
              <?php if (!empty($slide['cta_label']) && $ctaHref !== ''): ?>
                <div data-aos="fade-up" data-aos-delay="<?php echo $delayBase + 360; ?>">
                  <a class="hero-content__cta" href="<?php echo htmlspecialchars($ctaHref, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $ctaAttributes; ?>><?php echo htmlspecialchars($slide['cta_label'], ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <button class="hero-slider__control hero-slider__control--prev" type="button" aria-label="Anterior" data-hero-prev>
        <span aria-hidden="true">&#10094;</span>
      </button>
      <button class="hero-slider__control hero-slider__control--next" type="button" aria-label="Siguiente" data-hero-next>
        <span aria-hidden="true">&#10095;</span>
      </button>
      <div class="hero-slider__dots" data-hero-dots>
        <?php foreach ($homeHeroSlides as $index => $_): ?>
          <button type="button" class="hero-slider__dot<?php echo $index === 0 ? ' is-active' : ''; ?>" aria-label="Ir al slide <?php echo $index + 1; ?>" data-hero-dot></button>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="section-heading" data-aos="fade-up">
      <h2 data-aos="fade-up"><?php echo htmlspecialchars($homeHighlightCopy['allies']['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
      <p data-aos="fade-up" data-aos-delay="120"><?php echo htmlspecialchars($homeHighlightCopy['allies']['body'], ENT_QUOTES, 'UTF-8'); ?></p>
    </section>

    <section class="brand-grid" data-aos="fade-up" data-aos-offset="180">
      <?php foreach ($alliesForDisplay as $index => $ally):
        $cardClass = !empty($ally['featured']) ? 'brand-card featured' : 'brand-card';
        $delay = $index * 60;
      ?>
        <div class="<?php echo $cardClass; ?>" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
          <img src="<?php echo htmlspecialchars($ally['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars('Logo de ' . $ally['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
          <span><?php echo htmlspecialchars($ally['name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php endforeach; ?>
    </section>

    <section class="home-services" data-aos="fade-up" data-aos-offset="180">
      <div class="section-heading" data-aos="fade-up">
        <h2 data-aos="fade-up"><?php echo htmlspecialchars($homeHighlightCopy['services']['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p data-aos="fade-up" data-aos-delay="120"><?php echo htmlspecialchars($homeHighlightCopy['services']['body'], ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
      <div class="services-grid">
        <?php foreach ($featuredServices as $serviceIndex => $service):
          $serviceImage = $rootPath . '/inicio/servicios/' . $service['image'];
          $serviceDelay = 160 + ($serviceIndex * 80);
        ?>
          <a class="service-card" data-aos="fade-up" data-aos-delay="<?php echo $serviceDelay; ?>" href="<?php echo htmlspecialchars($service['link'], ENT_QUOTES, 'UTF-8'); ?>">
            <picture>
              <img src="<?php echo htmlspecialchars($serviceImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars('Servicio de ' . $service['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
            </picture>
            <div class="service-card__body">
              <h3><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <p><?php echo htmlspecialchars($service['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
              <span class="service-card__cta">Ver servicio</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="home-services__cta">
        <a class="home-services__link" data-aos="zoom-in" data-aos-delay="360" href="<?php echo $rootPath; ?>/servicios/">Ver todos los servicios</a>
      </div>
    </section>

    <section class="home-clients" id="clientes" data-aos="fade-up" data-aos-offset="180">
      <div class="section-heading" data-aos="fade-up">
        <h2 data-aos="fade-up"><?php echo htmlspecialchars($homeHighlightCopy['clients']['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p data-aos="fade-up" data-aos-delay="120"><?php echo htmlspecialchars($homeHighlightCopy['clients']['body'], ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
      <div class="clients-grid">
        <?php foreach ($clientsList as $clientIndex => $client):
          $logoPath = $rootPath . '/inicio/clientes/' . $client['logo'];
          $clientDelay = 140 + ($clientIndex * 60);
        ?>
          <article class="client-card" data-aos="fade-up" data-aos-delay="<?php echo $clientDelay; ?>">
            <div class="client-card__logo">
              <img src="<?php echo htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars('Logo de ' . $client['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
            </div>
            <h3><?php echo htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

  </main>
<?php include __DIR__ . '/includes/footer.php'; ?>
