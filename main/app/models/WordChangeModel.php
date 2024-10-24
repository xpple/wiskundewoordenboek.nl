<?php

namespace app\models;

readonly final class WordChangeModel {
    public function __construct(
        public string $changeId,
        public WordModel $wordModel,
        public string $changeDirectory,
        public string $meaningOption,
        public string $content,
        public string $description,
        public ?string $email,
    ) {
    }

    public static function wrapper(...$args): WordChangeModel {
        return new self($args[0], new WordModel(...array_slice($args, 1, 5)), ...array_slice($args, 6));
    }

    public static function fromJson(...$args): WordChangeModel {
        $values = array_values($args);
        return new self($values[0], new WordModel(...array_values($values[1])), ...array_slice($values, 2));
    }
}
