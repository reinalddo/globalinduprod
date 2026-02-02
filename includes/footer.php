<?php
$footerSettings = getSiteFooterSettings();
$footerSocials = getSiteFooterSocials();

$footerLogoUrl = $rootPath . '/logo.png';
if (!empty($footerSettings['logo_path'])) {
  $footerLogoUrl = $rootPath . '/' . ltrim($footerSettings['logo_path'], '/');
}

$footerContactHtml = '';
if (!empty($footerSettings['contact_text'])) {
  $footerContactHtml = nl2br(htmlspecialchars($footerSettings['contact_text'], ENT_QUOTES, 'UTF-8'), false);
}

$footerRightsText = htmlspecialchars($footerSettings['rights_text'] ?? '', ENT_QUOTES, 'UTF-8');

$defaultIconMap = [
  'facebook' => $assetPath . '/img/icons/facebook.svg',
  'instagram' => $assetPath . '/img/icons/instagram.svg',
  'youtube' => $assetPath . '/img/icons/youtube.svg',
  'tiktok' => $assetPath . '/img/icons/tiktok.svg',
  'linkedin' => $assetPath . '/img/icons/linkedin.svg',
  'vk' => $assetPath . '/img/icons/vk.svg',
  'pinterest' => $assetPath . '/img/icons/pinterest.svg',
];
?>

  <footer id="contacto">
    <div class="footer-inner">
      <div class="footer-block footer-brand">
        <img src="<?php echo htmlspecialchars($footerLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo Global Induprod" loading="lazy">
        <address>
          <?php if ($footerContactHtml !== ''): ?>
            <?php echo $footerContactHtml; ?>
          <?php else: ?>
            AGA Parts<br>210 41st Street, #202<br>Brooklyn, New York, 11232<br>sales@aga-parts-demo.com<br>+1 (347) 773-3247
          <?php endif; ?>
        </address>
      </div>
      <div class="footer-block">
        <h4>Empresa</h4>
        <ul>
          <li><a href="<?php echo $rootPath; ?>/">Inicio</a></li>
          <li><a href="<?php echo $rootPath; ?>/nosotros/">Nosotros</a></li>
          <li><a href="<?php echo $rootPath; ?>/servicios/">Servicios</a></li>
          <li><a href="<?php echo $rootPath; ?>/contacto/">Contacto</a></li>
        </ul>
      </div>
      <div class="footer-block" style="display: none;">
        <h4>Información</h4>
        <ul>
          <li><a href="<?php echo $rootPath; ?>/contacto/">Contáctenos</a></li>
          <li><a href="<?php echo $rootPath; ?>/servicios/">Cómo trabajamos</a></li>
          <li><a href="#terminos">Términos y condiciones</a></li>
          <li><a href="#privacidad">Política de privacidad</a></li>
        </ul>
      </div>
      <div class="footer-block">
        <h4>Síguenos</h4>
        <div class="social-links">
          <?php foreach ($footerSocials as $social):
            $iconUrl = '';
            if (!empty($social['icon_path'])) {
              $iconUrl = $rootPath . '/' . ltrim($social['icon_path'], '/');
            } elseif (!empty($social['icon_key']) && isset($defaultIconMap[$social['icon_key']])) {
              $iconUrl = $defaultIconMap[$social['icon_key']];
            }
            $socialName = htmlspecialchars($social['name'], ENT_QUOTES, 'UTF-8');
            $socialUrl = htmlspecialchars($social['url'], ENT_QUOTES, 'UTF-8');
            if ($iconUrl === '') {
              continue;
            }
          ?>
            <a href="<?php echo $socialUrl; ?>" target="_blank" rel="noopener noreferrer">
              <img src="<?php echo htmlspecialchars($iconUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo $socialName; ?>">
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="footer-note">
      <span><?php echo $footerRightsText !== '' ? $footerRightsText : sprintf('© %s AGA Parts Demo. Todos los derechos reservados.', date('Y')); ?></span>
      <span>Las marcas registradas pertenecen a sus respectivos dueños.</span>
    </div>
  </footer>
  <button class="scroll-top" type="button" aria-label="Ir arriba" data-scroll-top>↑</button>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js" defer></script>
  <script src="<?php echo $assetPath; ?>/js/main.js" defer></script>
</body>
</html>
