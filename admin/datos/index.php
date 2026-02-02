<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/brand.php';

$pageTitle = 'Datos Admin | Administración';
$pageHeader = 'Configuración de la cuenta administrador';
$activeNav = 'account';

$sessionUser = $_SESSION['admin_user'] ?? null;
$currentAdminId = isset($sessionUser['id']) ? (int) $sessionUser['id'] : 0;

if ($currentAdminId <= 0) {
    header('Location: ' . adminUrl('logout'));
    exit;
}

$currentAdmin = [
    'id' => $currentAdminId,
    'username' => '',
    'full_name' => '',
    'role' => '',
    'is_active' => 0
];

try {
    $stmt = $db->prepare('SELECT id, username, full_name, role, is_active FROM admin_users WHERE id = ? LIMIT 1');
    if ($stmt === false) {
        throw new RuntimeException('No se pudo preparar la consulta de usuario.');
    }
    $stmt->bind_param('i', $currentAdminId);
    if (!$stmt->execute()) {
        throw new RuntimeException('No se pudo obtener los datos del usuario.');
    }
    $stmt->bind_result($id, $username, $fullName, $role, $isActive);
    if ($stmt->fetch()) {
        $currentAdmin = [
            'id' => (int) $id,
            'username' => (string) $username,
            'full_name' => (string) $fullName,
            'role' => (string) $role,
            'is_active' => (int) $isActive
        ];
    }
    $stmt->close();
} catch (Throwable $exception) {
    http_response_code(500);
    echo 'No se pudieron cargar los datos del administrador.';
    exit;
}

if ($currentAdmin['id'] !== $currentAdminId) {
    header('Location: ' . adminUrl('logout'));
    exit;
}

$canCreateAdmins = strtolower((string) $currentAdmin['role']) === 'superadmin';

$feedback = [
    'profile' => ['success' => '', 'errors' => []],
    'password' => ['success' => '', 'errors' => []],
    'create' => ['success' => '', 'errors' => []],
    'branding' => ['success' => '', 'errors' => []]
];

$createOld = [
    'username' => '',
    'full_name' => '',
    'is_active' => 1
];

if (isset($_SESSION['admin_account_flash'])) {
    $flash = $_SESSION['admin_account_flash'];
    unset($_SESSION['admin_account_flash']);
    foreach (['profile', 'password', 'create', 'branding'] as $key) {
        if (isset($flash[$key]['success'])) {
            $feedback[$key]['success'] = (string) $flash[$key]['success'];
        }
        if (isset($flash[$key]['errors']) && is_array($flash[$key]['errors'])) {
            $feedback[$key]['errors'] = $flash[$key]['errors'];
        }
    }
    if (isset($flash['create']['old']) && is_array($flash['create']['old'])) {
        $createOld = array_merge($createOld, $flash['create']['old']);
    }
}

brandEnsureSchema($db);
$brandSettings = brandFetchSettings($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'update_profile') {
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');

        $errors = [];
        if ($username === '') {
            $errors[] = 'El usuario es obligatorio.';
        } elseif (strlen($username) < 4) {
            $errors[] = 'El usuario debe tener al menos 4 caracteres.';
        }

        if ($fullName === '') {
            $errors[] = 'El nombre completo es obligatorio.';
        }

        if (!$errors) {
            try {
                $stmt = $db->prepare('SELECT id FROM admin_users WHERE username = ? AND id <> ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo preparar la validación de usuario.');
                }
                $stmt->bind_param('si', $username, $currentAdminId);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = 'Ya existe otro administrador con ese usuario.';
                }
                $stmt->close();
            } catch (Throwable $exception) {
                $errors[] = 'No se pudo validar el usuario.';
            }
        }

        if (!$errors) {
            try {
                $stmt = $db->prepare('UPDATE admin_users SET username = ?, full_name = ? WHERE id = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo preparar la actualización.');
                }
                $stmt->bind_param('ssi', $username, $fullName, $currentAdminId);
                $stmt->execute();
                $stmt->close();

                $_SESSION['admin_user']['username'] = $username;
                $_SESSION['admin_user']['full_name'] = $fullName;

                $_SESSION['admin_account_flash'] = [
                    'profile' => ['success' => 'Datos actualizados correctamente.']
                ];
                header('Location: ' . adminUrl('datos'));
                exit;
            } catch (Throwable $exception) {
                $errors[] = 'No se pudieron guardar los datos.';
            }
        }

        $_SESSION['admin_account_flash'] = [
            'profile' => ['errors' => $errors]
        ];
        header('Location: ' . adminUrl('datos'));
        exit;
    } elseif ($formType === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];
        if ($currentPassword === '') {
            $errors[] = 'Debes ingresar tu contraseña actual.';
        }
        if ($newPassword === '') {
            $errors[] = 'Debes ingresar una contraseña nueva.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }
        if ($confirmPassword === '') {
            $errors[] = 'Debes confirmar la contraseña nueva.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (!$errors) {
            try {
                $stmt = $db->prepare('SELECT password_hash FROM admin_users WHERE id = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo preparar la validación.');
                }
                $stmt->bind_param('i', $currentAdminId);
                $stmt->execute();
                $stmt->bind_result($passwordHash);
                if ($stmt->fetch()) {
                    if (!password_verify($currentPassword, (string) $passwordHash)) {
                        $errors[] = 'La contraseña actual no es correcta.';
                    }
                } else {
                    $errors[] = 'No se encontró al usuario.';
                }
                $stmt->close();
            } catch (Throwable $exception) {
                $errors[] = 'No se pudo verificar la contraseña actual.';
            }
        }

        if (!$errors) {
            try {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo preparar el cambio de contraseña.');
                }
                $stmt->bind_param('si', $hashed, $currentAdminId);
                $stmt->execute();
                $stmt->close();

                $_SESSION['admin_account_flash'] = [
                    'password' => ['success' => 'Contraseña actualizada correctamente.']
                ];
                header('Location: ' . adminUrl('datos'));
                exit;
            } catch (Throwable $exception) {
                $errors[] = 'No se pudo actualizar la contraseña.';
            }
        }

        $_SESSION['admin_account_flash'] = [
            'password' => ['errors' => $errors]
        ];
        header('Location: ' . adminUrl('datos'));
        exit;
    } elseif ($formType === 'update_favicon') {
        $faviconFile = $_FILES['favicon_file'] ?? null;
        $errors = [];

        if (!$faviconFile || $faviconFile['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Debes seleccionar un archivo de favicon.';
        }

        $temporaryFavicon = null;
        if (!$errors) {
            try {
                $temporaryFavicon = brandSaveFaviconFile($faviconFile);
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if ($errors) {
            if ($temporaryFavicon) {
                brandDeleteStoredFavicon($temporaryFavicon);
            }
            $_SESSION['admin_account_flash'] = [
                'branding' => ['errors' => $errors]
            ];
            header('Location: ' . adminUrl('datos') . '#branding');
            exit;
        }

        try {
            $currentPath = $brandSettings['favicon_path'] ?? '';

            if (!empty($brandSettings['id'])) {
                $stmt = $db->prepare('UPDATE site_brand_assets SET favicon_path = ?, updated_at = NOW() WHERE id = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo guardar el favicon.');
                }
                $stmt->bind_param('si', $temporaryFavicon, $brandSettings['id']);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $db->prepare('INSERT INTO site_brand_assets (favicon_path) VALUES (?)');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo registrar el favicon.');
                }
                $stmt->bind_param('s', $temporaryFavicon);
                $stmt->execute();
                $stmt->close();
            }

            if ($currentPath && $currentPath !== $temporaryFavicon) {
                brandDeleteStoredFavicon($currentPath);
            }

            $_SESSION['admin_account_flash'] = [
                'branding' => ['success' => 'Favicon actualizado correctamente.']
            ];
            header('Location: ' . adminUrl('datos') . '#branding');
            exit;
        } catch (Throwable $exception) {
            if ($temporaryFavicon) {
                brandDeleteStoredFavicon($temporaryFavicon);
            }
            $_SESSION['admin_account_flash'] = [
                'branding' => ['errors' => ['No se pudo guardar el favicon. Intenta nuevamente.']]
            ];
            header('Location: ' . adminUrl('datos') . '#branding');
            exit;
        }
    } elseif ($formType === 'create_admin' && $canCreateAdmins) {
        $newUsername = trim($_POST['new_username'] ?? '');
        $newFullName = trim($_POST['new_full_name'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';
        $newIsActive = isset($_POST['new_is_active']) ? 1 : 0;

        $errors = [];
        if ($newUsername === '') {
            $errors[] = 'El usuario para el nuevo administrador es obligatorio.';
        } elseif (strlen($newUsername) < 4) {
            $errors[] = 'El usuario debe tener al menos 4 caracteres.';
        }

        if ($newFullName === '') {
            $errors[] = 'El nombre completo del nuevo administrador es obligatorio.';
        }

        if ($newPassword === '') {
            $errors[] = 'Debes ingresar una contraseña para el nuevo administrador.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'La contraseña del nuevo administrador debe tener al menos 8 caracteres.';
        }

        if ($newPasswordConfirm === '') {
            $errors[] = 'Debes confirmar la contraseña del nuevo administrador.';
        } elseif ($newPassword !== $newPasswordConfirm) {
            $errors[] = 'Las contraseñas del nuevo administrador no coinciden.';
        }

        if (!$errors) {
            try {
                $stmt = $db->prepare('SELECT id FROM admin_users WHERE username = ? LIMIT 1');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo preparar la validación del nuevo usuario.');
                }
                $stmt->bind_param('s', $newUsername);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = 'Ya existe un administrador con ese usuario.';
                }
                $stmt->close();
            } catch (Throwable $exception) {
                $errors[] = 'No se pudo validar el usuario nuevo.';
            }
        }

        if (!$errors) {
            try {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare('INSERT INTO admin_users (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)');
                if ($stmt === false) {
                    throw new RuntimeException('No se pudo preparar la creación del usuario.');
                }
                $role = 'admin';
                $stmt->bind_param('ssssi', $newUsername, $passwordHash, $newFullName, $role, $newIsActive);
                $stmt->execute();
                $stmt->close();

                $_SESSION['admin_account_flash'] = [
                    'create' => ['success' => 'Usuario administrador creado correctamente.']
                ];
                header('Location: ' . adminUrl('datos'));
                exit;
            } catch (Throwable $exception) {
                $errors[] = 'No se pudo crear el usuario administrador.';
            }
        }

        $_SESSION['admin_account_flash'] = [
            'create' => [
                'errors' => $errors,
                'old' => [
                    'username' => $newUsername,
                    'full_name' => $newFullName,
                    'is_active' => $newIsActive
                ]
            ]
        ];
        header('Location: ' . adminUrl('datos'));
        exit;
    }
}

$adminUsers = [];
if ($canCreateAdmins) {
    try {
        $result = $db->query('SELECT id, username, full_name, role, is_active FROM admin_users ORDER BY id ASC');
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $adminUsers[] = [
                    'id' => (int) $row['id'],
                    'username' => (string) $row['username'],
                    'full_name' => (string) ($row['full_name'] ?? ''),
                    'role' => (string) ($row['role'] ?? ''),
                    'is_active' => (int) ($row['is_active'] ?? 0)
                ];
            }
            $result->free();
        }
    } catch (Throwable $exception) {
        // Si falla la consulta, mostramos la tabla vacía.
    }
}

require_once __DIR__ . '/../includes/page-top.php';
?>
<section>
    <div style="display:grid;gap:32px;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));">
        <div>
            <h2 style="margin:0 0 16px;">Mis datos</h2>
            <?php if ($feedback['profile']['success']): ?>
                <div style="background:#ecfdf5;color:#065f46;border:1px solid #34d399;padding:14px 16px;border-radius:8px;margin-bottom:16px;">
                    <?php echo htmlspecialchars($feedback['profile']['success'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($feedback['profile']['errors']): ?>
                <div style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;padding:14px 16px;border-radius:8px;margin-bottom:16px;">
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($feedback['profile']['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <input type="hidden" name="form_type" value="update_profile">

                <label for="username">Usuario</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($currentAdmin['username'], ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="username">

                <label for="full_name">Nombre completo</label>
                <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($currentAdmin['full_name'], ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="name">

                <div style="display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));margin:18px 0 0;">
                    <div>
                        <label>Rol asignado</label>
                        <input type="text" value="<?php echo htmlspecialchars($currentAdmin['role'], ENT_QUOTES, 'UTF-8'); ?>" readonly style="background:#f9fafb;color:#6b7280;">
                    </div>
                    <div>
                        <label>Estado</label>
                        <input type="text" value="<?php echo $currentAdmin['is_active'] ? 'Activo' : 'Inactivo'; ?>" readonly style="background:#f9fafb;color:#6b7280;">
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>

        <div>
            <h2 style="margin:0 0 16px;">Cambiar contraseña</h2>
            <?php if ($feedback['password']['success']): ?>
                <div style="background:#ecfdf5;color:#065f46;border:1px solid #34d399;padding:14px 16px;border-radius:8px;margin-bottom:16px;">
                    <?php echo htmlspecialchars($feedback['password']['success'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($feedback['password']['errors']): ?>
                <div style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;padding:14px 16px;border-radius:8px;margin-bottom:16px;">
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($feedback['password']['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <input type="hidden" name="form_type" value="change_password">

                <label for="current_password">Contraseña actual</label>
                <input type="password" name="current_password" id="current_password" required autocomplete="current-password">

                <label for="new_password">Nueva contraseña</label>
                <input type="password" name="new_password" id="new_password" required autocomplete="new-password">

                <label for="confirm_password">Confirmar nueva contraseña</label>
                <input type="password" name="confirm_password" id="confirm_password" required autocomplete="new-password">

                <p style="margin:8px 0 18px;color:#6b7280;font-size:12px;">Por seguridad, utiliza al menos 8 caracteres combinando letras, números y símbolos.</p>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Actualizar contraseña</button>
                </div>
            </form>
        </div>
    </div>

    <div id="branding" style="margin-top:40px;max-width:420px;">
        <h2 style="margin:0 0 16px;">Favicon del sitio</h2>
        <p style="margin:0 0 18px;color:#6b7280;font-size:0.95rem;">Actualiza el icono que se muestra en las pestañas del navegador tanto del sitio público como del panel administrativo.</p>
        <?php if ($feedback['branding']['success']): ?>
            <div style="background:#ecfdf5;color:#065f46;border:1px solid #34d399;padding:14px 16px;border-radius:8px;margin-bottom:16px;">
                <?php echo htmlspecialchars($feedback['branding']['success'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <?php if ($feedback['branding']['errors']): ?>
            <div style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;padding:14px 16px;border-radius:8px;margin-bottom:16px;">
                <ul style="margin:0;padding-left:18px;">
                    <?php foreach ($feedback['branding']['errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off" style="display:flex;flex-direction:column;gap:14px;">
            <input type="hidden" name="form_type" value="update_favicon">

            <div style="display:flex;flex-direction:column;gap:6px;">
                <label for="favicon_file">Seleccionar favicon</label>
                <input type="file" name="favicon_file" id="favicon_file" accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/svg+xml,image/webp" required>
            </div>

            <?php if (!empty($brandSettings['favicon_path'])): ?>
                <div style="display:flex;align-items:center;gap:16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 16px;">
                    <span style="display:inline-flex;width:40px;height:40px;border-radius:10px;background:#ffffff;border:1px solid #e5e7eb;align-items:center;justify-content:center;">
                        <img src="<?php echo htmlspecialchars(adminAssetUrl($brandSettings['favicon_path']), ENT_QUOTES, 'UTF-8'); ?>" alt="Favicon actual" style="width:24px;height:24px;object-fit:contain;">
                    </span>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span style="font-size:0.85rem;color:#111111;">Archivo actual: <?php echo htmlspecialchars($brandSettings['favicon_path'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($brandSettings['updated_at'])): ?>
                            <span style="font-size:0.75rem;color:#6b7280;">Última actualización: <?php echo htmlspecialchars($brandSettings['updated_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <p style="margin:0;color:#6b7280;font-size:0.8rem;">Usa un PNG, WEBP, SVG o ICO cuadrado de hasta 1024px de lado y máximo 500KB.</p>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Actualizar favicon</button>
            </div>
        </form>
    </div>

    <?php if ($canCreateAdmins): ?>
        <div style="margin-top:40px;display:grid;gap:32px;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
            <div>
                <h2 style="margin:0 0 16px;">Crear nuevo usuario admin</h2>
                <?php if ($feedback['create']['success']): ?>
                    <div style="background:#ecfdf5;color:#065f46;border:1px solid #34d399;padding:14px 16px;border-radius:8px;margin-bottom:16px;">
                        <?php echo htmlspecialchars($feedback['create']['success'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($feedback['create']['errors']): ?>
                    <div style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;padding:14px 16px;border-radius:8px;margin-bottom:16px;">
                        <ul style="margin:0;padding-left:18px;">
                            <?php foreach ($feedback['create']['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <input type="hidden" name="form_type" value="create_admin">

                    <label for="new_username">Usuario</label>
                    <input type="text" name="new_username" id="new_username" value="<?php echo htmlspecialchars($createOld['username'], ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="off">

                    <label for="new_full_name">Nombre completo</label>
                    <input type="text" name="new_full_name" id="new_full_name" value="<?php echo htmlspecialchars($createOld['full_name'], ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="off">

                    <label for="new_password">Contraseña</label>
                    <input type="password" name="new_password" id="new_password" required autocomplete="new-password">

                    <label for="new_password_confirm">Confirmar contraseña</label>
                    <input type="password" name="new_password_confirm" id="new_password_confirm" required autocomplete="new-password">

                    <label style="display:flex;align-items:center;gap:8px;margin-top:12px;">
                        <input type="checkbox" name="new_is_active" value="1" <?php echo $createOld['is_active'] ? 'checked' : ''; ?>>
                        <span>Activar acceso inmediatamente</span>
                    </label>

                    <p style="margin:12px 0 18px;color:#6b7280;font-size:12px;">Los nuevos usuarios reciben el rol <strong>admin</strong> y no pueden crear cuentas adicionales.</p>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Crear usuario</button>
                    </div>
                </form>
            </div>

            <div style="overflow-x:auto;">
                <h2 style="margin:0 0 16px;">Usuarios administradores registrados</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($adminUsers): ?>
                            <?php foreach ($adminUsers as $user): ?>
                                <tr>
                                    <td><?php echo (int) $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php if ((int) $user['is_active'] === 1): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-muted">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;color:#6b7280;padding:18px 16px;">Sin registros adicionales.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../includes/page-bottom.php'; ?>
