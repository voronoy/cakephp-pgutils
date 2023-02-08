<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Database\Driver\Postgres;
use Cake\Datasource\ConnectionManager;
use Cake\Routing\RouteCollection;

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

require_once 'vendor/cakephp/cakephp/src/basics.php';
require_once 'vendor/autoload.php';

define('ROOT', $root . DS . 'tests' . DS . 'test_app' . DS);
define('APP', ROOT . 'App' . DS);
define('CONFIG', APP);
define('TMP', sys_get_temp_dir() . DS);
define('CACHE', TMP . 'cache' . DS);

define('CORE_PATH', $root . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS);

// Enable strict_variables Twig configuration
Configure::write('Bake.twigStrictVariables', true);

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'Voronoy\PgUtils\Test\TestApp',
    'paths' => [
        'plugins' => [ROOT . 'Plugin' . DS],
        'templates' => [ROOT . 'templates' . DS],
    ],
    'encoding' => 'UTF-8',
]);

Configure::write('Cache', [
    '_cake_model_' => [
        'className' => \Cake\Cache\Engine\NullEngine::class,
        'prefix' => 'myapp_cake_model_',
        'path' => ROOT . 'models' . DS,
        'serialize' => true,
        'duration' => '+1 minute',
        'url' => env('CACHE_CAKEMODEL_URL', null),
    ],
]);

if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';
}

//require $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';
if (is_readable(__DIR__ . '/env.php')) {
    require_once 'env.php';
}
//\Cake\Core\Configure::write('App.namespace', 'Voronoy\PgUtils\Test\TestApp');
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
ConnectionManager::drop('test_cached');
ConnectionManager::setConfig('test', $dbConfig);
$dbConfig['cacheMetadata'] = true;
ConnectionManager::setConfig('test_cached', $dbConfig);

\Cake\Core\Plugin::getCollection()->add(new \Bake\Plugin());
\Cake\Routing\Router::setRouteCollection(new RouteCollection());
\Cake\Cache\Cache::setConfig(Configure::consume('Cache'));
