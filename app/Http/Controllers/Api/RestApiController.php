<?php

namespace LaraWp\App\Http\Controllers\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class RestApiController extends WP_REST_Controller {
	const API_VERSION = 'lara-wp/v1';

	public function register_routes() {

	}

	public function is_admin(WP_REST_Request $request) {
		return current_user_can('manage_options');
	}
}