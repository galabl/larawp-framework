<?php

namespace LaraWp\App\Http\Controllers;

use LaraWp\Includes\Http\Request;

class DashboardController extends Controller {
    public function view( Request $request ) {
        $this->view->render('admin/dashboard');
    }
}