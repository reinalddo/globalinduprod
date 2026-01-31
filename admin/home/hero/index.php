<?php
$pageTitle = 'Inicio | Carrusel';
$pageHeader = 'Carrusel principal';
$activeNav = 'home';
require_once __DIR__ . '/../../includes/page-top.php';

$slides = [];
$errorMessage = '';

try {
    if ($result = $db->query('SELECT id, image_path, message_small, title, description, sort_order, is_active, updated_at FROM home_hero_slides ORDER BY sort_order ASC, id ASC')) {
        while ($row = $result->fetch_assoc()) {
            $slides[] = $row;
        }
        $result->free();
    }
} catch (Throwable $exception) {
    $errorMessage = 'No se pudieron cargar las diapositivas.';
}
?>
<section>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
        <p style="margin:0;color:#4b5563;">Administra las diapositivas que se muestran en la galería principal del inicio.</p>
        <a class="btn btn-primary" href="<?php echo adminUrl('home/hero/crear'); ?>">Añadir diapositiva</a>
    </div>

    <?php if ($errorMessage): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif (empty($slides)): ?>
        <div class="empty-state">
            <p style="margin:0 0 12px;">Aún no hay diapositivas registradas.</p>
            <a class="btn btn-primary" href="<?php echo adminUrl('home/hero/crear'); ?>">Crear la primera diapositiva</a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Imagen</th>
                        <th>Mensaje</th>
                        <th>Título</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Actualizado</th>
                        <th style="width:140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slides as $slide): ?>
                        <tr>
                            <td><?php echo (int) $slide['id']; ?></td>
                            <td style="max-width:220px;">
                                <?php $imageUrl = adminAssetUrl((string) $slide['image_path']); ?>
                                <?php if ($imageUrl): ?>
                                    <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Diapositiva" style="width:100%;max-width:200px;height:110px;object-fit:cover;border-radius:6px;display:block;border:1px solid #e5e7eb;background:#f3f4f6;">
                                <?php endif; ?>
                                <span style="display:block;font-size:12px;color:#6b7280;word-break:break-all;margin-top:6px;"><?php echo htmlspecialchars((string) $slide['image_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td style="max-width:220px;"><?php echo htmlspecialchars((string) $slide['message_small'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="max-width:220px;"><?php echo htmlspecialchars((string) $slide['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (int) $slide['sort_order']; ?></td>
                            <td>
                                <?php if ((int) $slide['is_active'] === 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-muted">Oculto</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($slide['updated_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-outline" style="padding:8px 12px;font-size:12px;" href="<?php echo adminUrl('home/hero/editar/' . (int) $slide['id']); ?>">Editar</a>
                                    <form action="<?php echo adminUrl('home/hero/eliminar/' . (int) $slide['id']); ?>" method="post" onsubmit="return confirm('¿Eliminar esta diapositiva?');">
                                        <button class="btn btn-outline" style="padding:8px 12px;font-size:12px;" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../../includes/page-bottom.php'; ?>
