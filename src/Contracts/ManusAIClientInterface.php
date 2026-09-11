<?php

namespace Tigusigalpa\ManusAI\Contracts;

/**
 * Public Manus API v2 client contract.
 *
 * All API methods return decoded JSON responses as associative arrays.
 */
interface ManusAIClientInterface
{
    /** @param array<string, mixed> $options @return array<string, mixed> */
    public function createTask(string $prompt, array $options = []): array;

    /** @param array<string, mixed> $filters @return array<string, mixed> */
    public function getTasks(array $filters = []): array;

    /** @return array<string, mixed> */
    public function getTask(string $taskId): array;

    /** @param array<string, mixed> $updates @return array<string, mixed> */
    public function updateTask(string $taskId, array $updates): array;

    /** @return array<string, mixed> */
    public function deleteTask(string $taskId): array;

    /** @return array<string, mixed> */
    public function createFile(string $filename): array;

    public function uploadFileContent(string $uploadUrl, string $fileContent, string $contentType = 'application/octet-stream'): bool;

    /** @deprecated Manus API v2 does not expose a file-list endpoint. */
    public function listFiles(int $limit = 0, string $cursor = ''): never;

    /** @return array<string, mixed> */
    public function getFile(string $fileId): array;

    /** @return array<string, mixed> */
    public function deleteFile(string $fileId): array;

    /** @param array<string, mixed> $webhook @return array<string, mixed> */
    public function createWebhook(array $webhook): array;

    public function deleteWebhook(string $webhookId): bool;

    /** @return array<string, mixed> */
    public function listMessages(string $taskId, int $limit = 50, string $cursor = '', string $order = 'desc', bool $verbose = false): array;

    /** @param array<int, array<string, mixed>> $attachments @param array<string, mixed> $options @return array<string, mixed> */
    public function sendMessage(string $taskId, string $message, array $attachments = [], array $options = []): array;

    /** @return array<string, mixed> */
    public function stopTask(string $taskId): array;

    /** @param array<string, mixed> $input @return array<string, mixed> */
    public function confirmAction(string $taskId, string $eventId, array $input): array;

    /** @return array<string, mixed> */
    public function createProject(string $name, string $instruction = ''): array;

    /** @return array<string, mixed> */
    public function listProjects(): array;

    /** @return array<string, mixed> */
    public function listSkills(string $projectId = ''): array;

    /** @return array<string, mixed> */
    public function listAgents(): array;

    /** @return array<string, mixed> */
    public function getAgent(string $agentId): array;

    /** @param array<string, mixed> $updates @return array<string, mixed> */
    public function updateAgent(string $agentId, array $updates): array;

    /** @return array<string, mixed> */
    public function listWebhooks(): array;

    /** @return array<string, mixed> */
    public function listOnlineBrowserClients(): array;

    /** @return array<string, mixed> */
    public function getWebhookPublicKey(): array;

    /** @return array<string, mixed> */
    public function listConnectors(): array;

    /** @return array<string, mixed> */
    public function listUsage(int $limit = 0, string $cursor = ''): array;

    /** @return array<string, mixed> */
    public function getTeamUsageStatistic(string $startDate = '', string $endDate = ''): array;

    /** @param array<string, mixed> $filters @return array<string, mixed> */
    public function listTeamUsageLog(array $filters = []): array;

    /** @return array<string, mixed> */
    public function getAvailableCredits(): array;

    /** @return array<string, mixed> */
    public function getWebsiteStatus(string $taskId, string $websiteId): array;

    /** @return array<string, mixed> */
    public function listWebsiteCheckpoints(string $taskId, string $websiteId): array;

    /** @return array<string, mixed> */
    public function publishWebsite(string $taskId, string $websiteId, string $visibility = ''): array;

    /** @param array<string, mixed> $updates @return array<string, mixed> */
    public function updateWebsite(string $taskId, string $websiteId, array $updates): array;
}
