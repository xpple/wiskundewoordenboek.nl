<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class WordController extends SuccessController {
    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                return new NewWordController([""]);
            case 1:
                $word = array_shift($path);

                $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/woord/" . urlencode($word));
                $message = $json["errorMessage"] ?? null;
                if ($message !== null) {
                    return new NewWordController($this->getPath());
                }
                $wordModel = new WordModel(...$json);
                return new ExistingWordController($this->getPath(), $wordModel);
            default:
                throw HttpException::notFound();
        }
    }
}
