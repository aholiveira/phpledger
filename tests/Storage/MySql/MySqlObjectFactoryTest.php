<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Storage\MySql\MySqlObjectFactory;
use PHPLedger\Storage\MySql\MySqlStorage;
use PHPLedger\Storage\MySql\MySqlAccount;
use PHPLedger\Storage\MySql\MySqlAccountType;
use PHPLedger\Storage\MySql\MySqlCurrency;
use PHPLedger\Storage\MySql\MySqlDefaults;
use PHPLedger\Storage\MySql\MySqlEntryCategory;
use PHPLedger\Storage\MySql\MySqlLedger;
use PHPLedger\Storage\MySql\MySqlLedgerEntry;
use PHPLedger\Storage\MySql\MySqlUser;
use PHPLedger\Contracts\DataStorageInterface;

describe('MySqlObjectFactory::dataStorage', function () {
    it('returns a DataStorageInterface instance', function () {
        $storage = MySqlObjectFactory::dataStorage();
        expect($storage)->toBeInstanceOf(DataStorageInterface::class);
    });

    it('returns a MySqlStorage instance', function () {
        $storage = MySqlObjectFactory::dataStorage();
        expect($storage)->toBeInstanceOf(MySqlStorage::class);
    });

    it('returns the same instance on multiple calls (singleton)', function () {
        $storage1 = MySqlObjectFactory::dataStorage();
        $storage2 = MySqlObjectFactory::dataStorage();
        expect($storage1)->toBe($storage2);
    });
});

describe('MySqlObjectFactory::account', function () {
    it('returns a new MySqlAccount instance', function () {
        $account = MySqlObjectFactory::account();
        expect($account)->toBeInstanceOf(MySqlAccount::class);
    });

    it('returns a new instance each time', function () {
        $account1 = MySqlObjectFactory::account();
        $account2 = MySqlObjectFactory::account();
        expect($account1)->not->toBe($account2);
    });
});

describe('MySqlObjectFactory::accountType', function () {
    it('returns a new MySqlAccountType instance', function () {
        $accountType = MySqlObjectFactory::accountType();
        expect($accountType)->toBeInstanceOf(MySqlAccountType::class);
    });

    it('returns a new instance each time', function () {
        $accountType1 = MySqlObjectFactory::accountType();
        $accountType2 = MySqlObjectFactory::accountType();
        expect($accountType1)->not->toBe($accountType2);
    });
});

describe('MySqlObjectFactory::currency', function () {
    it('returns a new MySqlCurrency instance', function () {
        $currency = MySqlObjectFactory::currency();
        expect($currency)->toBeInstanceOf(MySqlCurrency::class);
    });

    it('returns a new instance each time', function () {
        $currency1 = MySqlObjectFactory::currency();
        $currency2 = MySqlObjectFactory::currency();
        expect($currency1)->not->toBe($currency2);
    });
});

describe('MySqlObjectFactory::defaults', function () {
    it('returns a new MySqlDefaults instance', function () {
        $defaults = MySqlObjectFactory::defaults();
        expect($defaults)->toBeInstanceOf(MySqlDefaults::class);
    });

    it('returns a new instance each time', function () {
        $defaults1 = MySqlObjectFactory::defaults();
        $defaults2 = MySqlObjectFactory::defaults();
        expect($defaults1)->not->toBe($defaults2);
    });
});

describe('MySqlObjectFactory::entryCategory', function () {
    it('returns a new MySqlEntryCategory instance', function () {
        $category = MySqlObjectFactory::entryCategory();
        expect($category)->toBeInstanceOf(MySqlEntryCategory::class);
    });

    it('returns a new instance each time', function () {
        $category1 = MySqlObjectFactory::entryCategory();
        $category2 = MySqlObjectFactory::entryCategory();
        expect($category1)->not->toBe($category2);
    });
});

describe('MySqlObjectFactory::ledger', function () {
    it('returns a new MySqlLedger instance', function () {
        $ledger = MySqlObjectFactory::ledger();
        expect($ledger)->toBeInstanceOf(MySqlLedger::class);
    });

    it('returns a new instance each time', function () {
        $ledger1 = MySqlObjectFactory::ledger();
        $ledger2 = MySqlObjectFactory::ledger();
        expect($ledger1)->not->toBe($ledger2);
    });
});

describe('MySqlObjectFactory::ledgerEntry', function () {
    it('returns a new MySqlLedgerEntry instance', function () {
        $entry = MySqlObjectFactory::ledgerEntry();
        expect($entry)->toBeInstanceOf(MySqlLedgerEntry::class);
    });

    it('returns a new instance each time', function () {
        $entry1 = MySqlObjectFactory::ledgerEntry();
        $entry2 = MySqlObjectFactory::ledgerEntry();
        expect($entry1)->not->toBe($entry2);
    });
});

describe('MySqlObjectFactory::user', function () {
    it('returns a new MySqlUser instance', function () {
        $user = MySqlObjectFactory::user();
        expect($user)->toBeInstanceOf(MySqlUser::class);
    });

    it('returns a new instance each time', function () {
        $user1 = MySqlObjectFactory::user();
        $user2 = MySqlObjectFactory::user();
        expect($user1)->not->toBe($user2);
    });
});

describe('MySqlObjectFactory::constructor', function () {
    it('accepts optional backend parameter', function () {
        $factory = new MySqlObjectFactory("mysql");
        expect($factory)->toBeInstanceOf(MySqlObjectFactory::class);
    });

    it('creates factory with no backend parameter', function () {
        $factory = new MySqlObjectFactory();
        expect($factory)->toBeInstanceOf(MySqlObjectFactory::class);
    });
});

describe('MySqlObjectFactory factory pattern', function () {
    it('all factory methods are static and callable', function () {
        expect(method_exists(MySqlObjectFactory::class, 'account'))->toBeTrue();
        expect(method_exists(MySqlObjectFactory::class, 'accountType'))->toBeTrue();
        expect(method_exists(MySqlObjectFactory::class, 'currency'))->toBeTrue();
        expect(method_exists(MySqlObjectFactory::class, 'defaults'))->toBeTrue();
        expect(method_exists(MySqlObjectFactory::class, 'entryCategory'))->toBeTrue();
        expect(method_exists(MySqlObjectFactory::class, 'ledger'))->toBeTrue();
        expect(method_exists(MySqlObjectFactory::class, 'ledgerEntry'))->toBeTrue();
        expect(method_exists(MySqlObjectFactory::class, 'user'))->toBeTrue();
        expect(method_exists(MySqlObjectFactory::class, 'dataStorage'))->toBeTrue();
    });

    it('creates objects of correct type from factory', function () {
        expect(MySqlObjectFactory::account())->toBeInstanceOf(MySqlAccount::class);
        expect(MySqlObjectFactory::accountType())->toBeInstanceOf(MySqlAccountType::class);
        expect(MySqlObjectFactory::currency())->toBeInstanceOf(MySqlCurrency::class);
        expect(MySqlObjectFactory::defaults())->toBeInstanceOf(MySqlDefaults::class);
        expect(MySqlObjectFactory::entryCategory())->toBeInstanceOf(MySqlEntryCategory::class);
        expect(MySqlObjectFactory::ledger())->toBeInstanceOf(MySqlLedger::class);
        expect(MySqlObjectFactory::ledgerEntry())->toBeInstanceOf(MySqlLedgerEntry::class);
        expect(MySqlObjectFactory::user())->toBeInstanceOf(MySqlUser::class);
    });
});
