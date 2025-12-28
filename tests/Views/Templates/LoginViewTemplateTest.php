<?php

use PHPLedger\Views\Templates\LoginViewTemplate;

beforeEach(function () {
    $this->template = new LoginViewTemplate();
    $this->data = [
        'lang' => 'en',
        'htmlLang' => 'en-US',
        'pagetitle' => 'Login Page',
        'appTitle' => 'AppTitle',
        'csrf' => '<input type="hidden" name="csrf_token" value="123">',
        'postUser' => 'testuser',
        'label' => [
            'username' => 'Username',
            'password' => 'Password',
            'login' => 'Login'
        ],
        'footer' => [
            'versionText' => 'v1.0.0',
            'languageSelectorHtml' => '<select><option>EN</option></select>'
        ],
        'errorMessage' => ''
    ];
});

it('renders login form with correct fields', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<form class="login-form" aria-labelledby="login-title" method="POST" name="login" autocomplete="on" novalidate>');
    expect($output)->toContain('<input type="hidden" name="lang" value="en">');
    expect($output)->toContain('<input required="" maxlength="255" type="text" name="username" id="username"');
    expect($output)->toContain('value="testuser"');
    expect($output)->toContain('<input required="" maxlength="255" type="password" name="password"');
    expect($output)->toContain('<button type="submit" value="login" name="login">Login</button>');
    expect($output)->toContain('<a class="version-tag" href="https://github.com/aholiveira/phpledger"');
    expect($output)->toContain($this->data['footer']['versionText']);
    expect($output)->toContain($this->data['footer']['languageSelectorHtml']);
});

it('renders error message if provided', function () {
    $this->data['errorMessage'] = 'Invalid login';
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<p class="login-message" role="alert" aria-live="polite">Invalid login</p>');
});
