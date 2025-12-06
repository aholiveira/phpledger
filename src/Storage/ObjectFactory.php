<?php

namespace PHPLedger\Storage;

use PHPLedger\Contracts\DataObjectFactoryInterface;
use PHPLedger\Storage\Abstract\AbstractObjectFactory;

class ObjectFactory extends AbstractObjectFactory implements DataObjectFactoryInterface
{
    public function __construct(string $backend)
    {
        parent::init($backend);
    }
}
