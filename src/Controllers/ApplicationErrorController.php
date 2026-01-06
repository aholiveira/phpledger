<?php

/**
 * Controller for displaying application errors.
 *
 * Renders an error page with the current error message, localized text, and application title.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Views\Templates\ApplicationErrorViewTemplate;

final class ApplicationErrorController extends AbstractViewController
{
    /**
     * Handle application error display.
     */
    protected function handle(): void
    {
        $view = new ApplicationErrorViewTemplate();
        $view->render([
            'pagetitle' => $this->app->l10n()->l("Application error"),
            'appTitle'  => $this->app->config()->get('title', 'Prosperidade financeira'),
            'lang'      => $this->app->l10n()->html(),
            'message'   => $this->app->getErrorMessage(),
        ]);
    }
}
