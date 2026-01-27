<?php
$pageTitle = 'Proveedor global de repuestos para maquinaria pesada';
$rootPath = '.';
$assetPath = $rootPath . '/assets';
$activeNav = 'home';
$brands = require __DIR__ . '/data/brands.php';
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
      <h2>Mejores marcas de alrededor del mundo</h2>
      <p>Trabajamos codo a codo con fabricantes globales para asegurarle disponibilidad inmediata, trazabilidad certificada y soporte experto durante todo el ciclo de vida de su maquinaria.</p>
    </section>

    <section class="brand-grid">
      <?php foreach ($brands as $brand):
        $isFeatured = !empty($brand['featured']);
        $cardClass = $isFeatured ? 'brand-card featured' : 'brand-card';
        $brandHref = $rootPath . '/brands/caterpillar/';
        $imagePath = $assetPath . '/img/brands/' . $brand['image'];
      ?>
        <a class="<?php echo $cardClass; ?>" href="<?php echo $brandHref; ?>">
          <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
          <span><?php echo htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
      <?php endforeach; ?>
    </section>
    <section class="section-heading" style="margin:80px auto 100px;">
      <h2>Conozca el catálogo completo</h2>
      <p>Consulte más de 200 fabricantes con piezas certificadas y asesoría personalizada. Cada marca cuenta con fichas detalladas y disponibilidad global.</p>
      <div class="cta-panel" style="margin-top:36px;">
        <strong>¿Necesita otra marca o familia de producto?</strong>
        <a href="<?php echo $rootPath; ?>/marcas/">Ver catálogo</a>
      </div>
    </section>
  </main>
<?php include __DIR__ . '/includes/footer.php'; ?>
