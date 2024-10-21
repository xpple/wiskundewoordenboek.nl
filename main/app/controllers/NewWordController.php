<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class NewWordController extends SuccessController {

    private const string sanitizePattern = "/\p{Mn}|[^a-zA-Z0-9-]/u";

    private readonly string $word;

    /* @var WordModel[] */
    private readonly array $recentlyAddedWords;

    /**
     * @param string[] $path
     *
     * @throws ApiException
     */
    public function __construct(array $path) {
        parent::__construct($path);
        $this->word = $path[0];
        $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/recent/");
        $message = $json["errorMessage"] ?? null;
        if ($message !== null) {
            throw ApiException::withMessage($message);
        }
        $this->recentlyAddedWords = array_map(static fn($args) => new WordModel(...$args), $json);
    }

    #[\Override]
    public function handle(): void {
        $sanitisedWord = self::sanitize($this->word);

        if ($sanitisedWord !== $this->word) {
            header("Location: /woord/$sanitisedWord/", true, 308);
            exit;
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                require Controller::getViewPath("NewWordView");
                return;
            case 'POST':
                $result = $this->handlePost();
                header('Content-Type: application/json');
                echo json_encode($result);
                return;
            default:
                throw HttpException::methodNotSupported("GET, POST");
        }
    }

    private function handlePost(): object {
        $name = $_POST["name"] ?? null ?: null;
        if ($name === null) {
            return (object) ["success" => false, "errorMessage" => "Naam mag niet leeg zijn!"];
        }
        if (strlen($name) > 256) {
            return (object) ["success" => false, "errorMessage" => "Naam mag niet langer dan 256 karakters zijn!"];
        }
        $sanitisedName = self::sanitize($name);
        if ($sanitisedName !== $this->word) {
            return (object) ["success" => false, "errorMessage" => "Ongeldig verzoek!"];
        }
        $meaning = ($_POST["meaning"] ?? null) === "formeel" ? "formeel" : "standaard";
        $content = $_POST["content"] ?? null ?: null;
        if ($content === null) {
            return (object) ["success" => false, "errorMessage" => "Inhoud mag niet leeg zijn!"];
        }
        if (strlen($content) > Constants::DISCORD_EMBED_MAX_FIELD_VALUE) {
            return (object) ["success" => false, "errorMessage" => "Inhoud mag niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!"];
        }
        $description = $_POST["description"] ?? null ?: null;
        if ($description === null) {
            return (object) ["success" => false, "errorMessage" => "Beschrijving mag niet leeg zijn!"];
        }
        if (strlen($description) > Constants::DISCORD_EMBED_MAX_FIELD_VALUE) {
            return (object) ["success" => false, "errorMessage" => "Beschrijving mag niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!"];
        }
        $email = ($_POST["email"] ?? null) ? "`{$_POST["email"]}`" : "Geen e-mailadres opgegeven";
        if (strlen($email) > Constants::DISCORD_EMBED_MAX_FIELD_VALUE) {
            return (object) ["success" => false, "errorMessage" => "E-mailadres mag niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!"];
        }

        $curl = curl_init();
        require Controller::getRoot() . "/app/public-webhook.php";
        /* @var string $webhook */
        curl_setopt_array($curl, [
            CURLOPT_URL => $webhook,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                "username" => "Toevoegingen",
                "embeds" => [
                    [
                        "title" => "Nieuwe toevoeging",
                        "description" => "Er is een nieuw woord voorgesteld: [$name ($meaning)](https://wiskundewoordenboek.nl/woord/$sanitisedName/?betekenis=$meaning).",
                        "color" => 0x1e90ff,
                        "fields" => [
                            [
                                "name" => "Inhoud",
                                "value" => "$content"
                            ],
                            [
                                "name" => "Beschrijving",
                                "value" => "$description"
                            ],
                            [
                                "name" => "E-mail",
                                "value" => "$email"
                            ]
                        ]
                    ]
                ],
            ]),
        ]);
        $response = curl_exec($curl);
        if ($response === true) {
            return (object) ["success" => true];
        }
        return (object) ["success" => false, "errorMessage" => "Er ging achter de schermen iets mis!"];
    }

    private static function sanitize(string $string): string {
        $sanitisedString = $string;
        $sanitisedString = \Normalizer::normalize($sanitisedString, \Normalizer::NFD);
        $sanitisedString = preg_replace("/\s+/", '-', $sanitisedString);
        $sanitisedString = preg_replace(self::sanitizePattern, '', $sanitisedString);
        return mb_strtolower($sanitisedString);
    }
}
