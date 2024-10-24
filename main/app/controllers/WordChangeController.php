<?php

namespace app\controllers;

use app\models\WordChangeModel;
use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class WordChangeController extends SuccessController {
    /* @var WordModel[] */
    private readonly array $recentlyAddedWords;

    private readonly WordChangeModel $changeModel;

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
        $changedWord = array_shift($path);
        $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/aanpassing/" . urlencode($changedWord));
        $message = $json["errorMessage"] ?? null;
        if ($message !== null) {
            throw ApiException::withMessage($message);
        }
        $this->changeModel = WordChangeModel::fromJson(...$json);
        require Controller::getViewPath("WordChangeView");
    }
}
