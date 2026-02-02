<?php
require_once dirname(__DIR__) . '/includes/site-content.php';

$pageTitle = 'Nosotros';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'nosotros';
$bodyClass = 'page-nosotros';

$aboutContent = getAboutPageContent();
$hero = $aboutContent['hero'] ?? [];
$sections = $aboutContent['sections'] ?? [];
$presenceCards = $aboutContent['presence_cards'] ?? [];
$highlightCards = $aboutContent['highlight_cards'] ?? [];
$featuredServices = $aboutContent['featured_services'] ?? [];

if (!function_exists('renderAboutTextBlocks')) {
  function renderAboutTextBlocks(string $text): string
  {
    $trimmed = trim($text);
    if ($trimmed === '') {
      return '';
    }

    if (preg_match('/<[a-z][^>]*>/i', $trimmed)) {
      return $trimmed;
    }

    $chunks = preg_split('/\R{2,}/', $trimmed);
    $html = '';

    foreach ($chunks as $chunk) {
      $chunk = trim($chunk);
      if ($chunk === '') {
        continue;
      }

      $lines = preg_split('/\R+/', $chunk);
      $buffer = [];
      foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
          $buffer[] = $line;
        }
      }

      if (!$buffer) {
        continue;
      }

      if (count($buffer) > 1) {
        $html .= '<ul class="feature-list">';
        foreach ($buffer as $line) {
          $html .= '<li>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $html .= '</ul>';
      } else {
        $html .= '<p>' . htmlspecialchars($buffer[0], ENT_QUOTES, 'UTF-8') . '</p>';
      }
    }

    return $html;
  }
}

include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <?php
      $heroStyle = '';
      if (!empty($hero['image_path'])) {
        $heroStyle = ' style="background-image: url(\'' . htmlspecialchars($hero['image_path'], ENT_QUOTES, 'UTF-8') . '\')"';
      }
    ?>
    <section class="page-hero page-hero--bright page-hero--nosotros"<?php echo $heroStyle; ?>>
      <div class="page-hero-content">
        <?php if (!empty($hero['message_small'])): ?>
          <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;"><?php echo htmlspecialchars($hero['message_small'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if (!empty($hero['title'])): ?>
          <h1><?php echo htmlspecialchars($hero['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <?php endif; ?>
        <?php if (!empty($hero['description'])): ?>
          <p><?php echo htmlspecialchars($hero['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
      </div>
    </section>

    <section class="main-content">
      <?php $overview = $sections['about_overview'] ?? ['title' => '', 'body' => '']; ?>
      <?php if (!empty($overview['title'])): ?>
        <h2 style="margin-top:32px;"><?php echo htmlspecialchars($overview['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
      <?php endif; ?>
      <?php echo renderAboutTextBlocks($overview['body'] ?? ''); ?>

      <?php $history = $sections['about_history'] ?? ['title' => '', 'body' => '']; ?>
      <?php if (!empty($history['title'])): ?>
        <h3 style="margin-top:32px;"><?php echo htmlspecialchars($history['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
      <?php endif; ?>
      <?php echo renderAboutTextBlocks($history['body'] ?? ''); ?>

      <?php $presence = $sections['about_presence'] ?? ['title' => '', 'body' => '']; ?>
      <?php if (!empty($presence['title'])): ?>
        <h3 style="margin-top:32px;"><?php echo htmlspecialchars($presence['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
      <?php endif; ?>
      <?php echo renderAboutTextBlocks($presence['body'] ?? ''); ?>

      <?php if ($presenceCards): ?>
        <div class="parallax-grid parallax-grid--about">
          <?php foreach ($presenceCards as $index => $card): ?>
            <?php
              $cardStyle = '';
              $cardId = 'about-card-' . ($index + 1);
              if (!empty($card['image_path'])) {
                $cardStyle = ' style="background-image: url(\'' . htmlspecialchars($card['image_path'], ENT_QUOTES, 'UTF-8') . '\')"';
              }
            ?>
            <section class="parallax-block"<?php echo $cardStyle; ?> aria-labelledby="<?php echo htmlspecialchars($cardId, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="parallax-block__inner">
                <?php if (!empty($card['title'])): ?>
                  <h3 id="<?php echo htmlspecialchars($cardId, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <?php endif; ?>
                <?php if (!empty($card['description'])): ?>
                  <p><?php echo nl2br(htmlspecialchars($card['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                <?php endif; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($highlightCards): ?>
        <div class="clients-intro">
          <?php foreach ($highlightCards as $highlight): ?>
            <div class="clients-card">
              <?php if (!empty($highlight['title'])): ?>
                <h3><?php echo htmlspecialchars($highlight['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <?php endif; ?>
              <?php if (!empty($highlight['description'])): ?>
                <p><?php echo htmlspecialchars($highlight['description'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php $values = $sections['about_values'] ?? ['title' => '', 'body' => '']; ?>
      <?php if (!empty($values['title'])): ?>
        <h3><?php echo htmlspecialchars($values['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
      <?php endif; ?>
      <?php echo renderAboutTextBlocks($values['body'] ?? ''); ?>

      <?php $servicesSection = $sections['about_services'] ?? ['title' => '', 'body' => '']; ?>
      <?php if (!empty($servicesSection['title'])): ?>
        <h3 style="margin-top:36px;"><?php echo htmlspecialchars($servicesSection['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
      <?php endif; ?>
      <?php echo renderAboutTextBlocks($servicesSection['body'] ?? ''); ?>

      <?php $servicesForGrid = array_slice($featuredServices, 0, 6); ?>
      <?php if ($servicesForGrid): ?>
        <div class="parallax-grid parallax-grid--services">
          <?php foreach ($servicesForGrid as $index => $service): ?>
            <?php
              $serviceStyle = '';
              $serviceTitleId = 'about-service-' . ($index + 1) . '-title';
              if (!empty($service['image_path'])) {
                $serviceStyle = ' style="background-image: url(\'' . htmlspecialchars($service['image_path'], ENT_QUOTES, 'UTF-8') . '\')"';
              }
            ?>
            <section class="parallax-block"<?php echo $serviceStyle; ?> aria-labelledby="<?php echo htmlspecialchars($serviceTitleId, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="parallax-block__inner">
                <?php if (!empty($service['kicker'])): ?>
                  <span class="page-hero-kicker"><?php echo htmlspecialchars($service['kicker'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
                <?php if (!empty($service['title'])): ?>
                  <h3 id="<?php echo htmlspecialchars($serviceTitleId, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <?php endif; ?>
                <?php if (!empty($service['summary'])): ?>
                  <p><?php echo htmlspecialchars($service['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php $trust = $sections['about_trust'] ?? ['title' => '', 'body' => '']; ?>
      <?php if (!empty($trust['title'])): ?>
        <h3 style="margin-top:36px;"><?php echo htmlspecialchars($trust['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
      <?php endif; ?>
      <?php echo renderAboutTextBlocks($trust['body'] ?? ''); ?>

      <div class="cta-panel" style="margin-top:48px;">
        <div>
          <strong>Conversemos sobre sus requerimientos operativos.</strong>
          <p style="margin:8px 0 0; color:#111;">Coordinamos logística binacional, opciones OEM y aftermarket, y ejecutamos mantenimiento con personal certificado.</p>
        </div>
        <a href="<?php echo $rootPath; ?>/contacto/">Contacto</a>
      </div>
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
