<?php

namespace LaraWp\App\Hooks;

use LaraWp\App\Http\Controllers\CategoriesController;
use LaraWp\App\Http\Controllers\CouponController;
use LaraWp\App\Http\Controllers\DashboardController;
use LaraWp\App\Http\Controllers\EntriesController;
use LaraWp\App\Http\Controllers\EventsController;
use LaraWp\App\Http\Controllers\LocationsController;
use LaraWp\App\Http\Controllers\PricingController;
use LaraWp\App\Http\Controllers\SettingsController;
use LaraWp\App\Http\Controllers\TosController;
use LaraWp\App\Http\Controllers\ZipCodeController;
use LaraWp\Includes\Application;
use LaraWp\Includes\Http\Request;

class AdminMenu {
    protected Application $app;
    protected array $pages;
    protected $page = 'lara-wp';

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function handle() {
        add_menu_page(
            'Lara WP',
            'Lara WP',
            'manage_options',
            $this->page,
            ''
        );

        $this->addSubmenus([
            ['Lara WP', 'Lara WP', $this->page, DashboardController::class, 'view'],
        ]);
    }

    protected function addSubmenus(array $submenus) {
        foreach ($submenus as $submenu) {
            $this->pages[$submenu[2]] = add_submenu_page(
                $this->page,
                $submenu[0],
                $submenu[1],
                'manage_options',
                $submenu[2],
                function() use ($submenu) {
                    $controller = $this->app->get($submenu[3]);

                    // Extract all query parameters dynamically from the URL (using $_GET)
                    $params = new Request($_GET, $_POST, $_FILES );

                    // Call the controller's view method and pass the dynamic parameters
                    call_user_func([$controller, $submenu[4]], $params);
                }
            );
        }
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function enqueue_scripts( $hook_page ) {
        if ( in_array( $hook_page, $this->pages ) ) {
            $main_js = array_search( $hook_page, $this->pages );

            wp_enqueue_script(
                "lara-wp-admin-$main_js-js",
                LARAWP_PLUGIN_URL_PATH . "assets/admin/js/$main_js.js",
                [ 'jquery' ],
                filemtime( LARAWP_PLUGIN_DIR_PATH . "assets/admin/js/$main_js.js" ),
				[
					'strategy' => 'defer',
					'in_footer' => true
				]
            );

            // Defer Alpine.js loading and prevent auto-start
            wp_enqueue_script(
                'lara-wp-alpine-mask',
                LARAWP_PLUGIN_URL_PATH . 'assets/libs/js/alpine-mask.min.js',
                [ 'jquery' ],
                filemtime( LARAWP_PLUGIN_DIR_PATH . 'assets/libs/js/alpine-mask.min.js' ),
                [
                    'strategy' => 'defer',
                    'in_footer' => true
                ]
            );

            // Defer Alpine.js loading and prevent auto-start
            wp_enqueue_script(
                'lara-wp-alpine',
                LARAWP_PLUGIN_URL_PATH . 'assets/libs/js/alpine.min.js',
                [ 'jquery' ],
                filemtime( LARAWP_PLUGIN_DIR_PATH . 'assets/libs/js/alpine.min.js' ),
                [
                    'strategy' => 'defer',
                    'in_footer' => true
                ]
            );
        }
    }
}
