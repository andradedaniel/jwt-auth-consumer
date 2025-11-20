<?php

declare(strict_types=1);

namespace SpunetGestao\JwtAuthConsumer\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use SpunetGestao\JwtAuthConsumer\Auth\JwtUser;
use SpunetGestao\JwtAuthConsumer\Exceptions\AccessDeniedException;

class AccessControl
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next, string $permissions = '')
    {
        try {
            /** @var mixed $user */
            $user = Auth::user();

            if (! $user instanceof JwtUser) {
                $this->deny();
            }

            $required = $this->parsePermissions($permissions);

            if ($required === []) {
                return $next($request);
            }

            $userPermissions = Arr::wrap($user->permissions);

            foreach ($required as $permission) {
                if (in_array($permission, $userPermissions, true)) {
                    return $next($request);
                }
            }
            $this->deny();
        } catch (AccessDeniedException $exception) {
            return $this->forbidden();
        }

        return $this->forbidden();
    }

    /**
     * @return array<int, string>
     */
    private function parsePermissions(string $permissions): array
    {
        return array_values(array_filter(array_map('trim', explode('|', $permissions)), static fn ($value) => $value !== ''));
    }

    private function deny(): void
    {
        throw new AccessDeniedException;
    }

    private function forbidden(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Forbidden',
        ], 403);
    }
}
