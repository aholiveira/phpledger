<?php

namespace PHPLedger\Contracts;

enum LogLevel: int
{
    case ERROR = 0;
    case WARNING = 1;
    case NOTICE = 2;
    case INFO = 3;
    case DEBUG = 4;
}
