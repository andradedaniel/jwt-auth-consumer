<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    if (! Route::has('jwt.protected')) {
        Route::middleware('jwt.auth')->get('/protected', function (Request $request) {
            return response()->json([
                'auth_user' => Auth::user() !== null,
                'user' => $request->user()?->toArray(),
            ]);
        })->name('jwt.protected');
    }

    if (! Route::has('jwt.me')) {
        Route::middleware('jwt.auth')->get('/me', function () {
            return Auth::user();
        })->name('jwt.me');
    }

    if (! Route::has('jwt.restricted')) {
        Route::middleware(['jwt.auth', 'access-control:ATENDIMENTO_CRIAR_PRESENCIAL|ATENDIMENTO_CRIAR_TELEFONICO'])
            ->get('/restricted', fn () => response()->json(['allowed' => true]))
            ->name('jwt.restricted');
    }
});

it('authenticates a valid token', function (): void {
    $token = makeToken();

    $response = $this->getJson('/protected', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('auth_user', true)
        ->assertJsonPath('user.email', 'jane.doe@example.com')
        ->assertJsonPath('user.permissions.0', 'manage-users');
});

it('rejects when the authorization header is missing', function (): void {
    $response = $this->getJson('/protected');

    $response->assertStatus(401)
        ->assertExactJson(['message' => 'Invalid token']);
});

it('rejects expired tokens', function (): void {
    $token = makeToken([
        'exp' => time() - 60,
    ]);

    $response = $this->getJson('/protected', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(401)
        ->assertExactJson(['message' => 'Token expired']);
});

it('rejects tokens with invalid signature', function (): void {
    $token = JWT::encode(
        basePayload(),
        'wrong-secret',
        'HS256'
    );

    $response = $this->getJson('/protected', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(401)
        ->assertExactJson(['message' => 'Invalid token']);
});

it('maps claims to the JwtUser instance', function (): void {
    $token = makeToken([
        'name' => 'John Example',
        'permissions' => ['view-reports', 'edit-accounts'],
    ]);

    $response = $this->getJson('/protected', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('user.name', 'John Example')
        ->assertJsonPath('user.permissions.1', 'edit-accounts');
});

it('serializes the JwtUser when returned directly', function (): void {
    $token = makeToken();

    $response = $this->getJson('/me', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('email', 'jane.doe@example.com')
        ->assertJsonPath('permissions.0', 'manage-users');
});

it('allows access when at least one permission matches', function (): void {
    $token = makeToken([
        'permissions' => ['manage-users', 'ATENDIMENTO_CRIAR_PRESENCIAL'],
    ]);

    $response = $this->getJson('/restricted', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertOk()
        ->assertJsonPath('allowed', true);
});

it('denies access when permissions do not match', function (): void {
    $token = makeToken([
        'permissions' => ['ANOTHER_PERMISSION'],
    ]);

    $response = $this->getJson('/restricted', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(403)
        ->assertExactJson(['message' => 'Forbidden']);
});

/**
 * @return array<string, mixed>
 */
function basePayload(): array
{
    $now = time();

    return [
        'sub' => 'user-123',
        'cpf' => '12345678900',
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com',
        'phone' => '+55 11 99999-9999',
        'management_department' => [
            'id' => 1,
            'name' => 'Management',
        ],
        'permissions' => ['manage-users'],
        'iat' => $now,
        'exp' => $now + 3600,
    ];
}

function makeToken(array $overrides = []): string
{
    return JWT::encode(
        array_merge(basePayload(), $overrides),
        'test-secret',
        'HS256'
    );
}
