<?php

use PHPLedger\Views\Templates\ResetPasswordViewTemplate;

beforeEach(function () {
    $this->template = new ResetPasswordViewTemplate();
});

it('renders the page with success message', function () {
    $data = [
        'lang' => 'pt-pt',
        'apptitle' => 'PHPLedger',
        'success' => true,
        'message' => 'Password updated successfully',
    ];

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<h1>PHPLedger</h1>');
    expect($html)->toContain('Password updated successfully');
    expect($html)->toContain('color:green');
    expect($html)->not->toContain('<form id="resetForm"');
});

it('renders the page with error message', function () {
    $data = [
        'lang' => 'pt-pt',
        'apptitle' => 'PHPLedger',
        'success' => false,
        'message' => 'Token inválido',
        'tokenId' => '12345',
        'action' => 'reset_password',
    ];

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('<h1>PHPLedger</h1>');
    expect($html)->toContain('Token inválido');
    expect($html)->toContain('color:red');
    expect($html)->toContain('<form id="resetForm" method="POST">');
    expect($html)->toContain('value="12345"'); // hidden tokenId
    expect($html)->toContain('value="reset_password"'); // hidden action
    expect($html)->toContain('id="password" type="password" name="password"');
    expect($html)->toContain('id="verifyPassword" type="password" name="verifyPassword"');
    expect($html)->toContain('id="submitButton" type="submit" value="Repor"');
    expect($html)->toContain('<script>'); // JS present
});

it('disables the submit button initially', function () {
    $data = [
        'lang' => 'pt-pt',
        'apptitle' => 'PHPLedger',
        'success' => false,
        'message' => 'Enter new password',
        'tokenId' => 'abc',
        'action' => 'reset_password',
    ];

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain('disabled'); // submit button disabled initially
    expect($html)->toContain('password');
    expect($html)->toContain('verifyPassword');
});
