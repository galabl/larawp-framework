<?php

namespace LaraWp\Includes\WP_CLI;

use WP_CLI;
use WP_CLI_Command;

class MakeMigration extends WP_CLI_Command {

    protected string $namespace;
    protected string $blueprint;
    protected string $schema;

    public function __construct( $namespace ) {
        $this->namespace = $namespace . "\Database\Migrations";
        $this->blueprint = "$namespace\Includes\Database\Blueprint";
        $this->schema = "$namespace\Includes\Database\Schema";
        WP_CLI::add_command( 'larawp make:migration', [ $this, 'create' ] );
    }

    /**
     * Create new table migration.
     *
     * <tableName>
     * : Table name ( Example: users )
     *
     * @param array $args
     *
     * @subcommand make:migration
     */
    public function create( array $args ) {
        if ( empty( $args ) ) {
            WP_CLI::error( 'You must provide table name.' );
        }

        $table_name = $args[ 0 ];
        if ( !$this->valid_table_name( $table_name ) ) {
            WP_CLI::error( "Invalid table name. Allows lowercase letters, numbers, and underscores" );
        }
        $class_name = str_replace( '_', '', ucwords( $table_name, '_' ) );

        $file_path = sprintf( "%sdatabase/Migrations/%s.php", LARAWP_PLUGIN_DIR_PATH, $class_name );

        if ( file_exists( $file_path ) ) {
            \WP_CLI::error( "Migration $table_name already exists." );
        }

        $class_template = $this->generate_class_template( $table_name, $class_name );

        if ( !is_dir( dirname( $file_path ) ) ) {
            mkdir( dirname( $file_path ), 0755, true );
        }

        // Write the class template to the file
        file_put_contents( $file_path, $class_template );

        WP_CLI::success( "Migration class $table_name created successfully in $file_path." );
    }


    /**
     * Generate a PHP class template with the given  class name.
     *
     * @param string $class_name The name of the class.
     *
     * @return string The generated class template as a string.
     */
    private function generate_class_template( $table_name, string $class_name ) {
        return <<<PHP
            <?php
            
            namespace $this->namespace;
            
            use $this->schema;
            use $this->blueprint;
                        
            class $class_name  {            
                public static function migrate( \$type ) {
                    call_user_func( [ self::class, \$type ] );
                }
                
                public static function up() {
                    Schema::create('$table_name', function ( Blueprint \$table ) {
                        \$table->increments('id');
                        // Rest goes here
                    });
                }
                
                public static function down() {
                    Schema::dropIfExists('$table_name');
                }
            }
            PHP;
    }

    private function valid_table_name( $table_name ) {
        return preg_match( '/^[a-z0-9_]+$/', $table_name );
    }
}