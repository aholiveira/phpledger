<?php

use PHPLedger\Views\Templates\ForgotPasswordViewTemplate;
use PHPLedger\Util\Html;

beforeEach(function () {
    $this->template = new ForgotPasswordViewTemplate();

    $this->data = [
        'lang' => 'en',
        'appTitle' => 'PHPLedger App',
        'label' => [
            'password_recovery' => 'Password Recovery',
            'username' => 'Username',
            'email' => 'Email',
            'send_reset_link' => 'Send Reset Link'
        ],
        'message' => 'Test error message',
        'csrf' => '<input type="hidden" name="csrf_token" value="123">',
        'action' => 'forgot_password',
        'footer' => [
            'languageSelectorHtml' => '<select><option>EN</option></select>'
        ]
    ];
});

it('renders the correct title and language', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<html lang="en">');
    expect($output)->toContain('<title>' . Html::title('Password Recovery', 'PHPLedger App') . '</title>');
});

it('renders app title and password recovery label', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<h1>PHPLedger App</h1>');
    expect($output)->toContain('<p>Password Recovery</p>');
});

it('renders error message', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<p class=\'error\'>Test error message</p>');
});

it('renders form fields and CSRF token', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<input type="hidden" name="csrf_token" value="123">');
    expect($output)->toContain('<input id="username"');
    expect($output)->toContain('<input id="email"');
    expect($output)->toContain('<input type="submit" value="Send Reset Link">');
    expect($output)->toContain('<input type="hidden" name="action" value="forgot_password">');
});

it('renders language selector footer', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<p id="languageSelector" class="version-tag"><small><select><option>EN</option></select></small></p>');
});
