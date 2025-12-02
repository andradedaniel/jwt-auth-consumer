<?php

declare(strict_types=1);

namespace SpunetGestao\JwtAuthConsumer\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use JsonSerializable;

class JwtUser implements Arrayable, AuthenticatableContract, JsonSerializable
{
    use Authenticatable;

    public mixed $id = null;

    public mixed $cpf = null;

    public mixed $name = null;

    public mixed $email = null;

    public mixed $phone = null;

    public mixed $management_department = null;

    public mixed $permissions = null;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<string, mixed>
     */
    protected array $claims = [];

    /**
     * @param  array<string, mixed>  $claims
     * @param  array<string, string>  $claimMap
     */
    public function __construct(array $claims, array $claimMap)
    {
        $this->claims = $claims;

        foreach ($claimMap as $attribute => $claimKey) {
            $value = Arr::get($claims, $claimKey);
            $this->attributes[$attribute] = $value;

            if (property_exists($this, $attribute)) {
                $this->{$attribute} = $value;
            }
        }
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->attributes['id'] ?? null;
    }

    /**
     * Get the class name for polymorphic relations.
     * Used for owen-it/laravel-auditing package compatibility.
     */
    public function getMorphClass(): string
    {
        return get_class($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }
}
