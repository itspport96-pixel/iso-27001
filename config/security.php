<?php
declare(strict_types=1);

return [
    'csrf_token_name'   => $_ENV['CSRF_TOKEN_NAME']   ?? 'csrf_token',
    'session_lifetime'  => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
    'password_algo'     => $_ENV['PASSWORD_ALGO']     ?? PASSWORD_ARGON2ID,
    'password_cost'     => (int)($_ENV['PASSWORD_COST'] ?? 12),
    'rate_limits'       => [
        'login'  => [
            'max_attempts' => (int)($_ENV['LOGIN_MAX_ATTEMPTS']      ?? 5),
            'decay_minutes'=> (int)($_ENV['LOGIN_LOCKOUT_MINUTES']  ?? 15),
        ],
        'upload' => [
            'max_files'    => (int)($_ENV['UPLOAD_MAX_FILES_PER_HOUR'] ?? 10),
            'window'       => 3600,
        ],
        'api'    => [
            'max_requests' => (int)($_ENV['API_MAX_REQUESTS_PER_MINUTE'] ?? 100),
            'window'       => 60,
        ],
    ],
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options'        => 'DENY',
        'X-XSS-Protection'       => '1; mode=block',
        'Strict-Transport-Security'=> 'max-age=31536000; includeSubDomains',
    ],
];
