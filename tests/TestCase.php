<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SpunetGestao\JwtAuthConsumer\JwtAuthConsumerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            JwtAuthConsumerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('jwt-auth-consumer.secret', 'test-secret');
        $app['config']->set('jwt-auth-consumer.algo', 'HS256');
        $app['config']->set('jwt-auth-consumer.leeway', 0);
        $app['config']->set('jwt-auth-consumer.user_claims', [
            'id' => 'sub',
            'cpf' => 'cpf',
            'name' => 'name',
            'email' => 'email',
            'phone' => 'phone',
            'management_department' => 'management_department',
            'permissions' => 'permissions',
        ]);
    }
}
