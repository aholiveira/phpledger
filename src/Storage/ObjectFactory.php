<?php

namespace PHPLedger\Storage;
use \PHPLedger\Contracts\DataObjectFactoryInterface;
use \PHPLedger\Storage\Abstract\AbstractObjectFactory;
use \PHPLedger\Util\Logger;

class ObjectFactory extends AbstractObjectFactory implements DataObjectFactoryInterface
{
    private DataObjectFactoryInterface $backedFactory;
    private function __construct($backend = "mysql")
    {
        parent::init($backend, new Logger("/logs/ledger.log"));
    }
}
