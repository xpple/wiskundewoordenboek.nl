<?php

use App\Controllers\ErrorController;
use App\Controllers\IndexController;
use App\Util\HttpException;

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
        throw new HttpException("Onjuiste URL.", 400);
    }
    /* @var string $requestPath */
    if (str_ends_with($requestPath, "index.php")) {
        $requestPath = mb_substr($requestPath, 1, -mb_strlen("index.php"));
    } else {
        $requestPath = mb_substr($requestPath, 1);
    }
    $controller = new IndexController(array_filter(explode('/', $requestPath)));
    $controller->load();
} catch (HttpException $e) {
    $controller = new ErrorController($e);
    $controller->load();
}
