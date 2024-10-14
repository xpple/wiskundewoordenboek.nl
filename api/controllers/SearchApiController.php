<?php

namespace Api\Controllers;

use Api\Util\DatabaseHelper;
use App\Controllers\Controller;
use App\Controllers\SuccessController;
use App\Util\HttpException;

class SearchApiController extends SuccessController {
    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                throw HttpException::notFound();
            case 1:
                $query = array_shift($path);
                $databaseHelper = DatabaseHelper::getInstance();
                $wordModels = $databaseHelper->getWordsForQuery($query);
                header('Content-type: application/json');
                echo json_encode($wordModels);
                return null;
            default:
                throw HttpException::notFound();
        }
    }
}
