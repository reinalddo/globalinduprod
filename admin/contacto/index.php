<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = tenantText('admin.contact.title', 'Contacto | Administración');
$pageHeader = tenantText('admin.contact.header', 'Contenido de la sección Contacto');
$activeNav = 'contact';

contactEnsureSchema($db);

$settings = contactFetchSettings($db);
$formErrors = [];
$successMessage = '';

$defaults = [
    'hero_kicker' => tenantText('admin.contact.defaults.heroKicker', 'Atención 24/7'),
    'hero_description' => tenantText('admin.contact.defaults.heroDescription', 'Comparta los números de parte, modelo de equipo y requerimientos logísticos. Nuestro equipo responderá con disponibilidad, tiempos de entrega y alternativas compatibles.'),
    'phone_placeholder' => tenantText('admin.contact.defaults.phonePlaceholder', '+58 412-0000000'),
    'email_subject' => tenantText('admin.contact.defaults.emailSubject', 'Nuevo mensaje desde el sitio web'),
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

$heroTitleRequiredMessage = tenantText('admin.contact.error.heroTitle', 'Debes completar el título principal.');
$heroDescriptionRequiredMessage = tenantText('admin.contact.error.heroDescription', 'Debes completar el texto descriptivo del hero.');
$contactContentRequiredMessage = tenantText('admin.contact.error.content', 'Debes completar el contenido de contacto.');
$phonePlaceholderRequiredMessage = tenantText('admin.contact.error.phonePlaceholder', 'Debes indicar el teléfono de ejemplo para el placeholder.');
$contactEmailInvalidMessage = tenantText('admin.contact.error.contactEmail', 'Debes indicar un correo electrónico válido para recibir mensajes.');
$smtpFromEmailInvalidMessage = tenantText('admin.contact.error.smtpFromEmail', 'Debes indicar un correo válido como remitente.');
$emailSubjectRequiredMessage = tenantText('admin.contact.error.emailSubject', 'Debes indicar el asunto por defecto del mensaje.');
$contactSaveErrorMessage = tenantText('admin.contact.error.save', 'No se pudo guardar la configuración de contacto.');
$contactSaveSuccessMessage = tenantText('admin.contact.flash.saved', 'Se guardó la configuración de contacto correctamente.');
$editorHeadingParagraph = tenantText('admin.contact.editor.heading.paragraph', 'Párrafo');
$editorHeadingH2 = tenantText('admin.contact.editor.heading.h2', 'Encabezado 2');
$editorHeadingH3 = tenantText('admin.contact.editor.heading.h3', 'Encabezado 3');
$editorInitErrorMessage = tenantText('admin.contact.editor.initError', 'No se pudo inicializar el editor:');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentLogoPath = (string) ($settings['email_logo_path'] ?? '');
    $currentHeroPath = (string) ($settings['hero_image_path'] ?? '');
    $posted = [
        'hero_title' => trim($_POST['hero_title'] ?? ''),
        'hero_kicker' => trim($_POST['hero_kicker'] ?? ''),
        'hero_description' => trim($_POST['hero_description'] ?? ''),
        'content_html' => $_POST['content_html'] ?? '',
        'phone_placeholder' => trim($_POST['phone_placeholder'] ?? ''),
        'map_embed' => trim($_POST['map_embed'] ?? ''),
        'contact_email' => trim($_POST['contact_email'] ?? ''),
        'hero_image_path' => $settings['hero_image_path'] ?? '',
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
        'remove_hero_image' => isset($_POST['remove_hero_image']) ? 1 : 0,
        'id' => $settings['id']
    ];

    $posted['smtp_encryption'] = in_array($posted['smtp_encryption'], ['ssl', 'tls'], true) ? $posted['smtp_encryption'] : '';

    if ($posted['hero_title'] === '') {
        $formErrors[] = $heroTitleRequiredMessage;
    }

    if ($posted['hero_description'] === '') {
        $formErrors[] = $heroDescriptionRequiredMessage;
    }

    $plainContent = trim(strip_tags($posted['content_html']));
    if ($plainContent === '') {
        $formErrors[] = $contactContentRequiredMessage;
    }

    if ($posted['phone_placeholder'] === '') {
        $formErrors[] = $phonePlaceholderRequiredMessage;
    }

    if ($posted['contact_email'] === '' || !filter_var($posted['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors[] = $contactEmailInvalidMessage;
    }

    if ($posted['smtp_port'] < 0) {
        $posted['smtp_port'] = 0;
    }

    if ($posted['smtp_from_email'] !== '' && !filter_var($posted['smtp_from_email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors[] = $smtpFromEmailInvalidMessage;
    }

    if ($posted['email_subject'] === '') {
        $formErrors[] = $emailSubjectRequiredMessage;
    }

    $newLogoPath = null;
    $newHeroPath = null;
    $uploadedLogo = $_FILES['email_logo'] ?? null;
    $uploadedHero = $_FILES['hero_image'] ?? null;

    if (!$formErrors && $uploadedLogo && (int) $uploadedLogo['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $newLogoPath = contactSaveMailLogo($uploadedLogo);
        } catch (Throwable $exception) {
            $formErrors[] = $exception->getMessage();
        }
    }

    if (!$formErrors && $uploadedHero && (int) $uploadedHero['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $newHeroPath = contactSaveHeroImage($uploadedHero);
        } catch (Throwable $exception) {
            $formErrors[] = $exception->getMessage();
        }
    }

    if ($formErrors && $newLogoPath) {
        contactDeleteStoredAsset($newLogoPath);
        $newLogoPath = null;
    }

    if ($formErrors && $newHeroPath) {
        contactDeleteStoredAsset($newHeroPath, ['contact/hero']);
        $newHeroPath = null;
    }

    if (!$formErrors) {
        if ($posted['remove_email_logo']) {
            $posted['email_logo_path'] = '';
        } elseif ($newLogoPath) {
            $posted['email_logo_path'] = $newLogoPath;
        } else {
            $posted['email_logo_path'] = $currentLogoPath;
        }

        if ($posted['remove_hero_image']) {
            $posted['hero_image_path'] = '';
        } elseif ($newHeroPath) {
            $posted['hero_image_path'] = $newHeroPath;
        } else {
            $posted['hero_image_path'] = $currentHeroPath;
        }

        try {
            contactSaveSettings($db, $posted);

            if ($newLogoPath && $currentLogoPath && $currentLogoPath !== $newLogoPath) {
                contactDeleteStoredAsset($currentLogoPath);
            }
            if ($posted['remove_email_logo'] && $currentLogoPath) {
                contactDeleteStoredAsset($currentLogoPath);
            }

            if ($newHeroPath && $currentHeroPath && $currentHeroPath !== $newHeroPath) {
                contactDeleteStoredAsset($currentHeroPath, ['contact/hero']);
            }
            if ($posted['remove_hero_image'] && $currentHeroPath) {
                contactDeleteStoredAsset($currentHeroPath, ['contact/hero']);
            }

            $_SESSION['contact_flash'] = $contactSaveSuccessMessage;
            header('Location: ' . adminUrl('contacto'));
            exit;
        } catch (Throwable $exception) {
            $formErrors[] = $contactSaveErrorMessage;
            if ($newLogoPath) {
                contactDeleteStoredAsset($newLogoPath);
            }
            if ($newHeroPath) {
                contactDeleteStoredAsset($newHeroPath, ['contact/hero']);
            }
        }
    }

    $settings = array_merge($settings, $posted);
    if (!empty($settings['remove_email_logo'])) {
        $settings['email_logo_path'] = $currentLogoPath;
    }
    if (!empty($settings['remove_hero_image'])) {
        $settings['hero_image_path'] = $currentHeroPath;
    }
}

require_once __DIR__ . '/../includes/page-top.php';
?>
<section id="contact-settings">
    <h2><?php echo htmlspecialchars(tenantText('admin.contact.sectionHeading', 'Contenido principal de Contacto'), ENT_QUOTES, 'UTF-8'); ?></h2>

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
        <label for="contact_hero_title"><?php echo htmlspecialchars(tenantText('admin.contact.form.heroTitle', 'Título principal (H1)'), ENT_QUOTES, 'UTF-8'); ?></label>
        <input type="text" name="hero_title" id="contact_hero_title" required maxlength="200" value="<?php echo htmlspecialchars($settings['hero_title'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="contact_hero_kicker"><?php echo htmlspecialchars(tenantText('admin.contact.form.heroKicker', 'Texto pequeño del hero'), ENT_QUOTES, 'UTF-8'); ?></label>
        <input type="text" name="hero_kicker" id="contact_hero_kicker" maxlength="150" value="<?php echo htmlspecialchars($settings['hero_kicker'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="contact_hero_description"><?php echo htmlspecialchars(tenantText('admin.contact.form.heroDescription', 'Descripción del hero'), ENT_QUOTES, 'UTF-8'); ?></label>
        <textarea name="hero_description" id="contact_hero_description" rows="3" required><?php echo htmlspecialchars($settings['hero_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="contact_hero_image"><?php echo htmlspecialchars(tenantText('admin.contact.form.heroImage', 'Imagen de fondo del hero'), ENT_QUOTES, 'UTF-8'); ?></label>
        <input type="file" name="hero_image" id="contact_hero_image" accept="image/*">
        <?php if (!empty($settings['hero_image_path'])): ?>
            <div style="margin-top:12px;display:flex;flex-direction:column;gap:12px;">
                <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;max-width:380px;">
                    <img src="<?php echo htmlspecialchars(adminAssetUrl($settings['hero_image_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars(tenantText('admin.contact.form.heroImageAlt', 'Imagen actual del hero'), ENT_QUOTES, 'UTF-8'); ?>" style="width:100%;height:auto;display:block;">
                </div>
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="remove_hero_image" value="1" <?php echo !empty($settings['remove_hero_image']) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars(tenantText('admin.contact.form.removeHeroImage', 'Eliminar imagen actual'), ENT_QUOTES, 'UTF-8'); ?>
                </label>
                <p style="margin:0;font-size:12px;color:#6b7280;"><?php echo htmlspecialchars(tenantText('admin.contact.form.currentPath', 'Ruta actual:'), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($settings['hero_image_path'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php endif; ?>

        <label for="contact_content_html"><?php echo htmlspecialchars(tenantText('admin.contact.form.contactContent', 'Contenido de contacto (HTML)'), ENT_QUOTES, 'UTF-8'); ?></label>
        <textarea name="content_html" id="contact_content_html" rows="10"><?php echo htmlspecialchars($settings['content_html'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="contact_phone_placeholder"><?php echo htmlspecialchars(tenantText('admin.contact.form.phonePlaceholder', 'Teléfono de ejemplo para placeholder'), ENT_QUOTES, 'UTF-8'); ?></label>
        <input type="text" name="phone_placeholder" id="contact_phone_placeholder" required maxlength="120" value="<?php echo htmlspecialchars($settings['phone_placeholder'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="contact_map_embed"><?php echo htmlspecialchars(tenantText('admin.contact.form.mapEmbed', 'Ubicación en Google Maps (URL o iframe)'), ENT_QUOTES, 'UTF-8'); ?></label>
        <textarea name="map_embed" id="contact_map_embed" rows="4"><?php echo htmlspecialchars($settings['map_embed'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="contact_email"><?php echo htmlspecialchars(tenantText('admin.contact.form.contactEmail', 'Correo destinatario'), ENT_QUOTES, 'UTF-8'); ?></label>
        <input type="email" name="contact_email" id="contact_email" required maxlength="255" value="<?php echo htmlspecialchars($settings['contact_email'], ENT_QUOTES, 'UTF-8'); ?>">

        <fieldset style="border:1px solid #e5e7eb;border-radius:10px;padding:18px;margin-top:24px;">
            <legend style="padding:0 8px;font-weight:600;color:#111827;"><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpLegend', 'Configuración SMTP (PHPMailer)'), ENT_QUOTES, 'UTF-8'); ?></legend>

            <label for="contact_smtp_host"><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpHost', 'Servidor SMTP'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" name="smtp_host" id="contact_smtp_host" maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_host'], ENT_QUOTES, 'UTF-8'); ?>">

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
                <div>
                    <label for="contact_smtp_port"><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpPort', 'Puerto'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <input type="number" name="smtp_port" id="contact_smtp_port" min="0" max="65535" value="<?php echo (int) $settings['smtp_port']; ?>">
                </div>
                <div>
                    <label for="contact_smtp_encryption"><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpEncryption', 'Cifrado'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <select name="smtp_encryption" id="contact_smtp_encryption">
                        <option value="" <?php echo $settings['smtp_encryption'] === '' ? 'selected' : ''; ?>><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpEncryption.none', 'Sin cifrado'), ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpEncryption.tls', 'TLS'), ENT_QUOTES, 'UTF-8'); ?></option>
                        <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpEncryption.ssl', 'SSL'), ENT_QUOTES, 'UTF-8'); ?></option>
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end;">
                    <label style="display:flex;align-items:center;gap:8px;margin-bottom:0;">
                        <input type="checkbox" name="smtp_auth" value="1" <?php echo !empty($settings['smtp_auth']) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars(tenantText('admin.contact.form.smtpAuth', 'Usar autenticación SMTP'), ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                </div>
            </div>

            <label for="contact_smtp_username"><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpUsername', 'Usuario SMTP'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" name="smtp_username" id="contact_smtp_username" maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_username'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_smtp_password"><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpPassword', 'Contraseña SMTP'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="password" name="smtp_password" id="contact_smtp_password" maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_password'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_smtp_from_email"><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpFromEmail', 'Correo remitente'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="email" name="smtp_from_email" id="contact_smtp_from_email" maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_from_email'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_smtp_from_name"><?php echo htmlspecialchars(tenantText('admin.contact.form.smtpFromName', 'Nombre remitente'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" name="smtp_from_name" id="contact_smtp_from_name" maxlength="255" value="<?php echo htmlspecialchars($settings['smtp_from_name'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_email_subject"><?php echo htmlspecialchars(tenantText('admin.contact.form.emailSubject', 'Asunto por defecto'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" name="email_subject" id="contact_email_subject" required maxlength="255" value="<?php echo htmlspecialchars($settings['email_subject'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="contact_email_logo"><?php echo htmlspecialchars(tenantText('admin.contact.form.emailLogo', 'Logo para la plantilla de correo'), ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="file" name="email_logo" id="contact_email_logo" accept="image/*">
            <?php if (!empty($settings['email_logo_path'])): ?>
                <div style="margin-top:12px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <div style="border:1px solid #e5e7eb;border-radius:12px;padding:12px;">
                        <img src="<?php echo htmlspecialchars(adminAssetUrl($settings['email_logo_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars(tenantText('admin.contact.form.emailLogoAlt', 'Logo actual'), ENT_QUOTES, 'UTF-8'); ?>" style="max-width:220px;height:auto;display:block;">
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="remove_email_logo" value="1">
                        <?php echo htmlspecialchars(tenantText('admin.contact.form.removeEmailLogo', 'Eliminar logo actual'), ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                </div>
                <p style="margin:8px 0 0;font-size:12px;color:#6b7280;"><?php echo htmlspecialchars(tenantText('admin.contact.form.currentPath', 'Ruta actual:'), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($settings['email_logo_path'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </fieldset>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?php echo htmlspecialchars(tenantText('admin.contact.form.submit', 'Guardar cambios'), ENT_QUOTES, 'UTF-8'); ?></button>
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
                        { model: 'paragraph', title: <?php echo json_encode($editorHeadingParagraph, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, class: 'ck-heading_paragraph' },
                        { model: 'heading2', view: 'h2', title: <?php echo json_encode($editorHeadingH2, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: <?php echo json_encode($editorHeadingH3, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, class: 'ck-heading_heading3' }
                    ]
                }
            }).then(function (editor) {
                contactEditor = editor;
                editor.editing.view.change(function (writer) {
                    writer.setStyle('min-height', '320px', editor.editing.view.document.getRoot());
                });
            }).catch(function (error) {
                console.error(<?php echo json_encode($editorInitErrorMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, error);
            });
        }

        if (contactForm) {
            // CKEditor oculta el textarea; validamos manualmente antes de enviar.
            contactForm.addEventListener('submit', function (event) {
                if (contactEditor && contactEditor.getData().trim() === '') {
                    event.preventDefault();
                    alert(<?php echo json_encode($contactContentRequiredMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>);
                    contactEditor.editing.view.focus();
                }
            });
        }
    });
</script>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
