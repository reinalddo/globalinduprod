<?php
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__) . '/includes/site-content.php';

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

$pageTitle = 'Contáctenos';
$rootPath = '..';
$assetPath = $rootPath . '/assets';
$activeNav = 'contacto';
$bodyClass = 'page-contacto';

$contactSettings = getContactPageSettings();
$contactHeroClasses = 'page-hero page-hero--contact';
$contactHeroStyle = '';
if (!empty($contactSettings['hero_image_url'])) {
  $contactHeroClasses .= ' page-hero--contact-custom';
  $heroUrl = htmlspecialchars($contactSettings['hero_image_url'], ENT_QUOTES, 'UTF-8');
  $contactHeroStyle = ' style="background-image:url(\'' . $heroUrl . '\');"';
}
$contactWhatsappNumber = trim((string) ($contactSettings['contact_whatsapp_display'] ?? ''));
$contactWhatsappLink = isset($contactSettings['contact_whatsapp_link']) ? $contactSettings['contact_whatsapp_link'] : null;

$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'equipment' => '',
    'message' => ''
];
$formErrors = [];
$formSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name'] = trim($_POST['name'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['equipment'] = trim($_POST['equipment'] ?? '');
    $formData['message'] = trim($_POST['message'] ?? '');

    if ($formData['name'] === '' || mb_strlen($formData['name']) < 3) {
        $formErrors[] = 'Indica tu nombre y empresa.';
    }

    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors[] = 'Ingresa un correo electrónico válido.';
    }

    if ($formData['equipment'] === '') {
        $formErrors[] = 'Especifica el equipo o número de parte.';
    }

    if ($formData['message'] === '' || mb_strlen($formData['message']) < 10) {
        $formErrors[] = 'Detalla tu requerimiento con al menos 10 caracteres.';
    }

    if (!$formErrors) {
        try {
            if (!class_exists(PHPMailer::class)) {
                throw new RuntimeException('PHPMailer no está disponible. Ejecuta composer install para habilitar el envío de correos.');
            }

            $mailer = new PHPMailer(true);
            $mailer->CharSet = 'UTF-8';
            $mailer->isHTML(true);

            if ($contactSettings['smtp_host'] !== '') {
                $mailer->isSMTP();
                $mailer->Host = $contactSettings['smtp_host'];
                $mailer->Port = (int) ($contactSettings['smtp_port'] ?: 587);
                $mailer->SMTPAuth = !empty($contactSettings['smtp_auth']);
                if ($mailer->SMTPAuth) {
                    $mailer->Username = $contactSettings['smtp_username'];
                    $mailer->Password = $contactSettings['smtp_password'];
                }
                if ($contactSettings['smtp_encryption'] === 'ssl') {
                    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($contactSettings['smtp_encryption'] === 'tls') {
                    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
            } else {
                $mailer->isMail();
            }

            $fromEmail = $contactSettings['smtp_from_email'] ?: $contactSettings['contact_email'] ?: $contactSettings['smtp_username'] ?: $formData['email'];
            $fromName = $contactSettings['smtp_from_name'] ?: $formData['name'];
            $primaryRecipient = $contactSettings['contact_email'] ?: $contactSettings['smtp_from_email'] ?: $contactSettings['smtp_username'];
            if (!$primaryRecipient) {
              throw new RuntimeException('No se ha configurado un destinatario en la sección de contacto.');
            }

            $mailer->setFrom($fromEmail, $fromName);
            $mailer->addAddress($primaryRecipient);
            $mailer->addReplyTo($formData['email'], $formData['name']);

            $logoCid = null;
            if (!empty($contactSettings['email_logo_absolute']) && is_file($contactSettings['email_logo_absolute'])) {
                $logoCid = 'logo_' . md5($contactSettings['email_logo_absolute']);
                $mailer->addEmbeddedImage($contactSettings['email_logo_absolute'], $logoCid, basename($contactSettings['email_logo_absolute']));
            }

            $subject = $contactSettings['email_subject'] ?: 'Nuevo mensaje desde el sitio web';
            $mailer->Subject = $subject;

            $messageHtml = nl2br(htmlspecialchars($formData['message'], ENT_QUOTES, 'UTF-8'));
            $equipmentHtml = htmlspecialchars($formData['equipment'], ENT_QUOTES, 'UTF-8');
            $phoneHtml = $formData['phone'] !== '' ? htmlspecialchars($formData['phone'], ENT_QUOTES, 'UTF-8') : 'No suministrado';
            $nameHtml = htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8');
            $emailHtmlSafe = htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8');

            $logoBlock = '';
            if ($logoCid) {
                $logoBlock = '<tr><td style="padding:24px 24px 12px;text-align:center;border-bottom:1px solid #e5e7eb;"><img src="cid:' . $logoCid . '" alt="Logo" style="max-width:220px;height:auto;"></td></tr>';
            } elseif (!empty($contactSettings['email_logo_url'])) {
                $logoUrl = htmlspecialchars($contactSettings['email_logo_url'], ENT_QUOTES, 'UTF-8');
                $logoBlock = '<tr><td style="padding:24px 24px 12px;text-align:center;border-bottom:1px solid #e5e7eb;"><img src="' . $logoUrl . '" alt="Logo" style="max-width:220px;height:auto;"></td></tr>';
            }

            $emailBody = '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;">'
                . $logoBlock
                . '<tr><td style="padding:24px 24px 12px;background:#111827;color:#f8fafc;">
                        <h2 style="margin:0;font-size:22px;font-weight:600;">Nuevo mensaje de contacto</h2>
                        <p style="margin:8px 0 0;font-size:14px;color:#e5e7eb;">Enviado el ' . date('d/m/Y H:i') . '.</p>
                    </td></tr>
                    <tr><td style="padding:24px;color:#111827;font-size:15px;line-height:1.6;">
                        <p style="margin:0 0 12px;"><strong>Nombre y empresa:</strong> ' . $nameHtml . '</p>
                        <p style="margin:0 0 12px;"><strong>Correo electrónico:</strong> ' . $emailHtmlSafe . '</p>
                        <p style="margin:0 0 12px;"><strong>Teléfono:</strong> ' . $phoneHtml . '</p>
                        <p style="margin:0 0 12px;"><strong>Equipo / número de parte:</strong> ' . $equipmentHtml . '</p>
                        <p style="margin:0 0 2px;"><strong>Detalle del requerimiento</strong></p>
                        <div style="background:#f9fafb;border-radius:12px;padding:16px;margin:0 0 16px;color:#1f2937;">' . $messageHtml . '</div>
                        <p style="margin:0;font-size:13px;color:#6b7280;">IP origen: ' . htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/D', ENT_QUOTES, 'UTF-8') . '</p>
                    </td></tr>
                    <tr><td style="padding:16px 24px;background:#f9fafb;color:#4b5563;font-size:12px;text-align:center;">Mensaje generado automáticamente desde el sitio web.</td></tr>';

            $mailer->Body = $emailBody;
            $mailer->AltBody = "Nombre: {$formData['name']}\nCorreo: {$formData['email']}\nTeléfono: " . ($formData['phone'] !== '' ? $formData['phone'] : 'No suministrado') . "\nEquipo: {$formData['equipment']}\nRequerimiento:\n{$formData['message']}";

            $mailer->send();
            $formSuccess = true;
            $formData = [
                'name' => '',
                'email' => '',
                'phone' => '',
                'equipment' => '',
                'message' => ''
            ];
        } catch (PHPMailerException|RuntimeException $exception) {
            $formErrors[] = 'No se pudo enviar el mensaje. ' . $exception->getMessage();
        }
    }
}

include dirname(__DIR__) . '/includes/header.php';
?>
  <main>
    <section class="<?php echo $contactHeroClasses; ?>"<?php echo $contactHeroStyle; ?>>
      <div class="page-hero-content">
        <?php if (!empty($contactSettings['hero_kicker'])): ?>
          <p style="letter-spacing:0.08em; text-transform:uppercase; font-size:0.9rem; color:#f1c75b; margin-bottom:10px;">
            <?php echo htmlspecialchars($contactSettings['hero_kicker'], ENT_QUOTES, 'UTF-8'); ?>
          </p>
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($contactSettings['hero_title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <?php if (!empty($contactSettings['hero_description'])): ?>
          <p><?php echo htmlspecialchars($contactSettings['hero_description'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
      </div>
      <?php if (!empty($heroFloatingLogoMarkup)): ?>
        <?php echo $heroFloatingLogoMarkup; ?>
      <?php endif; ?>
    </section>

    <section class="main-content">
      <div class="contact-layout" style="margin-top:32px;">
        <article class="contact-card">
          <h2>Datos de contacto</h2>
          <?php if ($contactWhatsappNumber !== '' && $contactWhatsappLink): ?>
            <div class="contact-card__cta" style="margin:0 0 16px;padding:16px;border-radius:12px;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
              <div style="font-weight:600;font-size:0.95rem;">Atención inmediata por WhatsApp</div>
              <a href="<?php echo htmlspecialchars($contactWhatsappLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;background:#22c55e;color:#0f172a;font-weight:600;padding:10px 16px;border-radius:999px;">
                <span>Escríbenos</span>
                <span style="font-size:0.85rem;opacity:0.9;"><?php echo htmlspecialchars($contactWhatsappNumber, ENT_QUOTES, 'UTF-8'); ?></span>
              </a>
            </div>
          <?php endif; ?>
          <div class="contact-card__body">
            <?php echo $contactSettings['content_html']; ?>
          </div>
        </article>

        <form class="contact-card contact-form" method="post" action="">
          <h2>Enviar solicitud</h2>

          <?php if ($formSuccess): ?>
            <div class="empty-state" style="background:#dcfce7;color:#166534;text-align:left;">Mensaje enviado correctamente. Te contactaremos pronto.</div>
          <?php endif; ?>

          <?php if ($formErrors): ?>
            <div class="empty-state" style="background:#fee2e2;color:#b91c1c;text-align:left;">
              <ul style="margin:0;padding-left:18px;">
                <?php foreach ($formErrors as $error): ?>
                  <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <label for="contact-name">Nombre y empresa</label>
          <input id="contact-name" name="name" type="text" placeholder="Nombre completo / Compañía" required value="<?php echo htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>">

          <label for="contact-email">Correo electrónico</label>
          <input id="contact-email" name="email" type="email" placeholder="correo@empresa.com" required value="<?php echo htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>">

          <label for="contact-phone">Teléfono</label>
          <input id="contact-phone" name="phone" type="tel" placeholder="<?php echo htmlspecialchars($contactSettings['phone_placeholder'], ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($formData['phone'], ENT_QUOTES, 'UTF-8'); ?>">

          <label for="contact-equipment">Equipo o modelo</label>
          <input id="contact-equipment" name="equipment" type="text" placeholder="Modelo / Número de parte" required value="<?php echo htmlspecialchars($formData['equipment'], ENT_QUOTES, 'UTF-8'); ?>">

          <label for="contact-message">Detalle su requerimiento</label>
          <textarea id="contact-message" name="message" rows="5" placeholder="Incluya cantidades, ubicación y fecha objetivo" required><?php echo htmlspecialchars($formData['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>

          <button type="submit">Enviar solicitud</button>
        </form>
      </div>

      <?php $mapEmbed = trim((string) $contactSettings['map_embed']); ?>
      <?php if ($mapEmbed !== ''): ?>
        <div class="map-embed">
          <h2>Ubicación</h2>
          <?php if (stripos($mapEmbed, '<iframe') !== false): ?>
            <?php echo $mapEmbed; ?>
          <?php else: ?>
            <iframe title="Mapa de la oficina" src="<?php echo htmlspecialchars($mapEmbed, ENT_QUOTES, 'UTF-8'); ?>" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
