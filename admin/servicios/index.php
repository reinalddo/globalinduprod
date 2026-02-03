<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = 'Servicios | Administración';
$pageHeader = 'Contenido de la sección Servicios';
$activeNav = 'services';

servicesEnsureSchema($db);

$heroData = servicesFetchHero($db);
$heroErrors = [];
$heroSuccess = '';
$listSuccess = '';
$listErrors = [];

if (isset($_SESSION['services_flash'])) {
    $flash = $_SESSION['services_flash'];
    unset($_SESSION['services_flash']);
    if (isset($flash['hero']['success'])) {
        $heroSuccess = (string) $flash['hero']['success'];
    }
    if (!empty($flash['list']['success'])) {
        $listSuccess = (string) $flash['list']['success'];
    }
    if (!empty($flash['list']['errors']) && is_array($flash['list']['errors'])) {
        $listErrors = $flash['list']['errors'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'hero') {
        $heroData['kicker'] = trim($_POST['kicker'] ?? '');
        $heroData['title'] = trim($_POST['title'] ?? '');
        $heroData['description'] = trim($_POST['description'] ?? '');
        $heroData['listing_title'] = trim($_POST['listing_title'] ?? '');
        $heroData['listing_description'] = trim($_POST['listing_description'] ?? '');

        $uploadedImage = $_FILES['hero_image'] ?? null;
        $replacingImage = $uploadedImage && $uploadedImage['error'] !== UPLOAD_ERR_NO_FILE;

        if ($heroData['title'] === '') {
            $heroErrors[] = 'El título de la cabecera es obligatorio.';
        }
        if ($heroData['description'] === '') {
            $heroErrors[] = 'El párrafo de la cabecera es obligatorio.';
        }
        if ($heroData['listing_title'] === '') {
            $heroErrors[] = 'El título antes del listado es obligatorio.';
        }
        if ($heroData['listing_description'] === '') {
            $heroErrors[] = 'La descripción antes del listado es obligatoria.';
        }
        if (!$heroData['image_path'] && !$replacingImage) {
            $heroErrors[] = 'Debes cargar una imagen para la cabecera.';
        }
        if ($replacingImage && $uploadedImage['error'] !== UPLOAD_ERR_OK) {
            $heroErrors[] = 'No se pudo cargar la imagen seleccionada.';
        }

        $temporaryPath = null;
        if ($replacingImage && !$heroErrors) {
            try {
                $temporaryPath = servicesSaveOptimizedImage($uploadedImage, 'uploads/services/hero', 'services-hero', 1920, 1080);
            } catch (Throwable $exception) {
                $heroErrors[] = $exception->getMessage();
            }
        }

        if (!$heroErrors) {
            try {
                $newImagePath = $heroData['image_path'];
                if ($temporaryPath) {
                    $newImagePath = $temporaryPath;
                }

                if ($heroData['id']) {
                    $stmt = $db->prepare('UPDATE services_page_hero SET kicker = ?, title = ?, description = ?, listing_title = ?, listing_description = ?, image_path = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                    if ($stmt === false) {
                        throw new RuntimeException('No se pudo preparar la actualización de la cabecera.');
                    }
                    $stmt->bind_param('ssssssi', $heroData['kicker'], $heroData['title'], $heroData['description'], $heroData['listing_title'], $heroData['listing_description'], $newImagePath, $heroData['id']);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $db->prepare('INSERT INTO services_page_hero (kicker, title, description, listing_title, listing_description, image_path) VALUES (?, ?, ?, ?, ?, ?)');
                    if ($stmt === false) {
                        throw new RuntimeException('No se pudo preparar el registro de la cabecera.');
                    }
                    $stmt->bind_param('ssssss', $heroData['kicker'], $heroData['title'], $heroData['description'], $heroData['listing_title'], $heroData['listing_description'], $newImagePath);
                    $stmt->execute();
                    $heroData['id'] = (int) $stmt->insert_id;
                    $stmt->close();
                }

                if ($temporaryPath && $heroData['image_path'] && $heroData['image_path'] !== $temporaryPath) {
                    servicesDeleteStoredImage($heroData['image_path']);
                }

                if ($temporaryPath) {
                    $heroData['image_path'] = $temporaryPath;
                }

                $_SESSION['services_flash'] = [
                    'hero' => ['success' => 'Cabecera actualizada correctamente.']
                ];
                header('Location: ' . adminUrl('servicios') . '#hero');
                exit;
            } catch (Throwable $exception) {
                $heroErrors[] = 'No se pudo guardar la información de la cabecera.';
                if ($temporaryPath) {
                    servicesDeleteStoredImage($temporaryPath);
                }
            }
        } elseif ($temporaryPath) {
            servicesDeleteStoredImage($temporaryPath);
        }
    } elseif ($formType === 'delete_service') {
        $serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
        if ($serviceId <= 0) {
            $_SESSION['services_flash'] = [
                'list' => ['errors' => ['No se especificó el servicio a eliminar.']]
            ];
            header('Location: ' . adminUrl('servicios') . '#listado');
            exit;
        }

        $service = servicesFetchById($db, $serviceId);
        if (!$service) {
            $_SESSION['services_flash'] = [
                'list' => ['errors' => ['El servicio seleccionado no existe.']]
            ];
            header('Location: ' . adminUrl('servicios') . '#listado');
            exit;
        }

        $galleryImages = servicesFetchGallery($db, $serviceId);

        try {
            $stmt = $db->prepare('DELETE FROM services WHERE id = ? LIMIT 1');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo preparar la eliminación.');
            }
            $stmt->bind_param('i', $serviceId);
            $stmt->execute();
            $stmt->close();

            servicesDeleteStoredImage($service['hero_image_path']);
            foreach ($galleryImages as $image) {
                servicesDeleteStoredImage($image['image_path']);
            }

            $_SESSION['services_flash'] = [
                'list' => ['success' => 'Servicio eliminado correctamente.']
            ];
        } catch (Throwable $exception) {
            $_SESSION['services_flash'] = [
                'list' => ['errors' => ['No se pudo eliminar el servicio seleccionado.']]
            ];
        }

        header('Location: ' . adminUrl('servicios') . '#listado');
        exit;
    }
}

$services = servicesFetchAll($db);

require_once __DIR__ . '/../includes/page-top.php';
?>
<section id="hero">
    <h2>Cabecera de la página Servicios</h2>

    <?php if ($heroSuccess !== ''): ?>
        <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;"><?php echo htmlspecialchars($heroSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <?php if ($heroErrors): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($heroErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="form_type" value="hero">
        <label for="services_kicker">Texto pequeño</label>
        <input type="text" name="kicker" id="services_kicker" value="<?php echo htmlspecialchars($heroData['kicker'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="120">

        <label for="services_title">Título</label>
        <input type="text" name="title" id="services_title" value="<?php echo htmlspecialchars($heroData['title'], ENT_QUOTES, 'UTF-8'); ?>" required maxlength="200">

        <label for="services_description">Párrafo</label>
        <textarea name="description" id="services_description" rows="4" required><?php echo htmlspecialchars($heroData['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="services_listing_title">Título antes del listado</label>
        <input type="text" name="listing_title" id="services_listing_title" value="<?php echo htmlspecialchars($heroData['listing_title'], ENT_QUOTES, 'UTF-8'); ?>" required maxlength="200">

        <label for="services_listing_description">Descripción antes del listado</label>
        <textarea name="listing_description" id="services_listing_description" rows="3" required><?php echo htmlspecialchars($heroData['listing_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="services_hero_image">Imagen de cabecera</label>
        <input type="file" name="hero_image" id="services_hero_image" accept="image/*">
        <?php if (!empty($heroData['image_path'])): ?>
            <p style="margin:8px 0 0;font-size:0.9rem;color:#4b5563;">Imagen actual:</p>
            <div style="margin-top:8px;display:inline-block;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;max-width:320px;">
                <img src="<?php echo htmlspecialchars(adminAssetUrl($heroData['image_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="Hero servicios" style="display:block;width:100%;height:auto;">
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar cabecera</button>
        </div>
    </form>
</section>

<section id="listado" style="margin-top:48px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <h2 style="margin:0;">Servicios publicados</h2>
        <a class="btn btn-primary" href="<?php echo adminUrl('servicios/editar'); ?>">Añadir servicio</a>
    </div>

    <?php if ($listSuccess !== ''): ?>
        <div class="empty-state" style="margin:18px 0 0;background:#dcfce7;color:#166534;text-align:left;"><?php echo htmlspecialchars($listSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($listErrors): ?>
        <div class="empty-state" style="margin:18px 0 0;background:#fee2e2;color:#b91c1c;text-align:left;">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($listErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$services): ?>
        <div class="empty-state" style="margin-top:24px;">Aún no se han registrado servicios.</div>
    <?php else: ?>
        <div style="margin-top:24px;overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Slug</th>
                        <th>Inicio</th>
                        <th>Nosotros</th>
                        <th>Actualizado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($service['slug'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $service['is_featured_home'] ? 'Sí' : 'No'; ?></td>
                            <td><?php echo $service['is_featured_about'] ? 'Sí' : 'No'; ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($service['updated_at'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="text-align:right;white-space:nowrap;">
                                <a class="btn btn-outline" href="<?php echo adminUrl('servicios/editar') . '?id=' . (int) $service['id']; ?>">Editar</a>
                                <form method="post" style="display:inline-block;margin-left:8px;" onsubmit="return confirm('¿Seguro que deseas eliminar este servicio? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="form_type" value="delete_service">
                                    <input type="hidden" name="service_id" value="<?php echo (int) $service['id']; ?>">
                                    <button class="btn btn-danger" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
