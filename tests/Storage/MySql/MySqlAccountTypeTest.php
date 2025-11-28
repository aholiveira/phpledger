<?php

use PHPLedger\Domain\AccountType;
use PHPLedger\Storage\MySql\MysqlAccountType;
use PHPLedger\Storage\MySql\MySqlStorage;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\Logger;

beforeEach(function () {
    Config::init(ROOT_DIR . "/tests/config.json");
    ObjectFactory::init("mysql", new Logger(ROOT_DIR . "/logs/test.log"));

    $this->db = MySqlStorage::getConnection();
    $this->db->query("DELETE FROM tipo_contas");
});

it('creates a new account type', function () {
    $t = new MysqlAccountType();
    $t->id = 1;
    $t->description = "Normal";
    $t->savings = 0;

    expect($t->update())->toBeTrue();

    $list = MysqlAccountType::getList();
    expect($list)->toHaveCount(1);
    expect($list[1]->description)->toBe("Normal");
    expect($list[1]->savings)->toBe(0);
});

it('updates an existing account type', function () {
    $t = new MysqlAccountType();
    $t->id = 2;
    $t->description = "Original";
    $t->savings = 0;
    $t->update();

    $t2 = MysqlAccountType::getById(2);
    $t2->description = "Updated";
    $t2->savings = 1;
    $t2->update();

    $found = MysqlAccountType::getById(2);
    expect($found->description)->toBe("Updated");
    expect($found->savings)->toBe(1);
});

it('retrieves by id and returns empty object when not found', function () {
    $t = MysqlAccountType::getById(9999);
    expect($t)->toBeInstanceOf(AccountType::class);
    expect($t->id)->toBeNull();
});

it('filters list', function () {
    $t1 = new MysqlAccountType();
    $t1->id = 3;
    $t1->description = "A";
    $t1->savings = 0;
    $t1->update();

    $t2 = new MysqlAccountType();
    $t2->id = 4;
    $t2->description = "B";
    $t2->savings = 1;
    $t2->update();

    $filtered = MysqlAccountType::getList([
        'savings' => ['operator' => '=', 'value' => 1]
    ]);

    expect($filtered)->toHaveCount(1);
    expect($filtered[4]->description)->toBe("B");
});

it('deletes an account type', function () {
    $t = new MysqlAccountType();
    $t->id = 5;
    $t->description = "DeleteMe";
    $t->savings = 0;
    $t->update();

    expect($t->delete())->toBeTrue();

    $list = MysqlAccountType::getList();
    expect($list)->toHaveCount(0);
});
