<?php

namespace Tigusigalpa\ManusAI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Tigusigalpa\ManusAI\Contracts\ManusAIClientInterface;
use Tigusigalpa\ManusAI\Exceptions\AuthenticationException;
use Tigusigalpa\ManusAI\Exceptions\ManusAIException;
use Tigusigalpa\ManusAI\Exceptions\ValidationException;

class ManusAIClient implements ManusAIClientInterface
{
    private Client $http;
    private string $apiKey;
    private string $baseUri;

    public function __construct(string $apiKey, string $baseUri = 'https://api.manus.ai', ?Client $httpClient = null)
    {
        if (empty(trim($apiKey))) {
            throw new AuthenticationException('API key cannot be empty');
        }

        $this->apiKey = $apiKey;
        $this->baseUri = rtrim($baseUri, '/');
        $this->http = $httpClient ?: new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Create a new task
     */
    public function createTask(string $prompt, array $options = []): array
    {
        if (empty(trim($prompt))) {
            throw new ValidationException('Task prompt cannot be empty');
        }

        $message = [
            'content' => [
                [
                    'type' => 'text',
                    'text' => $prompt,
                ],
            ],
        ];

        $payload = [
            'message' => $message,
        ];

        if (isset($options['agent_profile'])) {
            $payload['agent_profile'] = $options['agent_profile'];
        } elseif (isset($options['agentProfile'])) {
            $payload['agent_profile'] = $options['agentProfile'];
        }

        if (isset($options['locale'])) {
            $payload['locale'] = $options['locale'];
        }

        if (isset($options['hide_in_task_list'])) {
            $payload['hide_in_task_list'] = $options['hide_in_task_list'];
        } elseif (isset($options['hideInTaskList'])) {
            $payload['hide_in_task_list'] = $options['hideInTaskList'];
        }

        if (isset($options['share_visibility'])) {
            $payload['share_visibility'] = $options['share_visibility'];
        } elseif (isset($options['shareVisibility'])) {
            $payload['share_visibility'] = $options['shareVisibility'];
        }

        if (isset($options['title'])) {
            $payload['title'] = $options['title'];
        }

        if (isset($options['project_id'])) {
            $payload['project_id'] = $options['project_id'];
        } elseif (isset($options['projectId'])) {
            $payload['project_id'] = $options['projectId'];
        }

        if (isset($options['enable_ask_user'])) {
            $payload['enable_ask_user'] = $options['enable_ask_user'];
        } elseif (isset($options['enableAskUser'])) {
            $payload['enable_ask_user'] = $options['enableAskUser'];
        }

        if (isset($options['connectors'])) {
            $message['connectors'] = $options['connectors'];
        }

        if (isset($options['enable_skills'])) {
            $message['enable_skills'] = $options['enable_skills'];
        } elseif (isset($options['enableSkills'])) {
            $message['enable_skills'] = $options['enableSkills'];
        }

        if (isset($options['force_skills'])) {
            $message['force_skills'] = $options['force_skills'];
        } elseif (isset($options['forceSkills'])) {
            $message['force_skills'] = $options['forceSkills'];
        }

        if (isset($options['attachments']) && is_array($options['attachments'])) {
            foreach ($options['attachments'] as $attachment) {
                $message['content'][] = $attachment;
            }
        }

        $payload['message'] = $message;

        return $this->request('POST', '/v2/task.create', $payload);
    }

    /**
     * Get list of tasks with optional filtering and pagination
     */
    public function getTasks(array $filters = []): array
    {
        $query = [];
        
        $allowedFilters = ['cursor', 'limit', 'order', 'scope', 'agent_id', 'project_id'];
        
        foreach ($allowedFilters as $filter) {
            if (isset($filters[$filter])) {
                $query[$filter] = $filters[$filter];
            }
        }

        return $this->request('GET', '/v2/task.list', null, $query);
    }

    /**
     * Get a specific task by ID
     */
    public function getTask(string $taskId): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }

        return $this->request('GET', '/v2/task.detail', null, ['task_id' => $taskId]);
    }

    /**
     * Update a task's metadata
     */
    public function updateTask(string $taskId, array $updates): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }

        if (empty($updates)) {
            throw new ValidationException('Updates array cannot be empty');
        }

        $payload = ['task_id' => $taskId];
        $hasUpdates = false;

        if (isset($updates['title'])) {
            $payload['title'] = $updates['title'];
            $hasUpdates = true;
        }

        if (isset($updates['share_visibility'])) {
            $payload['share_visibility'] = $updates['share_visibility'];
            $hasUpdates = true;
        } elseif (isset($updates['shareVisibility'])) {
            $payload['share_visibility'] = $updates['shareVisibility'];
            $hasUpdates = true;
        }

        if (isset($updates['hide_in_task_list'])) {
            $payload['hide_in_task_list'] = $updates['hide_in_task_list'];
            $hasUpdates = true;
        } elseif (isset($updates['hideInTaskList'])) {
            $payload['hide_in_task_list'] = $updates['hideInTaskList'];
            $hasUpdates = true;
        }

        if (!$hasUpdates) {
            throw new ValidationException('No valid update fields provided');
        }

        return $this->request('POST', '/v2/task.update', $payload);
    }

    /**
     * Delete a task
     */
    public function deleteTask(string $taskId): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }

        return $this->request('POST', '/v2/task.delete', ['task_id' => $taskId]);
    }

    /**
     * Create a file record and get presigned upload URL
     */
    public function createFile(string $filename): array
    {
        if (empty(trim($filename))) {
            throw new ValidationException('Filename cannot be empty');
        }

        return $this->request('POST', '/v2/file.upload', ['filename' => $filename]);
    }

    /**
     * Upload file content to presigned URL
     */
    public function uploadFileContent(string $uploadUrl, string $fileContent, string $contentType = 'application/octet-stream'): bool
    {
        if (empty(trim($uploadUrl))) {
            throw new ValidationException('Upload URL cannot be empty');
        }

        try {
            $response = $this->http->put($uploadUrl, [
                'body' => $fileContent,
                'headers' => [
                    'Content-Type' => $contentType,
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            throw new ManusAIException(
                'Failed to upload file content: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * List files (returns 10 most recent)
     */
    public function listFiles(int $limit = 0, string $cursor = ''): array
    {
        $query = [];
        if ($limit > 0) {
            $query['limit'] = $limit;
        }
        if (!empty($cursor)) {
            $query['cursor'] = $cursor;
        }

        return $this->request('GET', '/v2/file.list', null, $query);
    }

    /**
     * Get file details by ID
     */
    public function getFile(string $fileId): array
    {
        if (empty(trim($fileId))) {
            throw new ValidationException('File ID cannot be empty');
        }

        return $this->request('GET', '/v2/file.detail', null, ['file_id' => $fileId]);
    }

    /**
     * Delete a file
     */
    public function deleteFile(string $fileId): array
    {
        if (empty(trim($fileId))) {
            throw new ValidationException('File ID cannot be empty');
        }

        return $this->request('POST', '/v2/file.delete', ['file_id' => $fileId]);
    }

    /**
     * Create a webhook
     */
    public function createWebhook(array $webhook): array
    {
        if (empty($webhook)) {
            throw new ValidationException('Webhook configuration cannot be empty');
        }

        if (!isset($webhook['url'])) {
            throw new ValidationException('Webhook URL is required');
        }

        $payload = [
            'url' => $webhook['url'],
            'events' => $webhook['events'] ?? [],
        ];

        return $this->request('POST', '/v2/webhook.create', $payload);
    }

    /**
     * Delete a webhook
     */
    public function deleteWebhook(string $webhookId): bool
    {
        if (empty(trim($webhookId))) {
            throw new ValidationException('Webhook ID cannot be empty');
        }

        $this->request('POST', '/v2/webhook.delete', ['webhook_id' => $webhookId]);
        return true;
    }

    /**
     * List messages for a task
     */
    public function listMessages(string $taskId, int $limit = 50, string $cursor = '', string $order = 'desc', bool $verbose = false): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }

        $query = ['task_id' => $taskId];
        
        if ($limit > 0) {
            $query['limit'] = $limit;
        }
        if (!empty($cursor)) {
            $query['cursor'] = $cursor;
        }
        if (!empty($order)) {
            $query['order'] = $order;
        }
        if ($verbose) {
            $query['verbose'] = 'true';
        }

        return $this->request('GET', '/v2/task.listMessages', null, $query);
    }

    /**
     * Send a message to a task
     */
    public function sendMessage(string $taskId, string $message, array $attachments = []): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }
        if (empty(trim($message))) {
            throw new ValidationException('Message cannot be empty');
        }

        $content = [
            [
                'type' => 'text',
                'text' => $message,
            ],
        ];

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $content[] = $attachment;
            }
        }

        $payload = [
            'task_id' => $taskId,
            'message' => [
                'content' => $content,
            ],
        ];

        return $this->request('POST', '/v2/task.sendMessage', $payload);
    }

    /**
     * Stop a running task
     */
    public function stopTask(string $taskId): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }

        return $this->request('POST', '/v2/task.stop', ['task_id' => $taskId]);
    }

    /**
     * Confirm an action waiting for user input
     */
    public function confirmAction(string $taskId, string $eventId, array $input): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }
        if (empty(trim($eventId))) {
            throw new ValidationException('Event ID cannot be empty');
        }

        $payload = [
            'task_id' => $taskId,
            'event_id' => $eventId,
            'input' => $input,
        ];

        return $this->request('POST', '/v2/task.confirmAction', $payload);
    }

    /**
     * Make HTTP request to Manus AI API
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array|null $body Request body
     * @param array $query Query parameters
     * @return array Response data
     * @throws ManusAIException
     */
    private function request(string $method, string $endpoint, ?array $body = null, array $query = []): array
    {
        try {
            $options = [
                'headers' => [
                    'x-manus-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ];

            if ($body !== null) {
                $options['json'] = $body;
            }

            if (!empty($query)) {
                $options['query'] = $query;
            }

            $response = $this->http->request($method, $endpoint, $options);
            
            $statusCode = $response->getStatusCode();
            
            // Handle 204 No Content
            if ($statusCode === 204) {
                return [];
            }

            $content = (string) $response->getBody();
            
            if (empty($content)) {
                return [];
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ManusAIException('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;

        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();
            $message = $e->getMessage();

            if ($statusCode === 401 || $statusCode === 403) {
                throw new AuthenticationException(
                    'Authentication failed: ' . $message,
                    $statusCode,
                    $e
                );
            }

            if ($statusCode === 400) {
                throw new ValidationException(
                    'Validation error: ' . $message,
                    $statusCode,
                    $e
                );
            }

            throw new ManusAIException(
                'API request failed: ' . $message,
                $statusCode,
                $e
            );
        }
    }
}
