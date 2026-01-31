<?php
$pageTitle = 'Inicio | Administración';
$pageHeader = 'Gestión de la página de inicio';
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
    echo '<div class="empty-state">No se pudieron cargar los contadores actualmente.</div>';
}
?>
<section>
    <div style="display:grid;gap:18px;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
        <a href="<?php echo adminUrl('home/hero'); ?>" class="btn-outline" style="display:block;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 10px 20px rgba(17,17,17,0.06);">
            <h2 style="margin:0 0 8px;font-size:1.15rem;">Carrusel principal</h2>
            <p style="margin:0;color:#4b5563;font-size:0.95rem;">Gestiona las diapositivas del hero.</p>
            <span class="badge badge-muted" style="margin-top:14px;display:inline-block;">Total: <?php echo $counters['hero']; ?></span>
        </a>
        <a href="<?php echo adminUrl('home/textos'); ?>" class="btn-outline" style="display:block;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 10px 20px rgba(17,17,17,0.06);">
            <h2 style="margin:0 0 8px;font-size:1.15rem;">Textos destacados</h2>
            <p style="margin:0;color:#4b5563;font-size:0.95rem;">Edita títulos y párrafos de las secciones.</p>
            <span class="badge badge-muted" style="margin-top:14px;display:inline-block;">Total: <?php echo $counters['copies']; ?></span>
        </a>
        <a href="<?php echo adminUrl('home/aliados'); ?>" class="btn-outline" style="display:block;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 10px 20px rgba(17,17,17,0.06);">
            <h2 style="margin:0 0 8px;font-size:1.15rem;">Aliados estratégicos</h2>
            <p style="margin:0;color:#4b5563;font-size:0.95rem;">Administra logos principales y adicionales.</p>
            <span class="badge badge-muted" style="margin-top:14px;display:inline-block;">Total: <?php echo $counters['allies']; ?></span>
        </a>
        <a href="<?php echo adminUrl('home/clientes'); ?>" class="btn-outline" style="display:block;padding:24px;border-radius:12px;text-decoration:none;box-shadow:0 10px 20px rgba(17,17,17,0.06);">
            <h2 style="margin:0 0 8px;font-size:1.15rem;">Clientes destacados</h2>
            <p style="margin:0;color:#4b5563;font-size:0.95rem;">Sube logos y define el orden mostrado.</p>
            <span class="badge badge-muted" style="margin-top:14px;display:inline-block;">Total: <?php echo $counters['clients']; ?></span>
        </a>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
