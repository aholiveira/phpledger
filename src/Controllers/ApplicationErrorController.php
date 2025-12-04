<?php

namespace PHPLedger\Controllers;

use PHPLedger\Application;
use PHPLedger\Views\ApplicationErrorView;

class ApplicationErrorController
{
    public function handle(): void
    {
        $view = new ApplicationErrorView;
        $view->render(Application::getErrorMessage());
    }
}
