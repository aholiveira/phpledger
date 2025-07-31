<?php

class Logger
{
    private string $logFile;

    public function __construct(string $file)
    {
        $this->logFile = $file;
    }

    public function info(string $message, string $prefix = ""): void
    {
        $this->writeLog('INFO', $message, $prefix);
    }

    public function warning(string $message, string $prefix = ""): void
    {
        $this->writeLog('WARNING', $message, $prefix);
    }

    public function error(string $message, string $prefix = ""): void
    {
        $this->writeLog('ERROR', $message, $prefix);
    }

    public function dump(mixed $data, string $prefix = ""): void
    {
        $output = print_r($data, true);
        $this->writeLog('DUMP', $output, $prefix);
    }

    private function writeLog(string $level, string $message, string $prefix = ""): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] [$level] [$prefix] $message\n";
        file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
