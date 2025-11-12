<?php

namespace Tigusigalpa\ManusAI\Contracts;

interface ManusAIClientInterface
{
    /**
     * Create a new task
     *
     * @param string $prompt The task prompt or instruction
     * @param array $options Additional options (agentProfile, attachments, taskMode, etc.)
     * @return array Task details including task_id, task_title, task_url
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function createTask(string $prompt, array $options = []): array;

    /**
     * Get list of tasks with optional filtering and pagination
     *
     * @param array $filters Query parameters (after, limit, order, orderBy, query, status, etc.)
     * @return array Tasks list with pagination info
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function getTasks(array $filters = []): array;

    /**
     * Get a specific task by ID
     *
     * @param string $taskId The task ID
     * @return array Task details including status, output, credit_usage
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function getTask(string $taskId): array;

    /**
     * Update a task's metadata
     *
     * @param string $taskId The task ID
     * @param array $updates Fields to update (title, enableShared, enableVisibleInTaskList)
     * @return array Updated task details
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function updateTask(string $taskId, array $updates): array;

    /**
     * Delete a task
     *
     * @param string $taskId The task ID
     * @return array Deletion confirmation
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function deleteTask(string $taskId): array;

    /**
     * Create a file record and get presigned upload URL
     *
     * @param string $filename Name of the file to upload
     * @return array File details including upload_url
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function createFile(string $filename): array;

    /**
     * Upload file content to presigned URL
     *
     * @param string $uploadUrl Presigned S3 URL
     * @param string $fileContent File content (binary or string)
     * @param string $contentType MIME type of the file
     * @return bool Success status
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function uploadFileContent(string $uploadUrl, string $fileContent, string $contentType = 'application/octet-stream'): bool;

    /**
     * List files (returns 10 most recent)
     *
     * @return array List of files
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function listFiles(): array;

    /**
     * Get file details by ID
     *
     * @param string $fileId The file ID
     * @return array File details
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function getFile(string $fileId): array;

    /**
     * Delete a file
     *
     * @param string $fileId The file ID
     * @return array Deletion confirmation
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function deleteFile(string $fileId): array;

    /**
     * Create a webhook
     *
     * @param array $webhook Webhook configuration (url, events, etc.)
     * @return array Webhook details including webhook_id
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function createWebhook(array $webhook): array;

    /**
     * Delete a webhook
     *
     * @param string $webhookId The webhook ID
     * @return bool Success status
     * @throws \Tigusigalpa\ManusAI\Exceptions\ManusAIException
     */
    public function deleteWebhook(string $webhookId): bool;
}
