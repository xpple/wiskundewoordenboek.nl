<?php

use api\controllers\ApiController;
use api\controllers\ErrorApiController;
use app\util\HttpException;

spl_autoload_register(static function ($class) {
    static $root;
    static $appRoot; // to use classes from `main`
    if ($root === null or $appRoot === null) {
        $root = dirname($_SERVER['DOCUMENT_ROOT']);
        $appRoot = dirname($_SERVER['DOCUMENT_ROOT']) . "/main";
    }
    $parts = explode('\\', $class);
    $filename = array_pop($parts);
    $parts = strtolower(implode('/', $parts));
    if (str_starts_with($parts, "api")) {
        $filePath = $root . '/' . $parts . '/' . $filename . '.php';
    } elseif (str_starts_with($parts, "app")) {
        $filePath = $appRoot . '/' . $parts . '/' . $filename . '.php';
    } else {
        return;
    }
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
    $controller = new ApiController();
    $controller->handle($parts);
} catch (HttpException $e) {
    $controller = new ErrorApiController($e);
    $controller->handle($parts ?? []);
}
