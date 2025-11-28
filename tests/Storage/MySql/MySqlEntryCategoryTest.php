<?php

use PHPLedger\Storage\MySql\MySqlEntryCategory;
use PHPLedger\Storage\MySql\MySqlStorage;
use PHPLedger\Storage\ObjectFactory;
use PHPLedger\Util\Config;
use PHPLedger\Util\Logger;

if (!\defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
}

beforeEach(function () {
    Config::init(__DIR__ . '/../../config.json');
    ObjectFactory::init("mysql", new Logger(ROOT_DIR . "/logs/ledger.log"));
    $this->db = MySqlStorage::getConnection();
    $this->db->query("DELETE FROM movimentos");
    $this->db->query("DELETE FROM tipo_mov");
});

function createCategory(int $id = 1, ?int $parentId = null, string $desc = 'Cat', int $active = 1): MySqlEntryCategory
{
    $c = new MySqlEntryCategory();
    $c->id = $id;
    $c->parentId = $parentId;
    $c->description = $desc;
    $c->active = $active;
    return $c;
}

function addMovementCategory(mysqli $db, int $categoryId, string $date, float $amount): void
{
    $dir = $amount >= 0 ? 1 : -1;
    $amt = $amount;
    $stmt = $db->prepare("INSERT INTO movimentos (categoryId, entryDate, euroAmount, direction) VALUES (?,?,?,?)");
    $stmt->bind_param("isdi", $categoryId, $date, $amt, $dir);
    $stmt->execute();
    $stmt->close();
}

it('gets next id correctly', function () {
    expect(MySqlEntryCategory::getNextId())->toBe(1);

    // insert a row using the model so next-id advances
    createCategory(1, null, 'X')->update();

    expect(MySqlEntryCategory::getNextId())->toBe(2);
});

it('inserts and fetches a category', function () {
    $c = createCategory(10, null, 'Parent');
    expect($c->update())->toBeTrue();

    $f = MySqlEntryCategory::getById(10);
    expect($f->id)->toBe(10);
    expect($f->description)->toBe('Parent');
});

it('gets list with children (recursive)', function () {
    createCategory(1, null, 'Root')->update();
    createCategory(2, 1, 'Child')->update();

    $list = MySqlEntryCategory::getList();
    expect($list)->toBeArray();
    expect($list)->toHaveCount(1);

    $parent = reset($list);
    expect($parent->children)->toBeArray();
    expect($parent->children)->toHaveCount(1);
    expect(reset($parent->children)->description)->toBe('Child');
});

it('getById returns parentDescription and children', function () {
    createCategory(1, null, 'Root')->update();
    createCategory(3, 1, 'ChildA')->update();

    $f = MySqlEntryCategory::getById(1);
    expect($f->id)->toBe(1);
    expect($f->children)->toBeArray();
    expect($f->children)->toHaveCount(1);
    $child = reset($f->children);
    expect($child->parentDescription)->toBe('Root');
});

it('calculates balance for a category', function () {
    createCategory(5, null, 'BalCat')->update();
    addMovementCategory($this->db, 5, '2024-01-10', 100.0);
    addMovementCategory($this->db, 5, '2024-01-11', -50.0);

    $c = MySqlEntryCategory::getById(5);
    $b = $c->getBalance();
    // current implementation sums euroAmount (stored positive) and returns abs
    // so expected is 100 + 50 = 150
    expect($b)->toBe(50.0);
});

it('validate prevents self parenting', function () {
    $c = createCategory(7, 7, 'Invalid');
    expect($c->update())->toBeFalse();
});
