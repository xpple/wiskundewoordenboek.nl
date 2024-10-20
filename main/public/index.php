<?php

use app\controllers\ErrorController;
use app\controllers\IndexController;
use app\util\HttpException;

$f = fn($p) => $p;

spl_autoload_register(static function ($class) {
    static $root;
    if ($root == null) {
        $root = dirname($_SERVER['DOCUMENT_ROOT']);
    }
    $parts = explode('\\', $class);
    $filename = array_pop($parts);
    $parts = strtolower(implode('/', $parts));
    $filePath = $root . '/' . $parts . '/' . $filename . '.php';
    if (is_file($filePath)) {
        require $filePath;
    }
});

try {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($requestPath === null or $requestPath === false) {
        throw HttpException::badRequest();
    }
    /* @var string $requestPath */
    $parts = explode('/', mb_substr($requestPath, 1));
    $end = end($parts);
    if ($end === "" or $end === "index.php") {
        array_pop($parts);
    }
    $parts = array_map(urldecode(...), $parts);
    $controller = new IndexController();
    $controller->handle($parts);
} catch (HttpException $e) {
    $controller = new ErrorController($e);
    $controller->handle($parts ?? []);
}
