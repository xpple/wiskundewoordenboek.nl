<?php

namespace app\util;

final class ApiHelper {
    private function __construct() {
    }

    /**
     * @throws ApiException
     */
    public static function fetchJson($url): mixed {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($curl);
        if (curl_errno($curl) !== 0) {
            throw ApiException::unknownError();
        }
        $json = json_decode($response, true);
        if ($json === null) {
            throw ApiException::unknownError();
        }
        return $json;
    }
}
