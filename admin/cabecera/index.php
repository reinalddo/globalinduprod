<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';

headerEnsureSchema($db);

$pageTitle = 'Cabecera | Administración';
$pageHeader = 'Identidad del menú principal';
$activeNav = 'header';

$headerData = headerFetchSettings($db);
$errors = [];
$successMessage = '';

if (isset($_SESSION['header_flash'])) {
    $flash = $_SESSION['header_flash'];
    unset($_SESSION['header_flash']);
    if (!empty($flash['success'])) {
        $successMessage = (string) $flash['success'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logoLabel = trim($_POST['logo_label'] ?? '');
    $labelLength = function_exists('mb_strlen') ? mb_strlen($logoLabel, 'UTF-8') : strlen($logoLabel);
    if ($labelLength > 40) {
        $errors[] = 'El texto junto al logo no debe superar los 40 caracteres.';
    }

    $uploadedLogo = $_FILES['logo_file'] ?? null;
    $replacingLogo = $uploadedLogo && $uploadedLogo['error'] !== UPLOAD_ERR_NO_FILE;

    if ($replacingLogo && $uploadedLogo['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'No se pudo cargar el archivo seleccionado.';
    }

    if (!$headerData['logo_path'] && !$replacingLogo) {
        $errors[] = 'Debes cargar un logo para el menú.';
    }

    $temporaryLogo = null;
    if ($replacingLogo && !$errors) {
        try {
            $temporaryLogo = headerSaveLogoImage($uploadedLogo);
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if (!$errors) {
        try {
            $logoPathToStore = $headerData['logo_path'];
            if ($temporaryLogo) {
                $logoPathToStore = $temporaryLogo;
            }

            $logoLabelDb = $logoLabel !== '' ? $logoLabel : null;

            if ($headerData['id']) {
                $stmt = $db->prepare('UPDATE site_header SET logo_path = ?, logo_label = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo preparar la actualización de la cabecera.');
                }
                $stmt->bind_param('ssi', $logoPathToStore, $logoLabelDb, $headerData['id']);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $db->prepare('INSERT INTO site_header (logo_path, logo_label) VALUES (?, ?)');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo preparar el registro del logo.');
                }
                $stmt->bind_param('ss', $logoPathToStore, $logoLabelDb);
                $stmt->execute();
                $headerData['id'] = (int) $stmt->insert_id;
                $stmt->close();
            }

            if ($temporaryLogo && $headerData['logo_path'] && $headerData['logo_path'] !== $temporaryLogo) {
                headerDeleteStoredLogo($headerData['logo_path']);
            }

            if ($temporaryLogo) {
                $headerData['logo_path'] = $temporaryLogo;
            }
            $headerData['logo_label'] = $logoLabelDb ?? '';

            $_SESSION['header_flash'] = [
                'success' => 'Logo actualizado correctamente.'
            ];

            header('Location: ' . adminUrl('cabecera'));
            exit;
        } catch (Throwable $exception) {
            $errors[] = 'No se pudo guardar la información de la cabecera. Intenta nuevamente.';
            if ($temporaryLogo) {
                headerDeleteStoredLogo($temporaryLogo);
            }
        }
    } elseif ($temporaryLogo) {
        headerDeleteStoredLogo($temporaryLogo);
    }

    $headerData['logo_label'] = $logoLabel;
}

require_once __DIR__ . '/../includes/page-top.php';
?>
<section>
    <p style="margin:0 0 18px;color:#4b5563;font-size:0.95rem;">Sube un logo en formato PNG, JPG o WebP. Puedes añadir un texto opcional que se mostrará junto a la imagen en el menú principal.</p>

    <?php if ($successMessage !== ''): ?>
        <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;">
            <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
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

    <form method="post" enctype="multipart/form-data">
        <label for="logo_file">Logo del menú</label>
        <input type="file" name="logo_file" id="logo_file" accept="image/png,image/jpeg,image/webp">

        <?php if (!empty($headerData['logo_path'])): ?>
            <p style="margin:10px 0 0;font-size:0.9rem;color:#4b5563;">Logo actual:</p>
            <div style="margin-top:8px;display:inline-flex;flex-direction:column;gap:8px;">
                <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;max-width:220px;background:#f9fafb;padding:12px;display:flex;justify-content:center;">
                    <img src="<?php echo htmlspecialchars(adminAssetUrl($headerData['logo_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="Logo actual" style="width:auto;height:60px;object-fit:contain;">
                </div>
                <span style="font-size:0.8rem;color:#6b7280;word-break:break-all;">Ruta: <?php echo htmlspecialchars($headerData['logo_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if (!empty($headerData['updated_at'])): ?>
                    <span style="font-size:0.8rem;color:#6b7280;">Última actualización: <?php echo htmlspecialchars($headerData['updated_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <label for="logo_label" style="margin-top:22px;">Texto opcional junto al logo</label>
        <input type="text" name="logo_label" id="logo_label" value="<?php echo htmlspecialchars($headerData['logo_label'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="40" placeholder="Ej. Global Induprod">
        <span style="display:block;margin-top:6px;font-size:0.8rem;color:#6b7280;">Déjalo vacío si no deseas mostrar texto adicional.</span>

        <div class="form-actions" style="margin-top:28px;">
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
