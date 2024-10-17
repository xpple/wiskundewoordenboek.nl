<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\HttpException;

class IndexController extends SuccessController {
    /**
     * @var WordModel[] $randomWords
     */
    private readonly array $randomWords;

    /**
     * @var WordModel[] $recentlyAddedWords
     */
    private readonly array $recentlyAddedWords;

    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        if (count($path) === 0) {
            $json = ApiHelper::fetchJson("https://api.wiskundewoordenboek.nl/random/");
            $message = $json["errorMessage"] ?? null;
            if ($message !== null) {
                throw ApiException::withMessage($message);
            }
            $this->randomWords = array_map(static fn($args) => new WordModel(...$args), $json);
            $json = ApiHelper::fetchJson("https://api.wiskundewoordenboek.nl/recent/");
            $message = $json["errorMessage"] ?? null;
            if ($message !== null) {
                throw ApiException::withMessage($message);
            }
            $this->recentlyAddedWords = array_map(static fn($args) => new WordModel(...$args), $json);
            require Controller::getViewPath("IndexView");
            return null;
        }
        $topDir = array_shift($path);
        return match ($topDir) {
            "contact" => new ContactController($path),
            "letter" => new LetterController($path),
            "over-ons" => new AboutUsController($path),
            "woord" => new WordController($path),
            "zoek" => new SearchController($path),
            default => throw HttpException::notFound()
        };
    }
}
