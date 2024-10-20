<?php

namespace app\controllers;

use app\models\WordModel;
use app\util\ApiException;
use app\util\ApiHelper;
use app\util\Constants;
use app\util\HttpException;

class ExistingWordController extends SuccessController {
    /**
     * @var WordModel[] $recentlyAddedWords
     */
    private readonly array $recentlyAddedWords;

    private readonly WordModel $wordModel;

    /**
     * @param string[] $path
     * @param WordModel $wordModel
     *
     * @throws ApiException
     */
    public function __construct(array $path, WordModel $wordModel) {
        parent::__construct($path);
        $this->wordModel = $wordModel;
        $json = ApiHelper::fetchJson(Constants::getApiBaseUrl() . "/recent/");
        $message = $json["errorMessage"] ?? null;
        if ($message !== null) {
            throw ApiException::withMessage($message);
        }
        $this->recentlyAddedWords = array_map(static fn($args) => new WordModel(...$args), $json);
    }

    #[\Override]
    public function loadAndDelegate(): ?Controller {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                require Controller::getViewPath("ExistingWordView");
                return null;
            case 'POST':
                $result = $this->handlePost();
                header('Content-Type: application/json');
                echo json_encode($result);
                return null;
            default:
                throw HttpException::methodNotSupported("GET, POST");
        }
    }

    private function handlePost(): object {
        $changes = $_POST["changes"] ?? null ?: null;
        if ($changes === null) {
            return (object) ["success" => false, "errorMessage" => "Aanpassingen mogen niet leeg zijn!"];
        }
        if (strlen($changes) > Constants::DISCORD_EMBED_MAX_FIELD_VALUE) {
            return (object) ["success" => false, "errorMessage" => "Aanpassingen mogen niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!"];
        }
        $description = $_POST["description"] ?? null ?: null;
        if ($description === null) {
            return (object) ["success" => false, "errorMessage" => "Beschrijving mag niet leeg zijn!"];
        }
        if (strlen($description) > Constants::DISCORD_EMBED_MAX_FIELD_VALUE) {
            return (object) ["success" => false, "errorMessage" => "Beschrijving mag niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!"];
        }
        $meaning = ($_POST["meaning"] ?? null) === "formeel" ? "formeel" : "standaard";
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
                "username" => "Wijzigingen",
                "embeds" => [
                    [
                        "title" => "Nieuwe wijziging",
                        "description" => "Er is een nieuwe wijziging aan [" . $this->wordModel->wordCapitalised . " ($meaning)](https://wiskundewoordenboek.nl/woord/" . urlencode($this->wordModel->wordDirectory) . "/?betekenis=$meaning) voorgesteld.",
                        "color" => 0x1e90ff,
                        "fields" => [
                            [
                                "name" => "Aanpassingen",
                                "value" => "$changes"
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
}
