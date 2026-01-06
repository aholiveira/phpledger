<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Util\Html;

// Option generation tests
it('builds year options with selected year', function () {
    $output = Html::yearOptions(2024, 2020, 2025);
    expect(str_contains($output, '<option value="2024" selected>2024</option>'))->toBeTrue();
});

it('builds month options with selected month', function () {
    $output = Html::monthOptions('5');
    expect(str_contains($output, '<option value="5" selected>05</option>'))->toBeTrue();
});

it('builds day options with selected day', function () {
    $output = Html::dayOptions('15');
    expect(str_contains($output, '<option value="15" selected>15</option>'))->toBeTrue();
});

it('builds hour options with selected hour', function () {
    $output = Html::hourOptions('12');
    expect(str_contains($output, '<option value="12" selected>12</option>'))->toBeTrue();
});

it('builds minute options with selected minute', function () {
    $output = Html::minuteOptions('30');
    expect(str_contains($output, '<option value="30" selected>30</option>'))->toBeTrue();
});

it('builds generic options', function () {
    $output = Html::buildOptions(1, 3, '2');
    expect(str_contains($output, '<option value="2" selected>2</option>'))->toBeTrue();
});

// Header
it('renders header HTML with script and meta tags', function () {
    ob_start();
    Html::header();
    $output = ob_get_clean();

    expect(str_contains($output, '<script defer src="assets/js/set-timezone.js"></script>'))->toBeTrue();
    expect(str_contains($output, '<meta charset="utf-8">'))->toBeTrue();
    expect(str_contains($output, 'assets/styles.css'))->toBeTrue();
});

// Title
it('returns HTML-escaped title with page prefix', function () {
    $title = Html::title('Page', 'TestApp');
    expect($title)->toBe('Page - TestApp');
});

it('returns title without page prefix when empty', function () {
    $title = Html::title('', 'TestApp');
    expect($title)->toBe('TestApp');
});
