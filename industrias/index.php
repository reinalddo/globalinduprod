<?php
$pageTitle = 'Industrias a las que servimos';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'industrias';
include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="page-hero">
      <div class="page-hero-content">
        <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;">Sectores estratégicos</p>
        <h1>Soluciones de repuestos para industrias exigentes</h1>
        <p>Adaptamos nuestros procesos logísticos y de calidad a los estándares de minería, construcción, energía, agroindustria y gobiernos, garantizando continuidad operativa.</p>
      </div>
    </section>

    <section class="main-content">
      <nav class="breadcrumb">
        <a href="<?php echo $rootPath; ?>/">Inicio</a>
        <span>/</span>
        <span>Industrias</span>
      </nav>

      <h2 style="margin-top:32px;">Experiencia comprobada</h2>
      <p>Trabajamos con operadores globales que requieren suministro constante de componentes críticos. Implementamos métricas de desempeño, portales a medida y SLAs garantizados, reduciendo costos totales de propiedad.</p>

      <div style="margin-top:36px; display:grid; gap:26px;">
        <div>
          <h3>Minería y canteras</h3>
          <p>Gestión de repuestos para equipos de extracción, acarreo y procesamiento. Cumplimiento MSHA, documentación de seguridad y soporte técnico en sitio.</p>
        </div>
        <div>
          <h3>Construcción e infraestructura</h3>
          <p>Suministro para proyectos viales, edificación y obras civiles con entregas programadas y kits de mantenimiento para maquinaria pesada.</p>
        </div>
        <div>
          <h3>Energía y servicios públicos</h3>
          <p>Partes para generación térmica, eólica, petróleo y gas, incluyendo motores industriales, compresores y sistemas hidráulicos.</p>
        </div>
        <div>
          <h3>Agroindustria y forestal</h3>
          <p>Flotas de cosecha y procesamiento con disponibilidad durante las temporadas pico. Alternativas compatibles y asesoría para conversiones.</p>
        </div>
        <div>
          <h3>Logística y puertos</h3>
          <p>Equipos reach stacker, terminal tractors y montacargas especializados. Inventarios mínimos operativos y soporte técnico remoto.</p>
        </div>
      </div>

      <div class="cta-panel" style="margin-top:48px;">
        <div>
          <strong>Comparta sus indicadores clave.</strong>
          <p style="margin:8px 0 0; color:#111;">Diseñamos dashboards de compras, reportes de consumo y proyecciones de stock para sus stakeholders.</p>
        </div>
        <a href="mailto:sales@aga-parts-demo.com?subject=Soluciones%20por%20industria">Solicitar propuesta</a>
      </div>
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
