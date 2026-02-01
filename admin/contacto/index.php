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

if (isset($_SESSION['contact_flash'])) {
    $successMessage = (string) $_SESSION['contact_flash'];
    unset($_SESSION['contact_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = [
        'hero_title' => trim($_POST['hero_title'] ?? ''),
        'content_html' => $_POST['content_html'] ?? '',
        'phone_placeholder' => trim($_POST['phone_placeholder'] ?? ''),
        'map_embed' => trim($_POST['map_embed'] ?? ''),
        'contact_email' => trim($_POST['contact_email'] ?? ''),
        'id' => $settings['id']
    ];

    if ($posted['hero_title'] === '') {
        $formErrors[] = 'Debes completar el título principal.';
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

    if (!$formErrors) {
        try {
            contactSaveSettings($db, $posted);
            $_SESSION['contact_flash'] = 'Se guardó la configuración de contacto correctamente.';
            header('Location: ' . adminUrl('contacto'));
            exit;
        } catch (Throwable $exception) {
            $formErrors[] = 'No se pudo guardar la configuración de contacto.';
        }
    }

    $settings = array_merge($settings, $posted);
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

    <form method="post" id="contact_settings_form">
        <label for="contact_hero_title">Título principal (H1)</label>
        <input type="text" name="hero_title" id="contact_hero_title" required maxlength="200" value="<?php echo htmlspecialchars($settings['hero_title'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="contact_content_html">Contenido de contacto (HTML)</label>
        <textarea name="content_html" id="contact_content_html" rows="10"><?php echo htmlspecialchars($settings['content_html'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="contact_phone_placeholder">Teléfono de ejemplo para placeholder</label>
        <input type="text" name="phone_placeholder" id="contact_phone_placeholder" required maxlength="120" value="<?php echo htmlspecialchars($settings['phone_placeholder'], ENT_QUOTES, 'UTF-8'); ?>">

        <label for="contact_map_embed">Ubicación en Google Maps (URL o iframe)</label>
        <textarea name="map_embed" id="contact_map_embed" rows="4"><?php echo htmlspecialchars($settings['map_embed'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label for="contact_email">Correo destinatario</label>
        <input type="email" name="contact_email" id="contact_email" required maxlength="255" value="<?php echo htmlspecialchars($settings['contact_email'], ENT_QUOTES, 'UTF-8'); ?>">

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
