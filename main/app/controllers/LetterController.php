<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class LetterController extends SuccessController {
    private readonly string $letter;

    /**
     * @var WordModel[] $wordModels
     */
    private readonly array $wordModels;

    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                // perhaps implement later
                throw HttpException::notFound();
            case 1:
                $this->letter = array_shift($path);
                if (mb_strlen($this->letter) !== 1) {
                    throw HttpException::notFound();
                }
                $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/letter/" . urlencode($this->letter));
                $message = $json["errorMessage"] ?? null;
                if ($message !== null) {
                    throw ApiException::withMessage($message);
                }
                $this->wordModels = array_map(static fn($args) => new WordModel(...$args), $json);
                require Controller::getViewPath("LetterView");
                return null;
            default:
                throw HttpException::notFound();
        }
    }
}
