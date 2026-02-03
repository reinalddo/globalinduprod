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

$companyHeading = tenantText('footer.company', 'Empresa');
$informationHeading = tenantText('footer.information', 'Información');
$followUsHeading = tenantText('footer.followUs', 'Síguenos');
$contactUsLabel = tenantText('footer.contactUs', 'Contáctenos');
$howWeWorkLabel = tenantText('footer.howWeWork', 'Cómo trabajamos');
$termsLabel = tenantText('footer.terms', 'Términos y condiciones');
$privacyLabel = tenantText('footer.privacy', 'Política de privacidad');
$scrollTopLabel = tenantText('footer.scrollTop', 'Ir arriba');
$defaultRightsText = sprintf(tenantText('footer.rightsDefault', '© %s AGA Parts Demo. Todos los derechos reservados.'), date('Y'));
$defaultTrademarkText = tenantText('footer.rightsTrademark', 'Las marcas registradas pertenecen a sus respectivos dueños.');

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
        <h4><?php echo htmlspecialchars($companyHeading, ENT_QUOTES, 'UTF-8'); ?></h4>
        <ul>
          <li><a href="<?php echo $rootPath; ?>/"><?php echo htmlspecialchars(tenantText('header.nav.home', 'Inicio'), ENT_QUOTES, 'UTF-8'); ?></a></li>
          <li><a href="<?php echo $rootPath; ?>/nosotros/"><?php echo htmlspecialchars(tenantText('header.nav.about', 'Nosotros'), ENT_QUOTES, 'UTF-8'); ?></a></li>
          <li><a href="<?php echo $rootPath; ?>/servicios/"><?php echo htmlspecialchars(tenantText('header.nav.services', 'Servicios'), ENT_QUOTES, 'UTF-8'); ?></a></li>
          <li><a href="<?php echo $rootPath; ?>/contacto/"><?php echo htmlspecialchars(tenantText('header.nav.contact', 'Contacto'), ENT_QUOTES, 'UTF-8'); ?></a></li>
        </ul>
      </div>
      <div class="footer-block" style="display: none;">
        <h4><?php echo htmlspecialchars($informationHeading, ENT_QUOTES, 'UTF-8'); ?></h4>
        <ul>
          <li><a href="<?php echo $rootPath; ?>/contacto/"><?php echo htmlspecialchars($contactUsLabel, ENT_QUOTES, 'UTF-8'); ?></a></li>
          <li><a href="<?php echo $rootPath; ?>/servicios/"><?php echo htmlspecialchars($howWeWorkLabel, ENT_QUOTES, 'UTF-8'); ?></a></li>
          <li><a href="#terminos"><?php echo htmlspecialchars($termsLabel, ENT_QUOTES, 'UTF-8'); ?></a></li>
          <li><a href="#privacidad"><?php echo htmlspecialchars($privacyLabel, ENT_QUOTES, 'UTF-8'); ?></a></li>
        </ul>
      </div>
      <div class="footer-block">
        <h4><?php echo htmlspecialchars($followUsHeading, ENT_QUOTES, 'UTF-8'); ?></h4>
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
      <span><?php echo $footerRightsText !== '' ? $footerRightsText : htmlspecialchars($defaultRightsText, ENT_QUOTES, 'UTF-8'); ?></span>
      <span><?php echo htmlspecialchars($defaultTrademarkText, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
  </footer>
  <button class="scroll-top" type="button" aria-label="<?php echo htmlspecialchars($scrollTopLabel, ENT_QUOTES, 'UTF-8'); ?>" data-scroll-top>↑</button>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js" defer></script>
  <script src="<?php echo $assetPath; ?>/js/main.js" defer></script>
</body>
</html>
