<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseException;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

class SearchController extends SuccessController {

    private readonly string $query;

    /**
     * @var WordModel[] $wordModels
     */
    private readonly array $wordModels;

    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                if (array_key_exists("query", $_POST)) {
                    header("Location: /zoek/" . urlencode($_POST["query"]) . "/", true, 301);
                    exit;
                }
                // perhaps implement later
                throw HttpException::notFound();
            case 1:
                $this->query = array_shift($path);
                try {
                    $databaseHelper = new DatabaseHelper();
                    $this->wordModels = $databaseHelper->getWordsForQuery($this->query);
                } catch (DatabaseException $e) {
                    throw new HttpException($e->getMessage(), 500);
                }
                require Controller::getViewPath("SearchView");
                break;
            default:
                throw HttpException::notFound();
        }
    }
}
