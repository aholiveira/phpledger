<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\RedirectorServiceInterface;

class Redirector implements RedirectorServiceInterface
{
    private const array ALLOWED_REDIRECTS = ['index.php'];
    private $headerCallback;
    private $headersSentCallback;

    public function __construct(
        ?callable $headerCallback = null,
        ?callable $headersSentCallback = null
    ) {
        $this->headerCallback = $headerCallback ?: fn($string, $replace = true, $code = 303) => header($string, $replace, $code);
        $this->headersSentCallback = $headersSentCallback ?: fn() => headers_sent();
    }

    public function to($url, $delay = 0): void
    {
        $url_path = strtok($url, '?'); // strip query params
        if (!\in_array(basename($url_path), self::ALLOWED_REDIRECTS, true)) {
            $url = "index.php";
        }
        if (!($this->headersSentCallback)()) {
            ($this->headerCallback)($delay > 0 ? "Refresh: $delay; URL=$url" : "Location: $url", true, 303);
        }
    }
}
