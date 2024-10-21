<?php

namespace api\controllers;

use api\util\DatabaseHelper;
use app\controllers\SuccessController;
use app\util\HttpException;

class LetterApiController extends SuccessController {
    #[\Override]
    public function handle(array $path): void {
        switch (count($path)) {
            case 0:
                throw HttpException::notFound();
            case 1:
                $letter = array_shift($path);
                if (mb_strlen($letter) !== 1) {
                    throw HttpException::notFound();
                }
                $databaseHelper = DatabaseHelper::getInstance();
                $wordModels = $databaseHelper->getWordsForLetter($letter);
                header('Content-Type: application/json');
                echo json_encode($wordModels);
                return;
            default:
                throw HttpException::notFound();
        }
    }
}
