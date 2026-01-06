<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Storage\MySql\MySqlLedger;
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
    // Ensure we have a fresh connection and clean state
    ObjectFactory::init("mysql");
    $this->connection = MySqlStorage::getConnection();
    // Clear existing test data
    $this->connection->query("TRUNCATE TABLE `grupo_contas`");
});

afterAll(function () {
    // Clean up test data
    $conn = MySqlStorage::getConnection();
    if ($conn instanceof mysqli) {
        $conn->query("TRUNCATE TABLE `grupo_contas`");
    }
});

describe('MySqlLedger::getList', function () {
    it('returns empty array when no ledgers exist', function () {
        $list = MySqlLedger::getList();
        expect($list)->toBeArray();
        expect($list)->toBeEmpty();
    });

    it('returns all ledgers from database', function () {
        // Insert test ledgers
        $ledger1 = new MySqlLedger();
        $ledger1->id = 1;
        $ledger1->name = 'Test Ledger 1';
        $ledger1->update();

        $ledger2 = new MySqlLedger();
        $ledger2->id = 2;
        $ledger2->name = 'Test Ledger 2';
        $ledger2->update();

        $list = MySqlLedger::getList();
        expect($list)->toHaveLength(2);
        expect($list)->toHaveKey(1);
        expect($list)->toHaveKey(2);
    });

    it('returns ledger objects when populated', function () {
        $ledger = new MySqlLedger();
        $ledger->id = 1;
        $ledger->name = 'Ledger A';
        $ledger->update();

        $list = MySqlLedger::getList();

        expect($list[1])->toBeInstanceOf(MySqlLedger::class);
        expect($list[1]->name)->toBe('Ledger A');
    });
    it('filters by field when filter provided', function () {
        $ledger1 = new MySqlLedger();
        $ledger1->id = 10;
        $ledger1->name = 'Filter Test';
        $ledger1->update();

        $ledger2 = new MySqlLedger();
        $ledger2->id = 20;
        $ledger2->name = 'Other Ledger';
        $ledger2->update();

        $list = MySqlLedger::getList(['id' => ['operator' => '=', 'value' => 10]]);
        expect($list)->toHaveLength(1);
        expect($list)->toHaveKey(10);
    });

    it('returns results ordered by id', function () {
        $ledger3 = new MySqlLedger();
        $ledger3->id = 3;
        $ledger3->name = 'Third';
        $ledger3->update();

        $ledger1 = new MySqlLedger();
        $ledger1->id = 1;
        $ledger1->name = 'First';
        $ledger1->update();

        $list = MySqlLedger::getList();
        $ids = array_keys($list);

        expect($ids)->toBe([1, 3]);
    });
});

describe('MySqlLedger::getById', function () {
    it('returns null when ledger does not exist', function () {
        $ledger = MySqlLedger::getById(999);
        expect($ledger)->toBeNull();
    });

    it('retrieves a ledger by id', function () {
        $original = new MySqlLedger();
        $original->id = 5;
        $original->name = 'Test Ledger';
        $original->update();

        $retrieved = MySqlLedger::getById(5);

        expect($retrieved)->toBeInstanceOf(MySqlLedger::class);
        expect($retrieved->id)->toBe(5);
        expect($retrieved->name)->toBe('Test Ledger');
    });

    it('retrieves correct data for ledger with spaces in name', function () {
        $original = new MySqlLedger();
        $original->id = 6;
        $original->name = 'My Test Ledger';
        $original->update();

        $retrieved = MySqlLedger::getById(6);

        expect($retrieved->name)->toBe('My Test Ledger');
    });
});

describe('MySqlLedger::update', function () {
    it('creates a new ledger when id does not exist', function () {
        $ledger = new MySqlLedger();
        $ledger->id = 100;
        $ledger->name = 'New Ledger';

        $result = $ledger->update();

        expect($result)->toBeTrue();

        $retrieved = MySqlLedger::getById(100);
        expect($retrieved)->not->toBeNull();
        expect($retrieved->name)->toBe('New Ledger');
    });

    it('updates an existing ledger', function () {
        $ledger = new MySqlLedger();
        $ledger->id = 50;
        $ledger->name = 'Original Name';
        $ledger->update();

        $ledger->name = 'Updated Name';
        $result = $ledger->update();

        expect($result)->toBeTrue();

        $retrieved = MySqlLedger::getById(50);
        expect($retrieved->name)->toBe('Updated Name');
    });

    it('handles special characters in name', function () {
        $ledger = new MySqlLedger();
        $ledger->id = 101;
        $ledger->name = "Test & Special's \"Quotes\"";

        $result = $ledger->update();
        expect($result)->toBeTrue();

        $retrieved = MySqlLedger::getById(101);
        expect($retrieved->name)->toBe("Test & Special's \"Quotes\"");
    });

    it('trims name to 30 characters due to column constraint', function () {
        $ledger = new MySqlLedger();
        $ledger->id = 102;
        $ledger->name = 'This is a very long ledger name that exceeds thirty characters';
        $ledger->update();

        $retrieved = MySqlLedger::getById(102);

        expect(strlen($retrieved->name))->toBeLessThanOrEqual(30);
    });
});

describe('MySqlLedger::delete', function () {
    it('returns false (not implemented)', function () {
        $ledger = new MySqlLedger();
        $ledger->id = 77;
        $ledger->name = 'Delete Test';

        $result = $ledger->delete();

        expect($result)->toBeFalse();
    });
});

describe('MySqlLedger properties and instantiation', function () {
    it('can instantiate with properties', function () {
        $ledger = new MySqlLedger();
        $ledger->id = 200;
        $ledger->name = 'Property Test';

        expect($ledger->id)->toBe(200);
        expect($ledger->name)->toBe('Property Test');
    });

    it('inherits from Ledger domain class', function () {
        $ledger = new MySqlLedger();
        expect($ledger)->toBeInstanceOf(\PHPLedger\Domain\Ledger::class);
    });
});
