<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/brand.php';

$adminUser = $_SESSION['admin_user'] ?? null;
$pageTitle = $pageTitle ?? 'Panel administrativo';
$pageHeader = $pageHeader ?? $pageTitle;
$activeNav = $activeNav ?? '';

if (!function_exists('adminNavActiveClass')) {
    function adminNavActiveClass(string $key, string $active): string
    {
        return $key === $active ? ' sidebar__link--active active' : '';
    }
}

brandEnsureSchema($db);
$brandSettings = brandFetchSettings($db);
$adminFaviconUrl = adminAssetUrl('logo.png');
$adminFaviconType = 'image/png';
if (!empty($brandSettings['favicon_path'])) {
    $adminFaviconUrl = adminAssetUrl($brandSettings['favicon_path']);
    $extension = strtolower(pathinfo($brandSettings['favicon_path'], PATHINFO_EXTENSION));
    if ($extension === 'ico') {
        $adminFaviconType = 'image/x-icon';
    } elseif ($extension === 'svg') {
        $adminFaviconType = 'image/svg+xml';
    } elseif ($extension === 'webp') {
        $adminFaviconType = 'image/webp';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="<?php echo htmlspecialchars($adminFaviconType, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo htmlspecialchars($adminFaviconUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --color-gold: #f5c400;
            --color-black: #111111;
            --color-background: #f3f4f7;
            --color-text: #1f2329;
            --color-sidebar-active: rgba(245, 196, 0, 0.22);
        }
        * {
            box-sizing: border-box;
        }
        body {
            background-color: var(--color-background);
            color: var(--color-text);
            font-family: "Inter", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        a {
            color: inherit;
        }
        .layout {
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: var(--color-black);
            color: #ffffff;
            position: fixed;
            inset: 0 auto 0 0;
            padding: 32px 24px;
            display: flex;
            flex-direction: column;
            gap: 28px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.22);
        }
        .sidebar__brand {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--color-gold);
            margin: 0;
        }
        .sidebar__nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .sidebar__link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            color: inherit;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease;
            font-weight: 600;
        }
        .sidebar .nav-link {
            color: #ffffff;
        }
        .sidebar__link:hover,
        .sidebar__link:focus {
            background: rgba(245, 196, 0, 0.12);
            outline: none;
        }
        .sidebar__link--active {
            background: var(--color-sidebar-active);
            color: var(--color-gold);
        }
        .sidebar__link svg {
            width: 22px;
            height: 22px;
            fill: var(--color-gold);
            flex-shrink: 0;
        }
        .sidebar__link--active svg {
            fill: #ffffff;
        }
        .sidebar__spacer {
            flex: 1;
        }
        .sidebar__footer {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .content {
            margin-left: 260px;
        }
        .content section {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            margin-bottom: 32px;
        }
        .content section form {
            background: transparent;
            box-shadow: none;
            padding: 0;
            margin: 0;
        }
        .content section label {
            font-weight: 600;
            color: var(--color-black);
            margin-bottom: 6px;
        }
        .content section fieldset {
            border: none;
            padding: 0;
            margin: 0 0 24px;
        }
        .content section legend {
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: 16px;
            color: var(--color-black);
        }
        .content section > h2,
        .content section > h3 {
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--color-black);
        }
        .content header h1 {
            margin: 0;
            font-size: 2rem;
            color: var(--color-black);
        }
        .content header p {
            margin: 0;
            color: #6b7280;
        }
        .content header {
            margin-bottom: 32px;
        }
        .badge {
            border-radius: 999px;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.4rem 0.65rem;
        }
        .badge-muted {
            background: rgba(149, 165, 166, 0.18);
            color: #4b5563;
        }
        .badge-success {
            background: rgba(46, 204, 113, 0.12);
            color: #1e8449;
        }
        .table thead {
            background: var(--color-black);
            color: #ffffff;
        }
        .table thead th {
            border-color: var(--color-black);
        }
        .btn-dark {
            background-color: var(--color-black);
            border-color: var(--color-black);
        }
        .btn-dark:hover,
        .btn-dark:focus {
            background-color: #000000;
            border-color: #000000;
        }
        .btn-outline-dark {
            color: var(--color-black);
            border-color: var(--color-black);
        }
        .btn-outline-dark:hover,
        .btn-outline-dark:focus {
            color: #ffffff;
            background-color: var(--color-black);
            border-color: var(--color-black);
        }
        .empty-state {
            background: #ffffff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            color: #4b5563;
        }
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                z-index: 200;
            }
            .sidebar.is-open {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
            }
            .mobile-toggle {
                position: fixed;
                top: 18px;
                left: 18px;
                width: 44px;
                height: 44px;
                border: none;
                background: var(--color-black);
                color: #ffffff;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                z-index: 210;
            }
            .mobile-toggle span {
                width: 20px;
                height: 2px;
                background: currentColor;
                position: relative;
            }
            .mobile-toggle span::before,
            .mobile-toggle span::after {
                content: '';
                position: absolute;
                left: 0;
                width: 100%;
                height: 2px;
                background: currentColor;
            }
            .mobile-toggle span::before {
                top: -6px;
            }
            .mobile-toggle span::after {
                top: 6px;
            }
        }
    </style>
</head>
<body class="bg-body-tertiary">
    <button class="mobile-toggle" type="button" aria-label="Abrir menú" onclick="document.querySelector('.sidebar').classList.toggle('is-open');" hidden>
        <span></span>
    </button>
    <div class="layout d-flex">
        <aside class="sidebar d-flex flex-column" aria-label="Menú de navegación del panel" role="navigation">
            <h1 class="sidebar__brand text-uppercase">Administrador</h1>
            <nav class="sidebar__nav nav flex-column gap-2">
                <a class="sidebar__link nav-link<?php echo adminNavActiveClass('dashboard', $activeNav); ?>" href="<?php echo adminUrl('dashboard'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h8v8H4Zm12 0h4v8h-4ZM4 14h8v6H4Zm12 0h4v6h-4Z"/></svg>
                    <span>Dashboard</span>
                </a>
                <a class="sidebar__link nav-link<?php echo adminNavActiveClass('home', $activeNav); ?>" href="<?php echo adminUrl('home'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3 10h2v10h5V15h4v5h5V10h2Z"/></svg>
                    <span>Inicio</span>
                </a>
                <a class="sidebar__link nav-link<?php echo adminNavActiveClass('about', $activeNav); ?>" href="<?php echo adminUrl('nosotros'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a4 4 0 1 1-4 4 4 4 0 0 1 4-4Zm0 9c-4 0-7 2-7 4v3h14v-3c0-2-3-4-7-4Z"/></svg>
                    <span>Nosotros</span>
                </a>
                <a class="sidebar__link nav-link<?php echo adminNavActiveClass('services', $activeNav); ?>" href="<?php echo adminUrl('servicios'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 3h16v4H4Zm2 6h12v4H6Zm-2 6h16v4H4Z"/></svg>
                    <span>Servicios</span>
                </a>
                <a class="sidebar__link nav-link<?php echo adminNavActiveClass('contact', $activeNav); ?>" href="<?php echo adminUrl('contacto'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4Zm2 2v.51l6 4.5 6-4.5V6Zm12 12v-8.3l-6 4.5-6-4.5V18Z"/></svg>
                    <span>Contacto</span>
                </a>
                <a class="sidebar__link nav-link<?php echo adminNavActiveClass('header', $activeNav); ?>" href="<?php echo adminUrl('cabecera'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v6H4Zm0 8h16v2H4Zm0 4h10v2H4Z"/></svg>
                    <span>Cabecera</span>
                </a>
                <a class="sidebar__link nav-link<?php echo adminNavActiveClass('footer', $activeNav); ?>" href="<?php echo adminUrl('pie'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 18h16v2H4Zm0-4h10v2H4Zm0-8h16v6H4Z"/></svg>
                    <span>Pie de página</span>
                </a>
                <a class="sidebar__link nav-link<?php echo adminNavActiveClass('account', $activeNav); ?>" href="<?php echo adminUrl('datos'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a5 5 0 1 1-5 5 5 5 0 0 1 5-5Zm0 12c-4.42 0-8 2.24-8 5v3h16v-3c0-2.76-3.58-5-8-5Zm7.5-4h-1.09a6.97 6.97 0 0 0-1.1-2.65l.77-.77-1.4-1.41-.77.77A6.97 6.97 0 0 0 13 4.59V3h-2v1.09a6.97 6.97 0 0 0-2.65 1.1l-.77-.77-1.41 1.4.77.77A6.97 6.97 0 0 0 4.59 9H3v2h1.09a6.97 6.97 0 0 0 1.1 2.65l-.77.77 1.4 1.41.77-.77A6.97 6.97 0 0 0 11 17.41V18h2v-1.09a6.97 6.97 0 0 0 2.65-1.1l.77.77 1.41-1.4-.77-.77A6.97 6.97 0 0 0 20.41 11H21v-2Z"/></svg>
                    <span>Datos Admin</span>
                </a>
            </nav>
            <div class="sidebar__spacer"></div>
            <div class="sidebar__footer mt-auto">
                <a class="sidebar__link nav-link" href="<?php echo adminUrl('logout'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h7a2 2 0 0 1 2 2v3h-2V6H5v12h7v-3h2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm11.59 5.59L14.17 12l2.42 2.41L15 15.99 11 12l4-4 1.59 1.59Z"/></svg>
                    <span>Cerrar sesión</span>
                </a>
            </div>
        </aside>
        <main class="content flex-grow-1 container-fluid ps-lg-5 pe-lg-5 py-4">
            <header class="mb-4">
                <h1 class="fw-semibold mb-1"><?php echo htmlspecialchars($pageHeader, ENT_QUOTES, 'UTF-8'); ?></h1>
                <?php if ($adminUser && !empty($adminUser['full_name'])): ?>
                    <p class="text-secondary mb-0"><?php echo htmlspecialchars($adminUser['full_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </header>
