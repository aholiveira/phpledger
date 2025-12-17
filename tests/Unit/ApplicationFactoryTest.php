<?php

use PHPLedger\ApplicationFactory;
use PHPLedger\Application;

it('creates a valid Application instance', function () {
    $app = ApplicationFactory::create();

    expect($app)->toBeInstanceOf(Application::class);
    expect($app->config())->toBeInstanceOf(\PHPLedger\Contracts\ConfigurationServiceInterface::class);
    expect($app->dataFactory())->toBeInstanceOf(\PHPLedger\Contracts\DataObjectFactoryInterface::class);
    expect($app->reportFactory())->toBeInstanceOf(\PHPLedger\Storage\ReportFactory::class);
    expect($app->session())->toBeInstanceOf(\PHPLedger\Contracts\SessionServiceInterface::class);
    expect($app->logger())->toBeInstanceOf(\PHPLedger\Contracts\LoggerServiceInterface::class);
    expect($app->redirector())->toBeInstanceOf(\PHPLedger\Contracts\RedirectorServiceInterface::class);
    expect($app->l10n())->toBeInstanceOf(\PHPLedger\Contracts\L10nServiceInterface::class);
    expect($app->headerSender())->toBeInstanceOf(\PHPLedger\Contracts\HeaderSenderInterface::class);
    expect($app->csrf())->toBeInstanceOf(\PHPLedger\Contracts\CsrfServiceInterface::class);
    expect($app->fileResponseSender())->toBeInstanceOf(\PHPLedger\Services\FileResponseSender::class);
});
