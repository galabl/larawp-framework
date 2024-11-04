<?php

namespace LaraWp\App\Jobs;

use LaraWp\Includes\Application;

class Job {
    protected Application $app;

    public function __construct( Application $app ) {
        $this->app = $app;
    }

    /**
     * Jobs registration
     *
     * Job should be invoked by woo's actions scheduler
     *
     * Documentation: https://actionscheduler.org/
     * @link https://actionscheduler.org/
     * @return void
     */
    public function handle() {
        // Define jobs here

    }
}