<?php

namespace App\Views;

readonly class ViewSettings {
    public function __construct(
        public string $title,
        /* @var string[] $stylesheets */
        public array $stylesheets = [],
        public bool $mathsEnabled = false,
    ) {
    }
}
