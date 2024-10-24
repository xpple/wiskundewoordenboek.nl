<?php

namespace app\models;

readonly final class SuggestedWordModel {
    public function __construct(
        public string $suggestionId,
        public string $suggestionDirectory,
        public string $wordCapitalised,
        public string $meaningOption,
        public string $content,
        public string $description,
        public ?string $email,
    ) {
    }
}
