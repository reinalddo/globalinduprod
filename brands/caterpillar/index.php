<?php
$pageTitle = 'Repuestos Caterpillar certificados | AGA Parts Demo';
$rootPath = '../..';
$assetPath = $rootPath . '/assets';
$activeNav = 'marcas';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
  <main>
    <section class="brand-hero">
      <div class="brand-hero-content">
        <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;">Repuestos y refacciones</p>
        <h1>Repuestos Caterpillar para maquinaria de construcción</h1>
        <p>Disponibilidad global de repuestos originales y alternativos para excavadoras, motoniveladoras, cargadores y generadores Caterpillar. Asesoría en español y logística puerta a puerta.</p>
      </div>
    </section>

    <section class="main-content">
      <nav class="breadcrumb">
        <a href="<?php echo $rootPath; ?>/">Inicio</a>
        <span>/</span>
        <span>Caterpillar</span>
      </nav>

      <div class="brand-intro">
        <figure>
          <img src="<?php echo $assetPath; ?>/img/brands/caterpillar.svg" alt="Logotipo Caterpillar" loading="lazy">
        </figure>
        <div class="brand-intro-content">
          <h2>Buscar piezas Caterpillar en AGA Parts</h2>
          <p>Ingrese el número de parte o modelo en nuestro buscador para recibir un listado preciso de repuestos compatibles. Cada solicitud es verificada por especialistas en la línea Caterpillar para garantizar disponibilidad, trazabilidad y documentación completa.</p>
          <p>Atendemos flotas en minería, construcción, movimiento de tierras y energía. Ya sea que busque refacciones originales o alternativas certificadas, nuestro equipo prepara cotizaciones claras y competitivas.</p>
        </div>
      </div>

      <h3 style="margin-top:36px;">Componentes que suministramos habitualmente</h3>
      <ul class="feature-list">
        <li>Pavimentadoras y compactadoras.</li>
        <li>Bulldozers y tractores de oruga.</li>
        <li>Cosechadoras y maquinaria agrícola.</li>
        <li>Apisonadoras y rodillos vibratorios.</li>
        <li>Plataformas de perforación y equipos de minería.</li>
        <li>Excavadoras de ruedas, orugas y dragalinas.</li>
        <li>Carretillas elevadoras, telescópicas y cargadores de ruedas.</li>
        <li>Skidders para industrias forestales.</li>
        <li>Tractores con pala trasera y motoniveladoras.</li>
        <li>Componentes de línea de potencia y transmisiones.</li>
      </ul>

      <section style="margin-top:48px;">
        <h3>Por qué elegirnos</h3>
        <p>Garantizamos cobertura global gracias a una red de almacenes estratégicos, control de calidad continuo y soporte postventa. Integramos su ERP a nuestro catálogo para facilitar requisiciones recurrentes y seguimiento de órdenes.</p>
      </section>

      <div class="cta-panel" style="margin-top:56px;">
        <div>
          <strong>Cotice su próximo pedido Caterpillar con nuestro equipo.</strong>
          <p style="margin: 8px 0 0; color:#111; font-size:0.95rem;">Resolvemos dudas técnicas, tiempos de entrega y alternativas disponibles.</p>
        </div>
        <a href="mailto:sales@aga-parts-demo.com?subject=Cotización%20Caterpillar">Solicitar cotización</a>
      </div>
    </section>
  </main>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
