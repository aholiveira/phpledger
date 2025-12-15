<?php

namespace PHPLedger\Util;

use PHPLedger\Util\Config;
use PHPLedger\Util\Email;

function mail($to, $subject, $message, $headers, $params) {
    return true; // simulate a successful send
}

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
    $from = 'from@a.com';
    $to = 'to@b.com';
    $subject = 'subject';
    $body = 'body';
    expect(Email::sendEmail('', $to, $subject, $body, true))->toBeFalse();
    expect(Email::sendEmail($from, '', $subject, $body, true))->toBeFalse();
    expect(Email::sendEmail($from, $to, '', $body, true))->toBeFalse();
    expect(Email::sendEmail($from, $to, $subject, '', true))->toBeFalse();
});

it('returns true on valid email data', function () {
    $from = 'from@a.com';
    $to = 'to@b.com';
    $subject = 'subject';
    $body = 'body';
    $result = Email::sendEmail($from, $to, $subject, $body, true);
    expect($result)->toBeTrue();
});

it('returns true on an actual email sent', function () {
    $from = 'from@a.com';
    $to = 'to@b.com';
    $subject = 'subject';
    $body = 'body';
    $result = Email::sendEmail($from, $to, $subject, $body, false);
    expect($result)->toBeTrue();
});
