<?php
$pageTitle = 'Conozca nuestra compañía';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'compania';
include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="page-hero page-hero--bright">
      <div class="page-hero-content">
        <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;">Quiénes somos</p>
        <h1>Especialistas en repuestos para maquinaria crítica</h1>
        <p>Más de 25 años conectando fabricantes, distribuidores y operadores globales. Entregamos piezas certificadas, asesoría técnica y logística a medida.</p>
      </div>
    </section>

    <section class="main-content">
      <nav class="breadcrumb">
        <a href="<?php echo $rootPath; ?>/">Inicio</a>
        <span>/</span>
        <span>Compañía</span>
      </nav>

      <h2 style="margin-top:32px;">Nuestra historia</h2>
      <p>Iniciamos como un equipo boutique en Brooklyn, NY, apoyando contratistas locales. Hoy operamos con una red global de proveedores certificados, almacenes estratégicos y especialistas técnicos que dominan múltiples idiomas.</p>

      <h3 style="margin-top:36px;">Por qué confían en nosotros</h3>
      <ul class="feature-list">
        <li>Catálogo digital con más de 2 millones de referencias verificadas.</li>
        <li>Equipos de sourcing dedicados para piezas críticas o descontinuadas.</li>
        <li>Integración con ERPs del cliente para requisiciones automatizadas.</li>
        <li>Logística internacional con planificación puerta a puerta.</li>
        <li>Soporte técnico y asesoría de instalación en español, inglés y ruso.</li>
        <li>Procesos de calidad basados en ISO 9001 y controles de trazabilidad.</li>
      </ul>

      <h3 style="margin-top:36px;">Certificaciones y alianzas</h3>
      <p>Somos SAP Ariba Registered Supplier y trabajamos con distribuidores autorizados en Norteamérica, Europa y Asia. Cada envío incluye certificaciones y documentación solicitada por su industria.</p>

      <div class="cta-panel" style="margin-top:48px;">
        <div>
          <strong>¿Desea agendar una presentación corporativa?</strong>
          <p style="margin:8px 0 0; color:#111;">Coordinamos sesiones virtuales para conocer sus objetivos y proponer un plan de abastecimiento.</p>
        </div>
        <a href="mailto:info@aga-parts-demo.com?subject=Presentaci%C3%B3n%20corporativa">Programar demo</a>
      </div>
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
