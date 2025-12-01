<?php
require_once __DIR__ . '/prepend.php';

use PHPLedger\Controllers\ConfigController;

$controller = new ConfigController();
$controller->handle();
