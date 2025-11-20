<?php

declare(strict_types=1);

namespace SpunetGestao\JwtAuthConsumer\Services;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Arr;
use JsonException;
use SpunetGestao\JwtAuthConsumer\Exceptions\InvalidTokenException;
use SpunetGestao\JwtAuthConsumer\Exceptions\TokenExpiredException;
use UnexpectedValueException;

class JwtValidator
{
    /**
     * @param  array<string, string>  $claimMap
     */
    public function __construct(
        private readonly string $secret,
        private readonly string $algorithm,
        private readonly int $leeway,
        private readonly array $claimMap
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function validate(string $token): array
    {
        if ($this->secret === '') {
            throw new InvalidTokenException('JWT secret not configured');
        }

        if ($token === '') {
            throw new InvalidTokenException('Invalid token');
        }

        $previousLeeway = JWT::$leeway;
        JWT::$leeway = $this->leeway;

        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
        } catch (ExpiredException $exception) {
            throw new TokenExpiredException(previous: $exception);
        } catch (SignatureInvalidException|BeforeValidException|UnexpectedValueException $exception) {
            throw new InvalidTokenException(previous: $exception);
        } finally {
            JWT::$leeway = $previousLeeway;
        }

        try {
            /** @var array<string, mixed> $claims */
            $claims = json_decode(
                json_encode($decoded, flags: JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new InvalidTokenException(previous: $exception);
        }

        $identifierClaim = $this->claimMap['id'] ?? 'sub';
        $identifier = Arr::get($claims, $identifierClaim);

        if ($identifier === null || $identifier === '') {
            throw new InvalidTokenException('Invalid token payload');
        }

        return $claims;
    }
}
