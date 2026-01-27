<?php
$pageTitle = 'Government Contracts y programas públicos';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'contratos';
include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="page-hero">
      <div class="page-hero-content">
        <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;">Government contracts</p>
        <h1>Abastecimiento para contratos gubernamentales y GSA</h1>
        <p>Cumplimos con regulaciones federales y locales, manejando documentación, licencias y reportes de cumplimiento para agencias públicas y contratistas prime.</p>
      </div>
    </section>

    <section class="main-content">
      <nav class="breadcrumb">
        <a href="<?php echo $rootPath; ?>/">Inicio</a>
        <span>/</span>
        <span>Government Contracts</span>
      </nav>

      <h2 style="margin-top:32px;">Servicios especializados</h2>
      <p>Coordinamos licitaciones, contratos marco y compras recurrentes de repuestos para flotas militares, municipales y agencias de infraestructura. Gestionamos trazabilidad completa y control de costos.</p>

      <h3 style="margin-top:36px;">Qué ofrecemos</h3>
      <ul class="feature-list">
        <li>Gestión documental FAR/DFARS, MSDS y certificados de conformidad.</li>
        <li>Reportes de avance y entregas según estándares GovWin y SAM.gov.</li>
        <li>Integración con portales eProcurement y sistemas ERP gubernamentales.</li>
        <li>Soporte multilingüe y coordinación con contratistas prime y subcontratistas.</li>
        <li>Planes de contingencia y stock dedicado para emergencias.</li>
      </ul>

      <div class="cta-panel" style="margin-top:48px;">
        <div>
          <strong>¿Trabaja en un nuevo RFP o IDIQ?</strong>
          <p style="margin:8px 0 0; color:#111;">Envíenos los alcances del proyecto y preparamos una propuesta alineada a sus KPIs.</p>
        </div>
        <a href="mailto:contracts@aga-parts-demo.com?subject=Soporte%20Government%20Contracts">Solicitar apoyo</a>
      </div>
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
