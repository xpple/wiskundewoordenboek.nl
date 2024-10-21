<?php

namespace app\controllers;

use app\util\Constants;
use app\util\HttpException;

class ContactController extends SuccessController {
    #[\Override]
    public function handle(array $path): void {
        if (count($path) !== 0) {
            throw HttpException::notFound();
        }
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                require Controller::getViewPath("ContactView");
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
        $message = $_POST["message"] ?? null ?: null;
        if ($message == null) {
            return (object) ["success" => false, "errorMessage" => "Bericht mag niet leeg zijn!"];
        }
        if (strlen($message) > Constants::DISCORD_EMBED_MAX_FIELD_VALUE) {
            return (object) ["success" => false, "errorMessage" => "Bericht mag niet langer dan " . Constants::DISCORD_EMBED_MAX_FIELD_VALUE . " karakters zijn!"];
        }
        $email = ($_POST["email"] ?? null) ? "`{$_POST["email"]}`" : null;
        if ($email == null) {
            return (object) ["success" => false, "errorMessage" => "E-mailadres mag niet leeg zijn!"];
        }

        $curl = curl_init();
        require Controller::getRoot() . "/app/private-webhook.php";
        /* @var string $webhook */
        curl_setopt_array($curl, [
            CURLOPT_URL => $webhook,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                "username" => "Contactformulier inzending",
                "embeds" => [
                    [
                        "title" => "Nieuw bericht",
                        "description" => "$name heeft een bericht geplaatst.",
                        "color" => 0x1e90ff,
                        "fields" => [
                            [
                                "name" => "Bericht",
                                "value" => "$message"
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
