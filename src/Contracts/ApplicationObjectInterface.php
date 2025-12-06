<?php

namespace PHPLedger\Contracts;

interface ApplicationObjectInterface
{
    public function dataFactory(): DataObjectFactoryInterface;
    public function config(): ConfigurationServiceInterface;
    public function session(): SessionServiceInterface;
}
