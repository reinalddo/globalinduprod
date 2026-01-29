<?php
$pageTitle = 'Logística y Suministros';
$rootPath = '../..';
$assetPath = $rootPath . '/assets';
$activeNav = 'servicios';
$relatedServices = [
  [
    'slug' => 'construccion-montaje',
    'title' => 'Construcción y Montaje',
    'excerpt' => 'Ingeniería civil, estructuras metálicas y puesta en marcha de plantas industriales.',
  ],
  [
    'slug' => 'mantenimiento-industrial',
    'title' => 'Mantenimiento Integral',
    'excerpt' => 'Planes predictivos y overhaul de calderas, turbinas, bombas y sistemas eléctricos.',
  ],
  [
    'slug' => 'importacion-comercializacion',
    'title' => 'Importación y Comercialización',
    'excerpt' => 'Gestión aduanal, abastecimiento OEM y distribución nacional e internacional.',
  ],
];
$serviceGallery = [
  [
    'file' => 'gallery/gallery-01.jpg',
    'alt' => 'Centro logístico con inventarios industriales',
  ],
  [
    'file' => 'gallery/gallery-02.jpg',
    'alt' => 'Equipo coordinando despacho de carga crítica',
  ],
  [
    'file' => 'gallery/gallery-03.jpg',
    'alt' => 'Depósito multimodal con mercancía estratégica',
  ],
];
include dirname(__DIR__, 2) . '/includes/header.php';
?>
  <main>
    <section class="page-hero page-hero--bright page-hero--logistica">
      <div class="page-hero-content">
        <p class="page-hero-kicker">Cadena de suministro crítica</p>
        <h1>Logística, inventarios espejo y distribución 24/7</h1>
        <p>Integramos abastecimiento OEM, nacionalización y entrega de repuestos y consumibles estratégicos con trazabilidad total para petróleo, minería, energía y puertos.</p>
      </div>
    </section>

    <section class="main-content service-detail">
      <nav class="breadcrumb">
        <a href="<?php echo $rootPath; ?>/">Inicio</a>
        <span>/</span>
        <a href="<?php echo $rootPath; ?>/servicios/">Servicios</a>
        <span>/</span>
        <span>Logística y suministros</span>
      </nav>

      <header class="service-detail__header">
        <div>
          <div class="service-hero-gallery" data-gallery>
            <div class="service-hero-gallery__viewport">
              <?php foreach ($serviceGallery as $index => $item): ?>
                <figure class="service-hero-gallery__slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-index="<?php echo $index; ?>">
                  <img src="<?php echo htmlspecialchars($item['file'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['alt'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                </figure>
              <?php endforeach; ?>
            </div>
            <div class="service-hero-gallery__thumbs">
              <?php foreach ($serviceGallery as $index => $item): ?>
                <button type="button" class="service-hero-gallery__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>" data-target="<?php echo $index; ?>">
                  <img src="<?php echo htmlspecialchars($item['file'], ENT_QUOTES, 'UTF-8'); ?>" alt="Miniatura <?php echo $index + 1; ?>">
                </button>
              <?php endforeach; ?>
            </div>
          </div>
          <h2>Abastecimiento integral con cobertura binacional</h2>
          <p>Gestionamos compras internacionales, control de inventarios en Miami y Yaracuy, documentación aduanal y entregas puerta a puerta para asegurar continuidad operativa en entornos críticos y proyectos de gran escala.</p>
        </div>
        <ul class="service-detail__highlights">
          <li>Inventarios espejo Miami ↔ Yaracuy</li>
          <li>Certificación OEM y aftermarket</li>
          <li>Coordinación multimodal 24/7</li>
        </ul>
      </header>

      <div class="service-detail__content">
        <article class="service-detail__body">
          <h3>Soluciones que conectan proveedores y operación</h3>
          <p>Trabajamos con más de 300 aliados técnicos para entregar consumibles, repuestos, equipos médicos, herramientas y componentes de alta criticidad. Cada orden se procesa con trazabilidad, control documental y soporte técnico hasta su instalación en campo.</p>
          <p>El equipo de Global Induprod coordina evaluaciones técnicas, pruebas de calidad, embalaje especializado y logística terrestre, marítima o aérea para cumplir con los tiempos exigidos por refinerías, plantas eléctricas, instalaciones portuarias y proyectos agroindustriales.</p>
          <ul class="service-detail__list">
            <li>Planificación de compras y reposición de inventarios bajo demanda real.</li>
            <li>Consolidación de cargas, embalaje industrial y documentación aduanal completa.</li>
            <li>Distribución nacional con seguimiento GPS y protocolos de seguridad.</li>
            <li>Apoyo técnico post-entrega para instalación y puesta en marcha.</li>
          </ul>
        </article>
        <aside class="service-detail__sidebar">
          <div class="service-stats">
            <h3>Capacidades clave</h3>
            <ul>
              <li><strong>+300</strong> fabricantes aliados en América y Europa</li>
              <li><strong>72 h</strong> promedio de despacho desde inventarios espejo</li>
              <li><strong>ISO / API</strong> Cumplimiento de normas para equipos críticos</li>
              <li><strong>24/7</strong> Mesa de operación y seguimiento de cargas</li>
            </ul>
          </div>
          <div class="service-stats">
            <h3>Segmentos atendidos</h3>
            <ul>
              <li>Petróleo y gas upstream / downstream</li>
              <li>Minería y procesamiento de minerales</li>
              <li>Energía y utilities</li>
              <li>Operaciones portuarias y aeroportuarias</li>
              <li>Agroindustria y logística de frío</li>
            </ul>
          </div>
        </aside>
      </div>

      <section class="service-gallery">
        <h3>Otros Servicios</h3>
        <div class="services-grid">
          <?php foreach ($relatedServices as $service): ?>
            <?php $servicePath = $rootPath . '/servicios/' . rawurlencode($service['slug']); ?>
            <a class="service-card" href="<?php echo $servicePath; ?>/">
              <picture>
                <img src="<?php echo $servicePath; ?>/thumbnail.jpg" alt="<?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
              </picture>
              <div class="service-card__body">
                <h3><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars($service['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
                <span class="service-card__cta">Ver servicio</span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="service-detail__cta">
        <div>
          <h3>¿Necesita disponibilidad inmediata?</h3>
          <p>Compártanos los números de parte, cantidades y ubicación. Enviaremos alternativas OEM, aftermarket y cronograma de entrega con soporte técnico dedicado.</p>
        </div>
        <a class="service-detail__cta-link" href="<?php echo $rootPath; ?>/contacto/">Solicitar propuesta</a>
      </section>
    </section>
  </main>
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
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
