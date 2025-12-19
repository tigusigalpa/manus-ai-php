<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\ManusAI\ManusAIClient;

// Initialize client
$apiKey = getenv('MANUS_AI_API_KEY') ?: 'your-api-key-here';
$client = new ManusAIClient($apiKey);

echo "=== Manus AI PHP SDK - Basic Example ===\n\n";

try {
    // 1. Create a simple task
    echo "1. Creating a task...\n";
    $result = $client->createTask('Write a short poem about PHP programming', [
        'agentProfile' => 'manus-1.6',
        'taskMode' => 'chat',
    ]);

    echo "Task created successfully!\n";
    echo "Task ID: {$result['task_id']}\n";
    echo "Task URL: {$result['task_url']}\n\n";

    $taskId = $result['task_id'];

    // 2. Get task details
    echo "2. Fetching task details...\n";
    sleep(3); // Wait a bit for processing
    
    $task = $client->getTask($taskId);
    echo "Status: {$task['status']}\n";
    echo "Model: {$task['model']}\n\n";

    // 3. List recent tasks
    echo "3. Listing recent tasks...\n";
    $tasks = $client->getTasks(['limit' => 5]);
    echo "Total tasks retrieved: " . count($tasks['data']) . "\n";
    foreach ($tasks['data'] as $t) {
        echo "  - Task {$t['id']}: {$t['status']}\n";
    }
    echo "\n";

    // 4. Update task metadata
    echo "4. Updating task title...\n";
    $updated = $client->updateTask($taskId, [
        'title' => 'PHP Poem - Updated',
    ]);
    echo "Updated title: {$updated['task_title']}\n\n";

    echo "=== Example completed successfully! ===\n";

} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}
