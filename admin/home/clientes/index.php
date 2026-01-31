<?php
$pageTitle = 'Inicio | Clientes';
$pageHeader = 'Clientes destacados';
$activeNav = 'home';
require_once __DIR__ . '/../../includes/page-top.php';

$clients = [];
$errorMessage = '';

try {
    if ($result = $db->query('SELECT id, name, logo_path, sort_order, updated_at FROM home_clients ORDER BY sort_order ASC, id ASC')) {
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
        $result->free();
    }
} catch (Throwable $exception) {
    $errorMessage = 'No se pudo obtener la lista de clientes.';
}
?>
<section>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
        <p style="margin:0;color:#4b5563;">Agrega o actualiza los logos mostrados en la sección de clientes.</p>
        <a class="btn btn-primary" href="<?php echo adminUrl('home/clientes/crear'); ?>">Añadir cliente</a>
    </div>

    <?php if ($errorMessage): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php elseif (empty($clients)): ?>
        <div class="empty-state">
            <p style="margin:0 0 12px;">Aún no se han registrado clientes destacados.</p>
            <a class="btn btn-primary" href="<?php echo adminUrl('home/clientes/crear'); ?>">Agregar cliente</a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Logo</th>
                        <th>Orden</th>
                        <th>Actualizado</th>
                        <th style="width:140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo (int) $client['id']; ?></td>
                            <td><?php echo htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="max-width:200px;word-break:break-all;">
                                <?php $logoUrl = adminAssetUrl((string) $client['logo_path']); ?>
                                <?php if ($logoUrl): ?>
                                    <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo cliente" style="width:100%;max-width:160px;height:80px;object-fit:contain;background:#ffffff;border:1px solid #e5e7eb;border-radius:6px;display:block;padding:6px;">
                                <?php endif; ?>
                                <span style="display:block;font-size:12px;color:#6b7280;margin-top:6px;">&nbsp;<?php echo htmlspecialchars((string) $client['logo_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td><?php echo (int) $client['sort_order']; ?></td>
                            <td><?php echo htmlspecialchars($client['updated_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-outline" style="padding:8px 12px;font-size:12px;" href="<?php echo adminUrl('home/clientes/editar/' . (int) $client['id']); ?>">Editar</a>
                                    <form action="<?php echo adminUrl('home/clientes/eliminar/' . (int) $client['id']); ?>" method="post" onsubmit="return confirm('¿Eliminar este cliente?');">
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
