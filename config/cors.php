<?php

return [
    'paths' => ['api/*', 'docs/*', 'fatou.wade/api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Pour le développement, restreignez en production
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];