<?php

use PHPLedger\Contracts\DataObjectInterface;
use PHPLedger\Contracts\Domain\UserObjectInterface;
use PHPLedger\Domain\User;
use PHPLedger\Services\UserService;
use PHPLedgerTests\Support\MockApplication;

/* Minimal concrete User for instanceof checks */
final class TestUser extends User
{
    public function create(): DataObjectInterface { throw new \Exception('Not implemented'); }
    public function read(int $id): ?DataObjectInterface { throw new \Exception('Not implemented'); }
    public function validate(): bool { return true; }
    public function errorMessage(): string { return ''; }
    public function update(): bool { return true; }
    public function delete(): bool { return true; }
    public static function getNextId(): int { return 1; }
    public static function getList(array $fieldFilter = []): array { return []; }
    public static function getById(int $id): ?self { return null; }
    public static function getByUsername(string $username): ?self { return null; }
    public static function getByToken(string $token): ?self { return null; }
}

/* Tests */

it('returns empty username and null user when session is empty', function () {
    $app = new MockApplication();

    $app->session
        ->shouldReceive('get')
        ->with('user', '')
        ->andReturn(''); // remove ->once()

    $service = new UserService($app);

    expect($service->getCurrentUsername())->toBe('')
        ->and($service->getCurrentUser())->toBeNull();
});

it('returns username and null user when repository returns null', function () {
    $app = new MockApplication();

    $app->session
        ->shouldReceive('get')
        ->with('user', '')
        ->andReturn('john'); // allow multiple calls

    $userRepo = Mockery::mock(UserObjectInterface::class);
    $userRepo->shouldReceive('getByUsername')
        ->with('john')
        ->andReturn(null);

    $app->dataFactory
        ->shouldReceive('user')
        ->andReturn($userRepo);

    $service = new UserService($app);

    expect($service->getCurrentUsername())->toBe('john')
        ->and($service->getCurrentUser())->toBeNull();
});

it('returns User instance when session and repository are valid', function () {
    $app = new MockApplication();

    $app->session
        ->shouldReceive('get')
        ->with('user', '')
        ->andReturn('john');

    $user = new TestUser(); // use real instance instead of mocking final class

    $userRepo = Mockery::mock(UserObjectInterface::class);
    $userRepo->shouldReceive('getByUsername')
        ->with('john')
        ->andReturn($user);

    $app->dataFactory
        ->shouldReceive('user')
        ->andReturn($userRepo);

    $service = new UserService($app);

    expect($service->getCurrentUsername())->toBe('john')
        ->and($service->getCurrentUser())->toBe($user);
});
