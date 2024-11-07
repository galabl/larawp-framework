<?php

namespace LaraWp\App\Hooks;

use LaraWp\App\Http\Controllers\Api\RestApiController;
use LaraWp\App\Http\Controllers\SettingsController;
use LaraWp\Includes\Application;

class Hooks {

    protected Application $app;

    public function __construct( Application $app ) {
        $this->app = $app;
    }

    /**
     * Register hooks
     *
     * @return void
     * @throws \Exception
     */
    public function register(): void {
        $this->app->register_action( 'init', Shortcodes::class, 'handle' );
        $this->app->register_action( 'admin_menu', AdminMenu::class, 'handle' );
        $this->app->register_action( 'admin_enqueue_scripts', GlobalStylesHook::class, 'register' );
        $this->app->register_action( 'rest_api_init', RestApiController::class, 'register_routes' );
    }
}