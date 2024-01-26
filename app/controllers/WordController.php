<?php

namespace App\Controllers;

use App\Models\WordModel;
use App\Util\DatabaseException;
use App\Util\DatabaseHelper;
use App\Util\HttpException;

class WordController extends SuccessController {

    private readonly WordModel $wordModel;

    /**
     * @var WordModel[] $recentlyAddedWords
     */
    private readonly array $recentlyAddedWords;

    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        switch (count($path)) {
            case 0:
                // perhaps implement later
                throw HttpException::notFound();
            case 1:
                $word = array_shift($path);
                try {
                    $databaseHelper = new DatabaseHelper();

                    $wordModel = $databaseHelper->getWord($word);

                    if ($wordModel === null) {
                        $word = $databaseHelper->getPrimaryDirectoryForAlias($word);

                        if ($word !== null) {
                            header("Location: /woord/$word/", true, 301);
                            exit;
                        }

                        throw HttpException::notFound();
                    }

                    $this->wordModel = $wordModel;

                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'GET':
                            $this->recentlyAddedWords = $databaseHelper->getRecentlyAddedWords(3);
                            require Controller::getViewPath("WordView");
                            break;
                        case 'POST':
                            $result = self::handlePost($this->wordModel);
                            header('Content-Type: application/json');
                            echo json_encode($result);
                            break;
                        default:
                            throw HttpException::methodNotSupported("GET, POST");
                    }
                    break;
                } catch (DatabaseException $e) {
                    throw new HttpException($e->getMessage(), 500);
                }
            default:
                throw HttpException::notFound();
        }
    }

    private static function handlePost(WordModel $wordModel): object {
        $changes = $_POST["changes"] ?: null;
        if ($changes === null) {
            return (object) ["success" => false, "errorMessage" => "Aanpassingen mogen niet leeg zijn!"];
        }
        $description = $_POST["description"] ?: null;
        if ($description === null) {
            return (object) ["success" => false, "errorMessage" => "Beschrijving mag niet leeg zijn!"];
        }
        $meaning = ($_POST["meaning"] ?? null) === "formeel" ? "formeel" : "standaard";
        $email = $_POST["email"] ? "`{$_POST["email"]}`" : "Geen e-mailadres opgegeven";

        $curl = curl_init();
        require Controller::getRoot() . "/app/webhook.php";
        curl_setopt_array($curl, [
            CURLOPT_URL => $webhook,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                "embeds" => [
                    [
                        "title" => "Nieuwe wijziging",
                        "description" => "Er is een nieuwe wijziging aan [$wordModel->wordCapitalised ($meaning)](https://wiskundewoordenboek.nl/woord/" . urlencode($wordModel->wordDirectory) . "/?betekenis=$meaning) voorgesteld.",
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
