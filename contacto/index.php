<?php
$pageTitle = 'Contáctenos';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'contacto';
include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="page-hero page-hero--bright">
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
          <p>AGA Parts<br>210 41st Street, #202<br>Brooklyn, New York, 11232</p>
          <p><strong>Correo:</strong> sales@aga-parts-demo.com<br><strong>Teléfono:</strong> +1 (347) 773-3247</p>
          <p>Atención en español, inglés y ruso. Coordinamos videollamadas para análisis técnico o negociaciones de contratos.</p>
          <h3>Horario</h3>
          <p>Lunes a viernes: 08:00 - 20:00 EST<br>Sábados: 09:00 - 13:00 EST</p>
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
        <iframe title="Mapa de la oficina" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3021.641495928014!2d-74.0103375234088!3d40.65605954036408!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25b2f105cf6c1%3A0xd888fffbe671c5f!2s210%2041st%20St%20%23202%2C%20Brooklyn%2C%20NY%2011232%2C%20EE.%20UU.!5e0!3m2!1ses!2sar!4v1706500000000!5m2!1ses!2sar" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
