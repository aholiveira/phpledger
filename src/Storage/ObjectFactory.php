<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Storage;

use PHPLedger\Storage\Abstract\AbstractObjectFactory;

class ObjectFactory extends AbstractObjectFactory
{
    public function __construct(string $backend)
    {
        parent::init($backend);
    }
}
