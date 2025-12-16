<?php

namespace PHPLedger\Contracts;

interface ConfigFilesystemInterface
{
    public function exists(string $path): bool;
    public function read(string $path): string|false;
    public function write(string $path, string $data): bool;
    public function delete(string $path): void;
    public function replace(string $temp, string $target): bool;
    public function tempFile(string $dir): string;
    public function isWritable(string $path): bool;
    public function isDir(string $path): bool;
    public function mkdir(string $path): void;
}
