<?php

use PHPLedger\Storage\MySql\MySqlAccount;
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
    $this->db->query("DELETE FROM movimentos");
    $this->db->query("DELETE FROM contas");
});

function createAccount(int $id = 1, string $name = 'Acc', string $num = '001'): MySqlAccount
{
    $a = new MySqlAccount();
    $a->id = $id;
    $a->number = $num;
    $a->name = $name;
    $a->grupo = 1;
    $a->typeId = 1;
    $a->iban = '';
    $a->swift = '';
    $a->openDate = date("Y-m-d");
    $a->closeDate = date("Y-m-d");
    $a->activa = 1;
    return $a;
}

function addMovement(mysqli $db, int $id, string $date, float $amount, int $dir): void
{
    $stmt = $db->prepare(
        "INSERT INTO movimentos (accountId, entryDate, euroAmount, direction) VALUES (?,?,?,?)"
    );
    $stmt->bind_param("isis", $id, $date, $amount, $dir);
    $stmt->execute();
    $stmt->close();
}

it('gets next id correctly', function () {
    expect(MySqlAccount::getNextId())->toBe(1);

    $this->db->query(
        "INSERT INTO contas (id, number, name, grupo, typeId, iban, swift, openDate, closeDate, activa)
         VALUES (1,'n','x',1,1,'','', NOW(), NOW(), 1)"
    );

    expect(MySqlAccount::getNextId())->toBe(2);
});

it('inserts and fetches an account', function () {
    $a = createAccount(1, 'Main', 'A01');
    expect($a->update())->toBeTrue();

    $f = MySqlAccount::getById(1);
    expect($f->name)->toBe('Main');
    expect($f->number)->toBe('A01');
});

it('updates an existing account', function () {
    $a = createAccount(1, 'Before');
    $a->update();

    $a->name = 'After';
    expect($a->update())->toBeTrue();

    $f = MySqlAccount::getById(1);
    expect($f->name)->toBe('After');
});

it('gets list with filters', function () {
    for ($i = 1; $i <= 3; $i++) {
        $a = createAccount($i, "Acc$i", "N$i");
        $a->grupo = $i;
        $a->update();
    }

    $list = MySqlAccount::getList(['grupo' => ['operator' => '=', 'value' => 2]]);
    expect($list)->toHaveCount(1);
    expect(reset($list)->name)->toBe('Acc2');
});

it('deletes an account', function () {
    $a = createAccount(5, 'Del');
    $a->update();

    expect($a->delete())->toBeTrue();

    $f = MySqlAccount::getById(5);
    expect($f->id)->toBeNull();
});

it('calculates balance', function () {
    $a = createAccount(10, 'Bal');
    $a->update();

    addMovement($this->db, 10, "2024-01-10", 100, 1);
    addMovement($this->db, 10, "2024-01-11", -50, -1);

    $b = $a->getBalance();

    expect($b['income'])->toBe(100.0);
    expect($b['expense'])->toBe(50.0);
    expect($b['balance'])->toBe(50.0);
});

it('calculates balance on date', function () {
    $a = createAccount(20, 'Bal2');
    $a->update();

    addMovement($this->db, 20, "2024-01-10", 200, 1);
    addMovement($this->db, 20, "2024-02-01", -100, -1);

    $b = $a->getBalanceOnDate(new DateTime("2024-01-15"));

    expect($b['income'])->toBe(200.0);
    expect($b['expense'])->toBe(0.0);
    expect($b['balance'])->toBe(200.0);
});
