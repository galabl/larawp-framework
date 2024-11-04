<?php

namespace LaraWp;

use LaraWp\Includes\Container;
use LaraWp\Includes\Core\Router;

class Routes {
    protected Container $container;

    public function __construct( Container $container ) {
        $this->container = $container;
    }

    /**
     * Register HTTP/Ajax routes
     * TODO add documentation link
     * @link
     * @return void
     */
    public function registerRoutes() {
        $router = new Router( $this->container );
        $router->register();
    }
}