<?php

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\Domain\UserObjectInterface;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Views\Templates\UserProfileViewTemplate;

final class UserProfileController extends AbstractViewController
{
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
        $this->uiData['label'] = array_merge(
            $this->uiData['label'],
            $this->buildL10nLabels(
                $this->app->l10n(),
                [
                    'id',
                    'username',
                    'first_name',
                    'last_name',
                    'full_name',
                    'display_name',
                    'password',
                    'verify_password',
                    'save',
                    'email',
                ]
            )
        );
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
