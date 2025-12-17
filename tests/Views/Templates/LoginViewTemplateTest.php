<?php

use PHPLedger\Views\Templates\LoginViewTemplate;

beforeEach(function () {
    $this->template = new LoginViewTemplate();
    $this->data = [
        'lang' => 'en',
        'pagetitle' => 'Login Page',
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

    expect($output)->toContain('<form method="POST" action="?lang=en" name="login" autocomplete="off">');
    expect($output)->toContain('<input name="lang" value="en" type="hidden" />');
    expect($output)->toContain('<input required size="25" maxlength="50" type="text" name="username" id="username"');
    expect($output)->toContain('value="testuser"');
    expect($output)->toContain('<input required size="25" maxlength="255" type="password" name="password"');
    expect($output)->toContain('<input type="submit" value="Login">');
    expect($output)->toContain('<a href="https://github.com/aholiveira/phpledger"');
    expect($output)->toContain($this->data['footer']['versionText']);
    expect($output)->toContain($this->data['footer']['languageSelectorHtml']);
});

it('renders error message if provided', function () {
    $this->data['errorMessage'] = 'Invalid login';
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('<p class="invalid-login">Invalid login</p>');
});
