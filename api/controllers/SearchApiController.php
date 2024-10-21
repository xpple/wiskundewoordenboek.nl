<?php

namespace api\controllers;

use api\util\DatabaseHelper;
use app\controllers\SuccessController;
use app\util\HttpException;

class SearchApiController extends SuccessController {
    #[\Override]
    public function handle(array $path): void {
        switch (count($path)) {
            case 0:
                throw HttpException::notFound();
            case 1:
                $query = array_shift($path);
                $databaseHelper = DatabaseHelper::getInstance();
                $wordModels = $databaseHelper->getWordsForQuery($query);
                header('Content-Type: application/json');
                echo json_encode($wordModels);
                return;
            default:
                throw HttpException::notFound();
        }
    }
}
