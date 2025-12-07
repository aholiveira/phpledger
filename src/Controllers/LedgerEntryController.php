<?php

namespace PHPLedger\Controllers;

use DomainException;
use Exception;
use InvalidArgumentException;
use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\DateParser;

class LedgerEntryController
{
    /**
     * Validate, build and persist one ledger‐entry from $input.
     * Throws on any validation error.
     */
    private ApplicationObjectInterface $app;
    public function handleSave(ApplicationObjectInterface $app, array $input): int
    {
        $this->app = $app;
        // 1) parse date
        try {
            $dt = DateParser::parseNamed('data_mov', $input);
        } catch (Exception $e) {
            throw new DomainException($this->app->l10n()->l("invalid_date", $e->getMessage()));
        }
        if (!$dt) {
            throw new DomainException($this->app->l10n()->l("date_required"));
        }

        // 2) grab and validate the other fields
        foreach (['currencyAmount', 'direction', 'categoryId', 'currencyId', 'accountId'] as $fld) {
            if (!isset($input[$fld]) || $input[$fld] === '' || $input[$fld] === false) {
                throw new InvalidArgumentException($this->app->l10n()->l("invalid_parameter", $fld));
            }
        }

        // 3) hydrate and save
        $entry = ObjectFactory::ledgerentry();
        $entry->entryDate = $dt->format('Y-m-d');
        $entry->id = (int) $input['id'] ?? $entry::getNextId();
        $entry->currencyAmount = (float) $input['currencyAmount'];
        $entry->direction = (int) $input['direction'];
        $entry->euroAmount = $entry->direction * $entry->currencyAmount;
        $entry->categoryId = (int) $input['categoryId'];
        $entry->currencyId = $input['currencyId'];
        $entry->accountId = (int) $input['accountId'];
        $entry->remarks = $input['remarks'];
        $entry->remarks = $input['remarks'];
        $entry->username = $_SESSION['user'] ?? 'empty';

        if (!$entry->update()) {
            throw new DomainException($this->app->l10n()->l("ledger_save_error"));
        }

        // 4) update that user's "defaults" record - be defensive when session user is missing
        $username = $_SESSION['user'] ?? null;
        $defaults = null;
        if (!empty($username)) {
            $defaults = ObjectFactory::defaults()::getByUsername($username);
        }
        $defaults = $defaults ?? ObjectFactory::defaults()::init();

        $defaults->categoryId = $entry->categoryId;
        $defaults->currencyId = $entry->currencyId;
        $defaults->accountId = $entry->accountId;
        $defaults->entryDate = $entry->entryDate;
        $defaults->direction = $entry->direction;
        $defaults->language = $this->app->l10n()->lang();
        $defaults->username = $_SESSION['user'] ?? 'empty';

        if (!$defaults->update()) {
            throw new DomainException($this->app->l10n()->l("defaults_save_error"));
        }
        return $entry->id;
    }
}
