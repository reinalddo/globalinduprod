<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = 'Servicios | Editor de servicio';
$pageHeader = 'Gestionar servicio';
$activeNav = 'services';

servicesEnsureSchema($db);

$serviceId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;
    if ($postedId > 0) {
        $serviceId = $postedId;
    }
}

$service = [
    'id' => $serviceId,
    'slug' => '',
    'kicker' => '',
    'title' => '',
    'summary' => '',
    'hero_image_path' => '',
    'content_html' => '',
    'is_featured_home' => false,
    'is_featured_about' => false
];

if ($serviceId > 0) {
    $loaded = servicesFetchById($db, $serviceId);
    if (!$loaded) {
        $_SESSION['services_flash'] = [
            'list' => ['errors' => ['El servicio solicitado no existe.']]
        ];
        header('Location: ' . adminUrl('servicios') . '#listado');
        exit;
    }
    $service = array_merge($service, $loaded);
}

$flashSuccess = '';
$flashErrors = [];
$gallerySuccess = '';
$galleryErrors = [];

if (isset($_SESSION['service_edit_flash'])) {
    $flash = $_SESSION['service_edit_flash'];
    unset($_SESSION['service_edit_flash']);
    if (!empty($flash['success'])) {
        $flashSuccess = (string) $flash['success'];
    }
    if (!empty($flash['errors']) && is_array($flash['errors'])) {
        $flashErrors = $flash['errors'];
    }
    if (!empty($flash['gallery_success'])) {
        $gallerySuccess = (string) $flash['gallery_success'];
    }
    if (!empty($flash['gallery_errors']) && is_array($flash['gallery_errors'])) {
        $galleryErrors = $flash['gallery_errors'];
    }
}

$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'delete_gallery') {
        $imageId = isset($_POST['image_id']) ? (int) $_POST['image_id'] : 0;
        $ownerId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : 0;

        if ($service['id'] <= 0 || $ownerId !== $service['id']) {
            $_SESSION['service_edit_flash'] = [
                'gallery_errors' => ['No se encontró el servicio asociado a la imagen.']
            ];
            header('Location: ' . adminUrl('servicios/editar.php' . ($serviceId > 0 ? '?id=' . $serviceId : '')) . '#galeria');
            exit;
        }

        if ($imageId <= 0) {
            $_SESSION['service_edit_flash'] = [
                'gallery_errors' => ['No se pudo identificar la imagen a eliminar.']
            ];
            header('Location: ' . adminUrl('servicios/editar.php?id=' . $service['id']) . '#galeria');
            exit;
        }

        $stmt = $db->prepare('SELECT image_path FROM service_gallery_images WHERE id = ? AND service_id = ? LIMIT 1');
        if ($stmt === false) {
            $_SESSION['service_edit_flash'] = [
                'gallery_errors' => ['No se pudo eliminar la imagen seleccionada.']
            ];
            header('Location: ' . adminUrl('servicios/editar.php?id=' . $service['id']) . '#galeria');
            exit;
        }
        $stmt->bind_param('ii', $imageId, $service['id']);
        $imagePath = null;
        if ($stmt->execute()) {
            $stmt->bind_result($storedPath);
            if ($stmt->fetch()) {
                $imagePath = (string) $storedPath;
            }
        }
        $stmt->close();

        if (!$imagePath) {
            $_SESSION['service_edit_flash'] = [
                'gallery_errors' => ['No se encontró la imagen seleccionada.']
            ];
            header('Location: ' . adminUrl('servicios/editar.php?id=' . $service['id']) . '#galeria');
            exit;
        }

        $stmt = $db->prepare('DELETE FROM service_gallery_images WHERE id = ? AND service_id = ? LIMIT 1');
        if ($stmt === false) {
            $_SESSION['service_edit_flash'] = [
                'gallery_errors' => ['No se pudo eliminar la imagen seleccionada.']
            ];
            header('Location: ' . adminUrl('servicios/editar.php?id=' . $service['id']) . '#galeria');
            exit;
        }
        $stmt->bind_param('ii', $imageId, $service['id']);
        if ($stmt->execute()) {
            servicesDeleteStoredImage($imagePath);
            $_SESSION['service_edit_flash'] = [
                'gallery_success' => 'Imagen eliminada de la galería.'
            ];
        } else {
            $_SESSION['service_edit_flash'] = [
                'gallery_errors' => ['No se pudo eliminar la imagen seleccionada.']
            ];
        }
        $stmt->close();

        header('Location: ' . adminUrl('servicios/editar.php?id=' . $service['id']) . '#galeria');
        exit;
    }

    if ($formType === 'service') {
        $service['kicker'] = trim($_POST['kicker'] ?? '');
        $service['title'] = trim($_POST['title'] ?? '');
        $service['summary'] = trim($_POST['summary'] ?? '');
        $service['content_html'] = trim($_POST['content_html'] ?? '');
        $service['is_featured_home'] = isset($_POST['is_featured_home']);
        $service['is_featured_about'] = isset($_POST['is_featured_about']);

        $slugSource = trim($_POST['slug'] ?? '');
        if ($slugSource === '') {
            $slugSource = $service['title'];
        }
        try {
            $service['slug'] = servicesSlugify($slugSource);
        } catch (Throwable $exception) {
            $formErrors[] = 'No se pudo generar el identificador del servicio.';
        }

        if ($service['title'] === '') {
            $formErrors[] = 'El título del servicio es obligatorio.';
        }
        if ($service['summary'] === '') {
            $formErrors[] = 'El párrafo principal es obligatorio.';
        }
        if ($service['content_html'] === '') {
            $formErrors[] = 'La descripción detallada en HTML es obligatoria.';
        }

        if ($service['slug'] === '') {
            $formErrors[] = 'El slug del servicio no puede quedar vacío.';
        } else {
            $stmt = $db->prepare('SELECT id FROM services WHERE slug = ? AND id <> ? LIMIT 1');
            if ($stmt === false) {
                $formErrors[] = 'No se pudo validar el slug del servicio.';
            } else {
                $currentId = $service['id'] > 0 ? $service['id'] : 0;
                $stmt->bind_param('si', $service['slug'], $currentId);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows > 0) {
                        $formErrors[] = 'Ya existe otro servicio con el mismo slug.';
                    }
                } else {
                    $formErrors[] = 'No se pudo validar el slug del servicio.';
                }
                $stmt->close();

        $uploadedHero = $_FILES['hero_image'] ?? null;
        $replacingHero = $uploadedHero && $uploadedHero['error'] !== UPLOAD_ERR_NO_FILE;

        if ($service['id'] <= 0 && !$replacingHero) {
            $formErrors[] = 'Debes cargar una imagen para la cabecera del servicio.';
        }
        if ($replacingHero && $uploadedHero['error'] !== UPLOAD_ERR_OK) {
            $formErrors[] = 'No se pudo cargar la imagen del hero del servicio.';
        }

        $temporaryHero = null;
        $pendingGallery = [];
        $galleryUploads = $_FILES['gallery_images'] ?? null;

        if ($replacingHero && !$formErrors) {
            try {
                $temporaryHero = servicesSaveOptimizedImage($uploadedHero, 'uploads/services/items', 'service-hero', 1920, 1080);
            } catch (Throwable $exception) {
                $formErrors[] = $exception->getMessage();
            }
        }

        if ($galleryUploads && isset($galleryUploads['name']) && is_array($galleryUploads['name'])) {
            $filesCount = count($galleryUploads['name']);
            for ($index = 0; $index < $filesCount; $index++) {
                $fileError = $galleryUploads['error'][$index];
                if ($fileError === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ($fileError !== UPLOAD_ERR_OK) {
                    $formErrors[] = 'No se pudo cargar una de las imágenes de la galería.';
                    continue;
                }
                $singleFile = [
                    'name' => $galleryUploads['name'][$index],
                    'type' => $galleryUploads['type'][$index] ?? '',
                    'tmp_name' => $galleryUploads['tmp_name'][$index],
                    'error' => $galleryUploads['error'][$index],
                    'size' => $galleryUploads['size'][$index]
                ];
                try {
                    $optimizedPath = servicesSaveOptimizedImage($singleFile, 'uploads/services/gallery', 'service-gallery', 1600, 1200);
                    $pendingGallery[] = $optimizedPath;
                } catch (Throwable $exception) {
                    $formErrors[] = $exception->getMessage();
                }
            }
        }

        if ($formErrors) {
            if ($temporaryHero) {
                servicesDeleteStoredImage($temporaryHero);
            }
            if ($pendingGallery) {
                foreach ($pendingGallery as $path) {
                    servicesDeleteStoredImage($path);
                }
            }
        } else {
            $newHeroPath = $service['hero_image_path'];
            $oldHeroPath = $service['hero_image_path'];
            if ($temporaryHero) {
                $newHeroPath = $temporaryHero;
            }

            try {
                $homeFlag = $service['is_featured_home'] ? 1 : 0;
                $aboutFlag = $service['is_featured_about'] ? 1 : 0;

                if ($service['id'] > 0) {
                    $stmt = $db->prepare('UPDATE services SET slug = ?, kicker = ?, title = ?, summary = ?, hero_image_path = ?, content_html = ?, is_featured_home = ?, is_featured_about = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                    if ($stmt === false) {
                        throw new RuntimeException('No se pudo preparar la actualización del servicio.');
                    }
                    $stmt->bind_param('ssssssiii', $service['slug'], $service['kicker'], $service['title'], $service['summary'], $newHeroPath, $service['content_html'], $homeFlag, $aboutFlag, $service['id']);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $db->prepare('INSERT INTO services (slug, kicker, title, summary, hero_image_path, content_html, is_featured_home, is_featured_about) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                    if ($stmt === false) {
                        throw new RuntimeException('No se pudo preparar el registro del servicio.');
                    }
                    $stmt->bind_param('ssssssii', $service['slug'], $service['kicker'], $service['title'], $service['summary'], $newHeroPath, $service['content_html'], $homeFlag, $aboutFlag);
                    $stmt->execute();
                    $service['id'] = (int) $stmt->insert_id;
                    $stmt->close();
                }

                if ($temporaryHero && $oldHeroPath && $oldHeroPath !== $temporaryHero) {
                    servicesDeleteStoredImage($oldHeroPath);
                }
                if ($temporaryHero) {
                    $service['hero_image_path'] = $temporaryHero;
                }

                $galleryInsertErrors = [];
                if ($pendingGallery && $service['id'] > 0) {
                    $baseOrder = servicesNextGalleryOrder($db, $service['id']);
                    $stmt = $db->prepare('INSERT INTO service_gallery_images (service_id, image_path, sort_order) VALUES (?, ?, ?)');
                    if ($stmt === false) {
                        $galleryInsertErrors[] = 'No se pudo guardar las nuevas imágenes de la galería.';
                        foreach ($pendingGallery as $path) {
                            servicesDeleteStoredImage($path);
                        }
                    } else {
                        $serviceIdParam = $service['id'];
                        $imagePathParam = '';
                        $sortOrderParam = 0;
                        $stmt->bind_param('isi', $serviceIdParam, $imagePathParam, $sortOrderParam);
                        foreach ($pendingGallery as $path) {
                            $imagePathParam = $path;
                            $sortOrderParam = ++$baseOrder;
                            if (!$stmt->execute()) {
                                $galleryInsertErrors[] = 'No se pudo guardar una de las imágenes de la galería.';
                                servicesDeleteStoredImage($path);
                                $baseOrder--;
                            }
                        }
                        $stmt->close();
                    }
                }

                $flashPayload = ['success' => 'Servicio guardado correctamente.'];
                if (!empty($galleryInsertErrors)) {
                    $flashPayload['gallery_errors'] = $galleryInsertErrors;
                } elseif ($pendingGallery) {
                    $flashPayload['gallery_success'] = 'Se añadieron ' . count($pendingGallery) . ' imágenes a la galería.';
                }
                $_SESSION['service_edit_flash'] = $flashPayload;

                header('Location: ' . adminUrl('servicios/editar.php?id=' . $service['id']) . '#detalles');
                exit;
            } catch (Throwable $exception) {
                $formErrors[] = 'No se pudo guardar la información del servicio.';
                if ($temporaryHero) {
                    servicesDeleteStoredImage($temporaryHero);
                }
                if ($pendingGallery) {
                    foreach ($pendingGallery as $path) {
                        servicesDeleteStoredImage($path);
                    }
                }
            }
        }
    }
}

if ($flashErrors) {
    $formErrors = array_merge($flashErrors, $formErrors);
}

$galleryItems = $service['id'] > 0 ? servicesFetchGallery($db, $service['id']) : [];

$pageHeader = $service['id'] > 0 ? 'Editar servicio: ' . $service['title'] : 'Crear nuevo servicio';
$pageTitle = $service['id'] > 0 ? 'Servicios | Editar ' . $service['title'] : 'Servicios | Nuevo servicio';

require_once __DIR__ . '/../includes/page-top.php';
?>
<section id="detalles">
    <h2><?php echo $service['id'] > 0 ? 'Actualizar servicio existente' : 'Nuevo servicio'; ?></h2>

    <?php if ($flashSuccess !== ''): ?>
        <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;"><?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <?php if ($formErrors): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($formErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="form_type" value="service">
        <input type="hidden" name="service_id" value="<?php echo (int) $service['id']; ?>">

        <fieldset style="border:none;margin:0;padding:0 0 24px;">
            <legend style="font-weight:700;margin-bottom:16px;font-size:1.1rem;">Cabecera del servicio</legend>

            <label for="service_kicker">Texto pequeño</label>
            <input type="text" name="kicker" id="service_kicker" maxlength="120" value="<?php echo htmlspecialchars($service['kicker'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="service_title">Título</label>
            <input type="text" name="title" id="service_title" maxlength="200" required value="<?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="service_slug">Slug (enlaces del sitio)</label>
            <input type="text" name="slug" id="service_slug" maxlength="150" value="<?php echo htmlspecialchars($service['slug'], ENT_QUOTES, 'UTF-8'); ?>">
            <p style="margin:-10px 0 18px;font-size:0.9rem;color:#6b7280;">Se usará para las URL. Si se deja vacío se generará automáticamente.</p>

            <label for="service_summary">Párrafo principal</label>
            <textarea name="summary" id="service_summary" rows="4" required><?php echo htmlspecialchars($service['summary'], ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label for="service_hero_image">Imagen de cabecera</label>
            <input type="file" name="hero_image" id="service_hero_image" accept="image/*">
            <?php if (!empty($service['hero_image_path'])): ?>
                <p style="margin:8px 0 0;font-size:0.9rem;color:#4b5563;">Imagen actual:</p>
                <div style="margin-top:8px;display:inline-block;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;max-width:320px;">
                    <img src="<?php echo htmlspecialchars(adminAssetUrl($service['hero_image_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="Vista previa del servicio" style="display:block;width:100%;height:auto;">
                </div>
            <?php endif; ?>
        </fieldset>

        <fieldset style="border:none;margin:0;padding:0 0 24px;">
            <legend style="font-weight:700;margin-bottom:16px;font-size:1.1rem;">Contenido detallado</legend>

            <label for="service_content_html">Descripción en HTML</label>
            <textarea name="content_html" id="service_content_html" rows="10" required><?php echo htmlspecialchars($service['content_html'], ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label for="service_gallery_input_0">Añadir imágenes a la galería</label>
            <div data-gallery-inputs>
                <div style="margin-bottom:12px;">
                    <input type="file" name="gallery_images[]" id="service_gallery_input_0" accept="image/*" multiple>
                </div>
            </div>
            <button class="btn btn-outline" type="button" data-add-gallery-input>Añadir otra imagen</button>
            <p style="margin:12px 0 18px;font-size:0.9rem;color:#6b7280;">Puedes cargar imágenes desde distintas carpetas; cada nueva fila permite elegir otro archivo. Se optimizarán automáticamente.</p>
            <template id="service-gallery-input-template">
                <div style="margin-bottom:12px;">
                    <input type="file" name="gallery_images[]" accept="image/*" multiple>
                </div>
            </template>

            <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:8px;">
                <label style="display:flex;align-items:center;gap:8px;margin:0;">
                    <input type="checkbox" name="is_featured_home" value="1" <?php echo $service['is_featured_home'] ? 'checked' : ''; ?>>
                    Mostrar como destacado en Inicio
                </label>
                <label style="display:flex;align-items:center;gap:8px;margin:0;">
                    <input type="checkbox" name="is_featured_about" value="1" <?php echo $service['is_featured_about'] ? 'checked' : ''; ?>>
                    Mostrar como destacado en Nosotros
                </label>
            </div>
        </fieldset>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar servicio</button>
            <a class="btn btn-outline" href="<?php echo adminUrl('servicios'); ?>">Volver al listado</a>
        </div>
    </form>
</section>

<section id="galeria" style="margin-top:48px;">
    <h2>Galería del servicio</h2>

    <?php if ($gallerySuccess !== ''): ?>
        <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;"><?php echo htmlspecialchars($gallerySuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <?php if ($galleryErrors): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($galleryErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <?php if ($service['id'] <= 0): ?>
        <div class="empty-state" style="margin-top:16px;">Guarda el servicio para gestionar la galería.</div>
    <?php elseif (!$galleryItems): ?>
        <div class="empty-state" style="margin-top:16px;">Este servicio aún no tiene imágenes en la galería.</div>
    <?php else: ?>
        <div style="display:grid;gap:18px;margin-top:24px;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));">
            <?php foreach ($galleryItems as $item): ?>
                <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:12px;box-shadow:0 6px 14px rgba(15,23,42,0.06);">
                    <div style="border-radius:8px;overflow:hidden;margin-bottom:12px;">
                        <img src="<?php echo htmlspecialchars(adminAssetUrl($item['image_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen de galería" style="display:block;width:100%;height:auto;">
                    </div>
                    <form method="post" onsubmit="return confirm('¿Deseas eliminar esta imagen de la galería?');">
                        <input type="hidden" name="form_type" value="delete_gallery">
                        <input type="hidden" name="service_id" value="<?php echo (int) $service['id']; ?>">
                        <input type="hidden" name="image_id" value="<?php echo (int) $item['id']; ?>">
                        <button class="btn btn-danger" type="submit" style="width:100%;">Eliminar imagen</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addButton = document.querySelector('[data-add-gallery-input]');
        const inputsContainer = document.querySelector('[data-gallery-inputs]');
        const template = document.getElementById('service-gallery-input-template');

        if (!addButton || !inputsContainer || !template) {
            return;
        }

        let counter = inputsContainer.querySelectorAll('input[type="file"]').length;

        addButton.addEventListener('click', function () {
            const fragment = template.content.cloneNode(true);
            const newInput = fragment.querySelector('input[type="file"]');
            if (!newInput) {
                return;
            }

            counter += 1;
            newInput.id = 'service_gallery_input_' + counter;

            inputsContainer.appendChild(fragment);
            newInput.focus();
        });

        if (typeof ClassicEditor !== 'undefined') {
            ClassicEditor.create(document.querySelector('#service_content_html'), {
                toolbar: [
                    'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo'
                ],
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Párrafo', class: 'ck-heading_paragraph' },
                        { model: 'heading2', view: 'h2', title: 'Encabezado 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Encabezado 3', class: 'ck-heading_heading3' }
                    ]
                }
            }).then(function (editor) {
                editor.editing.view.change(function (writer) {
                    writer.setStyle('min-height', '360px', editor.editing.view.document.getRoot());
                });
            }).catch(function (error) {
                console.error('No se pudo inicializar el editor:', error);
            });
        }
    });
</script>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
