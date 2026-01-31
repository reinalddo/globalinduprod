<?php
session_start();

if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrativo</title>
    <style>
        :root {
            --color-gold: #f5c400;
            --color-black: #111111;
            --color-background: #f3f4f7;
            --color-text: #1f2329;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--color-background);
            color: var(--color-text);
        }
        .layout {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 248px;
            background: var(--color-black);
            color: #ffffff;
            position: fixed;
            inset: 0 auto 0 0;
            padding: 32px 24px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .sidebar__spacer {
            flex: 1;
        }
        .sidebar__footer {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .sidebar__brand {
            font-size: 1.3rem;
            font-weight: bold;
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
            border-radius: 8px;
            color: inherit;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .sidebar__link:hover,
        .sidebar__link:focus {
            background: rgba(245, 196, 0, 0.12);
            outline: none;
        }
        .sidebar__link svg {
            width: 22px;
            height: 22px;
            fill: var(--color-gold);
            flex-shrink: 0;
        }
        .content {
            margin-left: 248px;
            padding: 32px 40px;
            flex: 1;
        }
        .content header {
            margin-bottom: 32px;
        }
        .content h1 {
            margin: 0;
            font-size: 2rem;
            color: var(--color-black);
        }
        .content p {
            margin: 0 0 12px;
            line-height: 1.6;
        }
        @media (max-width: 960px) {
            .sidebar {
                position: fixed;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
                z-index: 200;
            }
            .sidebar.is-open {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
                padding: 24px;
            }
            .mobile-toggle {
                position: fixed;
                top: 18px;
                left: 18px;
                width: 42px;
                height: 42px;
                border: none;
                background: var(--color-black);
                color: #ffffff;
                border-radius: 8px;
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
<body>
    <button class="mobile-toggle" type="button" aria-label="Abrir menú" onclick="document.querySelector('.sidebar').classList.toggle('is-open');" hidden>
        <span></span>
    </button>
    <div class="layout">
        <aside class="sidebar" aria-label="Menú de navegación del panel" role="navigation">
            <h1 class="sidebar__brand">Administrador</h1>
            <nav class="sidebar__nav">
                <a class="sidebar__link" href="#inicio">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 3 10h2v10h5V15h4v5h5V10h2Z"/>
                    </svg>
                    <span>Inicio</span>
                </a>
                <a class="sidebar__link" href="#nosotros">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2a4 4 0 1 1-4 4 4 4 0 0 1 4-4Zm0 9c-4 0-7 2-7 4v3h14v-3c0-2-3-4-7-4Z"/>
                    </svg>
                    <span>Nosotros</span>
                </a>
                <a class="sidebar__link" href="#servicios">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 3h16v4H4Zm2 6h12v4H6Zm-2 6h16v4H4Z"/>
                    </svg>
                    <span>Servicios</span>
                </a>
                <a class="sidebar__link" href="#contacto">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 4h16v16H4Zm2 2v.51l6 4.5 6-4.5V6Zm12 12v-8.3l-6 4.5-6-4.5V18Z"/>
                    </svg>
                    <span>Contacto</span>
                </a>
                <a class="sidebar__link" href="#cabecera">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 4h16v6H4Zm0 8h16v2H4Zm0 4h10v2H4Z"/>
                    </svg>
                    <span>Cabecera</span>
                </a>
                <a class="sidebar__link" href="#pie">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 18h16v2H4Zm0-4h10v2H4Zm0-8h16v6H4Z"/>
                    </svg>
                    <span>Pie de página</span>
                </a>
            </nav>
            <div class="sidebar__spacer"></div>
            <div class="sidebar__footer">
                <a class="sidebar__link" href="logout.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 4h7a2 2 0 0 1 2 2v3h-2V6H5v12h7v-3h2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm11.59 5.59L14.17 12l2.42 2.41L15 15.99 11 12l4-4 1.59 1.59Z"/>
                    </svg>
                    <span>Cerrar sesión</span>
                </a>
            </div>
        </aside>
        <main class="content">
            <header>
                <h1>Panel administrativo</h1>
            </header>
            <section id="inicio">
                <p>Bienvenido, admin. Aquí podrás gestionar el contenido del sitio.</p>
                <p>Selecciona una sección del menú para comenzar a editar.</p>
            </section>
        </main>
    </div>
    <script>
        (function () {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            const handleResize = () => {
                if (window.innerWidth <= 960) {
                    toggle.hidden = false;
                } else {
                    toggle.hidden = true;
                    sidebar.classList.remove('is-open');
                }
            };
            window.addEventListener('resize', handleResize);
            handleResize();
        })();
    </script>
</body>
</html>
