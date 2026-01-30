<?php
session_start();

if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administrativo</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f0f2f5;
            color: #2f3640;
        }
        header {
            background: #273c75;
            color: #ffffff;
            padding: 16px 24px;
        }
        main {
            padding: 24px;
        }
        a {
            color: #273c75;
        }
    </style>
</head>
<body>
    <header>
        <h1>Panel administrativo</h1>
    </header>
    <main>
        <p>Bienvenido, admin. Aquí podrás gestionar el contenido del sitio.</p>
        <p>Próximamente agregaremos las herramientas para administrar servicios y contenido.</p>
    </main>
</body>
</html>
