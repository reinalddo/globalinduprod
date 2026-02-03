<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$pageTitle = tenantText('admin.home.title', 'Inicio | Administración');
$pageHeader = tenantText('admin.home.header', 'Gestión de la página de inicio');
$activeNav = 'home';
require_once __DIR__ . '/../includes/page-top.php';

$counters = [
    'hero' => 0,
    'copies' => 0,
    'allies' => 0,
    'clients' => 0
];

try {
    if ($result = $db->query('SELECT COUNT(*) AS total FROM home_hero_slides')) {
        $row = $result->fetch_assoc();
        $counters['hero'] = (int) ($row['total'] ?? 0);
        $result->free();
    }
    if ($result = $db->query('SELECT COUNT(*) AS total FROM home_section_copy')) {
        $row = $result->fetch_assoc();
        $counters['copies'] = (int) ($row['total'] ?? 0);
        $result->free();
    }
    if ($result = $db->query('SELECT COUNT(*) AS total FROM home_allies')) {
        $row = $result->fetch_assoc();
        $counters['allies'] = (int) ($row['total'] ?? 0);
        $result->free();
    }
    if ($result = $db->query('SELECT COUNT(*) AS total FROM home_clients')) {
        $row = $result->fetch_assoc();
        $counters['clients'] = (int) ($row['total'] ?? 0);
        $result->free();
    }
} catch (Throwable $exception) {
    // Mostrar mensajes discretos sin exponer detalles sensibles
    echo '<div class="empty-state">' . htmlspecialchars(tenantText('admin.notice.countersError', 'No se pudieron cargar los contadores actualmente.'), ENT_QUOTES, 'UTF-8') . '</div>';
}
?>
<section>
    <div style="display:grid;gap:18px;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
        <a href="<?php echo adminUrl('home/hero'); ?>" class="btn-outline" style="display:block;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 10px 20px rgba(17,17,17,0.06);">
            <h2 style="margin:0 0 8px;font-size:1.15rem;"><?php echo htmlspecialchars(tenantText('admin.home.heroCardTitle', 'Carrusel principal'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p style="margin:0;color:#4b5563;font-size:0.95rem;"><?php echo htmlspecialchars(tenantText('admin.home.heroCardDesc', 'Gestiona las diapositivas del hero.'), ENT_QUOTES, 'UTF-8'); ?></p>
            <span class="badge badge-muted" style="margin-top:14px;display:inline-block;"><?php echo htmlspecialchars(tenantText('admin.common.totalLabel', 'Total:'), ENT_QUOTES, 'UTF-8'); ?> <?php echo $counters['hero']; ?></span>
        </a>
        <a href="<?php echo adminUrl('home/textos'); ?>" class="btn-outline" style="display:block;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 10px 20px rgba(17,17,17,0.06);">
            <h2 style="margin:0 0 8px;font-size:1.15rem;"><?php echo htmlspecialchars(tenantText('admin.home.copyCardTitle', 'Textos destacados'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p style="margin:0;color:#4b5563;font-size:0.95rem;"><?php echo htmlspecialchars(tenantText('admin.home.copyCardDesc', 'Edita títulos y párrafos de las secciones.'), ENT_QUOTES, 'UTF-8'); ?></p>
            <span class="badge badge-muted" style="margin-top:14px;display:inline-block;"><?php echo htmlspecialchars(tenantText('admin.common.totalLabel', 'Total:'), ENT_QUOTES, 'UTF-8'); ?> <?php echo $counters['copies']; ?></span>
        </a>
        <a href="<?php echo adminUrl('home/aliados'); ?>" class="btn-outline" style="display:block;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 10px 20px rgba(17,17,17,0.06);">
            <h2 style="margin:0 0 8px;font-size:1.15rem;"><?php echo htmlspecialchars(tenantText('admin.home.alliesCardTitle', 'Aliados estratégicos'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p style="margin:0;color:#4b5563;font-size:0.95rem;"><?php echo htmlspecialchars(tenantText('admin.home.alliesCardDesc', 'Administra logos principales y adicionales.'), ENT_QUOTES, 'UTF-8'); ?></p>
            <span class="badge badge-muted" style="margin-top:14px;display:inline-block;"><?php echo htmlspecialchars(tenantText('admin.common.totalLabel', 'Total:'), ENT_QUOTES, 'UTF-8'); ?> <?php echo $counters['allies']; ?></span>
        </a>
        <a href="<?php echo adminUrl('home/clientes'); ?>" class="btn-outline" style="display:block;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 10px 20px rgba(17,17,17,0.06);">
            <h2 style="margin:0 0 8px;font-size:1.15rem;"><?php echo htmlspecialchars(tenantText('admin.home.clientsCardTitle', 'Clientes destacados'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p style="margin:0;color:#4b5563;font-size:0.95rem;"><?php echo htmlspecialchars(tenantText('admin.home.clientsCardDesc', 'Sube logos y define el orden mostrado.'), ENT_QUOTES, 'UTF-8'); ?></p>
            <span class="badge badge-muted" style="margin-top:14px;display:inline-block;"><?php echo htmlspecialchars(tenantText('admin.common.totalLabel', 'Total:'), ENT_QUOTES, 'UTF-8'); ?> <?php echo $counters['clients']; ?></span>
        </a>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
