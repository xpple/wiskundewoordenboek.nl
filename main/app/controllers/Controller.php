<?php

namespace app\controllers;

abstract class Controller {
    /**
     * Perform controlling actions and if needed delegate further tasks to a more specific controller.
     *
     * @return Controller|null A more specific controller if the task is delegated and null if not.
     */
    public abstract function loadAndDelegate(): ?Controller;

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
