<?php

namespace PHPLedger\Controllers;

use PHPLedger\Contracts\ApplicationObjectInterface;
use PHPLedger\Contracts\RequestInterface;
use PHPLedger\Contracts\ViewControllerInterface;

abstract class AbstractViewController implements ViewControllerInterface
{
    protected RequestInterface $request;
    protected ApplicationObjectInterface $app;
    public function handleRequest(ApplicationObjectInterface $app, RequestInterface $request): void
    {
        $this->request = $request;
        $this->app = $app;
        $this->handle();
    }
    abstract protected function handle(): void;
}
