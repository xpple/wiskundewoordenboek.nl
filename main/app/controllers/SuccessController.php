<?php

namespace app\controllers;

use app\util\HttpException;

abstract class SuccessController extends Controller {
    /* @var callable(string[]): void[] */
    private array $routes = [];

    /**
     * @return string[]
     */
    public function getRoutes(): array {
        return array_keys($this->routes);
    }

    /**
     * @param string $route
     * @param callable(string[]): void $handler
     * @return void
     */
    public function route(string $route, callable $handler): void {
        $this->routes[$route] = $handler;
    }

    /**
     * @param string $route
     * @return callable(string[]): void
     * @throws HttpException
     */
    public function getController(string $route): callable {
        return $this->routes[$route] ?? throw HttpException::notFound();
    }

    /**
     * @throws HttpException
     */
    public abstract function handle(array $path): void;
}
