<?php
$pageTitle = 'Servicios';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'servicios';

$services = [
  [
    'slug' => 'construccion-montaje',
    'title' => 'Construcción y Montaje',
    'excerpt' => 'Ingeniería civil, estructuras metálicas y puesta en marcha de plantas industriales.',
    'image' => 'construccion-montaje/thumbnail.jpg',
  ],
  [
    'slug' => 'mantenimiento-industrial',
    'title' => 'Mantenimiento Integral',
    'excerpt' => 'Planes predictivos y overhaul de calderas, turbinas, bombas y sistemas eléctricos.',
    'image' => 'mantenimiento-industrial/thumbnail.jpg',
  ],
  [
    'slug' => 'servicios-portuarios',
    'title' => 'Servicios Portuarios',
    'excerpt' => 'Overhaul de grúas pórtico, defensas, sistemas contra incendio y apoyo a muelles.',
    'image' => 'servicios-portuarios/thumbnail.jpg',
  ],
  [
    'slug' => 'servicio',
    'title' => 'Logística y Suministros',
    'excerpt' => 'Inventarios espejo, nacionalización y entrega de repuestos críticos 24/7.',
    'image' => 'servicio/thumbnail.jpg',
  ],
  [
    'slug' => 'importacion-comercializacion',
    'title' => 'Importación y Comercialización',
    'excerpt' => 'Gestión aduanal, abastecimiento OEM y distribución nacional e internacional.',
    'image' => 'importacion-comercializacion/thumbnail.jpg',
  ],
  [
    'slug' => 'gestion-residuos',
    'title' => 'Gestión de Residuos',
    'excerpt' => 'Recolección, transporte y tratamiento de desechos industriales y peligrosos.',
    'image' => 'gestion-residuos/thumbnail.jpg',
  ],
  [
    'slug' => 'alquiler-equipos',
    'title' => 'Alquiler de Equipos',
    'excerpt' => 'Flotas pesadas, grúas telescópicas, gabarras y soporte especializado in situ.',
    'image' => 'alquiler-equipos/thumbnail.jpg',
  ],
  [
    'slug' => 'capital-humano',
    'title' => 'Capital Humano',
    'excerpt' => 'Outsourcing operativo, selección, capacitación y certificación técnica.',
    'image' => 'capital-humano/thumbnail.jpg',
  ],
  [
    'slug' => 'seguridad-industrial',
    'title' => 'Seguridad Industrial',
    'excerpt' => 'Sistemas de protección, vigilancia integral y dotación de EPP certificados.',
    'image' => 'seguridad-industrial/thumbnail.jpg',
  ],
];

include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="page-hero page-hero--services">
      <div class="page-hero-content">
        <p class="page-hero-kicker">Portafolio integral</p>
        <h1>Soluciones industriales para cada etapa de su operación</h1>
        <p>Coordinamos logística binacional y ejecutamos servicios técnicos certificados para petróleo, minería, energía, puertos y agroindustria.</p>
      </div>
    </section>

    <section class="main-content services-overview">
      <header class="services-overview__intro">
        <h2>Nuestros pilares de servicio</h2>
        <p>Seleccionamos nueve líneas estratégicas que integran construcción, mantenimiento, logística, seguridad y talento humano para garantizar operaciones continuas en entornos críticos.</p>
      </header>
    <br>
      <div class="services-grid">
        <?php foreach ($services as $service): ?>
          <a class="service-card" href="<?php echo $rootPath; ?>/servicios/servicio/">
            <picture>
              <img src="<?php echo htmlspecialchars($service['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
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
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
