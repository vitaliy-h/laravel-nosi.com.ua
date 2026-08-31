<?php

return [

    // write authentication events (sign-in, social sign-in, password reset/change) to log_auth
    'enabled' => env('AUTH_LOG_ENABLED', true),

    // how long a login is blocked after a failed sign-in attempt. 0 disables the cooldown
    'cooldown_seconds' => env('AUTH_COOLDOWN_SECONDS', 5),

];
