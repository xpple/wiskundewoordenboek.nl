<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;

class IndexController extends SuccessController {
    /* @var WordModel[] */
    private readonly array $randomWords;

    /* @var WordModel[] */
    private readonly array $recentlyAddedWords;

    public function __construct() {
        $this->route("contact", (new ContactController())->handle(...));
        $this->route("letter", (new LetterController())->handle(...));
        $this->route("over-ons", (new AboutUsController())->handle(...));
        $this->route("woord", (new WordController())->handle(...));
        $this->route("zoek", (new SearchController())->handle(...));
    }

    #[\Override]
    public function handle(array $path): void {
        if (count($path) === 0) {
            $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/random/");
            $message = $json["errorMessage"] ?? null;
            if ($message !== null) {
                throw ApiException::withMessage($message);
            }
            $this->randomWords = array_map(static fn($args) => new WordModel(...$args), $json);
            $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/recent/");
            $message = $json["errorMessage"] ?? null;
            if ($message !== null) {
                throw ApiException::withMessage($message);
            }
            $this->recentlyAddedWords = array_map(static fn($args) => new WordModel(...$args), $json);
            require Controller::getViewPath("IndexView");
            return;
        }
        $topDir = array_shift($path);
        $this->getController($topDir)($path);
    }
}
