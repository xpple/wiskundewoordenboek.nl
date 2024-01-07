<?php

namespace App\Controllers;

use App\Util\HttpException;

class IndexController extends SuccessController {
    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        if (count($path) === 0) {
            require Controller::getViewPath("IndexView");
            return;
        }
        $topDir = array_shift($path);
        $controller = match ($topDir) {
            "letter" => new LetterController($path),
            "over-ons" => new AboutUsController($path),
            "woord" => new WordController($path),
            default => throw new HttpException("Niet gevonden.", 404)
        };
        $controller->load();
    }
}
