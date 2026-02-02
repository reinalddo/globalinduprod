<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = 'Contacto | Administración';
$pageHeader = 'Contenido de la sección Contacto';
$activeNav = 'contact';

contactEnsureSchema($db);

$settings = contactFetchSettings($db);
$formErrors = [];
$successMessage = '';

$defaults = [
    'hero_kicker' => 'Atención 24/7',
    'hero_description' => 'Comparta los números de parte, modelo de equipo y requerimientos logísticos. Nuestro equipo responderá con disponibilidad, tiempos de entrega y alternativas compatibles.',
    'phone_placeholder' => '+58 412-0000000',
    'email_subject' => 'Nuevo mensaje desde el sitio web',
];

foreach ($defaults as $key => $value) {
    if (isset($settings[$key]) && trim((string) $settings[$key]) !== '') {
        continue;
    }
    $settings[$key] = $value;
}

if (isset($_SESSION['contact_flash'])) {
    $successMessage = (string) $_SESSION['contact_flash'];
    unset($_SESSION['contact_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentLogoPath = (string) ($settings['email_logo_path'] ?? '');
    $posted = [
        'hero_title' => trim($_POST['hero_title'] ?? ''),
        'hero_kicker' => trim($_POST['hero_kicker'] ?? ''),
        'hero_description' => trim($_POST['hero_description'] ?? ''),
        'content_html' => $_POST['content_html'] ?? '',
        'phone_placeholder' => trim($_POST['phone_placeholder'] ?? ''),
        'map_embed' => trim($_POST['map_embed'] ?? ''),
        'contact_email' => trim($_POST['contact_email'] ?? ''),
        'smtp_host' => trim($_POST['smtp_host'] ?? ''),
        'smtp_port' => (int) ($_POST['smtp_port'] ?? 0),
        'smtp_username' => trim($_POST['smtp_username'] ?? ''),
        'smtp_password' => (string) ($_POST['smtp_password'] ?? ''),
        'smtp_encryption' => strtolower(trim($_POST['smtp_encryption'] ?? '')),
        'smtp_auth' => isset($_POST['smtp_auth']) ? 1 : 0,
        'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
        'smtp_from_name' => trim($_POST['smtp_from_name'] ?? ''),
        'email_subject' => trim($_POST['email_subject'] ?? ''),
        'remove_email_logo' => isset($_POST['remove_email_logo']) ? 1 : 0,
        'id' => $settings['id']
    ];

    $posted['smtp_encryption'] = in_array($posted['smtp_encryption'], ['ssl', 'tls'], true) ? $posted['smtp_encryption'] : '';

    if ($posted['hero_title'] === '') {
        $formErrors[] = 'Debes completar el título principal.';
    }

    if ($posted['hero_description'] === '') {
        $formErrors[] = 'Debes completar el texto descriptivo del hero.';
    }

    $plainContent = trim(strip_tags($posted['content_html']));
    if ($plainContent === '') {
        $formErrors[] = 'Debes completar el contenido de contacto.';
    }

    if ($posted['phone_placeholder'] === '') {
        $formErrors[] = 'Debes indicar el teléfono de ejemplo para el placeholder.';
    }

    if ($posted['contact_email'] === '' || !filter_var($posted['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors[] = 'Debes indicar un correo electrónico válido para recibir mensajes.';
    }

    if ($posted['smtp_host'] === '') {
        $formErrors[] = 'Debes indicar el host SMTP.';
    }

    if ($posted['smtp_port'] <= 0) {
        $formErrors[] = 'El puerto SMTP debe ser un número mayor que cero.';
    }

    if ($posted['smtp_username'] === '') {
        $formErrors[] = 'Debes indicar el usuario SMTP.';
    }

    if ($posted['smtp_password'] === '') {
        $formErrors[] = 'Debes indicar la contraseña SMTP.';
    }

    if ($posted['smtp_from_email'] === '' || !filter_var($posted['smtp_from_email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors[] = 'Debes indicar un correo válido como remitente.';
    }

    if ($posted['smtp_from_name'] === '') {
        $formErrors[] = 'Debes indicar el nombre del remitente.';
    }

    if ($posted['email_subject'] === '') {
        $formErrors[] = 'Debes indicar el asunto por defecto del mensaje.';
    }

    $newLogoPath = null;
    $uploadedLogo = $_FILES['email_logo'] ?? null;

    if (!$formErrors && $uploadedLogo && (int) $uploadedLogo['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $newLogoPath = contactSaveMailLogo($uploadedLogo);
        } catch (Throwable $exception) {
            $formErrors[] = $exception->getMessage();
        }
    }

    if ($formErrors && $newLogoPath) {
        contactDeleteStoredAsset($newLogoPath);
        $newLogoPath = null;
    }

    if (!$formErrors) {
        if ($posted['remove_email_logo']) {
            $posted['email_logo_path'] = '';
        } elseif ($newLogoPath) {
            $posted['email_logo_path'] = $newLogoPath;
        } else {
            $posted['email_logo_path'] = $currentLogoPath;
        }

        try {
            contactSaveSettings($db, $posted);

            if ($newLogoPath && $currentLogoPath && $currentLogoPath !== $newLogoPath) {
                contactDeleteStoredAsset($currentLogoPath);
            }
            if ($posted['remove_email_logo'] && $currentLogoPath) {
                contactDeleteStoredAsset($currentLogoPath);
            }

            $_SESSION['contact_flash'] = 'Se guardó la configuración de contacto correctamente.';
            header('Location: ' . adminUrl('contacto'));
            exit;
        } catch (Throwable $exception) {
            $formErrors[] = 'No se pudo guardar la configuración de contacto.';
            if ($newLogoPath) {
                contactDeleteStoredAsset($newLogoPath);
            }
        }
    }

    $settings = array_merge($settings, $posted);
    if (!empty($settings['remove_email_logo'])) {
        $settings['email_logo_path'] = $currentLogoPath;
    }
}

require_once __DIR__ . '/../includes/page-top.php';
?>
<section id="contact-settings">
    <h2>Contenido principal de Contacto</h2>

    <?php if ($successMessage !== ''): ?>
        <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;">
            <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
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

    <form method="post" id="contact_settings_form" enctype="multipart/form-data">
        <label for="contact_hero_title">Título principal (H1)</label>
        <input type="text" name="hero_title" id="contact_hero_title" required maxlength="200" value="<?php echo htmlspecialchars($settings['hero_title'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="contact_hero_kicker">Texto pequeño del hero</label>
        <input type="text" name="hero_kicker" id="contact_hero_kicker" maxlength="150" value="<?php echo htmlspecialchars($settings['hero_kicker'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="contact_hero_description">Descripción del hero</label>
        <textarea name="hero_description" id="contact_hero_description" rows="3" required><?php echo htmlspecialchars($settings['hero_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="contact_content_html">Contenido de contacto (HTML)</label>
        <textarea name="content_html" id="contact_content_html" rows="10"><?php echo htmlspecialchars($settings['content_html'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="contact_phone_placeholder">Teléfono de ejemplo para placeholder</label>
        <input type="text" name="phone_placeholder" id="contact_phone_placeholder" required maxlength="120" value="<?php echo htmlspecialchars($settings['phone_placeholder'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="contact_map_embed">Ubicación en Google Maps (URL o iframe)</label>
        <textarea name="map_embed" id="contact_map_embed" rows="4"><?php echo htmlspecialchars($settings['map_embed'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="contact_email">Correo destinatario</label>
        <input type="email" name="contact_email" id="contact_email" required maxlength="255" value="<?php echo htmlspecialchars($settings['contact_email'], ENT_QUOTES, 'UTF-8'); ?>">

        <fieldset style="border:1px solid #e5e7eb;border-radius:10px;padding:18px;margin-top:24px;">
            <legend style="padding:0 8px;font-weight:600;color:#111827;">Configuración SMTP (PHPMailer)</legend>

            <label for="contact_smtp_host">Servidor SMTP</label>
            <input type="text" name="smtp_host" id="contact_smtp_host" required maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_host'], ENT_QUOTES, 'UTF-8'); ?>">

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
                <div>
                    <label for="contact_smtp_port">Puerto</label>
                    <input type="number" name="smtp_port" id="contact_smtp_port" required min="1" max="65535" value="<?php echo (int) $settings['smtp_port']; ?>">
                </div>
                <div>
                    <label for="contact_smtp_encryption">Cifrado</label>
                    <select name="smtp_encryption" id="contact_smtp_encryption">
                        <option value="" <?php echo $settings['smtp_encryption'] === '' ? 'selected' : ''; ?>>Sin cifrado</option>
                        <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                        <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end;">
                    <label style="display:flex;align-items:center;gap:8px;margin-bottom:0;">
                        <input type="checkbox" name="smtp_auth" value="1" <?php echo !empty($settings['smtp_auth']) ? 'checked' : ''; ?>>
                        Usar autenticación SMTP
                    </label>
                </div>
            </div>

            <label for="contact_smtp_username">Usuario SMTP</label>
            <input type="text" name="smtp_username" id="contact_smtp_username" required maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_username'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_smtp_password">Contraseña SMTP</label>
            <input type="password" name="smtp_password" id="contact_smtp_password" required maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_password'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_smtp_from_email">Correo remitente</label>
            <input type="email" name="smtp_from_email" id="contact_smtp_from_email" required maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_from_email'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_smtp_from_name">Nombre remitente</label>
            <input type="text" name="smtp_from_name" id="contact_smtp_from_name" required maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_from_name'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_email_subject">Asunto por defecto</label>
            <input type="text" name="email_subject" id="contact_email_subject" required maxlength="255" value="<?php echo htmlspecialchars($settings['email_subject'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_email_logo">Logo para la plantilla de correo</label>
            <input type="file" name="email_logo" id="contact_email_logo" accept="image/*">
            <?php if (!empty($settings['email_logo_path'])): ?>
                <div style="margin-top:12px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <div style="border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
                        <img src="<?php echo htmlspecialchars(adminAssetUrl($settings['email_logo_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="Logo actual" style="max-width:220px;height:auto;display:block;">
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="remove_email_logo" value="1">
                        Eliminar logo actual
                    </label>
                </div>
                <p style="margin:8px 0 0;font-size:12px;color:#6b7280;">Ruta actual: <?php echo htmlspecialchars($settings['email_logo_path'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </fieldset>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar cambios</button>
        </div>
    </form>
</section>
<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const contactForm = document.getElementById('contact_settings_form');
        let contactEditor = null;

        if (typeof ClassicEditor !== 'undefined') {
            ClassicEditor.create(document.querySelector('#contact_content_html'), {
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
                contactEditor = editor;
                editor.editing.view.change(function (writer) {
                    writer.setStyle('min-height', '320px', editor.editing.view.document.getRoot());
                });
            }).catch(function (error) {
                console.error('No se pudo inicializar el editor:', error);
            });
        }

        if (contactForm) {
            // CKEditor oculta el textarea; validamos manualmente antes de enviar.
            contactForm.addEventListener('submit', function (event) {
                if (contactEditor && contactEditor.getData().trim() === '') {
                    event.preventDefault();
                    alert('Debes completar el contenido de contacto.');
                    contactEditor.editing.view.focus();
                }
            });
        }
    });
</script>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
