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
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $rootPath; ?>/logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
  <link rel="stylesheet" href="<?php echo $assetPath; ?>/css/style.css">
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
  <header class="site-header">
    <nav class="navbar transparent-nav" role="navigation" aria-label="Menú principal">
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
        <div class="navbar-actions">
          <div class="nav-search" data-search-panel>
            <button class="nav-search__toggle" type="button" aria-expanded="false" aria-controls="nav-search-form">
              <span class="sr-only">Abrir buscador</span>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.5 3a7.5 7.5 0 015.926 12.137l5.218 5.219a1 1 0 01-1.414 1.414l-5.219-5.218A7.5 7.5 0 1110.5 3zm0 2a5.5 5.5 0 100 11 5.5 5.5 0 000-11z"/></svg>
            </button>
            <form class="nav-search__form" id="nav-search-form" role="search" hidden>
              <label for="header-search" class="sr-only">Buscar</label>
              <input id="header-search" type="search" placeholder="Buscar piezas, ex. 123-4567" autocomplete="off">
            </form>
          </div>
          <div class="nav-languages" role="group" aria-label="Cambiar idioma">
            <a href="#" class="nav-languages__link is-active" data-lang="es">ES</a>
            <a href="#" class="nav-languages__link" data-lang="en">EN</a>
          </div>
          <a class="nav-quote" href="#cotizar">cotizar</a>
        </div>
      </div>
    </nav>
  </header>
