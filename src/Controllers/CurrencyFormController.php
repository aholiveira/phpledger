<?php

/**
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Domain\Currency;
use PHPLedger\Exceptions\PHPLedgerException;
use PHPLedger\Views\Templates\CurrencyFormViewTemplate;
use Throwable;

/**
 * Controller for managing account type forms.
 *
 * Handles displaying the account type form, saving, and deleting account types.
 * Includes CSRF validation, error handling, and localized success messages.
 *
 */
final class CurrencyFormController extends AbstractFormController
{
    protected array $filterArray = [
        "id" => FILTER_VALIDATE_INT,
        "code" => FILTER_DEFAULT,
        "description" => FILTER_DEFAULT,
        "exchangeRate" => FILTER_VALIDATE_FLOAT,
        "action" => FILTER_DEFAULT,
        "update" => FILTER_DEFAULT
    ];

    protected function setupObject(): DataObjectInterface
    {
        return $this->app->dataFactory()::currency();
    }

    protected function renderView(DataObjectInterface $object, bool $success): void
    {
        if (!($object instanceof Currency)) {
            return;
        }
        $view = new CurrencyFormViewTemplate();
        $view->render(array_merge($this->uiData, [
            'notification' => $this->message ?? '',
            'pagetitle' => $this->l10n->l('currencies'),
            'success' => $success ?? false,
            'row' => [
                'id' => $object->id ?? '',
                'code' => $object->code ?? '',
                'description' => $object->description ?? '',
                'exchangeRate' => $object->exchangeRate ?? 0,
            ]
        ]));
    }

    /**
     * Save account type data.
     *
     * @param Currency $object
     * @param array $filtered
     * @return bool True if update was successful
     */
    protected function handleSave(DataObjectInterface $object, array $filtered): bool
    {
        if (!($object instanceof Currency)) {
            return false;
        }

        $object->id = (int)(empty($filtered['id'] ?? 0) ? null : $filtered['id']);
        $object->code = $filtered['code'] ?? '';
        $object->description = $filtered['description'] ?? '';
        $object->exchangeRate = $filtered['exchangeRate'] ?? 1;
        return $object->update();
    }
}
