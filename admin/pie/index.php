<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';

footerEnsureSchema($db);
footerEnsureDefaultSocials($db);

$pageTitle = 'Pie de página | Administración';
$pageHeader = 'Contenido del pie de página';
$activeNav = 'footer';

$settings = footerFetchSettings($db);
$socials = footerFetchSocials($db);

$settingsErrors = [];
$settingsSuccess = '';
$socialErrors = [];
$socialSuccess = '';
$socialFormOld = ['name' => '', 'url' => ''];

if (isset($_SESSION['footer_flash'])) {
    $flash = $_SESSION['footer_flash'];
    unset($_SESSION['footer_flash']);

    if (!empty($flash['settings_errors']) && is_array($flash['settings_errors'])) {
        $settingsErrors = $flash['settings_errors'];
    }

    if (!empty($flash['settings_success'])) {
        $settingsSuccess = (string) $flash['settings_success'];
    }

    if (!empty($flash['social_errors']) && is_array($flash['social_errors'])) {
        $socialErrors = $flash['social_errors'];
    }

    if (!empty($flash['social_success'])) {
        $socialSuccess = (string) $flash['social_success'];
    }

    if (!empty($flash['settings_old']) && is_array($flash['settings_old'])) {
        if (array_key_exists('contact_text', $flash['settings_old'])) {
            $settings['contact_text'] = (string) $flash['settings_old']['contact_text'];
        }
        if (array_key_exists('rights_text', $flash['settings_old'])) {
            $settings['rights_text'] = (string) $flash['settings_old']['rights_text'];
        }
    }

    if (!empty($flash['social_old']) && is_array($flash['social_old'])) {
        $socialFormOld = array_merge($socialFormOld, $flash['social_old']);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? 'settings';
    $isAjax = false;
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $isAjax = true;
    } elseif (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        $isAjax = true;
    }

    if ($formType === 'settings') {
        $contactText = trim($_POST['contact_text'] ?? '');
        $rightsText = trim($_POST['rights_text'] ?? '');
        $logoFile = $_FILES['logo_file'] ?? null;
        $errors = [];

        $rightsLength = function_exists('mb_strlen') ? mb_strlen($rightsText, 'UTF-8') : strlen($rightsText);
        if ($rightsLength > 255) {
            $errors[] = 'El texto legal no debe superar los 255 caracteres.';
        }

        $replacingLogo = $logoFile && $logoFile['error'] !== UPLOAD_ERR_NO_FILE;
        if ($replacingLogo && $logoFile['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'No se pudo cargar el logo seleccionado.';
        }

        if (!$settings['logo_path'] && !$replacingLogo) {
            $errors[] = 'Debes cargar un logo para el pie de página.';
        }

        $temporaryLogo = null;
        if ($replacingLogo && !$errors) {
            try {
                $temporaryLogo = footerSaveLogoImage($logoFile);
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if ($errors) {
            if ($temporaryLogo) {
                footerDeleteStoredLogo($temporaryLogo);
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'errors' => $errors
                ]);
                exit;
            }
            $_SESSION['footer_flash'] = [
                'settings_errors' => $errors,
                'settings_old' => [
                    'contact_text' => $contactText,
                    'rights_text' => $rightsText,
                ]
            ];
            header('Location: ' . adminUrl('pie'));
            exit;
        }

        try {
            $logoPathToStore = $settings['logo_path'];
            if ($temporaryLogo) {
                $logoPathToStore = $temporaryLogo;
            }

            $contactDb = $contactText !== '' ? $contactText : null;
            $rightsDb = $rightsText !== '' ? $rightsText : '';

            if ($settings['id']) {
                $stmt = $db->prepare('UPDATE site_footer_settings SET logo_path = ?, contact_text = ?, rights_text = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo guardar el contenido del pie.');
                }
                $stmt->bind_param('sssi', $logoPathToStore, $contactDb, $rightsDb, $settings['id']);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $db->prepare('INSERT INTO site_footer_settings (logo_path, contact_text, rights_text) VALUES (?, ?, ?)');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo guardar el contenido del pie.');
                }
                $stmt->bind_param('sss', $logoPathToStore, $contactDb, $rightsDb);
                $stmt->execute();
                $stmt->close();
            }

            if ($temporaryLogo && $settings['logo_path'] && $settings['logo_path'] !== $temporaryLogo) {
                footerDeleteStoredLogo($settings['logo_path']);
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Contenido del pie actualizado correctamente.',
                    'logoUrl' => $logoPathToStore !== '' ? adminAssetUrl($logoPathToStore) : null,
                    'logoPath' => $logoPathToStore,
                    'contactText' => $contactDb ?? '',
                    'rightsText' => $rightsDb
                ]);
                exit;
            }

            $_SESSION['footer_flash'] = [
                'settings_success' => 'Contenido del pie actualizado correctamente.'
            ];
            header('Location: ' . adminUrl('pie'));
            exit;
        } catch (Throwable $exception) {
            if ($temporaryLogo) {
                footerDeleteStoredLogo($temporaryLogo);
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'errors' => ['No se pudo guardar la información del pie de página. Intenta nuevamente.']
                ]);
                exit;
            }
            $_SESSION['footer_flash'] = [
                'settings_errors' => ['No se pudo guardar la información del pie de página. Intenta nuevamente.'],
                'settings_old' => [
                    'contact_text' => $contactText,
                    'rights_text' => $rightsText,
                ]
            ];
            header('Location: ' . adminUrl('pie'));
            exit;
        }
    } elseif ($formType === 'social_update') {
        $socialId = isset($_POST['social_id']) ? (int) $_POST['social_id'] : 0;
        $name = trim($_POST['name'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $iconFile = $_FILES['icon_file'] ?? null;
        $errors = [];

        $social = $socialId > 0 ? footerFindSocialById($db, $socialId) : null;
        if (!$social) {
            $errors[] = 'La red social seleccionada no existe.';
        }

        if ($name === '') {
            $errors[] = 'El nombre de la red social es obligatorio.';
        }

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            $errors[] = 'Debes ingresar un enlace válido (incluye http o https).';
        }

        $temporaryIcon = null;
        if ($social && !$social['is_default'] && $iconFile && $iconFile['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($iconFile['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'No se pudo cargar el icono seleccionado.';
            } elseif (!$errors) {
                try {
                    $temporaryIcon = footerSaveCustomIcon($iconFile);
                } catch (Throwable $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        }

        if ($errors) {
            if ($temporaryIcon) {
                footerDeleteStoredLogo($temporaryIcon);
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'errors' => $errors
                ]);
                exit;
            }
            $_SESSION['footer_flash'] = [
                'social_errors' => $errors
            ];
            header('Location: ' . adminUrl('pie') . '#sociales');
            exit;
        }

        try {
            $iconPathToStore = $social['icon_path'];
            if ($temporaryIcon) {
                $iconPathToStore = $temporaryIcon;
            }

            if ($social['is_default']) {
                $stmt = $db->prepare('UPDATE site_footer_socials SET name = ?, url = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo actualizar la red social.');
                }
                $stmt->bind_param('ssi', $name, $url, $socialId);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $db->prepare('UPDATE site_footer_socials SET name = ?, url = ?, icon_path = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo actualizar la red social.');
                }
                $stmt->bind_param('sssi', $name, $url, $iconPathToStore, $socialId);
                $stmt->execute();
                $stmt->close();
            }

            if ($temporaryIcon && $social['icon_path'] && $social['icon_path'] !== $temporaryIcon) {
                footerDeleteStoredLogo($social['icon_path']);
            }

            if ($isAjax) {
                $iconUrl = null;
                if ($social['icon_key']) {
                    $iconUrl = adminAssetUrl('assets/img/icons/' . $social['icon_key'] . '.svg');
                } elseif ($iconPathToStore) {
                    $iconUrl = adminAssetUrl($iconPathToStore);
                }

                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Red social actualizada correctamente.',
                    'social' => [
                        'id' => (int) $social['id'],
                        'name' => $name,
                        'url' => $url,
                        'icon_key' => $social['icon_key'],
                        'icon_path' => $social['is_default'] ? null : $iconPathToStore,
                        'icon_url' => $iconUrl,
                        'is_default' => (bool) $social['is_default']
                    ]
                ]);
                exit;
            }

            $_SESSION['footer_flash'] = [
                'social_success' => 'Red social actualizada correctamente.'
            ];
            header('Location: ' . adminUrl('pie') . '#sociales');
            exit;
        } catch (Throwable $exception) {
            if ($temporaryIcon) {
                footerDeleteStoredLogo($temporaryIcon);
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'errors' => ['No se pudo guardar los cambios de la red social. Intenta nuevamente.']
                ]);
                exit;
            }
            $_SESSION['footer_flash'] = [
                'social_errors' => ['No se pudo guardar los cambios de la red social. Intenta nuevamente.']
            ];
            header('Location: ' . adminUrl('pie') . '#sociales');
            exit;
        }
    } elseif ($formType === 'social_create') {
        $name = trim($_POST['new_name'] ?? '');
        $url = trim($_POST['new_url'] ?? '');
        $iconFile = $_FILES['new_icon'] ?? null;
        $errors = [];

        if ($name === '') {
            $errors[] = 'Debes ingresar el nombre de la red social.';
        }
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            $errors[] = 'Debes ingresar un enlace válido (incluye http o https).';
        }
        if (!$iconFile || $iconFile['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Debes cargar el icono de la red social.';
        }

        $temporaryIcon = null;
        if (!$errors) {
            try {
                $temporaryIcon = footerSaveCustomIcon($iconFile);
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if ($errors) {
            if ($temporaryIcon) {
                footerDeleteStoredLogo($temporaryIcon);
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'errors' => $errors
                ]);
                exit;
            }
            $_SESSION['footer_flash'] = [
                'social_errors' => $errors,
                'social_old' => ['name' => $name, 'url' => $url]
            ];
            header('Location: ' . adminUrl('pie') . '#sociales');
            exit;
        }

        try {
            $sortOrder = 100;
            if ($result = $db->query('SELECT MAX(sort_order) AS max_sort FROM site_footer_socials')) {
                if ($row = $result->fetch_assoc()) {
                    $maxSort = isset($row['max_sort']) ? (int) $row['max_sort'] : 100;
                    $sortOrder = $maxSort + 10;
                }
                $result->free();
            }

            $stmt = $db->prepare('INSERT INTO site_footer_socials (name, url, icon_path, sort_order) VALUES (?, ?, ?, ?)');
            if ($stmt === false) {
                throw new RuntimeException('No se pudo registrar la red social.');
            }
            $stmt->bind_param('sssi', $name, $url, $temporaryIcon, $sortOrder);
            $stmt->execute();
            $newId = (int) $db->insert_id;
            $stmt->close();

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Red social añadida correctamente.',
                    'social' => [
                        'id' => $newId,
                        'name' => $name,
                        'url' => $url,
                        'icon_key' => null,
                        'icon_path' => $temporaryIcon,
                        'icon_url' => $temporaryIcon ? adminAssetUrl($temporaryIcon) : null,
                        'is_default' => false
                    ]
                ]);
                exit;
            }

            $_SESSION['footer_flash'] = [
                'social_success' => 'Red social añadida correctamente.'
            ];
            header('Location: ' . adminUrl('pie') . '#sociales');
            exit;
        } catch (Throwable $exception) {
            if ($temporaryIcon) {
                footerDeleteStoredLogo($temporaryIcon);
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'errors' => ['No se pudo registrar la nueva red social. Intenta nuevamente.']
                ]);
                exit;
            }
            $_SESSION['footer_flash'] = [
                'social_errors' => ['No se pudo registrar la nueva red social. Intenta nuevamente.']
            ];
            header('Location: ' . adminUrl('pie') . '#sociales');
            exit;
        }
    } elseif ($formType === 'social_delete') {
        $socialId = isset($_POST['social_id']) ? (int) $_POST['social_id'] : 0;
        $deleted = ($socialId > 0) && footerDeleteSocial($db, $socialId);

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($deleted) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Red social eliminada correctamente.',
                    'removedId' => $socialId
                ]);
                exit;
            }
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'errors' => ['No se pudo eliminar la red social seleccionada.']
            ]);
            exit;
        }

        if ($deleted) {
            $_SESSION['footer_flash'] = [
                'social_success' => 'Red social eliminada correctamente.'
            ];
        } else {
            $_SESSION['footer_flash'] = [
                'social_errors' => ['No se pudo eliminar la red social seleccionada.']
            ];
        }
        header('Location: ' . adminUrl('pie') . '#sociales');
        exit;
    }
}

require_once __DIR__ . '/../includes/page-top.php';
?>
<section>
    <p style="margin:0 0 18px;color:#4b5563;font-size:0.95rem;">Controla la imagen del logotipo, los datos de contacto y el texto legal del pie de página mostrado en el sitio público.</p>

    <?php if ($settingsSuccess !== ''): ?>
        <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;">
            <?php echo htmlspecialchars($settingsSuccess, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <?php if ($settingsErrors): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($settingsErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="form_type" value="settings">
        <label for="logo_file">Logo del pie de página</label>
        <input type="file" name="logo_file" id="logo_file" accept="image/png,image/jpeg,image/webp">

        <?php if (!empty($settings['logo_path'])): ?>
            <p style="margin:10px 0 0;font-size:0.9rem;color:#4b5563;">Logo actual:</p>
            <div style="margin-top:8px;display:inline-flex;flex-direction:column;gap:8px;">
                <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;max-width:200px;background:#f9fafb;padding:12px;display:flex;justify-content:center;">
                    <img src="<?php echo htmlspecialchars(adminAssetUrl($settings['logo_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="Logo actual" style="width:auto;height:70px;object-fit:contain;">
                </div>
                <span style="font-size:0.8rem;color:#6b7280;word-break:break-all;">Ruta: <?php echo htmlspecialchars($settings['logo_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if (!empty($settings['updated_at'])): ?>
                    <span style="font-size:0.8rem;color:#6b7280;">Última actualización: <?php echo htmlspecialchars($settings['updated_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <label for="contact_text" style="margin-top:24px;">Información de contacto</label>
        <textarea name="contact_text" id="contact_text" rows="5" placeholder="Ejemplo:&#10;Global Induprod&#10;Dirección completa&#10;Correo&#10;Teléfono"><?php echo htmlspecialchars($settings['contact_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        <span style="display:block;margin-top:6px;font-size:0.8rem;color:#6b7280;">Se mostrará tal como lo escribas, respetando saltos de línea.</span>

        <label for="rights_text" style="margin-top:24px;">Texto legal</label>
        <input type="text" name="rights_text" id="rights_text" value="<?php echo htmlspecialchars($settings['rights_text'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" placeholder="© 2026 Global Induprod. Todos los derechos reservados.">

        <div class="form-actions" style="margin-top:28px;">
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
        </div>
    </form>
</section>

<section id="sociales" style="margin-top:48px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <h2 style="margin:0;">Redes sociales</h2>
    </div>

    <p style="margin:12px 0 18px;color:#4b5563;font-size:0.95rem;">Edita los enlaces de Facebook, Instagram, YouTube y TikTok. Además, puedes añadir redes personalizadas con su propio icono.</p>

    <?php if ($socialSuccess !== ''): ?>
        <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;">
            <?php echo htmlspecialchars($socialSuccess, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <?php if ($socialErrors): ?>
        <div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($socialErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div style="height:16px;"></div>
    <?php endif; ?>

    <div data-social-feedback style="display:none;"></div>

    <?php if (!$socials): ?>
        <div class="empty-state" data-social-empty>No hay redes sociales configuradas.</div>
    <?php else: ?>
        <div style="margin-top:12px;display:grid;gap:18px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));" data-social-grid>
            <?php foreach ($socials as $social): ?>
                <div style="border:1px solid #e5e7eb;border-radius:12px;padding:18px;background:#ffffff;box-shadow:0 6px 20px rgba(15,23,42,0.07);display:flex;flex-direction:column;gap:12px;" data-social-card data-social-id="<?php echo (int) $social['id']; ?>">
                    <form method="post" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px;" data-social-form>
                        <input type="hidden" name="form_type" value="social_update">
                        <input type="hidden" name="social_id" value="<?php echo (int) $social['id']; ?>">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <?php if ($social['icon_key']): ?>
                                <span style="display:inline-flex;width:42px;height:42px;border-radius:8px;background:#f3f4f6;align-items:center;justify-content:center;">
                                    <img src="<?php echo htmlspecialchars(adminAssetUrl('assets/img/icons/' . $social['icon_key'] . '.svg'), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($social['name'], ENT_QUOTES, 'UTF-8'); ?>" style="width:22px;height:22px;">
                                </span>
                            <?php elseif ($social['icon_path']): ?>
                                <span style="display:inline-flex;width:42px;height:42px;border-radius:8px;background:#f3f4f6;align-items:center;justify-content:center;">
                                    <img src="<?php echo htmlspecialchars(adminAssetUrl($social['icon_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($social['name'], ENT_QUOTES, 'UTF-8'); ?>" style="width:22px;height:22px;object-fit:contain;">
                                </span>
                            <?php endif; ?>
                            <strong style="font-size:1rem;color:#111111;">Editar <?php echo htmlspecialchars($social['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label>Nombre</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($social['name'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="120" required>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <label>Enlace</label>
                            <input type="url" name="url" value="<?php echo htmlspecialchars($social['url'], ENT_QUOTES, 'UTF-8'); ?>" required placeholder="https://">
                        </div>
                        <?php if (!$social['is_default']): ?>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label>Icono (opcional)</label>
                                <input type="file" name="icon_file" accept="image/png,image/svg+xml,image/webp">
                                <?php if ($social['icon_path']): ?>
                                    <span style="font-size:0.8rem;color:#6b7280;word-break:break-all;" data-current-icon-path>Icono actual: <?php echo htmlspecialchars($social['icon_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span style="font-size:0.8rem;color:#6b7280;">El icono de esta red es fijo.</span>
                        <?php endif; ?>
                        <div style="display:flex;gap:10px;">
                            <button class="btn btn-primary" type="submit" style="flex:1;">Guardar</button>
                        </div>
                    </form>
                    <?php if (!$social['is_default']): ?>
                        <form method="post" onsubmit="return confirm('¿Eliminar esta red social?');" style="margin:0;" class="delete-social-form" data-social-delete>
                            <input type="hidden" name="form_type" value="social_delete">
                            <input type="hidden" name="social_id" value="<?php echo (int) $social['id']; ?>">
                            <button class="btn btn-secondary" type="submit" style="width:100%;background:#f9fafb;color:#b91c1c;border:1px solid #f87171;">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="margin-top:32px;border-top:1px solid #e5e7eb;padding-top:24px;">
        <h3 style="margin:0 0 14px;font-size:1.05rem;">Añadir nueva red</h3>
        <form method="post" enctype="multipart/form-data" id="create-social-form" data-social-create style="display:flex;flex-direction:column;gap:12px;max-width:420px;">
            <input type="hidden" name="form_type" value="social_create">
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label for="new_name">Nombre</label>
                <input type="text" name="new_name" id="new_name" value="<?php echo htmlspecialchars($socialFormOld['name'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="120" required>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label for="new_url">Enlace</label>
                <input type="url" name="new_url" id="new_url" value="<?php echo htmlspecialchars($socialFormOld['url'], ENT_QUOTES, 'UTF-8'); ?>" required placeholder="https://">
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label for="new_icon">Icono</label>
                <input type="file" name="new_icon" id="new_icon" accept="image/png,image/svg+xml,image/webp" required>
            </div>
            <button class="btn btn-primary" type="submit">Añadir red</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
