<?php

namespace App\Util;

class DatabaseException extends \Exception {
    public static function unknownError(): DatabaseException {
        return new DatabaseException("Er ging iets fout.");
    }
}
