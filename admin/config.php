<?php
$adminDbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'globalinduprod_admin',
    'port' => 3306,
    'charset' => 'utf8mb4'
];

function getAdminDb(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    global $adminDbConfig;

    $connection = new mysqli(
        $adminDbConfig['host'],
        $adminDbConfig['username'],
        $adminDbConfig['password'],
        $adminDbConfig['database'],
        $adminDbConfig['port']
    );

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión a la base de datos.');
    }

    if (!$connection->set_charset($adminDbConfig['charset'])) {
        throw new RuntimeException('No se pudo establecer el charset de la conexión.');
    }

    return $connection;
}
