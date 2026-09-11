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
 * @method static never listFiles(int $limit = 0, string $cursor = '')
 * @method static array getFile(string $fileId)
 * @method static array deleteFile(string $fileId)
 * @method static array createWebhook(array $webhook)
 * @method static bool deleteWebhook(string $webhookId)
 * @method static array listMessages(string $taskId, int $limit = 50, string $cursor = '', string $order = 'desc', bool $verbose = false)
 * @method static array sendMessage(string $taskId, string $message, array $attachments = [], array $options = [])
 * @method static array stopTask(string $taskId)
 * @method static array confirmAction(string $taskId, string $eventId, array $input)
 * @method static array createProject(string $name, string $instruction = '')
 * @method static array listProjects()
 * @method static array listSkills(string $projectId = '')
 * @method static array listAgents()
 * @method static array getAgent(string $agentId)
 * @method static array updateAgent(string $agentId, array $updates)
 * @method static array listWebhooks()
 * @method static array listOnlineBrowserClients()
 * @method static array getWebhookPublicKey()
 * @method static array listConnectors()
 * @method static array listUsage(int $limit = 0, string $cursor = '')
 * @method static array getTeamUsageStatistic(string $startDate = '', string $endDate = '')
 * @method static array listTeamUsageLog(array $filters = [])
 * @method static array getAvailableCredits()
 * @method static array getWebsiteStatus(string $taskId, string $websiteId)
 * @method static array listWebsiteCheckpoints(string $taskId, string $websiteId)
 * @method static array publishWebsite(string $taskId, string $websiteId, string $visibility = '')
 * @method static array updateWebsite(string $taskId, string $websiteId, array $updates)
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
