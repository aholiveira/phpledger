<?php

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\ApplicationErrorViewTemplate;

final class ApplicationErrorController extends AbstractViewController
{
    protected function handle(): void
    {
        $view = new ApplicationErrorViewTemplate();
        $view->render([
            'pagetitle' => $this->app->l10n()->l("Application error"),
            'lang'      => $this->app->l10n()->html(),
            'message'   => $this->app->getErrorMessage(),
        ]);
    }
}
