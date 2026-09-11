<?php

namespace Tigusigalpa\ManusAI\Laravel;

use Illuminate\Support\ServiceProvider;
use Tigusigalpa\ManusAI\Contracts\ManusAIClientInterface;
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

        // Register one client for the facade and dependency injection.
        $this->app->singleton(ManusAIClientInterface::class, function ($app) {
            $config = $app['config']->get('manus-ai', []);

            $apiKey = (string) ($config['api_key'] ?? '');
            $bearerToken = (string) ($config['bearer_token'] ?? '');
            if (trim($apiKey) === '' && trim($bearerToken) === '') {
                throw new \InvalidArgumentException('Set MANUS_AI_API_KEY or MANUS_AI_BEARER_TOKEN in configuration.');
            }

            $timeouts = $config['timeout'] ?? [];

            return new ManusAIClient(
                apiKey: $apiKey,
                baseUri: $config['base_uri'] ?? ManusAIClient::DEFAULT_BASE_URI,
                bearerToken: $bearerToken,
                timeout: (int) ($timeouts['request'] ?? ManusAIClient::DEFAULT_TIMEOUT),
                connectTimeout: (int) ($timeouts['connect'] ?? ManusAIClient::DEFAULT_CONNECT_TIMEOUT),
                defaultTaskOptions: $config['default_options'] ?? [],
            );
        });

        $this->app->alias(ManusAIClientInterface::class, 'manus-ai');
        $this->app->alias(ManusAIClientInterface::class, ManusAIClient::class);
    }
}
