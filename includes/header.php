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
  <link rel="stylesheet" href="<?php echo $assetPath; ?>/css/style.css">
</head>
<body>
  <header>
    <div class="top-bar">
      <div class="language-switcher">
        <button type="button" data-toggle="language-menu">
          <img src="<?php echo $assetPath; ?>/img/icons/flag-es.svg" alt="Bandera de España" loading="lazy">
          <span>Español</span>
        </button>
        <div class="language-menu" data-language-menu>
          <a href="#">English</a>
          <a href="#">Deutsch</a>
          <a href="#">Русский</a>
        </div>
      </div>
      <form class="search-wrapper" role="search">
        <label class="sr-only" for="header-search">Buscar</label>
        <input id="header-search" type="search" placeholder="Buscar piezas, ex. 123-4567">
        <button type="submit">Buscar</button>
      </form>
      <a class="quote-link" href="#cotizar">
        <span>cotizar</span>
      </a>
    </div>
    <div class="primary-nav">
      <div class="nav-left">
        <a class="brand-logo" href="<?php echo $rootPath; ?>/">
          <img src="<?php echo $assetPath; ?>/img/logo.svg" alt="Logo AGA Parts" loading="lazy">
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle>Menú</button>
      </div>
      <nav data-primary-nav>
        <ul class="nav-links">
          <li><a href="<?php echo $rootPath; ?>/marcas/" class="<?php echo $activeNav === 'marcas' ? 'is-active' : ''; ?>">Marcas</a></li>
          <li><a href="<?php echo $rootPath; ?>/maquinaria/" class="<?php echo $activeNav === 'maquinaria' ? 'is-active' : ''; ?>">Maquinaria</a></li>
          <li><a href="<?php echo $rootPath; ?>/industrias/" class="<?php echo $activeNav === 'industrias' ? 'is-active' : ''; ?>">Industrias</a></li>
          <li><a href="<?php echo $rootPath; ?>/compania/" class="<?php echo $activeNav === 'compania' ? 'is-active' : ''; ?>">Compañía</a></li>
          <li><a href="<?php echo $rootPath; ?>/government-contracts/" class="<?php echo $activeNav === 'contratos' ? 'is-active' : ''; ?>">Government Contracts</a></li>
          <li><a href="<?php echo $rootPath; ?>/contacto/" class="<?php echo $activeNav === 'contacto' ? 'is-active' : ''; ?>">Contacto</a></li>
        </ul>
      </nav>
    </div>
  </header>
