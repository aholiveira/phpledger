<?php

/**
 * Enumeration of log levels.
 *
 * Defines standard logging levels for use with LoggerServiceInterface.
 *
 * @author Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

enum LogLevel: int
{
    case ERROR   = 0;
    case WARNING = 1;
    case NOTICE  = 2;
    case INFO    = 3;
    case DEBUG   = 4;
}
