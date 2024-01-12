<?php

namespace App\Util;

class HttpException extends \Exception {
    /* @var int */
    protected $code;

    /**
     * @throws HttpException
     */
    public static function badRequest() {
        throw new HttpException("Onjuiste URL.", 400);
    }

    /**
     * @throws HttpException
     */
    public static function notFound() {
        throw new HttpException("Niet gevonden.", 404);
    }
}
