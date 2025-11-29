<?php

use PHPLedger\Storage\MySql\MySqlDefaults;
use PHPLedger\Storage\MySql\MySqlStorage;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;

beforeAll(function () {
    if (!\defined('ROOT_DIR')) {
        define('ROOT_DIR', __DIR__ . '/../../..');
    }
    // Initialize config and object factory
    Config::init(ROOT_DIR . '/tests/config.json');
});

beforeEach(function () {
    ObjectFactory::init("mysql");
    $this->db = MySqlStorage::getConnection();
    $this->db->query("DELETE FROM defaults");
});

function createDefaults(array $data = []): MySqlDefaults
{
    return new MySqlDefaults($data);
}

it('returns empty list initially', function () {
    $list = MySqlDefaults::getList();
    expect($list)->toBeArray()->toBeEmpty();
});

it('creates a new defaults record with constructor values', function () {
    $d = createDefaults([
        "id" => 1,
        "categoryId" => 500,
        "accountId" => 10,
        "currencyId" => "USD",
        "entryDate" => "2024-01-02",
        "direction" => -1,
        "language" => "en-US",
        "lastVisited" => "dashboard",
        "showReportGraph" => 1,
        "username" => "tester"
    ]);

    expect($d->update())->toBeTrue();

    $f = MySqlDefaults::getById(1);
    expect($f)->not->toBeNull();
    expect($f->categoryId)->toBe(500);
    expect($f->accountId)->toBe(10);
    expect($f->currencyId)->toBe("USD");
    expect($f->entryDate)->toBe("2024-01-02");
    expect($f->direction)->toBe(-1);
    expect($f->language)->toBe("en-US");
    expect($f->lastVisited)->toBe("dashboard");
    expect($f->username)->toBe("tester");
});

it('updates an existing defaults record', function () {
    $d = createDefaults([
        "id" => 1,
        "categoryId" => 100,
        "accountId" => 5,
        "currencyId" => "EUR",
        "entryDate" => "2024-01-01",
        "direction" => 1,
        "language" => "pt-PT",
        "lastVisited" => "home",
        "username" => "admin"
    ]);

    $d->update();

    $d->lastVisited = "reports";
    $d->language = "en-US";
    $d->direction = -1;
    $d->update();

    $f = MySqlDefaults::getById(1);
    expect($f->lastVisited)->toBe("reports");
    expect($f->language)->toBe("en-US");
    expect($f->direction)->toBe(-1);
});

it('fetches list with filters', function () {
    createDefaults(["id" => 1, "username" => "alice", "language" => "pt-PT"])->update();
    createDefaults(["id" => 2, "username" => "bob", "language" => "pt-PT"])->update();

    $list = MySqlDefaults::getList(["language" => ["operator" => "=", "value" => "pt-PT"]]);
    expect($list)->toHaveCount(2);
});

it('fetches by username case insensitive and trimmed', function () {
    createDefaults(["id" => 10, "username" => "JohnDoe"])->update();

    $f = MySqlDefaults::getByUsername("  johndoe ");
    expect($f)->not->toBeNull();
    expect($f->id)->toBe(10);
});

it('init creates default instance with prefilled values', function () {
    $d = MySqlDefaults::init();
    expect($d->id)->toBe(1);
    expect($d->categoryId)->toBe(990);
    expect($d->accountId)->toBe(0);
    expect($d->currencyId)->toBe("EUR");
    expect($d->direction)->toBe(1);
});

it('update persists default init values', function () {
    $d = MySqlDefaults::init();
    $d->update();

    $f = MySqlDefaults::getById(1);
    expect($f)->not->toBeNull();
    expect($f->categoryId)->toBe(990);
});
