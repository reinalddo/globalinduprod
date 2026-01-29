<?php
$pageTitle = 'Contáctenos';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'contacto';
include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="page-hero page-hero--bright page-hero--contact">
      <div class="page-hero-content">
        <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;">Atención 24/7</p>
        <h1>Estamos listos para apoyar su próximo proyecto</h1>
        <p>Comparta los números de parte, modelo de equipo y requerimientos logísticos. Nuestro equipo responderá con disponibilidad, tiempos de entrega y alternativas compatibles.</p>
      </div>
    </section>

    <section class="main-content">
      <nav class="breadcrumb">
        <a href="<?php echo $rootPath; ?>/">Inicio</a>
        <span>/</span>
        <span>Contacto</span>
      </nav>

      <div class="contact-layout" style="margin-top:32px;">
        <article class="contact-card">
          <h2>Oficinas principales</h2>
          <p><strong>Venezuela</strong><br>Global Induprod RL<br>Urbanización Colinas del Yurubí, Calle Polígono de Tiro, diagonal Hospital Pediátrico "Niño Jesús". Centro Profesional Spasso, Planta Baja Oficinas 02 y 03.<br>San Felipe, Yaracuy - Venezuela.</p>
          <p><strong>Teléfonos:</strong> 0412-762.25.47 / 0424-574.04.08 / 0424-312.7477 / 0412-268.2883</p>
          <p><strong>Correo:</strong> <a href="mailto:mauro@induprod.com">mauro@induprod.com</a></p>
          <p><strong>Estados Unidos</strong><br>Global Induprod International LLC<br>9858 NW 43rd Ter, Doral, FL 33178, USA.</p>
          <p><strong>Teléfono:</strong> +1 786 294 1719</p>
          <p><strong>Sitio web:</strong> <a href="https://www.globalinduprod.com" target="_blank" rel="noopener">www.globalinduprod.com</a></p>
          <h3>Datos corporativos</h3>
          <p><strong>Razón social:</strong> Asociación Cooperativa Global Induprod RL<br><strong>RIF:</strong> J-29964631-8<br><strong>Registro:</strong> Registro Público Estado Yaracuy No 29 del 10/09/2010. Tomo 30. Folio 151.<br><strong>Capital suscrito:</strong> 940.000,00 (Registro No 29 del 02/08/2012. Tomo 19. Folio 162)</p>
        </article>

        <form class="contact-card contact-form" action="#" method="post">
          <h2>Enviar solicitud</h2>
          <label for="contact-name">Nombre y empresa</label>
          <input id="contact-name" name="name" type="text" placeholder="Nombre completo / Compañía" required>

          <label for="contact-email">Correo electrónico</label>
          <input id="contact-email" name="email" type="email" placeholder="correo@empresa.com" required>

          <label for="contact-phone">Teléfono</label>
          <input id="contact-phone" name="phone" type="tel" placeholder="+1 (000) 000-0000">

          <label for="contact-equipment">Equipo o modelo</label>
          <input id="contact-equipment" name="equipment" type="text" placeholder="Modelo / Número de parte" required>

          <label for="contact-message">Detalle su requerimiento</label>
          <textarea id="contact-message" name="message" rows="5" placeholder="Incluya cantidades, ubicación y fecha objetivo" required></textarea>

          <button type="submit">Enviar solicitud</button>
        </form>
      </div>

      <div class="map-embed">
        <h2>Ubicación</h2>
        <iframe title="Mapa de la oficina" src="https://www.google.com/maps?q=Centro+Profesional+Spasso+San+Felipe+Yaracuy+Venezuela&output=embed" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
