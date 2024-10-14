<?php

namespace Api\Controllers;

use App\Controllers\Controller;
use App\Controllers\SuccessController;
use App\Util\HttpException;

class ApiController extends SuccessController {
    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        if (count($path) === 0) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw HttpException::methodNotSupported("GET");
            }
            header('Content-Type: application/json');
            echo json_encode([
                "message" => "Welkom!",
            ]);
            return null;
        }
        $topDir = array_shift($path);
        return match ($topDir) {
            "letter" => new LetterApiController($path),
            "woord" => new WordApiController($path),
            "zoek" => new SearchApiController($path),
            default => throw HttpException::notFound()
        };
    }
}
