<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedger\Services;

use PHPLedger\Services\Config;
use PHPLedger\Services\Email;

/* override global mail() inside PHPLedger\Services */

function mail($to, $subject, $message, $headers, $params)
{
    return true;
}

beforeEach(function () {
    Config::reset();

    $ref = new \ReflectionClass(Config::class);
    $prop = $ref->getProperty('configData');
    $prop->setAccessible(true);
    $prop->setValue(null, [
        'smtp' => [
            'host' => 'smtp.example.com',
            'port' => '25',
            'from' => 'noreply@example.com',
        ],
        'title' => 'TestApp',
    ]);
});

it('returns false if any required field is empty', function () {
    $email = new Email();

    expect($email->send('', 'to@b.com', 'subject', 'body'))->toBeFalse();
    expect($email->send('from@a.com', '', 'subject', 'body'))->toBeFalse();
    expect($email->send('from@a.com', 'to@b.com', '', 'body'))->toBeFalse();
    expect($email->send('from@a.com', 'to@b.com', 'subject', ''))->toBeFalse();
});

it('returns true when mail is sent', function () {
    $email = new Email();

    expect(
        $email->send('from@a.com', 'to@b.com', 'subject', 'body')
    )->toBeTrue();
});
