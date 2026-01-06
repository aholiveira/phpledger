<?php

/**
 * Controller for handling forgot password requests.
 *
 * Validates username and email, attempts to reset the user's password,
 * and renders the forgot password view with appropriate messages.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Views\Templates\ForgotPasswordViewTemplate;

final class ForgotPasswordController extends AbstractViewController
{
    /**
     * Handle forgot password form submission and display.
     */
    protected function handle(): void
    {
        $filterArray = [
            "username" => FILTER_SANITIZE_ENCODED,
            "email" => FILTER_SANITIZE_ENCODED
        ];

        if ($this->request->method() === "POST") {
            $filtered = filter_var_array($this->request->all(), $filterArray, true);

            if (empty($filtered["username"]) || empty($filtered["email"])) {
                $message = $this->app->l10n()->l('missing_username_or_email');
            }

            $user = $this->app->dataFactory()::user()::getByUsername($filtered["username"]);

            if (!($user instanceof User)) {
                $message = $this->app->l10n()->l('invalid_credentials_for_reset');
            }

            if ($user !== null && strtolower($user->getProperty('email')) === strtolower($filtered["email"])) {
                $message = $this->app->l10n()->l($user->resetPassword() ? 'link_sent' : 'reset_failed');
            }
        }

        $view = new ForgotPasswordViewTemplate();
        $view->render(array_merge(
            $this->uiData,
            [
                'message' => $message ?? "",
                'apptitle' => $this->app->config()->get("title"),
            ]
        ));
    }
}
