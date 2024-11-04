<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Lara WP
 * Plugin URI:        https://www.larawp.com
 * Description:       LaraWP plugin boilerplate
 * Version:           1.0.0
 * Author:            LaraWP
 * Author URI:        https://www.larawp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lara-wp
 * Domain Path:       /languages/
 */

use LaraWp\Includes\Core\Activator;
use LaraWp\Includes\Core\Deactivator;
use LaraWp\Includes\Core\Uninstaller;

define( 'LARAWP_DEV', true );
define( 'LARAWP_FILE', __FILE__ );
define( 'LARAWP_VERSION', '1.0.0' );
define( 'LARAWP_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'LARAWP_PLUGIN_URL_PATH', plugin_dir_url( __FILE__ ) );
define( 'LARAWP_TABLE_PREFIX', 'lara_wp_' );

include_once 'vendor/autoload.php';
require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

register_activation_hook( __FILE__, [ Activator::class, 'handle' ] );
register_deactivation_hook( __FILE__, [ Deactivator::class, 'handle' ] );
register_uninstall_hook( __FILE__, [ Uninstaller::class, 'handle' ] );

function run_lara_wp_plugin() {
    $application = new \LaraWp\Includes\Application();
    $application->boot();
}

add_action('plugins_loaded', 'run_lara_wp_plugin');

$cli = new \LaraWp\Includes\WP_CLI\Cli();