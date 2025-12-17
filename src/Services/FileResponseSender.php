<?php

namespace PHPLedger\Services;

use PHPLedger\Contracts\HeaderSenderInterface;

final class FileResponseSender
{
    private HeaderSenderInterface $hs;
    public function __construct(HeaderSenderInterface $hs)
    {
        $this->hs = $hs;
    }
    public function csv(string $data, string $filename): void
    {
        $this->hs->send('Content-Type: text/csv; charset=UTF-8');
        $this->hs->send(sprintf('Content-Disposition: attachment; filename="%s"', $filename));
        $this->hs->send('Content-Length: ' . strlen($data));
        echo $data;
    }
}
