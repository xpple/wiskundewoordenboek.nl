<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\HttpException;

class SearchController extends SuccessController {

    private readonly string $query;

    /**
     * @var WordModel[] $wordModels
     */
    private readonly array $wordModels;

    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                if (array_key_exists("query", $_POST)) {
                    header("Location: /zoek/" . urlencode($_POST["query"]) . "/", true, 303);
                    exit;
                }
                // perhaps implement later
                throw HttpException::notFound();
            case 1:
                $this->query = array_shift($path);
                $json = ApiHelper::fetchJson("https://api.wiskundewoordenboek.nl/zoek/" . urlencode($this->query));
                $message = $json["errorMessage"] ?? null;
                if ($message !== null) {
                    throw ApiException::withMessage($message);
                }
                $this->wordModels = array_map(static fn($args) => new WordModel(...$args), $json);
                require Controller::getViewPath("SearchView");
                return null;
            default:
                throw HttpException::notFound();
        }
    }
}
