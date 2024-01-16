<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseException;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

class IndexController extends SuccessController {
    /**
     * @var WordModel[] $randomWords
     */
    private readonly array $randomWords;

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
                $this->randomWords = $databaseHelper->getRandomWords(3);
                $this->recentlyAddedWords = $databaseHelper->getRecentlyAddedWords(3);
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
