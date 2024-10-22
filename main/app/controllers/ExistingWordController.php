<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class ExistingWordController extends SuccessController {
    /* @var WordModel[] */
    private readonly array $recentlyAddedWords;

    private readonly WordModel $wordModel;

    public function __construct(WordModel $wordModel) {
        $this->wordModel = $wordModel;
    }

    #[\Override]
    public function handle(array $path): void {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/recent/");
                $message = $json["errorMessage"] ?? null;
                if ($message !== null) {
                    throw ApiException::withMessage($message);
                }
                $this->recentlyAddedWords = array_map(static fn($args) => new WordModel(...$args), $json);
                require Controller::getViewPath("ExistingWordView");
                return;
            case 'POST':
                $json = ApiHelper::postJson(Constants::getApiBaseUrl() . "/woord/" . urlencode($this->wordModel->wordDirectory), $_POST);
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
