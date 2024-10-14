<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\ApiHelper;
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

                $json = ApiHelper::fetchJson("https://api.wiskundewoordenboek.nl/woord/" . urlencode($word));
                $message = $json["errorMessage"] ?? null;
                if ($message !== null) {
                    return new NewWordController($this->getPath());
                }
                $wordModel = new WordModel(...$json);
                return new ExistingWordController($this->getPath(), $wordModel);
            default:
                throw HttpException::notFound();
        }
    }
}
