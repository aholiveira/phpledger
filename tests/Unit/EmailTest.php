<?php

namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Util\Email;
use PHPLedger\Util\Config;

beforeEach(function () {
    $ref = new \ReflectionClass(Config::class);
    $prop = $ref->getProperty('configData');
    $prop->setValue(null, [
        "smtp" => "smtp.example.com",
        "smtp_port" => "25",
        "from" => "noreply@example.com",
        "title" => "TestApp"
    ]);
});

it('returns false if from, to, subject, or body is empty', function () {
    expect(Email::sendEmail('', 'a@b.com', 'subject', 'body', true))->toBeFalse();
    expect(Email::sendEmail('from@a.com', '', 'subject', 'body', true))->toBeFalse();
    expect(Email::sendEmail('from@a.com', 'to@b.com', '', 'body', true))->toBeFalse();
    expect(Email::sendEmail('from@a.com', 'to@b.com', 'subject', '', true))->toBeFalse();
});

it('returns true on valid email data', function () {
    $result = Email::sendEmail('from@a.com', 'to@b.com', 'subject', 'body', true);
    expect($result)->toBeTrue();
});
