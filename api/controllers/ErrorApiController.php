<?php

namespace api\controllers;

use app\controllers\Controller;
use app\util\HttpException;

class ErrorApiController extends Controller {

    private readonly \Exception $cause;

    public function __construct(\Exception $cause) {
        $this->cause = $cause;
    }

    #[\Override]
    public function handle(array $path): void {
        if ($this->cause instanceof HttpException) {
            $responseCode = $this->cause->getCode();
            $message = $this->cause->getMessage();
        } else {
            $responseCode = 500;
            $message = "Er heeft zich een onbekende fout opgedaan.";
        }
        http_response_code($responseCode);
        header('Content-Type: application/json');
        echo json_encode([
            "errorCode" => $responseCode,
            "errorMessage" => $message,
        ]);
    }
}
