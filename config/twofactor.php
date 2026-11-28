<?php

/*
| Two-factor authentication settings.
|
| enforce_admin : true  = admins must set up 2FA before using the admin panel.
|                 false = optional for everyone (use this until 2FA works for you).
| window        : how many 30-second steps either side of "now" a code is accepted.
|                 1 = ±30 seconds (production). 4 = ±2 minutes (local development).
*/
return [
    'enforce_admin'         => (bool) env('TWO_FACTOR_ENFORCE_ADMIN', false),
    'window'                => (int) env('TWO_FACTOR_WINDOW', 1),
    'challenge_ttl_minutes' => 10,   // how long a half-finished login may wait on the 2FA page
    'max_attempts'          => 5,    // wrong codes allowed per minute (per user + IP)
];
