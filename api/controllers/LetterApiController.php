<?php

namespace Api\Controllers;

use Api\Util\DatabaseHelper;
use App\Controllers\Controller;
use App\Controllers\SuccessController;
use App\Util\HttpException;

class LetterApiController extends SuccessController {
    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
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
                header('Content-type: application/json');
                echo json_encode($wordModels);
                return null;
            default:
                throw HttpException::notFound();
        }
    }
}
