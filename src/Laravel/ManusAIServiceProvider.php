<?php

namespace Tigusigalpa\ManusAI\Laravel;

use Illuminate\Support\ServiceProvider;
use Tigusigalpa\ManusAI\ManusAIClient;

class ManusAIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/manus-ai.php' => config_path('manus-ai.php'),
        ], 'manus-ai-config');

        // Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\ManusAITestCommand::class,
                Commands\ManusAITaskCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/manus-ai.php', 'manus-ai');

        // Register singleton
        $this->app->singleton('manus-ai', function ($app) {
            $config = $app['config']->get('manus-ai', []);

            $apiKey = $config['api_key'] ?? null;
            if (!$apiKey) {
                throw new \InvalidArgumentException('Manus AI API key must be set in configuration.');
            }

            $baseUri = $config['base_uri'] ?? 'https://api.manus.ai';

            return new ManusAIClient($apiKey, $baseUri);
        });

        // Register alias
        $this->app->alias('manus-ai', ManusAIClient::class);
    }
}
