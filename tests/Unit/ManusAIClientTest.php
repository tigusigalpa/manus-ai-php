<?php

namespace Tigusigalpa\ManusAI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\RequestInterface;
use Tigusigalpa\ManusAI\Exceptions\AuthenticationException;
use Tigusigalpa\ManusAI\Exceptions\ValidationException;
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;
use Tigusigalpa\ManusAI\ManusAIClient;
use Tigusigalpa\ManusAI\Tests\TestCase;

class ManusAIClientTest extends TestCase
{
    /**
     * @param Response[] $responses
     * @return array{0: ManusAIClient, 1: \stdClass}
     */
    private function createMockClient(array $responses, string $apiKey = 'test-api-key', ?string $bearerToken = null): array
    {
        $historyCapture = new \stdClass();
        $historyCapture->entries = [];
        $history = &$historyCapture->entries;
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($history));

        return [
            new ManusAIClient($apiKey, 'https://api.manus.ai', new Client(['handler' => $stack]), $bearerToken),
            $historyCapture,
        ];
    }

    public function test_constructor_validates_credentials_and_base_uri(): void
    {
        $this->expectException(AuthenticationException::class);
        new ManusAIClient('');
    }

    public function test_constructor_rejects_both_credential_types(): void
    {
        $this->expectException(ValidationException::class);
        new ManusAIClient('api-key', bearerToken: 'access-token');
    }

    public function test_create_task_builds_current_v2_payload(): void
    {
        [$client, $history] = $this->createMockClient([
            new Response(200, [], json_encode(['ok' => true, 'task_id' => 'task_123'])),
        ]);

        $client->createTask('Review this report', [
            'agent_profile' => 'standard',
            'interactive_mode' => false,
            'task_references' => ['abcdefghijklmnopqrstuv'],
            'attachments' => [TaskAttachment::fromFileId('file_123')],
        ]);

        $request = $history->entries[0]['request'];
        $payload = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/v2/task.create', $request->getUri()->getPath());
        $this->assertSame('test-api-key', $request->getHeaderLine('x-manus-api-key'));
        $this->assertSame('', $request->getHeaderLine('Authorization'));
        $this->assertSame('standard', $payload['agent_profile']);
        $this->assertFalse($payload['interactive_mode']);
        $this->assertSame(['abcdefghijklmnopqrstuv'], $payload['message']['task_references']);
        $this->assertSame('Review this report', $payload['message']['content'][0]['text']);
        $this->assertSame('file_123', $payload['message']['content'][1]['file_id']);
    }

    public function test_legacy_interactive_option_maps_to_current_field(): void
    {
        [$client, $history] = $this->createMockClient([
            new Response(200, [], '{"ok":true}'),
        ]);

        $client->createTask('Continue', ['enableAskUser' => true]);
        $payload = json_decode((string) $history->entries[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($payload['interactive_mode']);
        $this->assertArrayNotHasKey('enable_ask_user', $payload);
    }

    public function test_bearer_token_is_sent_instead_of_an_api_key(): void
    {
        [$client, $history] = $this->createMockClient([
            new Response(200, [], '{"ok":true,"task":{"id":"task_123"}}'),
        ], '', 'access-token');

        $client->getTask('task_123');

        $request = $history->entries[0]['request'];
        $this->assertSame('Bearer access-token', $request->getHeaderLine('Authorization'));
        $this->assertSame('', $request->getHeaderLine('x-manus-api-key'));
    }

    public function test_get_task_promotes_the_current_v2_task_envelope(): void
    {
        [$client] = $this->createMockClient([
            new Response(200, [], json_encode([
                'ok' => true,
                'request_id' => 'req_123',
                'task' => [
                    'id' => 'task_123',
                    'status' => 'running',
                    'agent_profile' => 'manus-1.6',
                ],
            ])),
        ]);

        $task = $client->getTask('task_123');

        $this->assertSame('task_123', $task['id']);
        $this->assertSame('running', $task['status']);
        $this->assertSame('req_123', $task['request_id']);
        $this->assertSame('task_123', $task['task']['id']);
    }

    public function test_update_task_maps_the_legacy_visibility_alias(): void
    {
        [$client, $history] = $this->createMockClient([
            new Response(200, [], '{"ok":true,"task_id":"task_123"}'),
        ]);

        $client->updateTask('task_123', ['hideInTaskList' => true]);
        $payload = json_decode((string) $history->entries[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('task_123', $payload['task_id']);
        $this->assertFalse($payload['enable_visible_in_task_list']);
        $this->assertArrayNotHasKey('hide_in_task_list', $payload);
    }

    public function test_file_and_webhook_envelopes_are_promoted(): void
    {
        [$client, $history] = $this->createMockClient([
            new Response(200, [], '{"ok":true,"file":{"id":"file_123","filename":"report.pdf"},"upload_url":"https://upload.example.com"}'),
            new Response(200, [], '{"ok":true,"webhook":{"id":"webhook_123"}}'),
        ]);

        $file = $client->createFile('report.pdf');
        $webhook = $client->createWebhook(['url' => 'https://example.com/manus']);

        $this->assertSame('file_123', $file['file_id']);
        $this->assertSame('report.pdf', $file['filename']);
        $this->assertSame('webhook_123', $webhook['webhook_id']);

        $webhookPayload = json_decode((string) $history->entries[1]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(['url' => 'https://example.com/manus'], $webhookPayload);
    }

    public function test_upload_accepts_all_successful_http_statuses(): void
    {
        [$client] = $this->createMockClient([new Response(204)]);

        $this->assertTrue($client->uploadFileContent('https://upload.example.com/file', 'content', 'text/plain'));
    }

    public function test_structured_api_errors_preserve_request_context(): void
    {
        [$client] = $this->createMockClient([
            new Response(400, [], '{"ok":false,"request_id":"req_456","error":{"code":"invalid_argument","message":"task_id is required"}}'),
        ]);

        try {
            $client->getTask('task_123');
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('req_456', $exception->getMessage());
            $this->assertSame('invalid_argument', $exception->getContext()['error_code']);
        }
    }

    public function test_list_files_fails_without_calling_a_nonexistent_endpoint(): void
    {
        [$client] = $this->createMockClient([]);

        $this->expectException(ValidationException::class);
        $client->listFiles();
    }

    /** @param callable(ManusAIClient): array<string, mixed> $call */
    #[DataProvider('additionalResourceCalls')]
    public function test_additional_api_resources_use_the_expected_endpoint(string $method, string $path, callable $call): void
    {
        [$client, $history] = $this->createMockClient([new Response(200, [], '{"ok":true,"data":[]}')]);

        $call($client);

        $request = $history->entries[0]['request'];
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($path, $request->getUri()->getPath());
    }

    /**
     * @return array<string, array{string, string, callable(ManusAIClient): array<string, mixed>}>
     */
    public static function additionalResourceCalls(): array
    {
        return [
            'create project' => ['POST', '/v2/project.create', static fn (ManusAIClient $client): array => $client->createProject('Reports')],
            'list projects' => ['GET', '/v2/project.list', static fn (ManusAIClient $client): array => $client->listProjects()],
            'list skills' => ['GET', '/v2/skill.list', static fn (ManusAIClient $client): array => $client->listSkills('project_123')],
            'list agents' => ['GET', '/v2/agent.list', static fn (ManusAIClient $client): array => $client->listAgents()],
            'get agent' => ['GET', '/v2/agent.detail', static fn (ManusAIClient $client): array => $client->getAgent('agent_123')],
            'update agent' => ['POST', '/v2/agent.update', static fn (ManusAIClient $client): array => $client->updateAgent('agent_123', ['nickname' => 'Researcher'])],
            'list webhooks' => ['GET', '/v2/webhook.list', static fn (ManusAIClient $client): array => $client->listWebhooks()],
            'list browser clients' => ['GET', '/v2/browser.onlineList', static fn (ManusAIClient $client): array => $client->listOnlineBrowserClients()],
            'webhook public key' => ['GET', '/v2/webhook.publicKey', static fn (ManusAIClient $client): array => $client->getWebhookPublicKey()],
            'list connectors' => ['GET', '/v2/connector.list', static fn (ManusAIClient $client): array => $client->listConnectors()],
            'list usage' => ['GET', '/v2/usage.list', static fn (ManusAIClient $client): array => $client->listUsage(20, 'next')],
            'team usage statistic' => ['GET', '/v2/usage.teamStatistic', static fn (ManusAIClient $client): array => $client->getTeamUsageStatistic('2026-01-01', '2026-01-31')],
            'team usage log' => ['GET', '/v2/usage.teamLog', static fn (ManusAIClient $client): array => $client->listTeamUsageLog(['limit' => 20])],
            'available credits' => ['GET', '/v2/usage.availableCredits', static fn (ManusAIClient $client): array => $client->getAvailableCredits()],
            'website status' => ['GET', '/v2/website.status', static fn (ManusAIClient $client): array => $client->getWebsiteStatus('task_123', 'website_123')],
            'website checkpoints' => ['GET', '/v2/website.listCheckpoints', static fn (ManusAIClient $client): array => $client->listWebsiteCheckpoints('task_123', 'website_123')],
            'publish website' => ['POST', '/v2/website.publish', static fn (ManusAIClient $client): array => $client->publishWebsite('task_123', 'website_123')],
            'update website' => ['POST', '/v2/website.update', static fn (ManusAIClient $client): array => $client->updateWebsite('task_123', 'website_123', ['title' => 'New title'])],
        ];
    }
}
