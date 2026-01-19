<?php

/**
 * Controller for managing account type forms.
 *
 * Handles displaying the account type form, saving, and deleting account types.
 * Includes CSRF validation, error handling, and localized success messages.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Domain\AccountType;
use PHPLedger\Views\Templates\AccountTypeFormViewTemplate;

final class AccountTypeFormController extends AbstractFormController
{
    protected array $filterArray = [
        "id" => FILTER_VALIDATE_INT,
        "description" => FILTER_DEFAULT,
        "savings" => FILTER_DEFAULT,
        "action" => FILTER_DEFAULT,
        "update" => FILTER_DEFAULT
    ];

    protected function setupObject(): DataObjectInterface
    {
        return $this->app->dataFactory()::accounttype();
    }

    protected function renderView(DataObjectInterface $object, bool $success): void
    {
        $view = new AccountTypeFormViewTemplate();
        $view->render(array_merge($this->uiData, [
            'notification' => $this->message ?? '',
            'success' => $success ?? false,
            'row' => [
                'id' => $object->id ?? '',
                'description' => $object->description ?? '',
                'savings' => $object->savings ?? false,
            ]
        ]));
    }

    /**
     * Save account type data.
     *
     * @param AccountType $object
     * @param array $filtered
     * @return bool True if update was successful
     */
    protected function handleSave(DataObjectInterface $object, array $filtered): bool
    {
        if (!($object instanceof AccountType)) {
            return false;
        }
        $object->id = (int)($filtered['id'] === false ? null : $filtered['id']);
        $object->description = $filtered['description'] ?? '';
        $object->savings = empty($filtered['savings']) ? 0 : 1;
        return $object->update();
    }
}
