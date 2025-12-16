<?php

namespace PHPLedger\Storage;

use PHPLedger\Storage\Abstract\AbstractObjectFactory;

class ObjectFactory extends AbstractObjectFactory
{
    public function __construct(string $backend)
    {
        parent::init($backend);
    }
}
