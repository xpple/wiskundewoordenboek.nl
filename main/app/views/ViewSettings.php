<?php

namespace app\views;

readonly class ViewSettings {
    public function __construct(
        public string $title,
        /* @var string[] $stylesheets */
        public array $stylesheets = [],
        /* @var string[] $scripts */
        public array $scripts = [],
        public bool $mathsEnabled = false,
        public bool $markdownEnabled = false,
    ) {
    }
}
