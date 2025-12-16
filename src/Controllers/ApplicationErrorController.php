<?php

namespace PHPLedger\Controllers;

use PHPLedger\Views\ApplicationErrorView;

final class ApplicationErrorController extends AbstractViewController
{
    protected function handle(): void
    {
        $view = new ApplicationErrorView;
        $view->render($this->app, $this->app->getErrorMessage());
    }
}
