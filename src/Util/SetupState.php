<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Util;

enum SetupState: string
{
    case CONFIG_REQUIRED     = 'config_required';
    case STORAGE_MISSING     = 'storage_missing';
    case MIGRATIONS_PENDING  = 'migrations_pending';
    case ADMIN_MISSING       = 'admin_missing';
    case COMPLETE            = 'complete';
}
