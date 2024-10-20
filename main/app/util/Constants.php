<?php

namespace app\util;

final class Constants {
    const int WW_DEV_API_PORT = 8081;

    const string WW_PROD_API_HOST = "api.wiskundewoordenboek.nl";

    /**
     * @link https://discord.com/developers/docs/resources/message#embed-object-embed-limits
     */
    const int DISCORD_EMBED_MAX_FIELD_VALUE = 1024;

    public static function isDevEnv(): bool {
        return getenv("WW_ENVIRONMENT") === "DEV";
    }

    public static function getApiBaseUrl(): string {
        if (self::isDevEnv()) {
            return "http://" . getenv("WW_HOST") . ':' . self::WW_DEV_API_PORT;
        }
        return "https://" . self::WW_PROD_API_HOST;
    }
}
