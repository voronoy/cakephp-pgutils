<?php
declare(strict_types=1);

namespace Voronoy\PgUtils;

use Cake\Core\BasePlugin;

class Plugin extends BasePlugin
{
    /**
     * Plugin name
     *
     * @var string
     */
    protected $name = 'PgUtils';

    /**
     * Do bootstrapping or not
     *
     * @var bool
     */
    protected $bootstrapEnabled = true;

    /**
     * Load routes or not
     *
     * @var bool
     */
    protected $routesEnabled = false;

    /**
     * Enable middleware
     *
     * @var bool
     */
    protected $middlewareEnabled = false;

    /**
     * Console middleware
     *
     * @var bool
     */
    protected $consoleEnabled = false;
}
