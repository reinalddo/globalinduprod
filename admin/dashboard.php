<?php
$pageTitle = 'Panel administrativo';
$pageHeader = 'Panel administrativo';
$activeNav = '';
require_once __DIR__ . '/includes/page-top.php';
?>
<section>
    <p>Bienvenido<?php echo $adminUser && !empty($adminUser['full_name']) ? ', ' . htmlspecialchars($adminUser['full_name'], ENT_QUOTES, 'UTF-8') : ''; ?>. Usa los accesos rápidos para gestionar el contenido.</p>
    <div style="display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));margin-top:24px;">
        <a href="<?php echo adminUrl('dashboard'); ?>" style="display:block;padding:18px;border-radius:10px;background:#ffffff;color:var(--color-black);box-shadow:0 8px 18px rgba(0,0,0,0.08);text-decoration:none;font-weight:600;">Resumen del panel</a>
        <a href="<?php echo adminUrl('home'); ?>" style="display:block;padding:18px;border-radius:10px;background:#ffffff;color:var(--color-black);box-shadow:0 8px 18px rgba(0,0,0,0.08);text-decoration:none;font-weight:600;">Gestionar Inicio</a>
    </div>
</section>
<?php require_once __DIR__ . '/includes/page-bottom.php'; ?>
