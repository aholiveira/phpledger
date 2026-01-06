<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\ApplicationErrorViewTemplate;

beforeEach(function () {
    $this->view = new ApplicationErrorViewTemplate();
});

it('renders a basic error page', function () {
    $data = [
        'lang' => 'en-us',
        'pagetitle' => 'Application Error',
        'appTitle' => 'AppTitle',
        'message' => 'Something went wrong'
    ];

    ob_start();
    $this->view->render($data);
    $output = ob_get_clean();

    expect($output)->toContain('<!DOCTYPE html>');
    expect($output)->toContain('<html lang="en-us">');
    expect($output)->toContain('<title>');
    expect($output)->toContain('Application Error');
    expect($output)->toContain('Application error: Something went wrong');
    expect($output)->toContain('Check your config.json file');
});

it('escapes HTML in the error message', function () {
    $data = [
        'lang' => 'en-us',
        'pagetitle' => 'Application Error',
        'appTitle' => 'AppTitle',
        'message' => '<script>alert("xss")</script>'
    ];

    ob_start();
    $this->view->render($data);
    $output = ob_get_clean();

    expect($output)->toContain('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
});

it('includes Html header and title', function () {
    $data = [
        'lang' => 'en-us',
        'pagetitle' => 'App Error',
        'appTitle' => 'AppTitle',
        'message' => 'Error text'
    ];

    ob_start();
    $this->view->render($data);
    $output = ob_get_clean();

    // just check that Html::title and Html::header output something
    expect($output)->toContain('<title>');
});
