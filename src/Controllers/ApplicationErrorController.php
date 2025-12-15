<?php

namespace PHPLedger\Controllers;

use PHPLedger\Application;
use PHPLedger\Views\ApplicationErrorView;

final class ApplicationErrorController extends AbstractViewController
{
    protected function handle(): void
    {
        $view = new ApplicationErrorView;
        $view->render($this->app, Application::getErrorMessage());
    }
}
