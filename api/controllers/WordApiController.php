<?php

namespace api\controllers;

use api\util\DatabaseHelper;
use app\controllers\Controller;
use app\controllers\SuccessController;
use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class WordApiController extends SuccessController {

    private const string sanitizePattern = "/\p{Mn}|[^a-zA-Z0-9-]/u";

    #[\Override]
    public function handle(array $path): void {
        if (count($path) !== 1) {
            throw HttpException::notFound();
        }
        $word = array_shift($path);
        $databaseHelper = DatabaseHelper::getInstance();
        $wordModel = $databaseHelper->getWord($word);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if ($wordModel !== null) {
                    header('Content-Type: application/json');
                    echo json_encode($wordModel);
                    return;
                }
                $primaryDirectory = $databaseHelper->getPrimaryDirectoryForAlias($word);
                if ($primaryDirectory !== null) {
                    header("Location: /woord/$primaryDirectory/", true, 308);
                    exit;
                }
                throw HttpException::notFound();
            case 'POST':
                try {
                    $_POST = json_decode(file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    throw ApiException::unknownError();
                }
                $isChange = match ($_POST["type"] ?? null) {
                    "aanpassing" => true,
                    "creatie" => false,
                    default => "'type' moet 'aanpassing' of 'creatie' zijn!"
                };
                if (is_string($isChange)) {
                    header('Content-Type: application/json');
                    echo json_encode(["errorCode" => 400, "errorMessage" => $isChange]);
                    return;
                }
                if ($wordModel !== null) {
                    if (!$isChange) {
                        self::wordAlreadyExists();
                        return;
                    }
                    $result = self::handleWordChange($wordModel);
                    header('Content-Type: application/json');
                    echo json_encode($result);
                    return;
                }
                $primaryDirectory = $databaseHelper->getPrimaryDirectoryForAlias($word);
                if ($primaryDirectory !== null) {
                    if (!$isChange) {
                        self::wordAlreadyExists();
                        return;
                    }
                    header("Location: /woord/$primaryDirectory/", true, 308);
                    exit;
                }
                $result = self::handleSuggestedWord($word);
                header('Content-Type: application/json');
                echo json_encode($result);
                return;
            default:
                throw HttpException::methodNotSupported("GET, POST");
        }
    }

    /**
     * @throws HttpException
     */
    private static function handleWordChange(WordModel $wordModel): object {
        $changes = $_POST["changes"] ?? null ?: null;
        $validation = self::validateString($changes, Constants::DISCORD_EMBED_MAX_FIELD_VALUE,
            "Aanpassingen mogen niet leeg zijn!",
            "Aanpassingen mogen niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!");
        if ($validation !== true) {
            return $validation;
        }
        $description = $_POST["description"] ?? null ?: null;
        $validation = self::validateString($description, Constants::DISCORD_EMBED_MAX_FIELD_VALUE,
            "Beschrijving mag niet leeg zijn!",
            "Beschrijving mag niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!");
        if ($validation !== true) {
            return $validation;
        }
        $meaning = ($_POST["meaning"] ?? null) === "formeel" ? "formeel" : "standaard";
        $email = $_POST["email"] ?? null ?: null;
        if ($email !== null and strlen($email) > Constants::DB_MAX_EMAIL_LENGTH) {
            http_response_code(400);
            return (object) ["errorCode" => 400, "errorMessage" => "E-mailadres mag niet langer dan " . Constants::DB_MAX_EMAIL_LENGTH . " karakters zijn!"];
        }

        require Controller::getRoot() . "/api/public-webhook.php";
        /* @var string $webhook */
        ApiHelper::postJson($webhook, [
            "username" => "Wijzigingen",
            "embeds" => [
                [
                    "title" => "Nieuwe wijziging",
                    "description" => "Er is een nieuwe wijziging aan [" . $wordModel->wordCapitalised . " ($meaning)](https://wiskundewoordenboek.nl/woord/" . urlencode($wordModel->wordDirectory) . "/?betekenis=$meaning) voorgesteld.",
                    "color" => 0x1e90ff,
                    "fields" => [
                        [
                            "name" => "Aanpassingen",
                            "value" => $changes
                        ],
                        [
                            "name" => "Beschrijving",
                            "value" => $description
                        ],
                        [
                            "name" => "E-mail",
                            "value" => $email
                        ]
                    ]
                ]
            ],
        ]);
        $databaseHelper = DatabaseHelper::getInstance();
        $databaseHelper->newWordChange($wordModel, $meaning, $changes, $description, $email);
        return (object) ["success" => true];
    }

    /**
     * @throws HttpException
     */
    private static function handleSuggestedWord(string $word): object {
        $name = $_POST["name"] ?? null ?: null;
        $validation = self::validateString($name, Constants::DB_MAX_NAME_LENGTH,
            "Naam mag niet leeg zijn!",
            "Naam mag niet langer dan " . Constants::DB_MAX_NAME_LENGTH . " karakters zijn!");
        if ($validation !== true) {
            return $validation;
        }
        $sanitisedName = self::sanitize($name);
        if ($sanitisedName !== $word) {
            http_response_code(400);
            return (object) ["errorCode" => 400, "errorMessage" => "Ongeldig verzoek!"];
        }
        $meaningOption = ($_POST["meaning"] ?? null) === "formeel" ? "formeel" : "standaard";
        $content = $_POST["content"] ?? null ?: null;
        $validation = self::validateString($content, Constants::DISCORD_EMBED_MAX_FIELD_VALUE,
            "Inhoud mag niet leeg zijn!",
            "Inhoud mag niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!");
        if ($validation !== true) {
            return $validation;
        }
        $description = $_POST["description"] ?? null ?: null;
        $validation = self::validateString($description, Constants::DISCORD_EMBED_MAX_FIELD_VALUE,
            "Beschrijving mag niet leeg zijn!",
            "Beschrijving mag niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!");
        if ($validation !== true) {
            return $validation;
        }
        $email = $_POST["email"] ?? null ?: null;
        if ($email !== null and strlen($email) > Constants::DB_MAX_EMAIL_LENGTH) {
            http_response_code(400);
            return (object) ["errorCode" => 400, "errorMessage" => "E-mailadres mag niet langer dan " . Constants::DB_MAX_EMAIL_LENGTH . " karakters zijn!"];
        }

        require Controller::getRoot() . "/api/public-webhook.php";
        /* @var string $webhook */
        ApiHelper::postJson($webhook, [
            "username" => "Toevoegingen",
            "embeds" => [
                [
                    "title" => "Nieuwe toevoeging",
                    "description" => "Er is een nieuw woord voorgesteld: [$name ($meaningOption)](https://wiskundewoordenboek.nl/woord/$sanitisedName/?betekenis=$meaningOption).",
                    "color" => 0x1e90ff,
                    "fields" => [
                        [
                            "name" => "Inhoud",
                            "value" => $content
                        ],
                        [
                            "name" => "Beschrijving",
                            "value" => $description
                        ],
                        [
                            "name" => "E-mail",
                            "value" => $email ? "`$email`" : "Geen e-mailadres opgegeven"
                        ]
                    ]
                ]
            ],
        ]);
        $databaseHelper = DatabaseHelper::getInstance();
        $databaseHelper->newWordSuggestion($name, $sanitisedName, $meaningOption, $content, $description, $email);
        return (object) ["success" => true];
    }

    private static function sanitize(string $string): string {
        $sanitisedString = $string;
        $sanitisedString = \Normalizer::normalize($sanitisedString, \Normalizer::NFD);
        $sanitisedString = preg_replace("/\s+/", '-', $sanitisedString);
        $sanitisedString = preg_replace(self::sanitizePattern, '', $sanitisedString);
        return mb_strtolower($sanitisedString);
    }

    /**
     * @param string|null $string
     * @param int $maxLength
     * @param string $emptyErrorMessage
     * @param string $tooLongErrorMessage
     * @return object|true
     */
    private static function validateString(?string $string, int $maxLength, string $emptyErrorMessage, string $tooLongErrorMessage): object|true {
        if ($string === null) {
            http_response_code(400);
            return (object) ["errorCode" => 400, "errorMessage" => $emptyErrorMessage];
        }
        if (strlen($string) > $maxLength) {
            http_response_code(400);
            return (object) ["errorCode" => 400, "errorMessage" => $tooLongErrorMessage];
        }
        return true;
    }

    private static function wordAlreadyExists(): void {
        http_response_code(409);
        header('Content-Type: application/json');
        echo json_encode([
            "errorCode" => 409,
            "errorMessage" => "Dit woord bestaat al!",
        ]);
    }
}
