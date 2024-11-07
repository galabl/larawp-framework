<?php

namespace LaraWp\Includes;

use LaraWp\App\Hooks\Hooks;
use LaraWp\App\Jobs\Job;

final class Application extends Container {

    /**
     * @throws \Exception
     */
    public function __construct() {
        $this->register();
        $router = $this->get('router');
        $router->registerRoutes();
    }

    /**
     * @throws \Exception
     */
    public function boot() {
        $this->get(Hooks::class)->register();
        $this->get(Job::class)->handle();
    }

    public function register_action( $action, $controller, $method, $priority = 10, $args = 1 ) {
        add_action( $action, [ $this->get($controller), $method ], $priority, $args ) ;
    }

    public function register_filter( $filter, $controller, $method, $priority = 10, $args = 1 ) {
        add_filter( $filter, [ $this->get($controller), $method ], $priority, $args ) ;
    }

    public function get_instance() {
        return $this;
    }
}