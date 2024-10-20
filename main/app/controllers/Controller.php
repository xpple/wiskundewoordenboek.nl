<?php

namespace app\controllers;

abstract class Controller {
    /**
     * @param string[] $path
     * @return void
     */
    public abstract function handle(array $path): void;

    public static final function getRoot(): string {
        return dirname($_SERVER['DOCUMENT_ROOT']);
    }

    public static final function getViewPath(string $view): string {
        return self::getRoot() . "/app/views/$view.phtml";
    }

    public static final function getTemplatePath(string $template): string {
        return self::getRoot() . "/app/views/templates/$template.phtml";
    }
}
