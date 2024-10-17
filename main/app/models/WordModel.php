<?php

namespace app\models;

readonly final class WordModel {
    public function __construct(
        public string $wordId,
        public string $wordDirectory,
        public string $wordCapitalised,
        public string $meaning,
        public string $formalMeaning,
    ) {
    }
}
