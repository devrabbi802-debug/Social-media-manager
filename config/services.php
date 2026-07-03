<?php

return [
    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    ],

    'groq' => [
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],
];
