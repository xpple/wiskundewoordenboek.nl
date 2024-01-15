<?php

namespace App\Controllers;

use App\Util\HttpException;

class AboutUsController extends SuccessController {
    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        if (count($path) === 0) {
            require Controller::getViewPath("AboutUsView");
            return;
        }
        throw HttpException::notFound();
    }
}
