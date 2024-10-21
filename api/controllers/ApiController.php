<?php

namespace api\controllers;

use app\controllers\SuccessController;
use app\util\HttpException;

class ApiController extends SuccessController {
    public function __construct() {
        $this->route("letter", (new LetterApiController())->handle(...));
        $this->route("random", (new RandomApiController())->handle(...));
        $this->route("recent", (new RecentApiController())->handle(...));
        $this->route("woord", (new WordApiController())->handle(...));
        $this->route("zoek", (new SearchApiController())->handle(...));
    }

    #[\Override]
    public function handle(array $path): void {
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
        $this->getController($topDir)($path);
    }
}
