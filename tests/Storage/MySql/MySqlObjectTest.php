<?php

use PHPLedger\Storage\MySql\MySqlObject;
use PHPLedger\Storage\MySql\MySqlStorage;
use PHPLedger\Services\Config;

beforeAll(function () {
    if (!defined('ROOT_DIR')) {
        define('ROOT_DIR', __DIR__ . '/../../..');
    }
    Config::init(ROOT_DIR . '/tests/config.json');
});

beforeEach(function () {
    $this->db = MySqlStorage::getConnection();
    $this->db->query("DROP TABLE IF EXISTS test_table");
    $this->db->query("
        CREATE TABLE test_table (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL
        )
    ");
});

// Concrete class using the trait
class MySqlTestObject {
    use MySqlObject;

    protected static string $tableName = 'test_table';
    public ?int $id = null;
    public string $name = '';

    public static function getList(array $fieldFilter = []): array { return []; }
    public static function getDefinition(): array { return []; }
    public function update(): bool {
        $stmt = MySqlStorage::getConnection()->prepare("INSERT INTO test_table (id, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=?");
        $stmt->bind_param('iss', $this->id, $this->name, $this->name);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    public function delete(): bool {
        $this->id = null;
        return true;
    }
    public static function getById(int $id): ?self {
        $obj = new self();
        $obj->id = $id;
        $obj->name = "test";
        return $obj;
    }
}

class MySqlTestObjectTestable extends MySqlTestObject {
    public static function copyfromObjectPublic($src, $dest) {
        parent::copyfromObject($src, $dest);
    }

    public static function getWhereFromArrayPublic(array $filter, ?string $table = null) {
        return parent::getWhereFromArray($filter, $table);
    }

    public static function setErrorMessagePublic(string $msg) {
        parent::setErrorMessage($msg);
    }

}

it('constructs with null id if not set', function () {
    $o = new MySqlTestObject();
    expect($o->id)->toBeNull();
});

it('sets and gets id', function () {
    $o = new MySqlTestObject();
    $o->setId(5);
    expect($o->getId())->toBe(5);
});

it('getNextId returns next id from table', function () {
    expect(MySqlTestObject::getNextId())->toBe(1);
    $o = new MySqlTestObject();
    $o->id = 1;
    $o->name = 'A';
    $o->update();
    expect(MySqlTestObject::getNextId())->toBe(2);
});

it('getWhereFromArray builds SQL correctly', function () {
    $filter = [
        'id' => ['operator' => '=', 'value' => 5],
        'name' => ['operator' => 'LIKE', 'value' => 'x%'],
    ];
    $sql = MySqlTestObjectTestable::getWhereFromArrayPublic($filter);
    expect($sql)->toContain('WHERE');
    expect($sql)->toContain("`id` = '5'");
    expect($sql)->toContain("`name` LIKE 'x%'");
});

it('__toString returns class name', function () {
    $o = new MySqlTestObject();
    expect((string)$o)->toBe(MySqlTestObject::class);
});

it('errorMessage and setErrorMessage work', function () {
    MySqlTestObjectTestable::setErrorMessagePublic('err');
    $o = new MySqlTestObjectTestable();
    expect($o->errorMessage())->toBeString();
});

it('clear removes all properties', function () {
    $o = new MySqlTestObject();
    $o->id = 1;
    $o->name = 'x';
    $o->clear();
    expect(isset($o->id))->toBeFalse();
    expect(isset($o->name))->toBeFalse();
});
