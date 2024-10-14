<?php

namespace App\Util;

class DatabaseException extends HttpException {
    private function __construct(string $message) {
        parent::__construct($message, 500);
    }

    public static function unknownError(): DatabaseException {
        return new DatabaseException("Er ging iets fout.");
    }
}
