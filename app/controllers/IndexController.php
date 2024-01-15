<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseException;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

class IndexController extends SuccessController {
    /**
     * @var WordModel[] $recentlyAddedWords
     */
    private readonly array $recentlyAddedWords;

    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        if (count($path) === 0) {
            try {
                $databaseHelper = new DatabaseHelper();
                $this->recentlyAddedWords = $databaseHelper->getRecentlyAddedWords();
            } catch (DatabaseException $e) {
                throw new HttpException($e->getMessage(), 500);
            }
            require Controller::getViewPath("IndexView");
            return;
        }
        $topDir = array_shift($path);
        $controller = match ($topDir) {
            "letter" => new LetterController($path),
            "over-ons" => new AboutUsController($path),
            "woord" => new WordController($path),
            "zoek" => new SearchController($path),
            default => throw HttpException::notFound()
        };
        $controller->load();
    }
}
