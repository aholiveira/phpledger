<?php

use PHPLedger\Util\NumberUtil;

it('returns empty string for null', function () {
    expect(NumberUtil::normalize(null))->toBe('');
});

it('formats positive numbers to two decimals', function () {
    expect(NumberUtil::normalize(12.3))->toBe('12.30');
});

it('formats negative numbers to two decimals', function () {
    expect(NumberUtil::normalize(-5.1))->toBe('-5.10');
});

it('formats whole numbers with trailing zeros', function () {
    expect(NumberUtil::normalize(7))->toBe('7.00');
});

it('rounds correctly', function () {
    expect(NumberUtil::normalize(1.999))->toBe('2.00');
});
