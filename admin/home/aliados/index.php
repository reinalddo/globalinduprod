<?php
$pageTitle = 'Inicio | Aliados';
$pageHeader = 'Aliados estratégicos';
$activeNav = 'home';
require_once __DIR__ . '/../../includes/page-top.php';

$allies = [];
$errorMessage = '';

try {
    if ($result = $db->query('SELECT id, name, logo_path, is_primary, sort_order, updated_at FROM home_allies ORDER BY is_primary DESC, sort_order ASC, id ASC')) {
        while ($row = $result->fetch_assoc()) {
            $allies[] = $row;
        }
        $result->free();
    }
} catch (Throwable $exception) {
    $errorMessage = 'No se pudo obtener la lista de aliados.';
}
?>
<section>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
        <p style="margin:0;color:#4b5563;">Carga logos principales y adicionales para la sección de aliados.</p>
        <a class="btn btn-primary" href="<?php echo adminUrl('home/aliados/crear'); ?>">Añadir aliado</a>
    </div>

    <?php if ($errorMessage): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif (empty($allies)): ?>
        <div class="empty-state">
            <p style="margin:0 0 12px;">Aún no se han registrado aliados.</p>
            <a class="btn btn-primary" href="<?php echo adminUrl('home/aliados/crear'); ?>">Agregar aliado</a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Logo</th>
                        <th>Tipo</th>
                        <th>Orden</th>
                        <th>Actualizado</th>
                        <th style="width:140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allies as $ally): ?>
                        <tr>
                            <td><?php echo (int) $ally['id']; ?></td>
                            <td><?php echo htmlspecialchars($ally['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="max-width:200px;word-break:break-all;">
                                <span style="font-size:12px;color:#6b7280;"><?php echo htmlspecialchars($ally['logo_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td>
                                <?php if ((int) $ally['is_primary'] === 1): ?>
                                    <span class="badge badge-success">Principal</span>
                                <?php else: ?>
                                    <span class="badge badge-muted">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo (int) $ally['sort_order']; ?></td>
                            <td><?php echo htmlspecialchars($ally['updated_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-outline" style="padding:8px 12px;font-size:12px;" href="<?php echo adminUrl('home/aliados/editar/' . (int) $ally['id']); ?>">Editar</a>
                                    <form action="<?php echo adminUrl('home/aliados/eliminar/' . (int) $ally['id']); ?>" method="post" onsubmit="return confirm('¿Eliminar este aliado?');">
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
