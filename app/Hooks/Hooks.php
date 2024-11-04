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
        add_action( 'init', [ $this->app->get(Shortcodes::class), 'handle' ] );
        add_action( 'admin_menu', [ $this->app->get(AdminMenu::class), 'handle' ] );
        add_action( 'admin_enqueue_scripts', [ $this->app->get(GlobalStylesHook::class), 'register' ] );
        add_action( 'rest_api_init', [ $this->app->get(RestApiController::class), 'register_routes'] );
    }
}