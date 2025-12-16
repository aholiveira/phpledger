<?php

namespace PHPLedger\Contracts;

use PHPLedger\Storage\ReportFactory;

interface ApplicationObjectInterface
{
    public function config(): ConfigurationServiceInterface;
    public function dataFactory(): DataObjectFactoryInterface;
    public function reportFactory(): ReportFactory;
    public function l10n(): L10nServiceInterface;
    public function logger(): LoggerServiceInterface;
    public function session(): SessionServiceInterface;
    public function setErrorMessage(string $message): void;
    public function clearErrorMessage(): void;
    public function getErrorMessage(): string;
}
