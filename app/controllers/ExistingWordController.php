<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseException;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

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
     * @throws DatabaseException
     */
    public function __construct(array $path, WordModel $wordModel) {
        parent::__construct($path);
        $this->wordModel = $wordModel;
        $this->recentlyAddedWords = DatabaseHelper::getInstance()->getRecentlyAddedWords(3);
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
        $description = $_POST["description"] ?? null ?: null;
        if ($description === null) {
            return (object) ["success" => false, "errorMessage" => "Beschrijving mag niet leeg zijn!"];
        }
        $meaning = ($_POST["meaning"] ?? null) === "formeel" ? "formeel" : "standaard";
        $email = ($_POST["email"] ?? null) ? "`{$_POST["email"]}`" : "Geen e-mailadres opgegeven";

        $curl = curl_init();
        require Controller::getRoot() . "/app/webhook.php";
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
        curl_close($curl);
        if ($response === true) {
            return (object) ["success" => true];
        }
        return (object) ["success" => false, "errorMessage" => "Er ging achter de schermen iets mis!"];
    }
}
