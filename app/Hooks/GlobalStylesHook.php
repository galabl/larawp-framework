<?php

namespace LaraWp\App\Hooks;

class GlobalStylesHook {
    public function register(): void {
        if ( isset( $_GET['page'] ) && str_contains($_GET['page'], 'pet-form-' ) ) {
            wp_enqueue_style(
                'lara-wp-css', // Handle
                LARAWP_PLUGIN_URL_PATH . 'assets/admin/css/main.css',
                [],
                filemtime(LARAWP_PLUGIN_DIR_PATH . 'assets/admin/css/main.css' ),
                'all'
            );
        }
    }
}