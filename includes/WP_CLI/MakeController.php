<?php

namespace LaraWp\Includes\WP_CLI;

use WP_CLI;
use WP_CLI_Command;

class MakeController extends WP_CLI_Command {

    protected string $namespace;
    public function __construct( $namespace ) {
        $this->namespace = $namespace . "\App\Http\Controllers";
        WP_CLI::add_command( 'larawp make:controller', [ $this, 'create' ] );
    }

    /**
     * Create new Controller class.
     *
     * <className>
     * : A controller class name ( UsersController )
     *
     * @param array $args
     *
     * @subcommand make:controller
     */
    public function create( array $args ) {
        if ( empty( $args ) ) {
            WP_CLI::error('You must provide controller class name.');
        }

        $class_name = $args[0];

        $file_path = LARAWP_PLUGIN_DIR_PATH . "app/Http/Controllers/$class_name.php";

        if (file_exists($file_path)) {
            \WP_CLI::error("Controller $class_name already exists.");
        }

        $class_template = $this->generate_class_template($class_name);

        if (!is_dir(dirname($file_path))) {
            mkdir(dirname($file_path), 0755, true);
        }

        // Write the class template to the file
        file_put_contents($file_path, $class_template);

        WP_CLI::success("Controller class $class_name created successfully in $file_path.");
    }


    /**
     * Generate a PHP class template with the given  class name.
     *
     * @param string $class_name The name of the class.
     * @return string The generated class template as a string.
     */
    private function generate_class_template($class_name) {
        return <<<PHP
            <?php
            
            namespace $this->namespace;
            
            class $class_name extends Controller {            
                // Class methods go here
            }
            PHP;
    }
}