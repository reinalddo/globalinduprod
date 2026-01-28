<?php
$pageTitle = 'Nosotros';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'nosotros';
include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="page-hero page-hero--bright">
      <div class="page-hero-content">
        <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;">Maquinaria crítica</p>
        <h1>Repuestos para maquinaria pesada y especializada</h1>
        <p>Programas de abastecimiento y mantenimiento predictivo para flotas de construcción, minería, puertos y energía. Integramos logística global y soporte técnico 24/7.</p>
      </div>
    </section>

    <section class="main-content">
      <nav class="breadcrumb">
        <a href="<?php echo $rootPath; ?>/">Inicio</a>
        <span>/</span>
        <span>Nosotros</span>
      </nav>

      <h2 style="margin-top:32px;">Planes de mantenimiento por tipo de activo</h2>
      <p>Desarrollamos catálogos personalizados por flota, integrando repuestos originales y alternativos certificados. Nuestro equipo documenta equivalencias, recomendaciones de instalación y componentes críticos para reducir tiempos fuera de servicio.</p>

      <h3 style="margin-top:36px;">Equipos que atendemos</h3>
      <ul class="feature-list">
        <li>Excavadoras hidráulicas, dragalinas y cargadores frontales.</li>
        <li>Motoniveladoras, bulldozers y tractores de oruga.</li>
        <li>Perforadoras, plataformas de gran altura y jumbo de minería.</li>
        <li>Equipos de pavimentación, compactación y mantenimiento vial.</li>
        <li>Grúas móviles, manipuladores telescópicos y montacargas pesados.</li>
        <li>Camiones fuera de carretera, volquetes articulados y tractocamiones.</li>
        <li>Equipos agrícolas: cosechadoras, pulverizadoras, tractores y ensiladoras.</li>
        <li>Generadores industriales, motobombas y compresores de aire.</li>
      </ul>

      <h3 style="margin-top:36px;">Servicios complementarios</h3>
      <p>Integramos monitoreo de consumos, kits de mantenimiento preventivo y soporte en campo. También gestionamos documentación para auditorías de seguridad, MSHA y OSHA.</p>

      <div class="cta-panel" style="margin-top:48px;">
        <div>
          <strong>Comparta su plan de mantenimiento anual.</strong>
          <p style="margin:8px 0 0; color:#111;">Comparamos costos entre OEM, aftermarket y remanufacturados y proponemos entregas escalonadas.</p>
        </div>
        <a href="mailto:sales@aga-parts-demo.com?subject=Programa%20de%20mantenimiento">Agendar reunión</a>
      </div>
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
