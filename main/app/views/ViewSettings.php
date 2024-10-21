<?php

namespace app\views;

readonly class ViewSettings {
    public function __construct(
        public string $title,
        /* @var string[] */
        public array $stylesheets = [],
        /* @var string[] */
        public array $scripts = [],
        public bool $mathsEnabled = false,
        public bool $markdownEnabled = false,
    ) {
    }
}
