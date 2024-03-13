<?php

namespace App\Controllers;

use App\Models\WordModel;
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
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        if (count($path) === 0) {
            $databaseHelper = DatabaseHelper::getInstance();
            $this->randomWords = $databaseHelper->getRandomWords(3);
            $this->recentlyAddedWords = $databaseHelper->getRecentlyAddedWords(3);
            require Controller::getViewPath("IndexView");
            return null;
        }
        $topDir = array_shift($path);
        return match ($topDir) {
            "contact" => new ContactController($path),
            "letter" => new LetterController($path),
            "over-ons" => new AboutUsController($path),
            "woord" => new WordController($path),
            "zoek" => new SearchController($path),
            default => throw HttpException::notFound()
        };
    }
}
