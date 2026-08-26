<?php

return [
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => getenv('DB_PORT') ?: '5432',
    'dbname' => getenv('DB_NAME') ?: 'cosmol_reportes',
    'user' => getenv('DB_USER') ?: 'cosmol_user',
    'password' => getenv('DB_PASSWORD') ?: 'cosmol_password',
];
