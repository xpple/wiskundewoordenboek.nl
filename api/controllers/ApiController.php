<?php

namespace api\controllers;

use app\controllers\SuccessController;
use app\util\HttpException;

class ApiController extends SuccessController {
    public function __construct(array $parts = []) {
        parent::__construct($parts);
        $this->route("letter", fn($path) => new LetterApiController($path));
        $this->route("random", fn($path) => new RandomApiController($path));
        $this->route("recent", fn($path) => new RecentApiController($path));
        $this->route("woord", fn($path) => new WordApiController($path));
        $this->route("zoek", fn($path) => new SearchApiController($path));
    }

    #[\Override]
    public function handle(): void {
        $path = $this->getPath();
        if (count($path) === 0) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw HttpException::methodNotSupported("GET");
            }
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Welkom!",
                "routes" => $this->getRoutes(),
            ]);
            return;
        }
        $topDir = array_shift($path);
        $this->getController($topDir)($path)->handle();
    }
}
