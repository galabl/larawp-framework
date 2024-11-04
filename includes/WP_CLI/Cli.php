<?php

namespace LaraWp\Includes\WP_CLI;

if ( !defined( 'WP_CLI' ) ) {
    wp_die( 'WP Cli is not defined' );
}

use WP_CLI;
use WP_CLI_Command;

class Cli extends WP_CLI_Command {
    protected MakeController $create_controller;
    protected MakeModel $create_model;
    protected MakeMigration $make_migration;
    protected string $namespace;

    /**
     * Register the command with WP-CLI
     */
    public function __construct() {
        if ( !defined( 'WP_CLI' ) || !WP_CLI ) {
            return;
        }

        $namespace = explode( "\\", __NAMESPACE__ )[ 0 ];

        $this->create_controller    = new MakeController( $namespace );
        $this->create_model         = new MakeModel( $namespace );
        $this->make_migration       = new MakeMigration( $namespace );
    }
}