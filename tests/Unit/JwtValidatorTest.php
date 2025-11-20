<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use SpunetGestao\JwtAuthConsumer\Exceptions\InvalidTokenException;
use SpunetGestao\JwtAuthConsumer\Exceptions\TokenExpiredException;
use SpunetGestao\JwtAuthConsumer\Services\JwtValidator;

it('validates a token and returns claims', function (): void {
    $validator = app(JwtValidator::class);

    $claims = $validator->validate(createToken());

    expect($claims)
        ->toHaveKey('sub', 'token-user')
        ->toHaveKey('email', 'token.user@example.com');
});

it('throws when token is expired', function (): void {
    $validator = app(JwtValidator::class);

    $token = createToken([
        'exp' => time() - 120,
    ]);

    $validator->validate($token);
})->throws(TokenExpiredException::class);

it('throws when signature is invalid', function (): void {
    $validator = app(JwtValidator::class);

    $token = JWT::encode(
        createPayload(),
        'invalid-secret',
        'HS256'
    );

    $validator->validate($token);
})->throws(InvalidTokenException::class);

it('throws when required subject claim is missing', function (): void {
    $validator = app(JwtValidator::class);

    $token = createToken([
        'sub' => null,
    ]);

    $validator->validate($token);
})->throws(InvalidTokenException::class);

/**
 * @return array<string, mixed>
 */
function createPayload(): array
{
    $now = time();

    return [
        'sub' => 'token-user',
        'cpf' => '99999999999',
        'name' => 'Token User',
        'email' => 'token.user@example.com',
        'phone' => '+55 11 90000-0000',
        'permissions' => ['read'],
        'management_department' => ['id' => 2],
        'iat' => $now,
        'exp' => $now + 600,
    ];
}

function createToken(array $overrides = []): string
{
    return JWT::encode(
        array_merge(createPayload(), $overrides),
        'test-secret',
        'HS256'
    );
}
