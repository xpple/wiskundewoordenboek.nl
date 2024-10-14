<?php

namespace App\Util;

class ApiException extends HttpException {
    private function __construct(string $message) {
        parent::__construct($message, 500);
    }

    public static function unknownError(): ApiException {
        return new ApiException("Er ging iets fout.");
    }

    public static function withMessage(string $message): ApiException {
        return new ApiException($message);
    }
}
