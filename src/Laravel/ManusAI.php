<?php

namespace Tigusigalpa\ManusAI\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array createTask(string $prompt, array $options = [])
 * @method static array getTasks(array $filters = [])
 * @method static array getTask(string $taskId)
 * @method static array updateTask(string $taskId, array $updates)
 * @method static array deleteTask(string $taskId)
 * @method static array createFile(string $filename)
 * @method static bool uploadFileContent(string $uploadUrl, string $fileContent, string $contentType = 'application/octet-stream')
 * @method static array listFiles()
 * @method static array getFile(string $fileId)
 * @method static array deleteFile(string $fileId)
 * @method static array createWebhook(array $webhook)
 * @method static bool deleteWebhook(string $webhookId)
 *
 * @see \Tigusigalpa\ManusAI\ManusAIClient
 */
class ManusAI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'manus-ai';
    }
}
