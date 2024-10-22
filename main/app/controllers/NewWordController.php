<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class NewWordController extends SuccessController {

    private readonly string $word;

    /* @var WordModel[] */
    private readonly array $recentlyAddedWords;

    #[\Override]
    public function handle(array $path): void {
        $this->word = $path[0];
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/recent/");
                $message = $json["errorMessage"] ?? null;
                if ($message !== null) {
                    throw ApiException::withMessage($message);
                }
                $this->recentlyAddedWords = array_map(static fn($args) => new WordModel(...$args), $json);
                require Controller::getViewPath("NewWordView");
                return;
            case 'POST':
                $json = ApiHelper::postJson(Constants::getApiBaseUrl() . "/woord/" . urlencode($this->word), $_POST);
                $message = $json["errorMessage"] ?? null;
                header('Content-Type: application/json');
                if ($message === null) {
                    echo json_encode(["success" => true]);
                    return;
                }
                echo json_encode([
                    "success" => false,
                    "errorMessage" => $message
                ]);
                return;
            default:
                throw HttpException::methodNotSupported("GET, POST");
        }
    }
}
