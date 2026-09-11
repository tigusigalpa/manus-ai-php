<?php

return [
    // API key for direct authentication. Set this or bearer_token, never both.
    'api_key' => env('MANUS_AI_API_KEY'),

    // OAuth access token for Manus Open Apps (optional alternative to api_key).
    'bearer_token' => env('MANUS_AI_BEARER_TOKEN'),

    // Base API URL
    'base_uri' => env('MANUS_AI_BASE_URI', 'https://api.manus.ai'),

    // Default agent profile: standard, lite, or max.
    // Versioned aliases such as manus-1.6 remain accepted by Manus for compatibility.
    'default_agent_profile' => env('MANUS_AI_DEFAULT_AGENT_PROFILE', 'standard'),

    // Default locale (e.g., "en-US", "zh-CN")
    'default_locale' => env('MANUS_AI_DEFAULT_LOCALE', 'en-US'),

    // Default options applied to every createTask() call. Per-call options win.
    'default_options' => [
        'agent_profile' => env('MANUS_AI_DEFAULT_AGENT_PROFILE', 'standard'),
        'locale' => env('MANUS_AI_DEFAULT_LOCALE', 'en-US'),
        'hide_in_task_list' => env('MANUS_AI_HIDE_IN_TASK_LIST', false),
        'share_visibility' => env('MANUS_AI_SHARE_VISIBILITY', 'private'),
        'interactive_mode' => env('MANUS_AI_INTERACTIVE_MODE', false),
    ],

    // Webhook configuration
    'webhook' => [
        'enabled' => env('MANUS_AI_WEBHOOK_ENABLED', false),
        'url' => env('MANUS_AI_WEBHOOK_URL'),
        'events' => ['task_created', 'task_stopped'], // Available events
    ],

    // Request timeouts (in seconds)
    'timeout' => [
        'request' => (int) env('MANUS_AI_TIMEOUT', 30),
        'connect' => (int) env('MANUS_AI_CONNECT_TIMEOUT', 10),
    ],

    // Logging settings
    'logging' => [
        'enabled' => env('MANUS_AI_LOGGING_ENABLED', false),
        'channel' => env('MANUS_AI_LOG_CHANNEL', 'default'),
        'level' => env('MANUS_AI_LOG_LEVEL', 'info'),
    ],
];
