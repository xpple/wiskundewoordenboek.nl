<?php

namespace app\controllers;

use app\util\HttpException;

abstract class SuccessController extends Controller {
    /* @var callable(string[]): SuccessController[] */
    private array $routes = [];

    /* @var string[] */
    private readonly array $path;

    /**
     * @param string[] $path
     */
    public function __construct(array $path = []) {
        $this->path = $path;
    }

    /**
     * @return string[]
     */
    public function getPath(): array {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getRoutes(): array {
        return array_keys($this->routes);
    }

    /**
     * @param string $route
     * @param callable(string[]): SuccessController $controller
     * @return void
     */
    public function route(string $route, callable $controller): void {
        $this->routes[$route] = $controller;
    }

    /**
     * @param string $route
     * @return callable(string[]): SuccessController
     * @throws HttpException
     */
    public function getController(string $route): callable {
        return $this->routes[$route] ?? throw HttpException::notFound();
    }

    /**
     * @throws HttpException
     */
    public abstract function handle(): void;
}
