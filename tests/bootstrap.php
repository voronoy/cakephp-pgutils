<?php
declare(strict_types=1);

use Cake\Database\Connection;
use Cake\Database\Driver\Postgres;
use Cake\Datasource\ConnectionManager;

$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);

    throw new Exception('Cannot find the root of the application, unable to run tests');
};
$root = $findRoot(__FILE__);
unset($findRoot);

chdir($root);
if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';
}

require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';
if (is_readable(__DIR__ . '/env.php')) {
    require_once 'env.php';
}
\Cake\Core\Configure::write('App.namespace', 'Voronoy\PgUtils\Test\TestApp');
$dbConfig = [
    'className' => Connection::class,
    'driver' => Postgres::class,
    'host' => getenv('db_host'),
    'username' => getenv('db_user') ?: null,
    'password' => getenv('db_pass') ?: null,
    'database' => getenv('db_name') ?: null,
    'timezone' => 'UTC',
    'quoteIdentifiers' => false,
    'cacheMetadata' => false,
];
ConnectionManager::drop('test');
ConnectionManager::setConfig('test', $dbConfig);
