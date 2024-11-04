<?php

namespace LaraWp\App\Filters;

use LaraWp\Includes\Application;

class Filters {
    protected Application $app;

    public function __construct( Application $app ) {
        $this->app = $app;
    }

    /**
     * Register filters
     *
     * add_filter: https://developer.wordpress.org/reference/functions/add_filter/
     *
     * @return void
     */
    public function register() {
        // Register filters
    }
}