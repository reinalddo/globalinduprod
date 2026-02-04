<?php
require_once dirname(__DIR__) . '/includes/site-content.php';

$searchQuery = '';
if (isset($_GET['q'])) {
  $searchQuery = trim((string) $_GET['q']);
}

$requestSlug = '';
if (isset($_GET['slug'])) {
  $requestSlug = trim((string) $_GET['slug']);
} elseif (!empty($_SERVER['PATH_INFO'])) {
  $requestSlug = trim((string) $_SERVER['PATH_INFO'], '/');
} elseif (!empty($_SERVER['REQUEST_URI'])) {
  $uri = strtok((string) $_SERVER['REQUEST_URI'], '?');
  $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/servicios/index.php'), '/');
  if ($basePath !== '' && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
  }
  $segments = array_values(array_filter(explode('/', trim($uri, '/'))));
  if (count($segments) >= 2 && $segments[0] === 'servicios') {
    $requestSlug = $segments[1];
  }
}

$rootPath = $requestSlug !== '' ? '../..' : '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'servicios';
$isSearchMode = $searchQuery !== '' && $requestSlug === '';

$servicesHero = [];
$servicesList = [];
$serviceDetail = null;
$serviceGallery = [];
$relatedServices = [];
$searchResults = [];

if ($requestSlug !== '') {
  $serviceDetail = getServiceBySlug($requestSlug);
  if ($serviceDetail) {
    $pageTitle = $serviceDetail['title'] !== '' ? $serviceDetail['title'] : 'Servicio';
    $serviceGallery = getServiceGalleryImages($serviceDetail['id']);
    $servicesList = getServicesListData();
    if ($servicesList) {
      $relatedServices = array_values(array_filter($servicesList, static function (array $service) use ($serviceDetail) {
        return $service['slug'] !== $serviceDetail['slug'];
      }));
    }
  } else {
    http_response_code(404);
    $pageTitle = 'Servicio no encontrado';
  }
} else {
  $servicesHero = getServicesPageHeroContent();
  if ($isSearchMode) {
    $pageTitle = 'Resultados de búsqueda';
    $servicesHero['title'] = 'Resultados de búsqueda';
    $servicesHero['description'] = sprintf('Mostrando coincidencias para "%s".', $searchQuery);
    $servicesHero['listing_title'] = 'Coincidencias encontradas';
    $servicesHero['listing_description'] = 'Lista de servicios que cumplen con tu búsqueda.';
    $searchResults = searchServices($searchQuery);
  } else {
    $pageTitle = 'Servicios';
    $servicesList = getServicesListData();
  }
}

include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <?php if ($requestSlug !== ''): ?>
      <?php if ($serviceDetail): ?>
        <section class="page-hero page-hero--services page-hero--service-single page-hero--bright">
          <?php if (!empty($serviceDetail['hero_image_path'])): ?>
            <div class="page-hero__media">
              <img src="<?php echo htmlspecialchars($serviceDetail['hero_image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($serviceDetail['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
            </div>
          <?php endif; ?>
          <div class="page-hero-content">
            <?php if (!empty($serviceDetail['kicker'])): ?>
              <p class="page-hero-kicker"><?php echo htmlspecialchars($serviceDetail['kicker'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($serviceDetail['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <?php if (!empty($serviceDetail['summary'])): ?>
              <p><?php echo htmlspecialchars($serviceDetail['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
          </div>
          <?php if (!empty($heroFloatingLogoMarkup)): ?>
            <?php echo $heroFloatingLogoMarkup; ?>
          <?php endif; ?>
        </section>

        <section class="main-content service-detail">
          <header class="service-detail__header">
            <div>
              <?php if (count($serviceGallery) > 0): ?>
                <div class="service-hero-gallery" data-gallery>
                  <div class="service-hero-gallery__viewport">
                    <?php foreach ($serviceGallery as $index => $imagePath): ?>
                      <figure class="service-hero-gallery__slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-index="<?php echo $index; ?>">
                        <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($serviceDetail['title'], ENT_QUOTES, 'UTF-8'); ?> - Imagen <?php echo $index + 1; ?>" loading="lazy">
                      </figure>
                    <?php endforeach; ?>
                  </div>
                  <?php if (count($serviceGallery) > 1): ?>
                    <div class="service-hero-gallery__thumbs">
                      <?php foreach ($serviceGallery as $index => $imagePath): ?>
                        <button type="button" class="service-hero-gallery__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>" data-target="<?php echo $index; ?>">
                          <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="Miniatura <?php echo $index + 1; ?>">
                        </button>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php elseif (!empty($serviceDetail['hero_image_path'])): ?>
                <figure class="service-detail__hero-figure">
                  <img src="<?php echo htmlspecialchars($serviceDetail['hero_image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($serviceDetail['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                </figure>
              <?php endif; ?>

              <?php if (!empty($serviceDetail['summary'])): ?>
                <p class="service-detail__lead"><?php echo htmlspecialchars($serviceDetail['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
            </div>
          </header>

          <div class="service-detail__content">
            <article class="service-detail__body">
              <div class="service-detail__richtext">
                <?php if ($serviceDetail['content_html'] !== ''): ?>
                  <?php echo $serviceDetail['content_html']; ?>
                <?php else: ?>
                  <p>Estamos actualizando la información detallada de este servicio. Contáctanos para más detalles.</p>
                <?php endif; ?>
              </div>
            </article>

            <aside class="service-detail__sidebar">
              <div class="service-detail__cta-card">
                <h3>¿Necesitas este servicio?</h3>
                <p>Comparte con nuestro equipo los requerimientos y coordinaremos una propuesta a medida.</p>
                <a class="service-detail__cta-button" href="<?php echo $rootPath; ?>/contacto/">Solicitar propuesta</a>
              </div>
              <?php if ($relatedServices): ?>
                <div class="service-detail__cta-card">
                  <h3>Servicios relacionados</h3>
                  <ul class="service-detail__related-list">
                    <?php foreach (array_slice($relatedServices, 0, 4) as $related): ?>
                      <?php $relatedUrl = $rootPath . '/servicios/' . rawurlencode($related['slug']) . '/'; ?>
                      <li><a href="<?php echo htmlspecialchars($relatedUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($related['title'], ENT_QUOTES, 'UTF-8'); ?> →</a></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </aside>
          </div>

          <?php if ($relatedServices): ?>
            <section class="service-gallery">
              <h3>Otros servicios</h3>
              <div class="services-grid">
                <?php foreach (array_slice($relatedServices, 0, 3) as $service): ?>
                  <?php $serviceUrl = $rootPath . '/servicios/' . rawurlencode($service['slug']) . '/'; ?>
                  <a class="service-card" href="<?php echo htmlspecialchars($serviceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                    <picture>
                      <img src="<?php echo htmlspecialchars($service['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    </picture>
                    <div class="service-card__body">
                      <?php if (!empty($service['kicker'])): ?>
                        <span class="service-card__kicker"><?php echo htmlspecialchars($service['kicker'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php endif; ?>
                      <h3><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                      <p><?php echo htmlspecialchars($service['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                      <span class="service-card__cta">Ver servicio</span>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <section class="service-detail__cta">
            <div>
              <h3>¿Necesita disponibilidad inmediata?</h3>
              <p>Compártanos los números de parte, cantidades y ubicación. Enviaremos alternativas y cronograma de entrega con soporte técnico dedicado.</p>
            </div>
            <a class="service-detail__cta-link" href="<?php echo $rootPath; ?>/contacto/">Solicitar propuesta</a>
          </section>

          <div class="service-detail__back">
            <a class="service-detail__back-link" href="<?php echo $rootPath; ?>/servicios/">← Volver a servicios</a>
          </div>
        </section>
      <?php else: ?>
        <section class="main-content services-overview">
          <header class="services-overview__intro">
            <h2>Servicio no encontrado</h2>
            <p>El servicio solicitado no existe o fue dado de baja.</p>
          </header>
          <div class="service-detail__back">
            <a class="service-detail__back-link" href="<?php echo $rootPath; ?>/servicios/">← Volver al listado de servicios</a>
          </div>
        </section>
      <?php endif; ?>
    <?php else: ?>
      <section class="page-hero page-hero--services">
        <?php if (!empty($servicesHero['image_path'])): ?>
          <div class="page-hero__media">
            <img src="<?php echo htmlspecialchars($servicesHero['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Hero servicios" loading="lazy">
          </div>
        <?php endif; ?>
        <div class="page-hero-content">
          <?php if (!empty($servicesHero['kicker'])): ?>
            <p class="page-hero-kicker"><?php echo htmlspecialchars($servicesHero['kicker'], ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endif; ?>
          <h1><?php echo htmlspecialchars($servicesHero['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
          <p><?php echo htmlspecialchars($servicesHero['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <?php if (!empty($heroFloatingLogoMarkup)): ?>
          <?php echo $heroFloatingLogoMarkup; ?>
        <?php endif; ?>
      </section>

      <section class="main-content services-overview<?php echo $isSearchMode ? ' services-overview--search' : ''; ?>">
        <header class="services-overview__intro">
          <?php if ($isSearchMode): ?>
            <?php $matchesCount = count($searchResults); ?>
            <?php $searchTermHtml = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>
            <h2>
              <?php if ($matchesCount === 0): ?>
                Sin coincidencias
              <?php elseif ($matchesCount === 1): ?>
                1 coincidencia encontrada
              <?php else: ?>
                <?php echo $matchesCount; ?> coincidencias encontradas
              <?php endif; ?>
            </h2>
            <p>
              <?php if ($matchesCount === 0): ?>
                No encontramos resultados para "<?php echo $searchTermHtml; ?>". Revisa la ortografía o explora todos los servicios disponibles.
              <?php else: ?>
                Resultados para "<?php echo $searchTermHtml; ?>".
              <?php endif; ?>
            </p>
          <?php else: ?>
            <h2><?php echo htmlspecialchars($servicesHero['listing_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?php echo htmlspecialchars($servicesHero['listing_description'], ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endif; ?>
        </header>

        <?php if ($isSearchMode): ?>
          <?php if ($searchResults): ?>
            <?php $searchTermEscaped = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>
            <div class="services-grid">
              <?php foreach ($searchResults as $service): ?>
                <?php
                  $serviceUrl = $rootPath . '/servicios/' . rawurlencode($service['slug']) . '/';
                  $descriptionSource = $service['excerpt'] !== '' ? $service['excerpt'] : ($service['summary'] ?? '');
                  $descriptionHtml = '';
                  if ($descriptionSource !== '') {
                    $descriptionHtml = htmlspecialchars($descriptionSource, ENT_QUOTES, 'UTF-8');
                    if ($service['excerpt'] !== '' && $searchTermEscaped !== '') {
                      $pattern = '/' . preg_quote($searchTermEscaped, '/') . '/iu';
                      $descriptionHtml = preg_replace($pattern, '<mark>$0</mark>', $descriptionHtml);
                    }
                  }
                ?>
                <a class="service-card" href="<?php echo htmlspecialchars($serviceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php if (!empty($service['image_path'])): ?>
                    <picture>
                      <img src="<?php echo htmlspecialchars($service['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                    </picture>
                  <?php endif; ?>
                  <div class="service-card__body">
                    <?php if (!empty($service['kicker'])): ?>
                      <span class="service-card__kicker"><?php echo htmlspecialchars($service['kicker'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <?php if ($descriptionHtml !== ''): ?>
                      <p><?php echo $descriptionHtml; ?></p>
                    <?php endif; ?>
                    <span class="service-card__cta">Ver servicio</span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p>No se encontraron servicios que coincidan con la búsqueda.</p>
          <?php endif; ?>
          <div class="services-overview__actions">
            <a class="button is-primary" href="<?php echo $rootPath; ?>/servicios/">Ver todos los servicios</a>
          </div>
        <?php else: ?>
          <div class="services-grid">
            <?php if (!$servicesList): ?>
              <p>No hay servicios publicados en este momento.</p>
            <?php else: ?>
              <?php foreach ($servicesList as $service): ?>
                <?php $serviceUrl = $rootPath . '/servicios/' . rawurlencode($service['slug']) . '/'; ?>
                <a class="service-card" href="<?php echo htmlspecialchars($serviceUrl, ENT_QUOTES, 'UTF-8'); ?>">
                  <picture>
                    <img src="<?php echo htmlspecialchars($service['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                  </picture>
                  <div class="service-card__body">
                    <?php if (!empty($service['kicker'])): ?>
                      <span class="service-card__kicker"><?php echo htmlspecialchars($service['kicker'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars($service['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <span class="service-card__cta">Ver servicio</span>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </main>
  <?php if ($requestSlug !== '' && $serviceDetail && count($serviceGallery) > 1): ?>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const galleries = document.querySelectorAll('[data-gallery]');
        galleries.forEach(function (gallery) {
          const slides = Array.from(gallery.querySelectorAll('.service-hero-gallery__slide'));
          const thumbs = Array.from(gallery.querySelectorAll('.service-hero-gallery__thumb'));
          let current = 0;
          let timerId;

          if (slides.length <= 1) {
            slides.forEach(function (slide) {
              slide.classList.add('is-active');
              slide.style.transform = 'translateX(0)';
            });
            thumbs.forEach(function (thumb) {
              thumb.classList.add('is-active');
            });
            return;
          }

          const update = function () {
            slides.forEach(function (slide, index) {
              slide.style.transform = 'translateX(' + (index - current) * 100 + '%)';
              slide.classList.toggle('is-active', index === current);
            });
            thumbs.forEach(function (thumb, index) {
              thumb.classList.toggle('is-active', index === current);
            });
          };

          const goTo = function (index) {
            const total = slides.length;
            current = (index + total) % total;
            update();
          };

          const startAuto = function () {
            clearInterval(timerId);
            timerId = setInterval(function () {
              goTo(current + 1);
            }, 6000);
          };

          thumbs.forEach(function (thumb, index) {
            thumb.addEventListener('click', function () {
              goTo(index);
              startAuto();
            });
          });

          gallery.addEventListener('mouseenter', function () {
            clearInterval(timerId);
          });

          gallery.addEventListener('mouseleave', function () {
            startAuto();
          });

          update();
          startAuto();
        });
      });
    </script>
  <?php endif; ?>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
