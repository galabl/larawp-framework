<?php

namespace LaraWp\Database;

require_once( ABSPATH . 'wp-admin/includes/upgrade.php');

class Migrator {
    public static function run( $type = 'up' ) {
        self::migrate( $type );
    }

    public static function migrate( $type ) {
    }
}