<?php

/**
 *
 * @author Antonio Henrique Oliveira
 * @copyright (c) 2017-2022, Antonio Henrique Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License (GPL) v3
 *
 */

namespace PHPLedger\Controllers;

use PHPLedger\Domain\User;
use PHPLedger\Views\Templates\ForgotPasswordViewTemplate;

final class ForgotPasswordController extends AbstractViewController
{
    protected function handle(): void
    {
        $filterArray = [
            "username" => FILTER_SANITIZE_ENCODED,
            "email" => FILTER_SANITIZE_ENCODED
        ];
        if ($this->request->method() == "POST") {
            $filtered = filter_var_array($this->request->all(), $filterArray, true);
            if (empty($filtered["username"]) || empty($filtered["email"])) {
                $message = $this->app->l10n()->l('missing_username_or_email');
            }
            $user = $this->app->dataFactory()::user()::getByUsername($filtered["username"]);
            if (!($user instanceof User)) {
                $message = $this->app->l10n()->l('invalid_credentials_for_reset');
            }
            if ($user !== null && strtolower($user->getProperty('email')) === $filtered["email"]) {
                $message = $this->app->l10n()->l($user->resetPassword() ? 'link_sent' : 'reset_failed');
            }
        }
        $view = new ForgotPasswordViewTemplate;
        $this->uiData['label'] = array_merge(
            $this->uiData['label'],
            $this->buildL10nLabels($this->app->l10n(), [
                'username',
                'email',
                'password_recovery',
                'send_reset_link'
            ])
        );
        $view->render(array_merge(
            $this->uiData,
            [
                'message' => $message ?? "",
                'apptitle' => $this->app->config()->get("title"),
            ]
        ));
    }
}
