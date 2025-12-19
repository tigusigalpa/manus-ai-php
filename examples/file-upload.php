<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\ManusAI\ManusAIClient;
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

// Initialize client
$apiKey = getenv('MANUS_AI_API_KEY') ?: 'your-api-key-here';
$client = new ManusAIClient($apiKey);

echo "=== Manus AI PHP SDK - File Upload Example ===\n\n";

try {
    // 1. Create a file record
    echo "1. Creating file record...\n";
    $fileResult = $client->createFile('example-document.txt');
    
    echo "File created:\n";
    echo "File ID: {$fileResult['id']}\n";
    echo "Upload URL: {$fileResult['upload_url']}\n";
    echo "Expires at: {$fileResult['upload_expires_at']}\n\n";

    // 2. Upload file content
    echo "2. Uploading file content...\n";
    $fileContent = "This is a sample text file content for Manus AI task.";
    $uploaded = $client->uploadFileContent(
        $fileResult['upload_url'],
        $fileContent,
        'text/plain'
    );

    if ($uploaded) {
        echo "File uploaded successfully!\n\n";
    }

    // 3. Create task with file attachment
    echo "3. Creating task with file attachment...\n";
    $attachment = TaskAttachment::fromFileId($fileResult['id']);
    
    $taskResult = $client->createTask(
        'Analyze the content of the attached file and provide a summary',
        [
            'agentProfile' => 'manus-1.6',
            'attachments' => [$attachment],
        ]
    );

    echo "Task created with attachment:\n";
    echo "Task ID: {$taskResult['task_id']}\n";
    echo "Task URL: {$taskResult['task_url']}\n\n";

    // 4. List files
    echo "4. Listing files...\n";
    $files = $client->listFiles();
    echo "Total files: " . count($files['data']) . "\n";
    foreach ($files['data'] as $file) {
        echo "  - {$file['filename']} ({$file['status']})\n";
    }
    echo "\n";

    echo "=== File upload example completed! ===\n";

} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}
