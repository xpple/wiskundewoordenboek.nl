<?php

namespace App\Util;

class HttpException extends \Exception {
    /* @var int */
    protected $code;

    public static function badRequest(): HttpException {
        return new HttpException("Onjuiste URL.", 400);
    }

    public static function notFound(): HttpException {
        return new HttpException("Niet gevonden.", 404);
    }

    public static function methodNotSupported(string $supportedMethods): HttpException {
        header("Allow: $supportedMethods");
        return new HttpException("Verzoekmethode wordt niet ondersteund.", 405);
    }
}
