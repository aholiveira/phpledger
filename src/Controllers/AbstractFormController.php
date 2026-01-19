<?php

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Contracts\L10nServiceInterface;
use PHPLedger\Exceptions\PHPLedgerException;
use Throwable;

abstract class AbstractFormController extends AbstractViewController
{
    protected ?string $message = null;
    protected L10nServiceInterface $l10n;
    protected array $filterArray = [];

    /**
     * Handle account type form request (GET form or POST save/delete).
     */
    protected function handle(): void
    {
        $object = $this->setupObject();
        $filtered = filter_var_array($this->request->all(), $this->filterArray, true);
        $this->l10n = $this->app->l10n();
        $success = false;
        if ($this->request->isPost()) {
            try {
                $this->handlePost($object, $filtered);
                $this->message = $this->l10n->l('save_success', $object->id);
                $success = true;
            } catch (Throwable $e) {
                $this->message = $e->getMessage();
            }
        }

        if ($this->request->isGet()) {
            $id = $filtered['id'] ?? 0;
            if ($id > 0) {
                $object = $object->getById($id);
            }
        }

        $this->renderView($object, $success);
    }

    abstract protected function setupObject(): DataObjectInterface;
    abstract protected function renderView(DataObjectInterface $object, bool $success): void;

    /**
     * Handle POST request for save or delete actions.
     *
     * @param DataObjectInterface $object
     * @param array $filtered
     * @throws PHPLedgerException
     */
    protected function handlePost(DataObjectInterface $object, $filtered): void
    {
        if (!$this->app->csrf()->validateToken($_POST['_csrf_token'] ?? null)) {
            http_response_code(400);
            throw new PHPLedgerException($this->l10n->l("token_verification_failure"));
        }

        if (strtolower($filtered['update'] ?? '') === "save" && !$this->handleSave($object, $filtered)) {
            throw new PHPLedgerException($this->l10n->l("error_saving"));
        }

        if (strtolower($filtered['update'] ?? '') === "delete") {
            $object->id = $filtered['id'] ?? 0;
            if ($object->id > 0 && !$object->delete()) {
                throw new PHPLedgerException($this->l10n->l("error_deleting"));
            }
        }
    }

    /**
     * Save account type data.
     *
     * @param DataObjectInterface $object
     * @param array $filtered
     * @return bool True if update was successful
     */
    abstract protected function handleSave(DataObjectInterface $object, array $filtered): bool;
}
