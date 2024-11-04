<?php

namespace LaraWp\Includes\Core;

use LaraWp\Database\Migrator;

class Activator {
    public static function handle( $network_wide = false ) {
        if ( is_multisite() && $network_wide ) {
            foreach ( get_sites( [ 'fields' => 'ids' ] ) as $blog_id ) {
                switch_to_blog( $blog_id );
                Migrator::run();
                restore_current_blog();
            }
        } else {
            Migrator::run();
        }
    }
}