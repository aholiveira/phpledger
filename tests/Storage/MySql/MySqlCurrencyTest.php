<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Storage\MySql\MySqlCurrency;
use PHPLedger\Storage\MySql\MySqlStorage;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Services\Config;

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
    $this->db->query("DELETE FROM moedas");
});

function createCurrency(
    int $id = 1,
    string $code = "EUR",
    string $desc = "Euro",
    float $rate = 1.0,
    string $user = "tester"
): MySqlCurrency {
    $c = new MySqlCurrency();
    $c->id = $id;
    $c->code = $code;
    $c->description = $desc;
    $c->exchangeRate = $rate;
    $c->username = $user;
    return $c;
}

it('gets list empty', function () {
    $list = MySqlCurrency::getList();
    expect($list)->toBeArray()->toBeEmpty();
});

it('inserts a currency and fetches it by id', function () {
    $c = createCurrency(10, "USD", "US Dollar", 1.2);
    expect($c->update())->toBeTrue();

    $f = MySqlCurrency::getById(10);
    expect($f)->not->toBeNull();
    expect($f->code)->toBe("USD");
    expect($f->description)->toBe("US Dollar");
    expect($f->exchangeRate)->toBe(1.2);
});

it('inserts and fetches by code', function () {
    $c = createCurrency(20, "GBP", "Pound", 0.9);
    $c->update();

    $f = MySqlCurrency::getByCode("GBP");
    expect($f)->not->toBeNull();
    expect($f->id)->toBe(20);
});

it('updates an existing currency', function () {
    $c = createCurrency(30, "CHF", "Swiss Franc", 1.05);
    $c->update();

    $c->description = "Franc";
    $c->exchangeRate = 1.10;
    $c->update();

    $f = MySqlCurrency::getById(30);
    expect($f->description)->toBe("Franc");
    expect($f->exchangeRate)->toBe(1.10);
});

it('gets list with filters', function () {
    createCurrency(1, "AAA", "Alpha", 1.0)->update();
    createCurrency(2, "BBB", "Beta", 2.0)->update();
    createCurrency(3, "CCC", "Gamma", 3.0)->update();

    $list = MySqlCurrency::getList(["code" => ["operator" => "=", "value" => "BBB"]]);
    expect($list)->toHaveCount(1);
    $first = reset($list);
    expect($first->code)->toBe("BBB");
});

it('update preserves username', function () {
    $c = createCurrency(40, "JPY", "Yen", 1.5, "alice");
    $c->update();

    $f = MySqlCurrency::getById(40);
    expect($f->username)->toBe("alice");

    $c->description = "Japanese Yen";
    $c->update();

    $f2 = MySqlCurrency::getById(40);
    expect($f2->username)->toBe("alice");
});
