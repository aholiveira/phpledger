<?php

use PHPLedger\Views\Templates\UserProfileViewTemplate;

beforeEach(function () {
    $this->template = new UserProfileViewTemplate();
    $this->data = [
        'lang' => 'en',
        'pagetitle' => 'Profile',
        'csrf' => '<input type="hidden" name="_csrf_token" value="token">',
        'action' => 'my_profile',
        'text' => [
            'id' => 1,
            'username' => 'admin',
            'firstName' => 'Antonio',
            'lastName' => 'Oliveira',
            'fullName' => 'Antonio Oliveira',
            'email' => 'aholiveira@gmail.com',
        ],
        'label' => [
            'username' => 'Username',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'display_name' => 'Full Name',
            'email' => 'Email',
            'password' => 'Password',
            'verify_password' => 'Verify Password',
            'save' => 'Save',
        ],
        'menu' => [],
        'footer' => [],
        'message' => '',
        'ui' => new class {
            public function menu($label, $menu) { echo '<nav>menu</nav>'; }
            public function footer($label, $footer) { echo '<footer>footer</footer>'; }
        }
    ];
});

it('renders all input fields with correct values', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('value="admin"');
    expect($output)->toContain('value="Antonio"');
    expect($output)->toContain('value="Oliveira"');
    expect($output)->toContain('value="Antonio Oliveira"');
    expect($output)->toContain('value="aholiveira@gmail.com"');
});

it('includes the CSRF token and action hidden fields', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('name="_csrf_token" value="token"');
    expect($output)->toContain('name="action" value="my_profile"');
});

it('renders the submit button with correct label', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('>Save<');
});

it('renders the error message container', function () {
    ob_start();
    $this->template->render($this->data);
    $output = ob_get_clean();

    expect($output)->toContain('id="errorMsg"');
});
