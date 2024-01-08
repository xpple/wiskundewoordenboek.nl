<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseException;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

class WordController extends SuccessController {

    private readonly ?WordModel $wordModel;

    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                echo "Zoek voor een woord";
                break;
            case 1:
                $word = array_shift($path);
                try {
                    $databaseHelper = new DatabaseHelper();
                    $this->wordModel = $databaseHelper->getWord($word);
                } catch (DatabaseException $e) {
                   throw new HttpException($e->getMessage(), 500);
                }
                if ($this->wordModel === null) {
                    throw new HttpException("Niet gevonden.", 404);
                }
                require Controller::getViewPath("WordView");
                break;
            default:
                throw new HttpException("Niet gevonden.", 404);
        }
    }
}
