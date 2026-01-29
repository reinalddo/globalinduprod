<?php
$pageTitle = $pageTitle ?? 'AGA Parts Demo';
$rootPath = $rootPath ?? '.';
$assetPath = $assetPath ?? $rootPath . '/assets';
$activeNav = $activeNav ?? 'home';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" integrity="sha256-w1SENuOeJ4+nobVhtSGcVwqDAVBBusZT6F4LmSpaefM=" crossorigin="anonymous">
  <link rel="stylesheet" href="<?php echo $assetPath; ?>/css/style.css">
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
  <header class="site-header">
    <div class="top-bar">
      <div class="top-bar-inner">
        <div class="language-switcher">
          <button type="button" data-toggle="language-menu">
            <img src="<?php echo $assetPath; ?>/img/icons/flag-es.svg" alt="Bandera de España" loading="lazy">
            <span>Español</span>
          </button>
          <div class="language-menu" data-language-menu>
            <a href="#">
              <img src="<?php echo $assetPath; ?>/img/icons/flag-es.svg" alt="Bandera de España" loading="lazy">
              <span>Español</span>
            </a>
            <a href="#">
              <img src="<?php echo $assetPath; ?>/img/icons/flag-us.svg" alt="Bandera de Estados Unidos" loading="lazy">
              <span>English</span>
            </a>
          </div>
        </div>
        <div class="top-search" data-search-panel>
          <button class="top-search__toggle" type="button" aria-expanded="false" aria-controls="top-search-content">
            <span>Buscar</span>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.5 3a7.5 7.5 0 015.926 12.137l5.218 5.219a1 1 0 01-1.414 1.414l-5.219-5.218A7.5 7.5 0 1110.5 3zm0 2a5.5 5.5 0 100 11 5.5 5.5 0 000-11z"/></svg>
          </button>
          <div class="top-search__panel" id="top-search-content" hidden>
            <form class="search-wrapper" role="search">
              <label class="sr-only" for="header-search">Buscar</label>
              <input id="header-search" type="search" placeholder="Buscar piezas, ex. 123-4567">
              <button type="submit">Buscar</button>
            </form>
            <a class="quote-link" href="#cotizar">
              <span>cotizar</span>
            </a>
          </div>
        </div>
      </div>
    </div>
    <nav class="navbar is-dark" role="navigation" aria-label="Menú principal">
      <div class="navbar-brand">
        <a class="navbar-item brand-logo" href="<?php echo $rootPath; ?>/">
          <img src="<?php echo $rootPath; ?>/logo.png" alt="Logo Global Induprod" loading="lazy">
        </a>
        <a role="button" class="navbar-burger" aria-label="Abrir menú" aria-expanded="false" data-nav-toggle>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </a>
      </div>
      <div class="navbar-menu" data-primary-nav>
        <div class="navbar-end">
          <a href="<?php echo $rootPath; ?>/" class="navbar-item <?php echo $activeNav === 'home' ? 'is-active' : ''; ?>">Inicio</a>
          <a href="<?php echo $rootPath; ?>/nosotros/" class="navbar-item <?php echo $activeNav === 'nosotros' ? 'is-active' : ''; ?>">Nosotros</a>
          <a href="<?php echo $rootPath; ?>/servicios/" class="navbar-item <?php echo $activeNav === 'servicios' ? 'is-active' : ''; ?>">Servicios</a>
          <a href="<?php echo $rootPath; ?>/contacto/" class="navbar-item <?php echo $activeNav === 'contacto' ? 'is-active' : ''; ?>">Contacto</a>
        </div>
      </div>
    </nav>
  </header>
