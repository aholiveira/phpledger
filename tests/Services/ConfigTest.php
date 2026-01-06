<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

namespace PHPLedgerTests\Unit\Services;

use PHPLedger\Services\Config;

afterEach(function () {
    Config::reset();
});

function validConfig(array $override = []): array
{
    return array_replace_recursive([
        'version' => 1,
        'title' => 'App',
        'storage' => [
            'type' => 'mysql',
            'settings' => [
                'host' => 'localhost',
                'user' => 'user',
                'database' => 'db',
                'port' => 3306,
            ],
        ],
        'smtp' => [
            'host' => 'smtp.example.com',
            'from' => 'admin@example.com',
            'port' => 25,
        ],
        'admin' => [
            'username' => 'admin',
            'password' => 'secret',
        ],
    ], $override);
}

function tmpConfigFile(array $cfg = []): string
{
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, json_encode($cfg ?: validConfig()));
    return $file;
}

it('loads valid configuration file in test mode', function () {
    $file = tmpConfigFile();
    expect(Config::init($file, true))->toBeTrue();
    expect(Config::instance()->get('title'))->toBe('App');
    unlink($file);
});

it('fails loading non existing config file', function () {
    expect(Config::init('/no/such/file.json'))->toBeFalse();
});

it('fails loading invalid json', function () {
    $file = tempnam(sys_get_temp_dir(), 'cfg');
    file_put_contents($file, '{invalid json');
    expect(Config::init($file))->toBeFalse();
    unlink($file);
});

it('gets and sets flat values', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    $cfg = Config::instance();

    $cfg->set('foo', 'bar', false);
    expect($cfg->get('foo'))->toBe('bar');

    unlink($file);
});

it('gets and sets nested values', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    $cfg = Config::instance();

    $cfg->set('a.b.c', 123, false);
    expect($cfg->get('a.b.c'))->toBe(123);

    unlink($file);
});

it('returns default when key is missing', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    $cfg = Config::instance();

    expect($cfg->get('missing', 'default'))->toBe('default');

    unlink($file);
});

it('validates correct config in test mode', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(), true))->toBeTrue();
});

it('fails validation without title', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(['title' => ''])))->toBeFalse();
});

it('fails validation with unsupported storage type', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig(['storage' => ['type' => 'file']])))->toBeFalse();
});

it('fails validation with invalid mysql settings', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'storage' => ['settings' => ['host' => '', 'user' => '', 'database' => '']]
    ])))->toBeFalse();
});

it('fails validation with invalid smtp from address', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'smtp' => ['host' => 'smtp', 'from' => 'invalid']
    ])))->toBeFalse();
});

it('fails validation with missing admin credentials', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'admin' => ['username' => null, 'password' => null]
    ])))->toBeFalse();
});

it('saves configuration to disk', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    $cfg = Config::instance();

    foreach (validConfig() as $k => $v) {
        $cfg->set($k, $v, false);
    }

    $cfg->save();
    $data = json_decode(file_get_contents($file), true);

    expect($data['title'])->toBe('App');
    expect($data['storage']['type'])->toBe('mysql');

    unlink($file);
});

it('throws when saving without file', function () {
    $cfg = Config::instance();
    $cfg->set('title', 'x', false);
    $cfg->save();
})->throws("Config not initialized");

it('throws when config is invalid on save', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    $cfg = Config::instance();

    $cfg->set('title', '', false);
    $cfg->save();
})->throws("Configuration data is not valid");

it('fails validation with invalid MySQL port', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'storage' => ['settings' => ['port' => 0]]
    ])))->toBeFalse();
});

it('fails validation with missing SMTP host', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'smtp' => ['host' => '', 'from' => 'a@b.com']
    ])))->toBeFalse();
});

it('fails validation with invalid SMTP port', function () {
    $cfg = Config::instance();
    expect($cfg->validate(validConfig([
        'smtp' => ['port' => 0]
    ])))->toBeFalse();
});

it('fails init when validation fails', function () {
    $file = tmpConfigFile([
        'version' => 1,
        'title' => '',
        'storage' => ['type' => 'mysql', 'settings' => ['host' => 'localhost', 'user' => 'u', 'database' => 'd']],
        'smtp' => ['host' => 'a', 'from' => 'b@c.com'],
        'admin' => ['username' => 'x', 'password' => 'y'],
    ]);

    expect(Config::init($file))->toBeFalse();
    unlink($file);
});

it('init sets loaded false on Throwable', function () {
    $file = tmpConfigFile();
    // Inject a broken fs that throws generic exception
    Config::setFilesystem(new class implements \PHPLedger\Contracts\ConfigFilesystemInterface {
        public function exists(string $path): bool { return true; }
        public function read(string $path): string|false { throw new \Exception("fail"); }
        public function write(string $path, string $data): bool { return true; }
        public function delete(string $path): void {}
        public function isDir(string $path): bool { return true; }
        public function mkdir(string $path): void {}
        public function isWritable(string $path): bool { return true; }
        public function tempFile(string $dir): string { return tempnam(sys_get_temp_dir(), 'tmp'); }
        public function replace(string $src, string $dest): bool { return true; }
    });
    expect(Config::init($file, true))->toBeFalse();
    unlink($file);
});

it('save throws when directory not writable', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    Config::setFilesystem(new class implements \PHPLedger\Contracts\ConfigFilesystemInterface {
        public function exists(string $path): bool { return false; }
        public function read(string $path): string|false { return ''; }
        public function write(string $path, string $data): bool { return true; }
        public function delete(string $path): void {}
        public function isDir(string $path): bool { return false; }
        public function mkdir(string $path): void {}
        public function isWritable(string $path): bool { return false; }
        public function tempFile(string $dir): string { return tempnam(sys_get_temp_dir(), 'tmp'); }
        public function replace(string $src, string $dest): bool { return true; }
    });
    $cfg = Config::instance();
    $cfg->set('title', 'x', false);
    expect(fn() => $cfg->save())->toThrow("Configuration directory is not writable");
    unlink($file);
});

it('validator sets message for invalid+missing field', function () {
    $cfg = Config::instance();
    $invalid = validConfig(['title' => '']);
    expect($cfg->validate($invalid, false))->toBeFalse();
    print $cfg->getValidationMessage();
    expect(str_contains($cfg->getValidationMessage(), 'title'))->toBeTrue();
});

it('init triggers save when config changed', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    $cfg = Config::instance();

    $cfg->set('newkey', 'value', false);

    Config::setFilesystem(new class implements \PHPLedger\Contracts\ConfigFilesystemInterface {
        public function exists(string $path): bool { return true; }
        public function read(string $path): string|false { return json_encode(validConfig()); }
        public function write(string $path, string $data): bool { return true; }
        public function delete(string $path): void {}
        public function isDir(string $path): bool { return true; }
        public function mkdir(string $path): void {}
        public function isWritable(string $path): bool { return true; }
        public function tempFile(string $dir): string { return tempnam(sys_get_temp_dir(), 'tmp'); }
        public function replace(string $temp, string $target): bool { return true; }
    });

    expect(Config::init($file))->toBeTrue();
    unlink($file);
});

it('set merges arrays recursively', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    $cfg = Config::instance();

    $cfg->set('nested', ['a' => ['b' => 1]], false);
    $cfg->set('nested', ['a' => ['c' => 2]], false);

    expect($cfg->get('nested.a.b'))->toBe(1);
    expect($cfg->get('nested.a.c'))->toBe(2);

    unlink($file);
});

it('get returns default for deeply missing nested key', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    $cfg = Config::instance();

    expect($cfg->get('non.existing.path', 99))->toBe(99);

    unlink($file);
});

it('save throws when write fails', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    Config::setFilesystem(new class implements \PHPLedger\Contracts\ConfigFilesystemInterface {
        public function exists(string $path): bool { return true; }
        public function read(string $path): string|false { return json_encode(validConfig()); }
        public function write(string $path, string $data): bool { return false; }
        public function delete(string $path): void {}
        public function isDir(string $path): bool { return true; }
        public function mkdir(string $path): void {}
        public function isWritable(string $path): bool { return true; }
        public function tempFile(string $dir): string { return tempnam(sys_get_temp_dir(), 'tmp'); }
        public function replace(string $temp, string $target): bool { return true; }
    });
    $cfg = Config::instance();
    $cfg->set('title', 'x', false);
    expect(fn() => $cfg->save())->toThrow("Unable to save configuration file");

    unlink($file);
});

it('save throws when replace fails', function () {
    $file = tmpConfigFile();
    Config::init($file, true);
    Config::setFilesystem(new class implements \PHPLedger\Contracts\ConfigFilesystemInterface {
        public function exists(string $path): bool { return true; }
        public function read(string $path): string|false { return json_encode(validConfig()); }
        public function write(string $path, string $data): bool { return true; }
        public function delete(string $path): void {}
        public function isDir(string $path): bool { return true; }
        public function mkdir(string $path): void {}
        public function isWritable(string $path): bool { return true; }
        public function tempFile(string $dir): string { return tempnam(sys_get_temp_dir(), 'tmp'); }
        public function replace(string $temp, string $target): bool { return false; }
    });
    $cfg = Config::instance();
    $cfg->set('title', 'x', false);
    expect(fn() => $cfg->save())->toThrow("Unable to replace configuration file");

    unlink($file);
});
