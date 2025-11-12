<?php

return [
    // Manus AI API Key (get it from: http://manus.im/app?show_settings=integrations&app_name=api)
    'api_key' => env('MANUS_AI_API_KEY'),

    // Base API URL
    'base_uri' => env('MANUS_AI_BASE_URI', 'https://api.manus.ai'),

    // Default agent profile: manus-1.5, manus-1.5-lite, speed, quality
    'default_agent_profile' => env('MANUS_AI_DEFAULT_AGENT_PROFILE', 'manus-1.5'),

    // Default task mode: chat, adaptive, agent
    'default_task_mode' => env('MANUS_AI_DEFAULT_TASK_MODE', 'agent'),

    // Default locale (e.g., "en-US", "zh-CN")
    'default_locale' => env('MANUS_AI_DEFAULT_LOCALE', 'en-US'),

    // Default task options
    'default_options' => [
        'hideInTaskList' => env('MANUS_AI_HIDE_IN_TASK_LIST', false),
        'createShareableLink' => env('MANUS_AI_CREATE_SHAREABLE_LINK', false),
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
