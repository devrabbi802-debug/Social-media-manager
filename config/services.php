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

    'zernio' => [
        'base_url' => env('ZERNIO_BASE_URL', 'https://zernio.com/api/v1'),
        'webhook_secret' => env('ZERNIO_WEBHOOK_SECRET'),
    ],

    'clip' => [
        'server_url' => env('CLIP_SERVER_URL', 'http://localhost:8089'),
        'threshold' => env('CLIP_THRESHOLD', 0.8),
        'timeout' => env('CLIP_TIMEOUT', 30),
    ],

    // Public URL for product images sent to Facebook/Zernio.
    // Set to Ngrok tunnel or public domain for local dev (e.g. https://abc123.ngrok.io).
    // Defaults to APP_URL which works in production with a real domain.
    'media_url' => env('MEDIA_URL', config('app.url')),
];
