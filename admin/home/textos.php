<?php
$pageTitle = 'Inicio | Textos destacados';
$pageHeader = 'Textos destacados del inicio';
$activeNav = 'home';
require_once __DIR__ . '/../includes/page-top.php';

$sections = [
    'allies' => ['title' => '', 'body' => ''],
    'services' => ['title' => '', 'body' => ''],
    'clients' => ['title' => '', 'body' => '']
];
$labels = [
    'allies' => 'Nuestros aliados estratégicos',
    'services' => 'Servicios para operaciones industriales críticas',
    'clients' => 'Clientes que confían en Global Induprod'
];

try {
    if ($result = $db->query("SELECT section_key, title, body FROM home_section_copy")) {
        while ($row = $result->fetch_assoc()) {
            $key = $row['section_key'];
            if (isset($sections[$key])) {
                $sections[$key]['title'] = $row['title'];
                $sections[$key]['body'] = $row['body'];
            }
        }
        $result->free();
    }
} catch (Throwable $exception) {
    echo '<div class="empty-state">No se pudieron cargar los textos actuales.</div>';
}

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($sections as $key => $data) {
        $sections[$key]['title'] = trim($_POST[$key . '_title'] ?? '');
        $sections[$key]['body'] = trim($_POST[$key . '_body'] ?? '');

        if ($sections[$key]['title'] === '') {
            $errors[] = 'El título de la sección "' . $labels[$key] . '" es obligatorio.';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare('INSERT INTO home_section_copy (section_key, title, body) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body), updated_at = NOW()');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar la actualización.');
            }
            foreach ($sections as $key => $values) {
                $stmt->bind_param('sss', $key, $values['title'], $values['body']);
                $stmt->execute();
            }
            $stmt->close();
            $success = true;
        } catch (Throwable $exception) {
            $errors[] = 'No se pudieron guardar los textos. Intenta nuevamente.';
        }
    }
}
?>
<section>
    <?php if ($success): ?>
        <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;">Cambios guardados correctamente.</div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <form method="post">
        <?php foreach ($sections as $key => $values): ?>
            <fieldset style="border:none;margin:0 0 26px;padding:0;">
                <legend style="font-weight:700;margin-bottom:14px;font-size:1.05rem;"><?php echo htmlspecialchars($labels[$key], ENT_QUOTES, 'UTF-8'); ?></legend>

                <label for="<?php echo $key; ?>_title">Título</label>
                <input type="text" name="<?php echo $key; ?>_title" id="<?php echo $key; ?>_title" value="<?php echo htmlspecialchars($values['title'], ENT_QUOTES, 'UTF-8'); ?>" required>

                <label for="<?php echo $key; ?>_body">Párrafo</label>
                <textarea name="<?php echo $key; ?>_body" id="<?php echo $key; ?>_body" rows="4"><?php echo htmlspecialchars($values['body'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </fieldset>
        <?php endforeach; ?>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
            <a class="btn btn-outline" href="<?php echo adminUrl('home'); ?>">Volver</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
