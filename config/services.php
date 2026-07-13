<?php

return [
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    ],

    'groq' => [
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

    'cerebras' => [
        'model' => env('CEREBRAS_MODEL', 'gpt-oss-120b'),
    ],

    'gemini' => [
        'model' => env('GEMINI_MODEL', 'gemini-3.1-flash-lite'),
    ],

    'clip' => [
        'server_url' => env('CLIP_SERVER_URL', 'http://localhost:8089'),
        'threshold' => env('CLIP_THRESHOLD', 0.7),
        'timeout' => env('CLIP_TIMEOUT', 30),
    ],
];
