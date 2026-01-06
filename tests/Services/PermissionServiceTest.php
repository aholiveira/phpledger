<?php

/**
 * @author Antonio Oliveira
 * @copyright Copyright (c) 2026 Antonio Oliveira
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
 */

use PHPLedger\Services\PermissionService;
use PHPLedger\Contracts\Domain\UserObjectInterface;

/*
|--------------------------------------------------------------------------
| Fake user
|--------------------------------------------------------------------------
*/

final class FakeUser implements UserObjectInterface
{
    public function __construct(private int $role) {}

    public function getProperty(string $name, mixed $default = null): mixed
    {
        return $name === 'role' ? $this->role : $default;
    }

    public function setProperty(string $name, mixed $value): void {}
    public function setPassword(string $value) {}
    public function getPassword(): ?string
    {
        return null;
    }
    public function verifyPassword(string $password): bool
    {
        return false;
    }
    public function createToken(): string
    {
        return '';
    }
    public function isTokenValid(string $token): bool
    {
        return false;
    }
    public function resetPassword(): bool
    {
        return false;
    }
    public function hasRole(int $role): bool
    {
        return $this->role === $role;
    }

    public function validate(): bool
    {
        return true;
    }
    public function errorMessage(): string
    {
        return '';
    }
    public function create(): self
    {
        return $this;
    }
    public function read(int $id): ?self
    {
        return $this;
    }
    public function update(): bool
    {
        return true;
    }
    public function delete(): bool
    {
        return true;
    }
    public static function getNextId(): int
    {
        return 1;
    }
    public static function getList(array $fieldFilter = []): array
    {
        return [];
    }
    public static function getById(int $id): ?self
    {
        return null;
    }
    public static function getByUsername(string $username): ?self
    {
        return null;
    }
    public static function getByToken(string $token): ?self
    {
        return null;
    }
}

/*
|--------------------------------------------------------------------------
| Tests
|--------------------------------------------------------------------------
*/

it('allows read only for RO role', function () {
    $svc = new PermissionService(new FakeUser(UserObjectInterface::USER_ROLE_RO));

    expect($svc->canRead())->toBeTrue()
        ->and($svc->canWrite())->toBeFalse()
        ->and($svc->isAdmin())->toBeFalse();
});

it('allows read and write for RW role', function () {
    $svc = new PermissionService(new FakeUser(UserObjectInterface::USER_ROLE_RW));

    expect($svc->canRead())->toBeTrue()
        ->and($svc->canWrite())->toBeTrue()
        ->and($svc->isAdmin())->toBeFalse();
});

it('allows full access for admin role', function () {
    $svc = new PermissionService(new FakeUser(UserObjectInterface::USER_ROLE_ADM));

    expect($svc->canRead())->toBeTrue()
        ->and($svc->canWrite())->toBeTrue()
        ->and($svc->isAdmin())->toBeTrue();
});

it('defaults to read-only when role is missing', function () {
    $user = new class implements UserObjectInterface {
        public function getProperty(string $name, mixed $default = null): mixed
        {
            return $default;
        }
        public function setProperty(string $name, mixed $value): void {}
        public function setPassword(string $value) {}
        public function getPassword(): ?string
        {
            return null;
        }
        public function verifyPassword(string $password): bool
        {
            return false;
        }
        public function createToken(): string
        {
            return '';
        }
        public function isTokenValid(string $token): bool
        {
            return false;
        }
        public function resetPassword(): bool
        {
            return false;
        }
        public function hasRole(int $role): bool
        {
            return false;
        }
        public function validate(): bool
        {
            return true;
        }
        public function errorMessage(): string
        {
            return '';
        }
        public function create(): self
        {
            return $this;
        }
        public function read(int $id): ?self
        {
            return $this;
        }
        public function update(): bool
        {
            return true;
        }
        public function delete(): bool
        {
            return true;
        }
        public static function getNextId(): int
        {
            return 1;
        }
        public static function getList(array $fieldFilter = []): array
        {
            return [];
        }
        public static function getById(int $id): ?self
        {
            return null;
        }
        public static function getByUsername(string $username): ?self
        {
            return null;
        }
        public static function getByToken(string $token): ?self
        {
            return null;
        }
    };

    $svc = new PermissionService($user);

    expect($svc->canRead())->toBeTrue()
        ->and($svc->canWrite())->toBeFalse()
        ->and($svc->isAdmin())->toBeFalse();
});
