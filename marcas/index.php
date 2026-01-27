<?php
$pageTitle = 'Catálogo de fabricantes de repuestos';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'marcas';
$brands = require dirname(__DIR__) . '/data/brands.php';
$brandNames = array_column($brands, 'name');
$brandHighlights = implode(', ', array_slice($brandNames, 0, min(8, count($brandNames))));
include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="page-hero">
      <div class="page-hero-content">
        <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;">Catálogo de fabricantes</p>
        <h1>Catálogo de fabricantes de repuestos para equipos especiales</h1>
        <p>Más de doscientas marcas disponibles para maquinaria de construcción, minería, transporte y agricultura. Localizamos piezas críticas y aseguramos entregas globales en tiempos competitivos.</p>
      </div>
    </section>

    <section class="catalog-intro">
      <nav class="breadcrumb">
        <a href="<?php echo $rootPath; ?>/">Inicio</a>
        <span>/</span>
        <span>Marcas</span>
      </nav>
      <p>Con marcas como <?php echo htmlspecialchars($brandHighlights, ENT_QUOTES, 'UTF-8'); ?> y muchas otras referencias líderes, cubrimos todas las líneas de producción y flotas mixtas. Nuestro catálogo combina piezas originales y alternativas certificadas con garantía y trazabilidad.</p>
      <p>¿No ve su marca? Escríbanos y la localizamos para usted. Nuestro equipo de compras global trabaja con fabricantes y distribuidores autorizados para asegurar la continuidad operativa de sus equipos.</p>
    </section>

    <section class="catalog-grid" aria-label="Catálogo de marcas">
      <?php foreach ($brands as $brand):
        $brandHref = $rootPath . '/brands/' . $brand['slug'] . '/';
        $imagePath = $assetPath . '/img/brands/' . $brand['image'];
      ?>
        <a class="catalog-card" href="<?php echo $brandHref; ?>">
          <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
        </a>
      <?php endforeach; ?>
    </section>

    <section class="catalog-note">
      Para proyectos gubernamentales, contratos marco o flotas multipaís, elaboramos catálogos personalizados con disponibilidad, plazos de entrega, equivalencias y soporte técnico bilingüe.
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
