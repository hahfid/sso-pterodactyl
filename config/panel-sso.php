<?php

return [
    'secret' => env('PANEL_SSO_SECRET'),
    'token' => [
        'length' => 48,
        'lifetime' => 60,
    ],
];
