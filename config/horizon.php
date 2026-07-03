<?php

use Illuminate\Support\Str;

return [

    "name" => env("HORIZON_NAME", "SocialBoost AI"),

    "domain" => env("HORIZON_DOMAIN"),

    "path" => env("HORIZON_PATH", "horizon"),

    "use" => "default",

    "prefix" => env(
        "HORIZON_PREFIX",
        Str::slug(env("APP_NAME", "laravel"), "_")."_horizon:"
    ),

    "middleware" => ["web"],

    "waits" => [
        "redis:facebook" => 60,
        "redis:default" => 60,
    ],

    "trim" => [
        "recent" => 60,
        "pending" => 60,
        "completed" => 60,
        "recent_failed" => 10080,
        "failed" => 10080,
        "monitored" => 10080,
    ],

    "silenced" => [],

    "silenced_tags" => [],

    "metrics" => [
        "trim_snapshots" => [
            "job" => 24,
            "queue" => 24,
        ],
    ],

    "fast_termination" => false,

    "memory_limit" => 256,

    "defaults" => [
        "supervisor-1" => [
            "connection" => "redis",
            "queue" => ["facebook"],
            "balance" => "auto",
            "autoScalingStrategy" => "time",
            "minProcesses" => 1,
            "maxProcesses" => 10,
            "maxTime" => 3600,
            "maxJobs" => 0,
            "memory" => 256,
            "tries" => 5,
            "backoff" => 60,
            "timeout" => 90,
            "nice" => 0,
        ],
    ],

    "environments" => [
        "production" => [
            "supervisor-1" => [
                "maxProcesses" => 10,
                "balanceMaxShift" => 1,
                "balanceCooldown" => 3,
            ],
        ],

        "local" => [
            "supervisor-1" => [
                "maxProcesses" => 5,
                "minProcesses" => 1,
                "balanceMaxShift" => 1,
                "balanceCooldown" => 3,
            ],
        ],
    ],

    "watch" => [
        "app",
        "bootstrap",
        "config/**/*.php",
        "database/**/*.php",
        "public/**/*.php",
        "resources/**/*.php",
        "routes",
        "composer.lock",
        "composer.json",
        ".env",
    ],
];
