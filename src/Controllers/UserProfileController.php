<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\Domain\UserObjectInterface;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Views\Templates\UserProfileViewTemplate;

/**
 * Controller responsible for displaying and updating the current user's profile.
 */
final class UserProfileController extends AbstractViewController
{
    /**
     * Handle the user profile request.
     *
     * Loads the current user, processes profile updates on POST requests,
     * and renders the user profile view template.
     *
     * @return void
     */
    protected function handle(): void
    {
        $user = $this->app->dataFactory()::user()->getByUsername($this->app->session()->get('user'));
        if ($this->request->method() === 'POST' && $this->request->input('itemaction', '') === 'save') {
            try {
                $this->handlePost($user);
            } catch (\Throwable $e) {
                $message = $e->getMessage();
            }
        }
        $template = new UserProfileViewTemplate();
        $template->render(array_merge($this->uiData, [
            'pagetitle' => $this->app->l10n()->l('my_profile'),
            'message' => $message ?? '',
            'text' => [
                'id'          => $user->getProperty('id') ?? '',
                'username'    => $user->getProperty('userName') ?? '',
                'firstName'   => $user->getProperty('firstName') ?? '',
                'lastName'    => $user->getProperty('lastName') ?? '',
                'fullName'    => $user->getProperty('fullName') ?? '',
                'email'       => $user->getProperty('email') ?? ''
            ],
        ]));
    }

    /**
     * Handle POST data for updating the user profile.
     *
     * Validates input, updates user properties, handles password changes,
     * and persists the user object.
     *
     * @param UserObjectInterface|null $user
     * @return void
     *
     * @throws PHPLedgerException
     */
    private function handlePost(?UserObjectInterface $user): void
    {
        if ($user === null) {
            throw new PHPLedgerException("Invalid user");
        }
        $data = $this->request->all();
        $user->setProperty('firstName', $data['firstname'] ?? '');
        $user->setProperty('lastName', $data['lastname'] ?? '');
        $user->setProperty('fullName', $data['fullName'] ?? '');
        $user->setProperty('email', $data['email'] ?? '');
        $password = $data['password'] ?? '';
        $verify = $data['verifyPassword'] ?? '';
        if ($password !== '' || $verify !== '') {
            if ($password !== $verify) {
                throw new PHPLedgerException($this->app->l10n()->l('password_mismatch'));
            }
            $user->setPassword($password);
        }
        if (!$user->update()) {
            throw new PHPLedgerException("Unknown error while updating data");
        }
    }
}
