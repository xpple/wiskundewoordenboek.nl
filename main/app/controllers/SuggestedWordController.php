<?php

namespace app\controllers;

use app\models\SuggestedWordModel;
use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class SuggestedWordController extends SuccessController {
    /* @var WordModel[] */
    private readonly array $recentlyAddedWords;

    private readonly SuggestedWordModel $wordModel;

    #[\Override]
    public function handle(array $path): void {
        if (count($path) !== 1) {
            throw HttpException::notFound();
        }
        $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/recent/");
        $message = $json["errorMessage"] ?? null;
        if ($message !== null) {
            throw ApiException::withMessage($message);
        }
        $this->recentlyAddedWords = array_map(static fn($args) => new WordModel(...$args), $json);
        $suggestedWord = array_shift($path);
        $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/suggestie/" . urlencode($suggestedWord));
        $message = $json["errorMessage"] ?? null;
        if ($message !== null) {
            throw ApiException::withMessage($message);
        }
        $this->wordModel = new SuggestedWordModel(...$json);
        require Controller::getViewPath("SuggestedWordView");
    }
}
