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
$contactWhatsappNumber = $globalContactSettings['contact_whatsapp_display'] ?? '';
$contactWhatsappLink = $globalContactSettings['contact_whatsapp_link'] ?? null;
$showWhatsappFloat = is_string($contactWhatsappNumber) && trim($contactWhatsappNumber) !== '' && !empty($contactWhatsappLink);

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
  <?php if ($showWhatsappFloat): ?>
    <a href="<?php echo htmlspecialchars($contactWhatsappLink, ENT_QUOTES, 'UTF-8'); ?>" class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
      <svg viewBox="0 0 32 32" aria-hidden="true" focusable="false">
        <path d="M16 3C9.4 3 4 8.2 4 14.7c0 2.4.7 4.6 2 6.6L4 29l7.9-1.9c2 1.1 4.1 1.6 6.1 1.6 6.6 0 12-5.3 12-11.8C30 8.2 24.6 3 18 3h-2Zm0 2.5c5.2 0 9.5 4.1 9.5 9.2 0 5.1-4.3 9.3-9.5 9.3-1.9 0-3.7-.6-5.3-1.5l-.4-.2-4.7 1.1 1-4.5-.3-.4c-1.2-1.7-1.8-3.6-1.8-5.6 0-5.1 4.3-9.4 9.5-9.4Zm5.6 5.6c-.3-.7-.7-.7-1-.7h-.8c-.3 0-.8.1-1.2.6-.4.5-1.5 1.5-1.5 3.7 0 .3 0 .7.1 1 .2.7.7 1.3 1.4 2 .7.7 2.6 1.8 2.6 1.8.2.1.5.2.7.3.3.1.5.1.6-.1.2-.2.8-.8 1-1 .2-.2.2-.4.2-.6s-.1-.4-.1-.4-.1-.1-.3-.2c-.2-.1-1.6-.8-1.8-.9-.2-.1-.3-.1-.4.1-.1.2-.5.5-.6.5-.1 0-.2 0-.4-.2-.2-.2-.8-.7-1.1-1.2-.3-.5-.5-1-.3-1.2.1-.1.3-.3.4-.5.1-.2.1-.3.1-.4 0-.1 0-.3-.1-.4-.1-.1-.8-2.1-1-2.6Z" fill="currentColor"/>
      </svg>
      <span class="sr-only"><?php echo htmlspecialchars($contactWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?></span>
    </a>
  <?php endif; ?>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js" defer></script>
  <script src="<?php echo $assetPath; ?>/js/main.js" defer></script>
</body>
</html>
