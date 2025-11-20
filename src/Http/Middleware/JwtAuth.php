<?php

declare(strict_types=1);

namespace SpunetGestao\JwtAuthConsumer\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SpunetGestao\JwtAuthConsumer\Auth\JwtUser;
use SpunetGestao\JwtAuthConsumer\Exceptions\InvalidTokenException;
use SpunetGestao\JwtAuthConsumer\Exceptions\TokenExpiredException;
use SpunetGestao\JwtAuthConsumer\Services\JwtValidator;

class JwtAuth
{
    public function __construct(
        private readonly JwtValidator $validator
    ) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $authorization = (string) $request->headers->get('Authorization', '');

        if (! str_starts_with($authorization, 'Bearer ')) {
            return $this->unauthorized('Invalid token');
        }

        $token = trim(substr($authorization, 7));

        if ($token === '') {
            return $this->unauthorized('Invalid token');
        }

        try {
            $claims = $this->validator->validate($token);
        } catch (TokenExpiredException $exception) {
            return $this->unauthorized('Token expired');
        } catch (InvalidTokenException $exception) {
            return $this->unauthorized('Invalid token');
        }

        $user = new JwtUser(
            $claims,
            config('jwt-auth-consumer.user_claims', [])
        );

        Auth::setUser($user);
        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
        ], 401);
    }
}
