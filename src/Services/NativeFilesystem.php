<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\ConfigFilesystemInterface;

final class NativeFilesystem implements ConfigFilesystemInterface
{
    public function exists(string $p): bool
    {
        return file_exists($p);
    }
    public function read(string $p): string|false
    {
        return file_get_contents($p);
    }
    public function write(string $p, string $d): bool
    {
        return file_put_contents($p, $d, LOCK_EX) !== false;
    }
    public function delete(string $p): void
    {
        @unlink($p);
    }
    public function replace(string $t, string $f): bool
    {
        return rename($t, $f);
    }
    public function tempFile(string $d): string
    {
        return tempnam($d, 'cfg_');
    }
    public function isWritable(string $p): bool
    {
        return is_writable($p);
    }
    public function isDir(string $p): bool
    {
        return is_dir($p);
    }
    public function mkdir(string $p): void
    {
        mkdir($p, 0777, true);
    }
}
