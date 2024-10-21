<?php

namespace app\controllers;

use app\util\HttpException;

class AboutUsController extends SuccessController {
    #[\Override]
    public function handle(): void {
        $path = $this->getPath();
        if (count($path) === 0) {
            require Controller::getViewPath("AboutUsView");
            return;
        }
        throw HttpException::notFound();
    }
}
