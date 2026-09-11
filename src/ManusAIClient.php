<?php

namespace Tigusigalpa\ManusAI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Tigusigalpa\ManusAI\Contracts\ManusAIClientInterface;
use Tigusigalpa\ManusAI\Exceptions\AuthenticationException;
use Tigusigalpa\ManusAI\Exceptions\ManusAIException;
use Tigusigalpa\ManusAI\Exceptions\ValidationException;

/**
 * Client for Manus API v2.
 *
 * Methods return API responses as associative arrays. Detail methods also expose
 * the nested resource at the top level for convenient access.
 */
class ManusAIClient implements ManusAIClientInterface
{
    public const DEFAULT_BASE_URI = 'https://api.manus.ai';
    public const DEFAULT_TIMEOUT = 30;
    public const DEFAULT_CONNECT_TIMEOUT = 10;
    private const MAX_RESPONSE_BYTES = 4 * 1024 * 1024;

    private Client $http;
    private string $apiKey;
    private string $bearerToken;
    private string $baseUri;

    /** @var array<string, mixed> */
    private array $defaultTaskOptions;

    /**
     * @param array<string, mixed> $defaultTaskOptions
     */
    public function __construct(
        string $apiKey = '',
        string $baseUri = self::DEFAULT_BASE_URI,
        ?Client $httpClient = null,
        ?string $bearerToken = null,
        int $timeout = self::DEFAULT_TIMEOUT,
        int $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT,
        array $defaultTaskOptions = [],
    ) {
        $this->apiKey = trim($apiKey);
        $this->bearerToken = trim((string) $bearerToken);

        if ($this->apiKey === '' && $this->bearerToken === '') {
            throw new AuthenticationException('API key or bearer token cannot be empty');
        }
        if ($this->apiKey !== '' && $this->bearerToken !== '') {
            throw new ValidationException('API key and bearer token cannot be used together');
        }

        $this->baseUri = $this->validateBaseUri($baseUri);
        if ($timeout <= 0 || $connectTimeout <= 0) {
            throw new ValidationException('Request timeouts must be greater than zero');
        }

        $this->http = $httpClient ?? new Client([
            'base_uri' => $this->baseUri,
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
        ]);
        $this->defaultTaskOptions = $defaultTaskOptions;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function createTask(string $prompt, array $options = []): array
    {
        if (trim($prompt) === '') {
            throw new ValidationException('Task prompt cannot be empty');
        }

        $options = array_replace($this->defaultTaskOptions, $options);
        $message = [
            'content' => [[
                'type' => 'text',
                'text' => $prompt,
            ]],
        ];
        $payload = [];

        $this->copyOption($options, $payload, 'agent_profile', 'agentProfile');
        $this->copyOption($options, $payload, 'locale');
        $this->copyOption($options, $payload, 'hide_in_task_list', 'hideInTaskList');
        $this->copyOption($options, $payload, 'share_visibility', 'shareVisibility');
        $this->copyOption($options, $payload, 'title');
        $this->copyOption($options, $payload, 'project_id', 'projectId');
        $this->copyOption($options, $payload, 'interactive_mode', 'interactiveMode', 'enable_ask_user', 'enableAskUser');
        $this->copyOption($options, $payload, 'structured_output_schema', 'structuredOutputSchema');

        $this->copyOption($options, $message, 'connectors');
        $this->copyOption($options, $message, 'enable_skills', 'enableSkills');
        $this->copyOption($options, $message, 'force_skills', 'forceSkills');
        $this->copyOption($options, $message, 'task_references', 'taskReferences');

        if (array_key_exists('attachments', $options) && $options['attachments'] !== null) {
            if (!is_array($options['attachments'])) {
                throw new ValidationException('Attachments must be an array of attachment arrays');
            }
            foreach ($options['attachments'] as $attachment) {
                if (!is_array($attachment)) {
                    throw new ValidationException('Each attachment must be an array');
                }
                $message['content'][] = $attachment;
            }
        }

        $payload['message'] = $message;

        return $this->request('POST', '/v2/task.create', $payload);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getTasks(array $filters = []): array
    {
        $query = [];
        foreach (['cursor', 'limit', 'order', 'scope', 'agent_id', 'project_id', 'oauth_client_id', 'api_key_id'] as $filter) {
            if (array_key_exists($filter, $filters) && $filters[$filter] !== null) {
                $query[$filter] = $filters[$filter];
            }
        }

        return $this->request('GET', '/v2/task.list', null, $query);
    }

    /** @return array<string, mixed> */
    public function getTask(string $taskId): array
    {
        $this->requireIdentifier($taskId, 'Task ID');

        return $this->promoteResource(
            $this->request('GET', '/v2/task.detail', null, ['task_id' => $taskId]),
            'task',
        );
    }

    /**
     * @param array<string, mixed> $updates
     * @return array<string, mixed>
     */
    public function updateTask(string $taskId, array $updates): array
    {
        $this->requireIdentifier($taskId, 'Task ID');
        if ($updates === []) {
            throw new ValidationException('Updates array cannot be empty');
        }

        $payload = ['task_id' => $taskId];
        $hasUpdates = false;
        $hasUpdates = $this->copyOption($updates, $payload, 'title') || $hasUpdates;
        $hasUpdates = $this->copyOption($updates, $payload, 'share_visibility', 'shareVisibility') || $hasUpdates;
        $hasUpdates = $this->copyOption($updates, $payload, 'enable_visible_in_task_list', 'enableVisibleInTaskList') || $hasUpdates;

        $legacyVisibility = $this->optionValue($updates, 'hide_in_task_list', 'hideInTaskList');
        if (!$hasUpdates && $legacyVisibility['found']) {
            $payload['enable_visible_in_task_list'] = !(bool) $legacyVisibility['value'];
            $hasUpdates = true;
        }
        if (!$hasUpdates) {
            throw new ValidationException('No valid update fields provided');
        }

        return $this->request('POST', '/v2/task.update', $payload);
    }

    /** @return array<string, mixed> */
    public function deleteTask(string $taskId): array
    {
        $this->requireIdentifier($taskId, 'Task ID');

        return $this->request('POST', '/v2/task.delete', ['task_id' => $taskId]);
    }

    /** @return array<string, mixed> */
    public function createFile(string $filename): array
    {
        $this->requireIdentifier($filename, 'Filename');
        $response = $this->promoteResource(
            $this->request('POST', '/v2/file.upload', ['filename' => $filename]),
            'file',
        );

        if (isset($response['id']) && !isset($response['file_id'])) {
            $response['file_id'] = $response['id'];
        }

        return $response;
    }

    public function uploadFileContent(string $uploadUrl, string $fileContent, string $contentType = 'application/octet-stream'): bool
    {
        $this->requireIdentifier($uploadUrl, 'Upload URL');
        $contentType = trim($contentType) ?: 'application/octet-stream';

        try {
            $response = $this->http->put($uploadUrl, [
                'body' => $fileContent,
                'http_errors' => false,
                'headers' => ['Content-Type' => $contentType],
            ]);
        } catch (GuzzleException $exception) {
            throw new ManusAIException('Failed to upload file content: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->throwForResponse($response, 'File upload failed');
        }

        return true;
    }

    /**
     * Manus API v2 has no file-list endpoint.
     *
     * @deprecated Retained only so existing code fails safely instead of requesting a non-existent endpoint.
     * @return never
     */
    public function listFiles(int $limit = 0, string $cursor = ''): never
    {
        throw new ValidationException('Manus API v2 does not provide a file-list endpoint');
    }

    /** @return array<string, mixed> */
    public function getFile(string $fileId): array
    {
        $this->requireIdentifier($fileId, 'File ID');
        $response = $this->promoteResource(
            $this->request('GET', '/v2/file.detail', null, ['file_id' => $fileId]),
            'file',
        );

        if (isset($response['id']) && !isset($response['file_id'])) {
            $response['file_id'] = $response['id'];
        }

        return $response;
    }

    /** @return array<string, mixed> */
    public function deleteFile(string $fileId): array
    {
        $this->requireIdentifier($fileId, 'File ID');

        return $this->request('POST', '/v2/file.delete', ['file_id' => $fileId]);
    }

    /**
     * @param array<string, mixed> $webhook
     * @return array<string, mixed>
     */
    public function createWebhook(array $webhook): array
    {
        if (!isset($webhook['url']) || !is_string($webhook['url'])) {
            throw new ValidationException('Webhook URL is required');
        }
        $this->requireIdentifier($webhook['url'], 'Webhook URL');

        $response = $this->promoteResource(
            $this->request('POST', '/v2/webhook.create', ['url' => $webhook['url']]),
            'webhook',
        );

        if (isset($response['id']) && !isset($response['webhook_id'])) {
            $response['webhook_id'] = $response['id'];
        }

        return $response;
    }

    public function deleteWebhook(string $webhookId): bool
    {
        $this->requireIdentifier($webhookId, 'Webhook ID');
        $this->request('POST', '/v2/webhook.delete', ['webhook_id' => $webhookId]);

        return true;
    }

    /** @return array<string, mixed> */
    public function listMessages(string $taskId, int $limit = 50, string $cursor = '', string $order = 'desc', bool $verbose = false): array
    {
        $this->requireIdentifier($taskId, 'Task ID');
        $query = ['task_id' => $taskId];
        if ($limit > 0) {
            $query['limit'] = $limit;
        }
        if (trim($cursor) !== '') {
            $query['cursor'] = $cursor;
        }
        if (trim($order) !== '') {
            $query['order'] = $order;
        }
        if ($verbose) {
            $query['verbose'] = 'true';
        }

        return $this->request('GET', '/v2/task.listMessages', null, $query);
    }

    /**
     * @param array<int, array<string, mixed>> $attachments
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function sendMessage(string $taskId, string $message, array $attachments = [], array $options = []): array
    {
        $this->requireIdentifier($taskId, 'Task ID');
        if (trim($message) === '') {
            throw new ValidationException('Message cannot be empty');
        }

        $content = [['type' => 'text', 'text' => $message]];
        foreach ($attachments as $attachment) {
            if (!is_array($attachment)) {
                throw new ValidationException('Each attachment must be an array');
            }
            $content[] = $attachment;
        }

        $payload = ['task_id' => $taskId, 'message' => ['content' => $content]];
        $this->copyOption($options, $payload, 'agent_profile', 'agentProfile');
        $this->copyOption($options, $payload['message'], 'connectors');
        $this->copyOption($options, $payload['message'], 'enable_skills', 'enableSkills');
        $this->copyOption($options, $payload['message'], 'force_skills', 'forceSkills');
        $this->copyOption($options, $payload['message'], 'task_references', 'taskReferences');

        return $this->request('POST', '/v2/task.sendMessage', $payload);
    }

    /** @return array<string, mixed> */
    public function stopTask(string $taskId): array
    {
        $this->requireIdentifier($taskId, 'Task ID');

        return $this->request('POST', '/v2/task.stop', ['task_id' => $taskId]);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function confirmAction(string $taskId, string $eventId, array $input): array
    {
        $this->requireIdentifier($taskId, 'Task ID');
        $this->requireIdentifier($eventId, 'Event ID');

        return $this->request('POST', '/v2/task.confirmAction', [
            'task_id' => $taskId,
            'event_id' => $eventId,
            'input' => $input,
        ]);
    }

    /** @return array<string, mixed> */
    public function createProject(string $name, string $instruction = ''): array
    {
        $this->requireIdentifier($name, 'Project name');
        $payload = ['name' => $name];
        if (trim($instruction) !== '') {
            $payload['instruction'] = $instruction;
        }

        return $this->request('POST', '/v2/project.create', $payload);
    }

    /** @return array<string, mixed> */
    public function listProjects(): array
    {
        return $this->request('GET', '/v2/project.list');
    }

    /** @return array<string, mixed> */
    public function listSkills(string $projectId = ''): array
    {
        return $this->request('GET', '/v2/skill.list', null, trim($projectId) === '' ? [] : ['project_id' => $projectId]);
    }

    /** @return array<string, mixed> */
    public function listAgents(): array
    {
        return $this->request('GET', '/v2/agent.list');
    }

    /** @return array<string, mixed> */
    public function getAgent(string $agentId): array
    {
        $this->requireIdentifier($agentId, 'Agent ID');

        return $this->request('GET', '/v2/agent.detail', null, ['agent_id' => $agentId]);
    }

    /**
     * @param array<string, mixed> $updates
     * @return array<string, mixed>
     */
    public function updateAgent(string $agentId, array $updates): array
    {
        $this->requireIdentifier($agentId, 'Agent ID');
        $payload = ['agent_id' => $agentId];
        $hasUpdates = $this->copyOption($updates, $payload, 'nickname')
            || $this->copyOption($updates, $payload, 'about');
        if (!$hasUpdates) {
            throw new ValidationException('At least one agent update field is required');
        }

        return $this->request('POST', '/v2/agent.update', $payload);
    }

    /** @return array<string, mixed> */
    public function listWebhooks(): array
    {
        return $this->request('GET', '/v2/webhook.list');
    }

    /** @return array<string, mixed> */
    public function listOnlineBrowserClients(): array
    {
        return $this->request('GET', '/v2/browser.onlineList');
    }

    /** @return array<string, mixed> */
    public function getWebhookPublicKey(): array
    {
        return $this->request('GET', '/v2/webhook.publicKey');
    }

    /** @return array<string, mixed> */
    public function listConnectors(): array
    {
        return $this->request('GET', '/v2/connector.list');
    }

    /** @return array<string, mixed> */
    public function listUsage(int $limit = 0, string $cursor = ''): array
    {
        return $this->request('GET', '/v2/usage.list', null, $this->paginationQuery($limit, $cursor));
    }

    /** @return array<string, mixed> */
    public function getTeamUsageStatistic(string $startDate = '', string $endDate = ''): array
    {
        return $this->request('GET', '/v2/usage.teamStatistic', null, $this->dateRangeQuery($startDate, $endDate));
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function listTeamUsageLog(array $filters = []): array
    {
        $query = [];
        foreach (['limit', 'cursor', 'start_date', 'end_date', 'sort_by', 'is_asc'] as $filter) {
            if (array_key_exists($filter, $filters) && $filters[$filter] !== null) {
                $query[$filter] = $filters[$filter];
            }
        }

        return $this->request('GET', '/v2/usage.teamLog', null, $query);
    }

    /** @return array<string, mixed> */
    public function getAvailableCredits(): array
    {
        return $this->request('GET', '/v2/usage.availableCredits');
    }

    /** @return array<string, mixed> */
    public function getWebsiteStatus(string $taskId, string $websiteId): array
    {
        return $this->request('GET', '/v2/website.status', null, $this->websitePayload($taskId, $websiteId));
    }

    /** @return array<string, mixed> */
    public function listWebsiteCheckpoints(string $taskId, string $websiteId): array
    {
        return $this->request('GET', '/v2/website.listCheckpoints', null, $this->websitePayload($taskId, $websiteId));
    }

    /** @return array<string, mixed> */
    public function publishWebsite(string $taskId, string $websiteId, string $visibility = ''): array
    {
        $payload = $this->websitePayload($taskId, $websiteId);
        if (trim($visibility) !== '') {
            $payload['visibility'] = $visibility;
        }

        return $this->request('POST', '/v2/website.publish', $payload);
    }

    /**
     * @param array<string, mixed> $updates
     * @return array<string, mixed>
     */
    public function updateWebsite(string $taskId, string $websiteId, array $updates): array
    {
        $payload = $this->websitePayload($taskId, $websiteId);
        $hasUpdates = $this->copyOption($updates, $payload, 'title')
            || $this->copyOption($updates, $payload, 'visibility');
        if (!$hasUpdates) {
            throw new ValidationException('At least one website update field is required');
        }

        return $this->request('POST', '/v2/website.update', $payload);
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function request(string $method, string $endpoint, ?array $body = null, array $query = []): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $headers[$this->bearerToken === '' ? 'x-manus-api-key' : 'Authorization'] = $this->bearerToken === ''
            ? $this->apiKey
            : 'Bearer ' . $this->bearerToken;

        $options = ['headers' => $headers, 'http_errors' => false];
        if ($body !== null) {
            $options['json'] = $body;
        }
        if ($query !== []) {
            $options['query'] = $query;
        }

        try {
            // Use an absolute URI so injected Guzzle clients do not need their own base_uri.
            $response = $this->http->request($method, $this->baseUri . $endpoint, $options);
        } catch (GuzzleException $exception) {
            throw new ManusAIException('API request failed: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->throwForResponse($response, 'API request failed');
        }

        $content = $this->readResponseContent($response);
        if ($content === '') {
            return [];
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new ManusAIException('Invalid JSON response: ' . $exception->getMessage(), 0, $exception);
        }
        if (!is_array($data)) {
            throw new ManusAIException('Invalid JSON response: expected an object');
        }
        if (($data['ok'] ?? true) === false) {
            $this->throwApiError($response->getStatusCode(), $data, 'API request failed');
        }

        return $data;
    }

    private function validateBaseUri(string $baseUri): string
    {
        $baseUri = rtrim(trim($baseUri), '/');
        $parts = parse_url($baseUri);
        if ($baseUri === '' || $parts === false || !isset($parts['scheme'], $parts['host']) || !in_array($parts['scheme'], ['http', 'https'], true)) {
            throw new ValidationException('Base URI must be an absolute HTTP or HTTPS URL');
        }

        return $baseUri;
    }

    private function requireIdentifier(string $value, string $name): void
    {
        if (trim($value) === '') {
            throw new ValidationException("{$name} cannot be empty");
        }
    }

    /**
     * @param array<string, mixed> $source
     * @param array<string, mixed> $target
     */
    private function copyOption(array $source, array &$target, string $canonicalKey, string ...$aliases): bool
    {
        $option = $this->optionValue($source, $canonicalKey, ...$aliases);
        if (!$option['found']) {
            return false;
        }
        $target[$canonicalKey] = $option['value'];

        return true;
    }

    /**
     * @param array<string, mixed> $options
     * @return array{found: bool, value: mixed}
     */
    private function optionValue(array $options, string ...$keys): array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $options) && $options[$key] !== null) {
                return ['found' => true, 'value' => $options[$key]];
            }
        }

        return ['found' => false, 'value' => null];
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    private function promoteResource(array $response, string $resource): array
    {
        if (!isset($response[$resource]) || !is_array($response[$resource])) {
            return $response;
        }

        return array_replace($response, $response[$resource]);
    }

    /** @return array<string, mixed> */
    private function paginationQuery(int $limit, string $cursor): array
    {
        $query = [];
        if ($limit > 0) {
            $query['limit'] = $limit;
        }
        if (trim($cursor) !== '') {
            $query['cursor'] = $cursor;
        }

        return $query;
    }

    /** @return array<string, mixed> */
    private function dateRangeQuery(string $startDate, string $endDate): array
    {
        $query = [];
        if (trim($startDate) !== '') {
            $query['start_date'] = $startDate;
        }
        if (trim($endDate) !== '') {
            $query['end_date'] = $endDate;
        }

        return $query;
    }

    /** @return array<string, string> */
    private function websitePayload(string $taskId, string $websiteId): array
    {
        $this->requireIdentifier($taskId, 'Task ID');
        $this->requireIdentifier($websiteId, 'Website ID');

        return ['task_id' => $taskId, 'website_id' => $websiteId];
    }

    private function readResponseContent(ResponseInterface $response): string
    {
        $body = $response->getBody();
        if ($body->getSize() !== null && $body->getSize() > self::MAX_RESPONSE_BYTES) {
            throw new ManusAIException('Response body exceeds ' . self::MAX_RESPONSE_BYTES . ' bytes');
        }

        if ($body->isSeekable()) {
            $body->rewind();
        }
        $content = $body->read(self::MAX_RESPONSE_BYTES + 1);
        if (strlen($content) > self::MAX_RESPONSE_BYTES) {
            throw new ManusAIException('Response body exceeds ' . self::MAX_RESPONSE_BYTES . ' bytes');
        }

        return $content;
    }

    /** @return never */
    private function throwForResponse(ResponseInterface $response, string $prefix): never
    {
        $content = $this->readResponseContent($response);
        $data = json_decode($content, true);
        $this->throwApiError($response->getStatusCode(), is_array($data) ? $data : [], $prefix, $content);
    }

    /**
     * @param array<string, mixed> $data
     * @return never
     */
    private function throwApiError(int $statusCode, array $data, string $prefix, string $fallbackBody = ''): never
    {
        $error = is_array($data['error'] ?? null) ? $data['error'] : [];
        $message = is_string($error['message'] ?? null) ? $error['message'] : ($fallbackBody !== '' ? $fallbackBody : 'Unknown error');
        $requestId = is_string($data['request_id'] ?? null) ? $data['request_id'] : null;
        $context = [
            'status_code' => $statusCode,
            'request_id' => $requestId,
            'error_code' => $error['code'] ?? null,
        ];
        $description = $prefix . ': ' . $message . ($requestId !== null ? " (request_id: {$requestId})" : '');

        if ($statusCode === 401 || $statusCode === 403) {
            throw new AuthenticationException($description, $statusCode, null, $context);
        }
        if ($statusCode === 400 || ($error['code'] ?? null) === 'invalid_argument') {
            throw new ValidationException($description, $statusCode, null, $context);
        }

        throw new ManusAIException($description, $statusCode, null, $context);
    }
}
