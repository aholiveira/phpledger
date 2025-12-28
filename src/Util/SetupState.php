<?php

namespace PHPLedger\Util;

enum SetupState: string
{
    case CONFIG_REQUIRED     = 'config_required';
    case STORAGE_MISSING     = 'storage_missing';
    case MIGRATIONS_PENDING  = 'migrations_pending';
    case ADMIN_MISSING       = 'admin_missing';
    case COMPLETE            = 'complete';
}
