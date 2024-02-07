<?php

namespace App\Controllers;

use App\Util\HttpException;

class ErrorController extends Controller {

    private readonly \Exception $cause;

    public function __construct(\Exception $cause) {
        $this->cause = $cause;
    }

    #[\Override]
    public function loadAndDelegate(): ?Controller {
        if ($this->cause instanceof HttpException) {
            http_response_code($this->cause->getCode());
        } else {
            http_response_code(500);
        }
        require Controller::getViewPath("ErrorView");
        return null;
    }
}
