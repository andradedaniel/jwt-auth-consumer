<?php

declare(strict_types=1);

return [
    'secret' => env('JWT_SHARED_SECRET', ''),
    'algo' => env('JWT_ALGO', 'HS256'),
    'leeway' => (int) env('JWT_LEEWAY', 60),
    'user_claims' => [
        'id' => 'sub',
        'cpf' => 'cpf',
        'name' => 'name',
        'email' => 'email',
        'phone' => 'phone',
        'management_department' => 'management_department',
        'permissions' => 'permissions',
    ],
];
