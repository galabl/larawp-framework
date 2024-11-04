<?php

namespace LaraWp\App\Http\Controllers;

use LaraWp\Includes\View\View;

class Controller {

    protected View $view;

    public function __construct( View $view ) {
        $this->view = $view;
    }
}