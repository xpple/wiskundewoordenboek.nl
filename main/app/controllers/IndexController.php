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

    public function __construct($parts) {
        parent::__construct($parts);
        $this->route("contact", fn($path) => new ContactController($path));
        $this->route("letter", fn($path) => new LetterController($path));
        $this->route("over-ons", fn($path) => new AboutUsController($path));
        $this->route("woord", fn($path) => new WordController($path));
        $this->route("zoek", fn($path) => new SearchController($path));
    }

    #[\Override]
    public function handle(): void {
        $path = $this->getPath();
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
        $this->getController($topDir)($path)->handle();
    }
}
