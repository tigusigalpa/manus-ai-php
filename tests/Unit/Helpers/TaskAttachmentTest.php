<?php

namespace Tigusigalpa\ManusAI\Tests\Unit\Helpers;

use Tigusigalpa\ManusAI\Helpers\TaskAttachment;
use Tigusigalpa\ManusAI\Tests\TestCase;

class TaskAttachmentTest extends TestCase
{
    public function test_from_file_id(): void
    {
        $attachment = TaskAttachment::fromFileId('file_123');

        $this->assertEquals('file_id', $attachment['type']);
        $this->assertEquals('file_123', $attachment['file_id']);
    }

    public function test_from_url(): void
    {
        $url = 'https://example.com/file.pdf';
        $attachment = TaskAttachment::fromUrl($url);

        $this->assertEquals('url', $attachment['type']);
        $this->assertEquals($url, $attachment['url']);
    }

    public function test_from_base64(): void
    {
        $base64Data = base64_encode('test content');
        $mimeType = 'text/plain';
        
        $attachment = TaskAttachment::fromBase64($base64Data, $mimeType);

        $this->assertEquals('data', $attachment['type']);
        $this->assertEquals($base64Data, $attachment['data']);
        $this->assertEquals($mimeType, $attachment['mime_type']);
    }
}
