<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Services;

use PHPLedger\Contracts\HeaderSenderInterface;

class HeaderSender implements HeaderSenderInterface
{
    private $headersSentCallable = null;
    private $headerCallable = null;

    public function __construct(
        ?callable $headersSentCallable = null,
        ?callable $headerCallable = null
    ) {
        $this->headersSentCallable = $headersSentCallable ?? 'headers_sent';
        $this->headerCallable = $headerCallable ?? 'header';
    }

    public function send(string $header, bool $replace = true, int $code = 0): void
    {
        if (!($this->headersSentCallable)()) {
            ($this->headerCallable)($header, $replace, $code);
        }
    }

    public function sent(): bool
    {
        return ($this->headersSentCallable)();
    }
}
