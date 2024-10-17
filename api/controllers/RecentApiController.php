<?php

namespace api\controllers;

use api\util\DatabaseHelper;
use app\controllers\Controller;
use app\controllers\SuccessController;
use app\util\HttpException;

class RecentApiController extends SuccessController {
    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                $databaseHelper = DatabaseHelper::getInstance();
                $wordModels = $databaseHelper->getRecentlyAddedWords(3);
                header('Content-Type: application/json');
                echo json_encode($wordModels);
                return null;
            case 1:
                $amount = intval(array_shift($path));
                if ($amount <= 0) {
                    throw HttpException::notFound();
                }
                $databaseHelper = DatabaseHelper::getInstance();
                $wordModels = $databaseHelper->getRecentlyAddedWords($amount);
                header('Content-Type: application/json');
                echo json_encode($wordModels);
                return null;
            default:
                throw HttpException::notFound();
        }
    }
}
