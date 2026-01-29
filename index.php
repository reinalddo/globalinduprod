<?php
$pageTitle = 'Proveedor global de repuestos para maquinaria pesada';
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
include __DIR__ . '/includes/header.php';
?>
  <main>
    <section class="hero">
      <div class="hero-content">
        <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.95rem; color:#f1c75b; margin-bottom:12px;">Proveedor global</p>
        <h1>Repuestos y soluciones para maquinaria pesada</h1>
        <p>Distribuimos componentes originales y alternativos para flotas de minería, construcción y agricultura. Cobertura mundial, logística ágil y asesoría técnica especializada.</p>
      </div>
    </section>

    <section class="section-heading">
      <h2>Nuestros Aliados Estratégicos</h2>
      <p>Contamos con más de 300 aliados estratégicos comerciales alrededor del mundo, los cuales nos han dado su representación y nos brindan respaldo total para nuestros clientes, a su vez, damos mayor capacidad de respuesta a nivel nacional e internacional.</p>
    </section>

    <section class="brand-grid">
      <?php foreach ($brands as $index => $brand):
        $isFeatured = !empty($brand['featured']);
        $cardClass = $isFeatured ? 'brand-card featured' : 'brand-card';
        $imagePath = $assetPath . '/img/brands/' . $brand['image'];
        if (!empty($alliesImages[$index])) {
          $imagePath = $rootPath . '/inicio/aliados/' . rawurlencode(basename($alliesImages[$index]));
        }
      ?>
        <div class="<?php echo $cardClass; ?>">
          <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
          <span><?php echo htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php endforeach; ?>
    </section>

    <section class="clients-intro" id="clientes">
      <div class="clients-card">
        <h3>Confianza global</h3>
        <p>Abastecemos flotas que operan con fabricantes como <?php echo htmlspecialchars($brandHighlights, ENT_QUOTES, 'UTF-8'); ?> y más de doscientas marcas aliadas. Coordinamos entregas internacionales con control de trazabilidad y documentación completa.</p>
      </div>
      <div class="clients-card">
        <h3>¿No ve su marca?</h3>
        <p>Nuestro equipo de compras localiza piezas críticas en cuestión de horas. Envíenos su listado y le proponemos alternativas OEM, aftermarket o remanufacturadas con garantía.</p>
      </div>
    </section>

    <section class="section-heading" style="margin:80px auto 100px;">
      <h2>Conozca el catálogo completo</h2>
      <p>Consulte más de 200 fabricantes con piezas certificadas y asesoría personalizada. Cada marca cuenta con fichas detalladas y disponibilidad global.</p>
      <div class="cta-panel" style="margin-top:36px;">
        <strong>¿Necesita otra marca o familia de producto?</strong>
        <a href="<?php echo $rootPath; ?>/contacto/">Escríbanos</a>
      </div>
    </section>
  </main>
<?php include __DIR__ . '/includes/footer.php'; ?>
