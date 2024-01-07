<?php

namespace App\Models;

readonly class WordModel {
    public function __construct(public string $wordId, public string $wordDirectory, public string $wordCapitalised, public string $meaning) {
    }
}
