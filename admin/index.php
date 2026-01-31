<?php
session_start();

if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config.php';

$error = '';
$oldUsername = '';

if (isset($_SESSION['admin_flash'])) {
    $error = $_SESSION['admin_flash']['error'] ?? '';
    $oldUsername = $_SESSION['admin_flash']['username'] ?? '';
    unset($_SESSION['admin_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $storeError = function (string $message) use ($username): void {
        $_SESSION['admin_flash'] = [
            'error' => $message,
            'username' => $username
        ];
        header('Location: index.php');
        exit;
    };

    if ($username === '' || $password === '') {
        $storeError('Debes completar ambos campos.');
    }

    try {
        $db = getAdminDb();
        $stmt = $db->prepare('SELECT id, username, password_hash, full_name, role, is_active FROM admin_users WHERE username = ? LIMIT 1');

        if ($stmt === false) {
            throw new RuntimeException('No se pudo preparar la consulta.');
        }

        $stmt->bind_param('s', $username);

        if (!$stmt->execute()) {
            throw new RuntimeException('No se pudo ejecutar la consulta.');
        }

        $stmt->store_result();

        if ($stmt->num_rows !== 1) {
            $stmt->close();
            $storeError('Usuario o contraseña incorrectos.');
        }

        $stmt->bind_result($id, $dbUsername, $passwordHash, $fullName, $role, $isActive);
        $stmt->fetch();
        $stmt->close();

        if ((int) $isActive !== 1) {
            $storeError('Usuario o contraseña incorrectos.');
        }

        if (!password_verify($password, $passwordHash)) {
            $storeError('Usuario o contraseña incorrectos.');
        }

        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_user'] = [
            'id' => (int) $id,
            'username' => $dbUsername,
            'full_name' => $fullName,
            'role' => $role
        ];
        header('Location: dashboard.php');
        exit;
    } catch (Throwable $exception) {
        $storeError('No se pudo iniciar sesión. Intenta nuevamente.');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrativo - Iniciar sesión</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f6fa;
            color: #2f3640;
        }
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-box {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
        }
        .login-title {
            margin: 0 0 20px;
            font-size: 24px;
            text-align: center;
        }
        .login-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .login-form input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dcdde1;
            border-radius: 4px;
            font-size: 15px;
            margin-bottom: 16px;
        }
        .login-form button {
            width: 100%;
            padding: 12px 0;
            background: #273c75;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .login-form button:hover {
            background: #1e2f5a;
        }
        .login-error {
            background: #ffdddd;
            color: #c23616;
            border: 1px solid #e84118;
            padding: 10px 12px;
            border-radius: 4px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <h1 class="login-title">Panel administrativo</h1>
            <?php if (!empty($error)) : ?>
                <div class="login-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form class="login-form" method="post" novalidate>
                <label for="username">Usuario</label>
                <input type="text" name="username" id="username" required autocomplete="username" value="<?php echo htmlspecialchars($oldUsername, ENT_QUOTES, 'UTF-8'); ?>">

                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required autocomplete="current-password">

                <button type="submit">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>
