<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Services\FileResponseSender;
use PHPLedger\Contracts\HeaderSenderInterface;

it('sends headers and outputs CSV', function () {
    $data = "ID;Name\n1;Alice\n";
    $filename = "test.csv";

    $mockHeaderSender = Mockery::mock(HeaderSenderInterface::class);
    $mockHeaderSender->shouldReceive('send')->once()->with('Content-Type: text/csv; charset=UTF-8');
    $mockHeaderSender->shouldReceive('send')->once()->with('Content-Disposition: attachment; filename="test.csv"');
    $mockHeaderSender->shouldReceive('send')->once()->with('Content-Length: ' . strlen($data));

    $sender = new FileResponseSender($mockHeaderSender);

    ob_start();
    $sender->csv($data, $filename);
    $output = ob_get_clean();

    expect($output)->toBe($data);
});
