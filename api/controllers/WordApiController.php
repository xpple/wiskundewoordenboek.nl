<?php

namespace api\controllers;

use api\util\DatabaseHelper;
use app\controllers\Controller;
use app\controllers\SuccessController;
use app\util\HttpException;

class WordApiController extends SuccessController {
    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                throw HttpException::notFound();
            case 1:
                $word = array_shift($path);
                $databaseHelper = DatabaseHelper::getInstance();
                $wordModel = $databaseHelper->getWord($word);
                if ($wordModel !== null) {
                    header('Content-Type: application/json');
                    echo json_encode($wordModel);
                    return null;
                }
                $primaryDirectory = $databaseHelper->getPrimaryDirectoryForAlias($word);
                if ($primaryDirectory !== null) {
                    header("Location: /woord/$primaryDirectory/", true, 308);
                    exit;
                }
                throw HttpException::notFound();
            default:
                throw HttpException::notFound();
        }
    }
}