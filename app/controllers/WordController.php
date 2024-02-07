<?php

namespace App\Controllers;

use App\Util\DatabaseHelper;
use App\Util\HttpException;

class WordController extends SuccessController {
    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                return new NewWordController([""]);
            case 1:
                $word = array_shift($path);

                $databaseHelper = DatabaseHelper::getInstance();

                $wordModel = $databaseHelper->getWord($word);

                if ($wordModel === null) {
                    $primaryDirectory = $databaseHelper->getPrimaryDirectoryForAlias($word);

                    if ($primaryDirectory !== null) {
                        header("Location: /woord/$primaryDirectory/", true, 301);
                        exit;
                    }

                    return new NewWordController($this->getPath());
                }

                return new ExistingWordController($this->getPath(), $wordModel);
            default:
                throw HttpException::notFound();
        }
    }
}
