<?php
class LedgerEntryController
{
    private object_factory $factory;

    public function __construct(object_factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Validate, build and persist one ledger‐entry from $input.
     * Throws on any validation error.
     */
    public function handleSave(array $input): int
    {
        // 1) parse date
        try {
            $dt = DateParser::parseNamed('data_mov', $input);
        } catch (\Exception $e) {
            throw new DomainException("Data inválida: " . $e->getMessage());
        }
        if (!$dt) {
            throw new DomainException("Data obrigatória");
        }

        // 2) grab and validate the other fields
        foreach (['currency_amount', 'direction', 'category_id', 'currency_id', 'account_id'] as $fld) {
            if (!isset($input[$fld]) || $input[$fld] === '' || $input[$fld] === false) {
                throw new DomainException("Parâmetro {$fld} inválido");
            }
        }

        // 3) hydrate and save
        $entry = $this->factory->ledgerentry();
        $entry->entry_date = $dt->format('Y-m-d');
        $entry->id = (int) $input['id'] ?? $entry::getNextId();
        $entry->currency_amount = (float) $input['currency_amount'];
        $entry->direction = (int) $input['direction'];
        $entry->euro_amount = $entry->direction * $entry->currency_amount;
        $entry->category_id = (int) $input['category_id'];
        $entry->currency_id = $input['currency_id'];
        $entry->account_id = (int) $input['account_id'];
        $entry->remarks = $input['remarks'];
        $entry->username = $_SESSION['user'] ?? 'empty';

        if (!$entry->update()) {
            throw new \RuntimeException("Erro ao gravar movimento");
        }

        // 4) update that user’s “defaults” record
        $defaults = $this->factory->defaults()->getByUsername($_SESSION['user'])
            ?? \Defaults::init();

        $defaults->category_id = $entry->category_id;
        $defaults->currency_id = $entry->currency_id;
        $defaults->account_id = $entry->account_id;
        $defaults->entry_date = $entry->entry_date;
        $defaults->direction = $entry->direction;
        $defaults->username = $_SESSION['user'] ?? 'empty';

        if (!$defaults->update()) {
            throw new \RuntimeException("Erro ao gravar defaults");
        }
        return $entry->id;
    }
}
