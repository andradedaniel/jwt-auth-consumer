<?php

declare(strict_types=1);

namespace SpunetGestao\JwtAuthConsumer;

use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use SpunetGestao\JwtAuthConsumer\Http\Middleware\AccessControl;
use SpunetGestao\JwtAuthConsumer\Http\Middleware\JwtAuth;
use SpunetGestao\JwtAuthConsumer\Services\JwtValidator;

class JwtAuthConsumerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings and configuration.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jwt-auth-consumer.php', 'jwt-auth-consumer');

        $this->app->singleton(JwtValidator::class, function (Container $app): JwtValidator {
            $config = $app['config']->get('jwt-auth-consumer', []);

            return new JwtValidator(
                $config['secret'] ?? '',
                $config['algo'] ?? 'HS256',
                (int) ($config['leeway'] ?? 0),
                $config['user_claims'] ?? []
            );
        });
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/jwt-auth-consumer.php' => $this->app->configPath('jwt-auth-consumer.php'),
        ], 'config');

        /** @var Router $router */
        $router = $this->app['router'];

        $router->aliasMiddleware('jwt.auth', JwtAuth::class);
        $router->aliasMiddleware('access-control', AccessControl::class);
    }
}
