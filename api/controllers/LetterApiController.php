<?php

namespace api\controllers;

use api\util\DatabaseHelper;
use app\controllers\SuccessController;
use app\util\HttpException;

class LetterApiController extends SuccessController {
    #[\Override]
    public function handle(array $path): void {
        if (count($path) !== 1) {
            throw HttpException::notFound();
        }
        $letter = array_shift($path);
        if (mb_strlen($letter) !== 1) {
            throw HttpException::notFound();
        }
        $databaseHelper = DatabaseHelper::getInstance();
        $wordModels = $databaseHelper->getWordsForLetter($letter);
        header('Content-Type: application/json');
        echo json_encode($wordModels);
    }
}
