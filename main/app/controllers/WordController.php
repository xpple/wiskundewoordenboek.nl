<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class WordController extends SuccessController {
    #[\Override]
    public function handle(array $path): void {
        switch (count($path)) {
            case 0:
                $controller = new NewWordController();
                $controller->handle([""]);
                return;
            case 1:
                $word = array_shift($path);

                $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/woord/" . urlencode($word));
                $message = $json["errorMessage"] ?? null;
                if ($message !== null) {
                    $controller = new NewWordController($word);
                    $controller->handle([]);
                    return;
                }
                $wordModel = new WordModel(...$json);
                $controller = new ExistingWordController($wordModel);
                $controller->handle([]);
                return;
            default:
                throw HttpException::notFound();
        }
    }
}
