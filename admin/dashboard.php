<?php
$pageTitle = 'Panel administrativo';
$pageHeader = 'Panel administrativo';
$activeNav = '';
require_once __DIR__ . '/includes/page-top.php';
?>
<section>
    <p>Bienvenido<?php echo $adminUser && !empty($adminUser['full_name']) ? ', ' . htmlspecialchars($adminUser['full_name'], ENT_QUOTES, 'UTF-8') : ''; ?>. Usa los accesos rápidos para gestionar el contenido.</p>
    <div style="display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));margin-top:24px;">
        <?php
        $shortcuts = [
            ['icon' => 'home', 'title' => 'Gestionar Inicio', 'href' => adminUrl('home')],
            ['icon' => 'users', 'title' => 'Sección Nosotros', 'href' => adminUrl('nosotros')],
            ['icon' => 'layers', 'title' => 'Servicios', 'href' => adminUrl('servicios')],
            ['icon' => 'mail', 'title' => 'Contacto', 'href' => adminUrl('contacto')],
            ['icon' => 'bookmark', 'title' => 'Cabecera', 'href' => adminUrl('cabecera')],
            ['icon' => 'layout', 'title' => 'Pie de página', 'href' => adminUrl('pie')],
            ['icon' => 'shield', 'title' => 'Datos admin', 'href' => adminUrl('datos')],
        ];

        foreach ($shortcuts as $item):
            $iconPaths = [
                'home' => 'M3 12l9-9 9 9h-2v9h-5v-5h-4v5H5v-9H3Z',
                'users' => 'M9 4a3 3 0 110 6 3 3 0 010-6Zm6 0a3 3 0 110 6 3 3 0 010-6ZM5 12h8a4 4 0 014 4v3H1v-3a4 4 0 014-4Zm10.5 0c.5.6.9 1.3 1.2 2.1.2.6.3 1.2.3 1.9v3h4v-3c0-2.2-1.8-4-4-4h-1.5Z',
                'layers' => 'M12 2l9 5-9 5-9-5 9-5Zm0 8l9 5-9 5-9-5 9-5Zm-7.5 6.33L12 21l7.5-4.67',
                'mail' => 'M4 5h16a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1Zm0 2.5v.51l8 5.33 8-5.33V7.5Zm16 10V10.2l-7.6 5.07a1 1 0 01-1.1 0L4 10.2V17.5Z',
                'bookmark' => 'M6 4h12a1 1 0 011 1v15l-7-3-7 3V5a1 1 0 011-1Z',
                'layout' => 'M4 4h16v4H4Zm0 6h7v10H4Zm9 0h7v5h-7Zm0 7h7v3h-7Z',
                'shield' => 'M12 2l8 3v6c0 5-3.5 9.7-8 11-4.5-1.3-8-6-8-11V5l8-3Zm0 4.1L8 7v3c0 3.1 1.9 5.9 4 6.8 2.1-.9 4-3.7 4-6.8V7l-4-0.9Z',
            ];
            $path = $iconPaths[$item['icon']] ?? 'M4 4h16v16H4Z';
        ?>
            <a href="<?php echo $item['href']; ?>" style="display:flex;flex-direction:column;gap:14px;padding:22px;border-radius:16px;background:#ffffff;color:var(--color-black);box-shadow:0 14px 35px rgba(15,23,42,0.12);text-decoration:none;transition:transform 0.15s ease, box-shadow 0.15s ease;">
                <span style="width:50px;height:50px;border-radius:14px;background:linear-gradient(135deg,#f5c400,#facc15);display:flex;align-items:center;justify-content:center;box-shadow:0 10px 20px rgba(245,196,0,0.25);">
                    <svg viewBox="0 0 24 24" aria-hidden="true" style="width:24px;height:24px;fill:#111111;">
                        <path d="<?php echo $path; ?>"/>
                    </svg>
                </span>
                <strong style="font-size:1.02rem;font-weight:600;"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <span style="font-size:0.85rem;color:#6b7280;">Administrar módulo</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/page-bottom.php'; ?>
