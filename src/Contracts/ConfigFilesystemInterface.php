<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Contracts;

/**
 * Interface for configuration file system operations.
 *
 * Defines methods to manage configuration files and directories,
 * including reading, writing, deleting, replacing, and creating
 * temporary files.
 */
interface ConfigFilesystemInterface
{
    /**
     * Check if a file or directory exists at the given path.
     *
     * @param string $path
     * @return bool True if exists, false otherwise
     */
    public function exists(string $path): bool;

    /**
     * Read the contents of a file.
     *
     * @param string $path
     * @return string|false File contents, or false on failure
     */
    public function read(string $path): string|false;

    /**
     * Write data to a file.
     *
     * @param string $path
     * @param string $data
     * @return bool True on success, false on failure
     */
    public function write(string $path, string $data): bool;

    /**
     * Delete a file or directory.
     *
     * @param string $path
     */
    public function delete(string $path): void;

    /**
     * Replace a target file with a temporary file.
     *
     * @param string $temp
     * @param string $target
     * @return bool True on success, false on failure
     */
    public function replace(string $temp, string $target): bool;

    /**
     * Create a temporary file in the given directory.
     *
     * @param string $dir
     * @return string Path to the temporary file
     */
    public function tempFile(string $dir): string;

    /**
     * Check if a path is writable.
     *
     * @param string $path
     * @return bool True if writable, false otherwise
     */
    public function isWritable(string $path): bool;

    /**
     * Check if a path is a directory.
     *
     * @param string $path
     * @return bool True if a directory, false otherwise
     */
    public function isDir(string $path): bool;

    /**
     * Create a directory at the specified path.
     *
     * @param string $path
     */
    public function mkdir(string $path): void;
}
