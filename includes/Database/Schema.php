<?php

namespace LaraWp\Includes\Database;

class Schema {
    public static function create( $table, $callback ) {
        global $wpdb;
        $table = $wpdb->prefix . LARAWP_TABLE_PREFIX . $table;
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {
            $blueprint = new Blueprint( true ); // Set true for debugging SQL
            $callback( $blueprint ); // Call the closure that defines the table
            $sql = $blueprint->getTableSQL( $table ); // Get the generated SQL

            dbDelta( $sql );
        }
    }

    public static function dropIfExists($table) {
        global $wpdb;

        $tableName = $wpdb->prefix . LARAWP_TABLE_PREFIX . $table;
        dbDelta( "DROP TABLE IF EXISTS $tableName");
    }
}

