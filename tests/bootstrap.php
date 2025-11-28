<?php

// Load .env.test if present
$envTest = __DIR__ . '/../.env.test';
if (file_exists($envTest)) {
    foreach (file($envTest, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_contains($line, '=')) {
            putenv(trim($line));
        }
    }
}

