<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseException;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

class WordController extends SuccessController {

    private readonly WordModel $wordModel;

    /**
     * @var WordModel[] $recentlyAddedWords
     */
    private readonly array $recentlyAddedWords;

    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                // perhaps implement later
                throw HttpException::notFound();
            case 1:
                $word = array_shift($path);
                try {
                    $databaseHelper = new DatabaseHelper();

                    $wordModel = $databaseHelper->getWord($word);

                    if ($wordModel === null) {
                        $word = $databaseHelper->getPrimaryDirectoryForAlias($word);

                        if ($word !== null) {
                            header("Location: /woord/{$word}/", true, 301);
                            exit;
                        }

                        throw HttpException::notFound();
                    }

                    $this->wordModel = $wordModel;
                    $this->recentlyAddedWords = $databaseHelper->getRecentlyAddedWords();
                    require Controller::getViewPath("WordView");
                    break;
                } catch (DatabaseException $e) {
                   throw new HttpException($e->getMessage(), 500);
                }
            default:
                throw HttpException::notFound();
        }
    }
}
