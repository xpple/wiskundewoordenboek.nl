<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseException;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

class LetterController extends SuccessController {
    private readonly string $letter;

    /**
     * @var WordModel[] $wordModels
     */
    private readonly array $wordModels;

    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                echo "Zoek voor een letter";
                break;
            case 1:
                $letter = array_shift($path);
                if (mb_strlen($letter) !== 1) {
                    throw new HttpException("Niet gevonden.", 404);
                }
                $this->letter = $letter;
                $databaseHelper = new DatabaseHelper();
                try {
                    $this->wordModels = $databaseHelper->getWordsForLetter($letter);
                } catch (DatabaseException $e) {
                    throw new HttpException($e->getMessage(), 500);
                }
                require Controller::getViewPath("LetterView");
                break;
            default:
                throw new HttpException("Niet gevonden.", 404);
        }
    }
}
