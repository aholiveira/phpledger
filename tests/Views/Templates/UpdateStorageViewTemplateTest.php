<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Views\Templates\UpdateStorageViewTemplate;

beforeEach(function () {
    $this->template = new UpdateStorageViewTemplate();
    $this->defaultData = [
        'lang' => 'en-us',
        'pagetitle' => 'Update Storage',
        'appTitle' => 'AppTitle',
        'csrf' => '<input type="hidden" name="csrf" value="token">',
        'footer' => ['languageSelectorHtml' => '<div>LangSelector</div>'],
        'label' => [
            'db_needs_update' => 'Database needs update',
            'cannot_use_app' => 'Cannot use application',
            'start_update' => 'Start update',
            'do_update' => 'Update now',
            'db_ok' => 'Database is OK',
            'go_login' => 'Go to login',
            'login_screen' => 'Login Screen',
            'db_updated' => 'Database updated',
            'redirecting' => 'Redirecting...',
            'update_fail' => 'Update failed',
            'error_msg' => 'Error message',
        ],
        'message' => 'Some message',
    ];
});

it('renders needs_update section correctly', function () {
    $data = array_merge($this->defaultData, ['showSection' => 'needs_update']);

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain($data['label']['db_needs_update']);
    expect($html)->toContain($data['label']['cannot_use_app']);
    expect($html)->toContain($data['label']['start_update']);
    expect($html)->toContain(htmlspecialchars($data['message']));
    expect($html)->toContain($data['csrf']);
    expect($html)->toContain($data['label']['do_update']);
});

it('renders storage_is_ok section correctly', function () {
    $data = array_merge($this->defaultData, ['showSection' => 'storage_is_ok']);

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain($data['label']['db_ok']);
    expect($html)->toContain($data['label']['go_login']);
    expect($html)->toContain($data['label']['login_screen']);
});

it('renders update_success section correctly', function () {
    $data = array_merge($this->defaultData, ['showSection' => 'update_success']);

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain(htmlspecialchars($data['message']));
    expect($html)->toContain($data['label']['db_updated']);
    expect($html)->toContain($data['label']['redirecting']);
});

it('renders default section (failure) correctly', function () {
    $data = array_merge($this->defaultData, ['showSection' => 'unknown']);

    ob_start();
    $this->template->render($data);
    $html = ob_get_clean();

    expect($html)->toContain($data['label']['update_fail']);
    expect($html)->toContain($data['label']['error_msg']);
    expect($html)->toContain(htmlspecialchars($data['message']));
});

it('includes language selector in all sections', function () {
    foreach (['needs_update', 'storage_is_ok', 'update_success', 'unknown'] as $section) {
        $data = array_merge($this->defaultData, ['showSection' => $section]);

        ob_start();
        $this->template->render($data);
        $html = ob_get_clean();

        expect($html)->toContain($data['footer']['languageSelectorHtml']);
    }
});
