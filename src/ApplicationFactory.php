<?php

namespace PHPLedger;

use PHPLedger\Services\Config;
use PHPLedger\Services\CSRF;
use PHPLedger\Services\FileResponseSender;
use PHPLedger\Services\HeaderSender;
use PHPLedger\Services\L10n;
use PHPLedger\Services\Logger;
use PHPLedger\Services\Redirector;
use PHPLedger\Services\SessionManager;
use PHPLedger\Services\TimezoneService;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Storage\ReportFactory;
use PHPLedger\Util\ConfigPath;
use PHPLedger\Util\Path;

class ApplicationFactory
{
    public static function create(): Application
    {
        $logfile = Path::combine(ROOT_DIR, "logs", "ledger.log");
        $config = new Config();
        Config::setInstance($config);
        Config::init(ConfigPath::get());
        $logger = new Logger($logfile);
        Logger::setInstance($logger);
        $backend = $config->get('storage.type', 'mysql');
        $headerSender = new HeaderSender();
        return new Application(
            $config,
            new ObjectFactory($backend),
            new ReportFactory($backend),
            new SessionManager(),
            $logger,
            new Redirector(),
            new L10n(),
            $headerSender,
            new TimezoneService,
            new CSRF,
            new FileResponseSender($headerSender)
        );
    }
}
