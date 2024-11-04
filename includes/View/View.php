<?php

namespace LaraWp\Includes\View;

use Exception;

class View {
    protected $path;

    protected $data = [];

    public function render( $path, $data = [] ) {
        echo $this->make( $path, $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function make( $path, $data = [] ) {
        if ( file_exists( $this->path = $this->resolveFilePath( $path ) ) ) {
            $this->data = $data;
            return $this;
        }

        throw new Exception( "The view file [{$this->path}] doesn't exists!" );
    }

    protected function resolveFilePath( $path ) {
        return LARAWP_PLUGIN_DIR_PATH . "views/$path.php";
    }

    protected function renderContent(): string {
        $renderOutput = function () {
            ob_start() && extract( $this->data );

            include $this->path;

            return ltrim( ob_get_clean() );
        };

        return $renderOutput();
    }

    public function __set( $key, $value ) {
        $this->data[ $key ] = $value;
    }

    public function __toString() {
        return $this->renderContent();
    }
}