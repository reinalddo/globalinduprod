<?php
$pageTitle = $pageTitle ?? 'AGA Parts Demo';
$rootPath = $rootPath ?? '.';
$assetPath = $assetPath ?? $rootPath . '/assets';
$activeNav = $activeNav ?? 'home';

require_once __DIR__ . '/site-content.php';
$languageCode = tenantLanguageCode();
$headerSettings = getSiteHeaderSettings();
$brandAssets = getSiteBrandAssets();
$navLogoPath = $headerSettings['logo_path'] ?? null;
$navLogoLabel = $headerSettings['logo_label'] ?? null;
$navLogoUrl = $rootPath . '/logo.png';

if ($navLogoPath) {
  $navLogoUrl = $rootPath . '/' . ltrim($navLogoPath, '/');
}

$navLogoAlt = $navLogoLabel ? 'Logo de ' . $navLogoLabel : 'Logo Global Induprod';

$faviconUrl = $rootPath . '/logo.png';
$faviconType = 'image/png';
if (!empty($brandAssets['favicon_path'])) {
  $faviconUrl = $rootPath . '/' . ltrim($brandAssets['favicon_path'], '/');
  $extension = strtolower(pathinfo($brandAssets['favicon_path'], PATHINFO_EXTENSION));
  if ($extension === 'ico') {
    $faviconType = 'image/x-icon';
  } elseif ($extension === 'svg') {
    $faviconType = 'image/svg+xml';
  } elseif ($extension === 'webp') {
    $faviconType = 'image/webp';
  }
}
$currentSearchQuery = '';
if (isset($_GET['q'])) {
  $currentSearchQuery = trim((string) $_GET['q']);
}

$navItems = [
  [
    'key' => 'home',
    'href' => $rootPath . '/',
    'label' => tenantText('header.nav.home', 'Inicio'),
  ],
  [
    'key' => 'nosotros',
    'href' => $rootPath . '/nosotros/',
    'label' => tenantText('header.nav.about', 'Nosotros'),
  ],
  [
    'key' => 'servicios',
    'href' => $rootPath . '/servicios/',
    'label' => tenantText('header.nav.services', 'Servicios'),
  ],
  [
    'key' => 'contacto',
    'href' => $rootPath . '/contacto/',
    'label' => tenantText('header.nav.contact', 'Contacto'),
  ],
];

$searchToggleLabel = tenantText('header.nav.searchToggle', 'Abrir buscador');
$searchToggleSr = tenantText('header.nav.searchToggleSr', 'Abrir buscador');
$searchPlaceholder = tenantText('header.nav.searchPlaceholder', 'Buscar servicios...');
$searchLabel = tenantText('header.nav.searchLabel', 'Buscar servicios');
$languageSwitcherLabel = tenantText('header.nav.languageSwitcher', 'Cambiar idioma');
$navQuoteLabel = tenantText('header.nav.quote', 'cotizar');
$languageLabels = [
  'es' => tenantText('header.nav.language.es', 'ES'),
  'en' => tenantText('header.nav.language.en', 'EN'),
];
$languageLinks = [
  'es' => 'https://globalinduprod.com',
  'en' => 'https://globalinduprodinternational.com',
];
$languageActive = [
  'es' => '',
  'en' => '',
];
$currentHost = tenantNormalizeHost($_SERVER['HTTP_HOST'] ?? '');
foreach ($languageLinks as $code => $url) {
  $targetHost = tenantNormalizeHost((string) parse_url($url, PHP_URL_HOST));
  if ($currentHost !== '' && $targetHost !== '' && $currentHost === $targetHost) {
    $languageActive[$code] = ' is-active';
  }
}
if ($languageActive['es'] === '' && $languageActive['en'] === '') {
  $languageActive[$languageCode] = ' is-active';
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="icon" type="<?php echo htmlspecialchars($faviconType, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo htmlspecialchars($faviconUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
  <link rel="stylesheet" href="<?php echo $assetPath; ?>/css/style.css">
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
  <header class="site-header">
    <nav class="navbar transparent-nav" role="navigation" aria-label="<?php echo htmlspecialchars(tenantText('header.nav.ariaMain', 'Menú principal'), ENT_QUOTES, 'UTF-8'); ?>">
      <div class="navbar-brand">
        <a class="navbar-item brand-logo" href="<?php echo $rootPath; ?>/">
          <img src="<?php echo htmlspecialchars($navLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($navLogoAlt, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
          <?php if ($navLogoLabel): ?>
            <span class="brand-logo__label"><?php echo htmlspecialchars($navLogoLabel, ENT_QUOTES, 'UTF-8'); ?></span>
          <?php endif; ?>
        </a>
        <a role="button" class="navbar-burger" aria-label="Abrir menú" aria-expanded="false" data-nav-toggle>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </a>
      </div>
      <div class="navbar-menu" data-primary-nav>
        <div class="navbar-end">
          <?php foreach ($navItems as $item): ?>
            <a href="<?php echo $item['href']; ?>" class="navbar-item <?php echo $activeNav === $item['key'] ? 'is-active' : ''; ?>"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
          <?php endforeach; ?>
        </div>
        <div class="navbar-actions">
          <div class="nav-search" data-search-panel>
            <button class="nav-search__toggle" type="button" aria-expanded="false" aria-controls="nav-search-form">
              <span class="sr-only"><?php echo htmlspecialchars($searchToggleSr, ENT_QUOTES, 'UTF-8'); ?></span>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.5 3a7.5 7.5 0 015.926 12.137l5.218 5.219a1 1 0 01-1.414 1.414l-5.219-5.218A7.5 7.5 0 1110.5 3zm0 2a5.5 5.5 0 100 11 5.5 5.5 0 000-11z"/></svg>
            </button>
            <form class="nav-search__form" id="nav-search-form" role="search" action="<?php echo $rootPath; ?>/servicios/" method="get" hidden>
              <label for="header-search" class="sr-only"><?php echo htmlspecialchars($searchLabel, ENT_QUOTES, 'UTF-8'); ?></label>
              <input id="header-search" name="q" type="search" placeholder="<?php echo htmlspecialchars($searchPlaceholder, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($currentSearchQuery, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off" required>
            </form>
          </div>
  
          <div class="nav-languages" role="group" aria-label="<?php echo htmlspecialchars($languageSwitcherLabel, ENT_QUOTES, 'UTF-8'); ?>">
            <a href="https://globalinduprod.com" class="nav-languages__link<?php echo $languageActive['es']; ?>" data-lang="es" target="_self" rel="noopener noreferrer"><?php echo htmlspecialchars($languageLabels['es'], ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="https://globalinduprodinternational.com" class="nav-languages__link<?php echo $languageActive['en']; ?>" data-lang="en" target="_self" rel="noopener noreferrer"><?php echo htmlspecialchars($languageLabels['en'], ENT_QUOTES, 'UTF-8'); ?></a>
          </div>
          <a class="nav-quote" href="<?php echo $rootPath; ?>/contacto/"><?php echo htmlspecialchars($navQuoteLabel, ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
      </div>
    </nav>
  </header>
