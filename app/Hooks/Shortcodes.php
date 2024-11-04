<?php

namespace LaraWp\App\Hooks;

use LaraWp\App\Http\Controllers\SignupController;
use LaraWp\Includes\Application;

class Shortcodes {
    protected Application $app;

    public function __construct( Application $app ) {
        $this->app = $app;
    }

    public function handle() {
        // Define shortcodes here
    }
}