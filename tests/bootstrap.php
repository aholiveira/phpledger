<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

// Load .env.test if present
$envTest = __DIR__ . '/../.env.test';
if (file_exists($envTest)) {
    foreach (file($envTest, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_contains($line, '=')) {
            putenv(trim($line));
        }
    }
}
