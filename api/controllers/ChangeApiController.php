<?php

namespace api\controllers;

use api\util\DatabaseHelper;
use app\controllers\SuccessController;
use app\util\HttpException;

class ChangeApiController extends SuccessController {
    #[\Override]
    public function handle(array $path): void {
        if (count($path) !== 1) {
            throw HttpException::notFound();
        }
        $change = array_shift($path);
        $databaseHelper = DatabaseHelper::getInstance();
        $wordModel = $databaseHelper->getWordChange($change);
        if ($wordModel === null) {
            throw HttpException::notFound();
        }
        header('Content-Type: application/json');
        echo json_encode($wordModel);
    }
}