<?php

namespace LaraWp\Includes\WP_CLI;

use WP_CLI;
use WP_CLI_Command;

class MakeModel extends WP_CLI_Command {

    protected string $namespace;
    public function __construct( $namespace ) {
        $this->namespace = $namespace . "\App\Models";
        WP_CLI::add_command( 'larawp make:model', [ $this, 'create' ] );
    }

    /**
     * Create new Model class.
     *
     * <className>
     * : A model class name ( example: User )
     *
     * [--table_name=<string>]
     * : (Optional) Table name. Default, it tries to match table name from model class name ( Example: User will resolve to `users` )
     *
     * @param array $args
     *
     * @subcommand make:model
     */
    public function create( array $args, array $assoc_args ) {
        if ( empty( $args ) ) {
            WP_CLI::error('You must provide model class name.');
        }

        $class_name = $args[0];
        $table_name = ( isset( $assoc_args['table_name'] ) ) ? $assoc_args['table_name'] : $output = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $class_name)), '_') . 's';
        $file_path = LARAWP_PLUGIN_DIR_PATH . "app/Models/$class_name.php";

        if (file_exists($file_path)) {
            \WP_CLI::error("Model $class_name already exists.");
        }

        $class_template = $this->generate_class_template($class_name, $table_name);

        if (!is_dir(dirname($file_path))) {
            mkdir(dirname($file_path), 0755, true);
        }

        // Write the class template to the file
        file_put_contents($file_path, $class_template);

        WP_CLI::success("Model class $class_name created successfully in $file_path.");
    }


    /**
     * Generate a PHP class template with the given  class name.
     *
     * @param string $class_name The name of the class.
     * @return string The generated class template as a string.
     */
    private function generate_class_template($class_name, $table_name) {
        return <<<PHP
            <?php
            
            namespace $this->namespace;
            
            class $class_name extends Model {
                protected static \$table = LARAWP_TABLE_PREFIX . '$table_name';
                public static \$timestamps = true;            
                
                // Class methods go here
            }
            PHP;
    }
}