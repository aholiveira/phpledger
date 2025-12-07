<?php

namespace PHPLedger\Contracts;

interface ApplicationObjectInterface
{
    public function config(): ConfigurationServiceInterface;
    public function dataFactory(): DataObjectFactoryInterface;
    public function l10n(): L10nServiceInterface;
    public function logger(): LoggerServiceInterface;
    public function session(): SessionServiceInterface;
}
