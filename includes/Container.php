<?php

namespace LaraWp\Includes;

use LaraWp\App\Hooks\AdminMenu;
use LaraWp\App\Hooks\GlobalStylesHook;
use LaraWp\App\Hooks\Hooks;
use LaraWp\App\Hooks\Shortcodes;
use LaraWp\Includes\Http\Request;
use LaraWp\Includes\Http\Response;
use LaraWp\Includes\View\View;

class Container {
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $abstract, callable $concrete) {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, callable $concrete) {
        $this->bindings[$abstract] = function () use ($abstract, $concrete) {
            return $this->instances[$abstract] ??= $concrete();
        };
    }

    public function get(string $abstract) {
        return isset($this->bindings[$abstract]) ? $this->bindings[$abstract]() : throw new \Exception("No binding found for {$abstract}");
    }

    public function register() {
        $this->simpleBindings();
        $this->controllerBindings();
        $this->hookBindings();
        $this->jobsProvider();
    }

    protected function simpleBindings() {
        $this->bind(Request::class, fn() => new Request($_GET, $_POST, $_FILES));
        $this->bind(Response::class, fn() => new Response());
        $this->bind(View::class, fn() => new View());
        $this->bind(GlobalStylesHook::class, fn() => new GlobalStylesHook());
    }

    protected function controllerBindings() {
        $this->autoBindControllers('LaraWp\\App\\Http\\Controllers', __DIR__ . '/../app/Http/Controllers');
        $this->autoBindControllers('LaraWp\\App\\Http\\Controllers\\Api', __DIR__ . '/../app/Http/Controllers/Api');
    }

    protected function autoBindControllers(string $namespace, string $directory) {
        foreach (new \DirectoryIterator($directory) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $class = $namespace . '\\' . $file->getBasename('.php');
                if (class_exists($class)) {
                    $this->bind($class, fn() => new $class($this->get(View::class)));
                }
            }
        }
    }

    protected function hookBindings() {
        $hooks = [ AdminMenu::class, Hooks::class, Shortcodes::class ];

        foreach ($hooks as $hook ) {
            $this->bind($hook, fn() => new $hook($this->get_instance()));
        }

        $this->singleton('router', fn() => new \LaraWp\Routes($this));
    }

    protected function jobsProvider() {
        $directory = __DIR__ . '/../app/Jobs';
        $namespace = "LaraWp\\App\\Jobs";
        foreach (new \DirectoryIterator($directory) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $class = $namespace . '\\' . $file->getBasename('.php');
                if (class_exists($class)) {
                    // Bind the Job
                    $this->bind($class, fn() => new $class($this->get_instance()));
                    // Register the job using add_action
                }
            }
        }
    }
}
