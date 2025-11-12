<?php

namespace Tigusigalpa\ManusAI\Tests\Unit\Helpers;

use Tigusigalpa\ManusAI\Helpers\WebhookHandler;
use Tigusigalpa\ManusAI\Tests\TestCase;

class WebhookHandlerTest extends TestCase
{
    public function test_parse_payload_success(): void
    {
        $json = json_encode([
            'event_type' => 'task_created',
            'task_detail' => ['task_id' => 'task_123'],
        ]);

        $payload = WebhookHandler::parsePayload($json);

        $this->assertEquals('task_created', $payload['event_type']);
        $this->assertEquals('task_123', $payload['task_detail']['task_id']);
    }

    public function test_parse_payload_throws_exception_for_invalid_json(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        WebhookHandler::parsePayload('invalid json');
    }

    public function test_is_task_created(): void
    {
        $payload = ['event_type' => 'task_created'];
        $this->assertTrue(WebhookHandler::isTaskCreated($payload));

        $payload = ['event_type' => 'task_stopped'];
        $this->assertFalse(WebhookHandler::isTaskCreated($payload));
    }

    public function test_is_task_stopped(): void
    {
        $payload = ['event_type' => 'task_stopped'];
        $this->assertTrue(WebhookHandler::isTaskStopped($payload));

        $payload = ['event_type' => 'task_created'];
        $this->assertFalse(WebhookHandler::isTaskStopped($payload));
    }

    public function test_is_task_completed(): void
    {
        $payload = [
            'event_type' => 'task_stopped',
            'task_detail' => ['stop_reason' => 'finish'],
        ];
        $this->assertTrue(WebhookHandler::isTaskCompleted($payload));

        $payload = [
            'event_type' => 'task_stopped',
            'task_detail' => ['stop_reason' => 'ask'],
        ];
        $this->assertFalse(WebhookHandler::isTaskCompleted($payload));
    }

    public function test_is_task_asking_for_input(): void
    {
        $payload = [
            'event_type' => 'task_stopped',
            'task_detail' => ['stop_reason' => 'ask'],
        ];
        $this->assertTrue(WebhookHandler::isTaskAskingForInput($payload));

        $payload = [
            'event_type' => 'task_stopped',
            'task_detail' => ['stop_reason' => 'finish'],
        ];
        $this->assertFalse(WebhookHandler::isTaskAskingForInput($payload));
    }

    public function test_get_task_detail(): void
    {
        $taskDetail = ['task_id' => 'task_123'];
        $payload = ['task_detail' => $taskDetail];

        $result = WebhookHandler::getTaskDetail($payload);

        $this->assertEquals($taskDetail, $result);
    }

    public function test_get_attachments(): void
    {
        $attachments = [
            ['file_name' => 'report.pdf', 'url' => 'https://example.com/report.pdf'],
        ];
        
        $payload = [
            'task_detail' => ['attachments' => $attachments],
        ];

        $result = WebhookHandler::getAttachments($payload);

        $this->assertEquals($attachments, $result);
    }
}
