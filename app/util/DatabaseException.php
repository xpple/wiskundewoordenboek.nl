<?php

namespace App\Util;

class DatabaseException extends \Exception {
    /**
     * @throws DatabaseException
     */
    public static function unknownError() {
        throw new DatabaseException("Er ging iets fout.");
    }
}
