<?php
require_once __DIR__ . '/../config/tenant.php';

$defaultAdminDbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'globalinduprod_admin',
    'port' => 3306,
    'charset' => 'utf8mb4'
];

$tenantAdminConfig = tenantInfo('admin_db');
if (!is_array($tenantAdminConfig)) {
    $tenantAdminConfig = [];
}

$adminDbConfig = array_replace($defaultAdminDbConfig, $tenantAdminConfig);

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
        throw new RuntimeException(tenantText('admin.error.db_connect', 'Error de conexión a la base de datos.'));
    }

    if (!$connection->set_charset($adminDbConfig['charset'])) {
        throw new RuntimeException(tenantText('admin.error.charset', 'No se pudo establecer el charset de la conexión.'));
    }

    return $connection;
}
