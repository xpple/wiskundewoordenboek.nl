<?php

namespace app\controllers;

use app\util\HttpException;

abstract class SuccessController extends Controller {

    private readonly array $path;

    public function __construct(array $path = []) {
        $this->path = $path;
    }

    public function getPath(): array {
        return $this->path;
    }

    /**
     * @throws HttpException
     */
    public abstract function loadAndDelegate(): ?Controller;
}
