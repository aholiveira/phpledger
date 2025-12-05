<?php

namespace PHPLedger\Util;

use PHPLedger\Util\Redirector;

$GLOBALS['redirector_headers'] = [];
$GLOBALS['redirector_headers_sent'] = false;

function headers_sent() {
    return $GLOBALS['redirector_headers_sent'];
}

function header($string, $replace = true, $code = 303) {
    $GLOBALS['redirector_headers'][] = [$string, $replace, $code];
}

beforeEach(function () {
    $GLOBALS['redirector_headers'] = [];
    $GLOBALS['redirector_headers_sent'] = false;
});

it('redirects to allowed url', function () {
    Redirector::to('index.php');
    expect($GLOBALS['redirector_headers'][0][0])->toBe('Location: index.php');
});

it('sanitizes disallowed urls', function () {
    Redirector::to('admin.php');
    expect($GLOBALS['redirector_headers'][0][0])->toBe('Location: index.php');
});

it('uses refresh header when delay is provided', function () {
    Redirector::to('index.php', 3);
    expect($GLOBALS['redirector_headers'][0][0])->toBe('Refresh: 3; URL=index.php');
});

it('does nothing when headers already sent', function () {
    $GLOBALS['redirector_headers_sent'] = true;
    Redirector::to('index.php');
    expect($GLOBALS['redirector_headers'])->toBe([]);
});
