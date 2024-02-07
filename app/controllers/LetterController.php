<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

class LetterController extends SuccessController {
    private readonly string $letter;

    /**
     * @var WordModel[] $wordModels
     */
    private readonly array $wordModels;

    #[\Override]
    public function loadAndDelegate(): ?Controller {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                // perhaps implement later
                throw HttpException::notFound();
            case 1:
                $letter = array_shift($path);
                if (mb_strlen($letter) !== 1) {
                    throw HttpException::notFound();
                }
                $this->letter = $letter;

                $databaseHelper = DatabaseHelper::getInstance();
                $this->wordModels = $databaseHelper->getWordsForLetter($this->letter);
                require Controller::getViewPath("LetterView");
                return null;
            default:
                throw HttpException::notFound();
        }
    }
}
