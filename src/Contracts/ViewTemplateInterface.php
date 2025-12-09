<?php

namespace PHPLedger\Contracts;

interface ViewTemplateInterface
{
    public function render(array $data): void;
}
