<?php

namespace LaraWp\Includes\Core;

use LaraWp\Includes\Container;
use LaraWp\Includes\Http\Request;

class Router {
    protected Container $container;
    protected $routes = [];

    public function __construct( Container $container ) {
        $this->container = $container;
    }

    public function post($action, $callback, $type = 'http', $middleware = 'admin') {
        $this->routes[] = ['method' => 'POST', 'action' => $action, 'callback' => $callback, 'type' => $type, 'middleware' => $middleware];
    }

    public function get($action, $callback, $type = 'http', $middleware = 'admin') {
        $this->routes[] = ['method' => 'GET', 'action' => $action, 'callback' => $callback, 'type' => $type, 'middleware' => $middleware];
    }

    public function register() {
        foreach ($this->routes as $route) {
            $action_name = $route['action'];
            $method = $route['method'];
            $callback = $route['callback'];
            $type = match ($route['type']) {
                "http" => "admin_post",
                "ajax" => "wp_ajax"
            };

            $middleware = $route['middleware'];

            add_action("{$type}_$action_name", function () use ($method, $callback) {
                $this->handleRequest($method, $callback);
            });
            if ( $middleware != 'admin' ) {
                add_action("{$type}_nopriv_$action_name", function () use ($method, $callback) {
                    $this->handleRequest($method, $callback);
                });
            }
        }
    }

    protected function handleRequest($method, $callback) {
        if ($_SERVER['REQUEST_METHOD'] === strtoupper($method)) {
            // Create a Request instance
            $request = new Request($_GET, $_POST, $_FILES);
            // Call the callback with the Request instance
            call_user_func([$this->container->get($callback[0]), $callback[1]], $request);
        } else {
            wp_die('Invalid request method.');
        }
    }
}
