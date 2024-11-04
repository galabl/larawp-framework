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

    public function get_instance() {
        return $this;
    }
}