<?php

namespace Tigusigalpa\ManusAI\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tigusigalpa\ManusAI\Exceptions\AuthenticationException;
use Tigusigalpa\ManusAI\Exceptions\ValidationException;
use Tigusigalpa\ManusAI\ManusAIClient;
use Tigusigalpa\ManusAI\Tests\TestCase;

class ManusAIClientTest extends TestCase
{
    private function createMockClient(array $responses): ManusAIClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        return new ManusAIClient('test-api-key', 'https://api.manus.ai', $httpClient);
    }

    public function test_constructor_throws_exception_for_empty_api_key(): void
    {
        $this->expectException(AuthenticationException::class);
        new ManusAIClient('');
    }

    public function test_create_task_success(): void
    {
        $mockResponse = [
            'task_id' => 'task_123',
            'task_title' => 'Test Task',
            'task_url' => 'https://manus.im/app/task_123',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $result = $client->createTask('Test prompt');

        $this->assertEquals('task_123', $result['task_id']);
        $this->assertEquals('Test Task', $result['task_title']);
    }

    public function test_create_task_with_empty_prompt_throws_exception(): void
    {
        $client = $this->createMockClient([]);
        
        $this->expectException(ValidationException::class);
        $client->createTask('');
    }

    public function test_get_task_success(): void
    {
        $mockResponse = [
            'id' => 'task_123',
            'status' => 'completed',
            'model' => 'manus-1.6',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $result = $client->getTask('task_123');

        $this->assertEquals('task_123', $result['id']);
        $this->assertEquals('completed', $result['status']);
    }

    public function test_get_tasks_success(): void
    {
        $mockResponse = [
            'object' => 'list',
            'data' => [
                ['id' => 'task_1', 'status' => 'pending'],
                ['id' => 'task_2', 'status' => 'running'],
            ],
            'has_more' => false,
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $result = $client->getTasks(['limit' => 2]);

        $this->assertCount(2, $result['data']);
        $this->assertEquals('task_1', $result['data'][0]['id']);
    }

    public function test_update_task_success(): void
    {
        $mockResponse = [
            'task_id' => 'task_123',
            'task_title' => 'Updated Title',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $result = $client->updateTask('task_123', ['title' => 'Updated Title']);

        $this->assertEquals('Updated Title', $result['task_title']);
    }

    public function test_delete_task_success(): void
    {
        $mockResponse = [
            'id' => 'task_123',
            'object' => 'task.deleted',
            'deleted' => true,
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $result = $client->deleteTask('task_123');

        $this->assertTrue($result['deleted']);
    }

    public function test_create_file_success(): void
    {
        $mockResponse = [
            'id' => 'file_123',
            'filename' => 'test.txt',
            'upload_url' => 'https://s3.amazonaws.com/...',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $result = $client->createFile('test.txt');

        $this->assertEquals('file_123', $result['id']);
        $this->assertEquals('test.txt', $result['filename']);
    }

    public function test_upload_file_content_success(): void
    {
        $client = $this->createMockClient([
            new Response(200),
        ]);

        $result = $client->uploadFileContent('https://example.com/upload', 'content');

        $this->assertTrue($result);
    }

    public function test_create_webhook_success(): void
    {
        $mockResponse = [
            'webhook_id' => 'webhook_123',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $result = $client->createWebhook([
            'url' => 'https://example.com/webhook',
            'events' => ['task_created'],
        ]);

        $this->assertEquals('webhook_123', $result['webhook_id']);
    }

    public function test_delete_webhook_success(): void
    {
        $client = $this->createMockClient([
            new Response(204),
        ]);

        $result = $client->deleteWebhook('webhook_123');

        $this->assertTrue($result);
    }
}
