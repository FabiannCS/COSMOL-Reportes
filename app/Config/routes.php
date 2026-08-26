<?php

return [
    'GET' => [
        '/' => ['AuthController', 'showLogin', []],
        '/login' => ['AuthController', 'showLogin', []],
        '/dashboard' => ['AuthController', 'dashboard', []],
    ],
    'POST' => [
        '/login' => ['AuthController', 'login', []],
    ],
];
