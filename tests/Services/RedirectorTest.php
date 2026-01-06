<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedgerTests\Unit\Util;

use PHPLedger\Services\Redirector;

beforeEach(function () {
    $this->sentHeaders = [];
    $this->headersSent = false;
    $this->redirector = new Redirector(
        fn($string, $replace = true, $code = 303) => $this->sentHeaders[] = [$string, $replace, $code],
        fn() => $this->headersSent
    );
});

it('redirects to allowed url', function () {
    $this->redirector->to('index.php');
    expect($this->sentHeaders[0][0])->toBe('Location: index.php');
});

it('sanitizes disallowed urls', function () {
    $this->redirector->to('admin.php');
    expect($this->sentHeaders[0][0])->toBe('Location: index.php');
});

it('uses refresh header when delay is provided', function () {
    $this->redirector->to('index.php', 3);
    expect($this->sentHeaders[0][0])->toBe('Refresh: 3; URL=index.php');
});

it('does nothing when headers already sent', function () {
    $this->headersSent = true;
    $this->redirector->to('index.php');
    expect($this->sentHeaders)->toBe([]);
});
