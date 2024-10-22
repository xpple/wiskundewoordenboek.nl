<?php

namespace app\util;

final class ApiHelper {
    private function __construct() {
    }

    /**
     * @throws ApiException
     */
    public static function fetchJson(string $url): mixed {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($curl);
        if (curl_errno($curl) !== 0) {
            throw ApiException::unknownError();
        }
        try {
            $json = json_decode($response, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ApiException::unknownError();
        }
        if ($json === null) {
            throw ApiException::unknownError();
        }
        return $json;
    }

    /**
     * @throws ApiException
     */
    public static function postJson(string $url, mixed $object): mixed {
        $curl = curl_init();
        try {
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($object, JSON_THROW_ON_ERROR),
            ]);
        } catch (\JsonException) {
            throw ApiException::unknownError();
        }
        $response = curl_exec($curl);
        if (curl_errno($curl) !== 0) {
            throw ApiException::unknownError();
        }
        try {
            $json = json_decode($response, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ApiException::unknownError();
        }
        if ($json === null) {
            throw ApiException::unknownError();
        }
        return $json;
    }
}
