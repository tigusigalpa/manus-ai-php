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

        $payload = array_merge([
            'prompt' => $prompt,
            'agentProfile' => 'manus-1.6',
        ], $options);

        return $this->request('POST', '/v1/tasks', $payload);
    }

    /**
     * Get list of tasks with optional filtering and pagination
     */
    public function getTasks(array $filters = []): array
    {
        $query = [];
        
        $allowedFilters = ['after', 'limit', 'order', 'orderBy', 'query', 'status', 'createdAfter', 'createdBefore'];
        
        foreach ($allowedFilters as $filter) {
            if (isset($filters[$filter])) {
                if ($filter === 'status' && is_array($filters[$filter])) {
                    $query[$filter] = $filters[$filter];
                } else {
                    $query[$filter] = $filters[$filter];
                }
            }
        }

        return $this->request('GET', '/v1/tasks', null, $query);
    }

    /**
     * Get a specific task by ID
     */
    public function getTask(string $taskId): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }

        return $this->request('GET', "/v1/tasks/{$taskId}");
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

        $allowedFields = ['title', 'enableShared', 'enableVisibleInTaskList'];
        $payload = array_intersect_key($updates, array_flip($allowedFields));

        if (empty($payload)) {
            throw new ValidationException('No valid update fields provided');
        }

        return $this->request('PATCH', "/v1/tasks/{$taskId}", $payload);
    }

    /**
     * Delete a task
     */
    public function deleteTask(string $taskId): array
    {
        if (empty(trim($taskId))) {
            throw new ValidationException('Task ID cannot be empty');
        }

        return $this->request('DELETE', "/v1/tasks/{$taskId}");
    }

    /**
     * Create a file record and get presigned upload URL
     */
    public function createFile(string $filename): array
    {
        if (empty(trim($filename))) {
            throw new ValidationException('Filename cannot be empty');
        }

        return $this->request('POST', '/v1/files', ['filename' => $filename]);
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
    public function listFiles(): array
    {
        return $this->request('GET', '/v1/files');
    }

    /**
     * Get file details by ID
     */
    public function getFile(string $fileId): array
    {
        if (empty(trim($fileId))) {
            throw new ValidationException('File ID cannot be empty');
        }

        return $this->request('GET', "/v1/files/{$fileId}");
    }

    /**
     * Delete a file
     */
    public function deleteFile(string $fileId): array
    {
        if (empty(trim($fileId))) {
            throw new ValidationException('File ID cannot be empty');
        }

        return $this->request('DELETE', "/v1/files/{$fileId}");
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

        return $this->request('POST', '/v1/webhooks', ['webhook' => $webhook]);
    }

    /**
     * Delete a webhook
     */
    public function deleteWebhook(string $webhookId): bool
    {
        if (empty(trim($webhookId))) {
            throw new ValidationException('Webhook ID cannot be empty');
        }

        $this->request('DELETE', "/v1/webhooks/{$webhookId}");
        return true;
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
                    'Authorization' => $this->apiKey,
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
