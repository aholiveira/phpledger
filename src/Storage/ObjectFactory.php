<?php

namespace PHPLedger\Storage;
use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Storage\Abstract\AbstractObjectFactory;

class ObjectFactory extends AbstractObjectFactory implements DataObjectFactoryInterface
{
    private DataObjectFactoryInterface $backedFactory;
    private function __construct($backend = "mysql")
    {

    }

}
