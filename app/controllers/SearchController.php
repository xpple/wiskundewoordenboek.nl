<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

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
                $databaseHelper = DatabaseHelper::getInstance();
                $this->wordModels = $databaseHelper->getWordsForQuery($this->query);
                require Controller::getViewPath("SearchView");
                return null;
            default:
                throw HttpException::notFound();
        }
    }
}
