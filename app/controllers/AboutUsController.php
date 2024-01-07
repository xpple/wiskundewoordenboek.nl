<?php

namespace App\Controllers;

class AboutUsController extends SuccessController {
    #[\Override]
    public function load(): void {
        $path = $this->getPath();
        if (count($path) === 0) {
            echo "a";
            return;
        }
    }
}
