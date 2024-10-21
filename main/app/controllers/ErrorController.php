<?php

namespace app\controllers;

use app\util\HttpException;

class ErrorController extends Controller {

    private readonly \Exception $cause;

    public function __construct(\Exception $cause) {
        $this->cause = $cause;
    }

    #[\Override]
    public function handle(array $path): void {
        if ($this->cause instanceof HttpException) {
            http_response_code($this->cause->getCode());
        } else {
            http_response_code(500);
        }
        require Controller::getViewPath("ErrorView");
    }
}
