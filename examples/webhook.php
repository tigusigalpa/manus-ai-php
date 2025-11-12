<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\ManusAI\ManusAIClient;

// Initialize client
$apiKey = getenv('MANUS_AI_API_KEY') ?: 'your-api-key-here';
$client = new ManusAIClient($apiKey);

echo "=== Manus AI PHP SDK - Webhook Example ===\n\n";

try {
    // 1. Create a webhook
    echo "1. Creating webhook...\n";
    $webhookUrl = 'https://your-domain.com/webhook/manus-ai';
    
    $webhookConfig = [
        'url' => $webhookUrl,
        'events' => ['task_created', 'task_stopped'],
    ];

    $result = $client->createWebhook($webhookConfig);
    echo "Webhook created successfully!\n";
    echo "Webhook ID: {$result['webhook_id']}\n\n";

    $webhookId = $result['webhook_id'];

    // Note: In a real application, you would set up an endpoint to receive webhooks
    // Here's an example of how to handle incoming webhooks:
    
    echo "2. Example webhook handler:\n";
    echo "------------------------------\n";
    echo <<<'PHP'
// In your webhook endpoint (e.g., routes/web.php or api.php):

use Tigusigalpa\ManusAI\Helpers\WebhookHandler;

Route::post('/webhook/manus-ai', function (Request $request) {
    try {
        $payload = WebhookHandler::parsePayload($request->getContent());
        
        if (WebhookHandler::isTaskCompleted($payload)) {
            $taskDetail = WebhookHandler::getTaskDetail($payload);
            $attachments = WebhookHandler::getAttachments($payload);
            
            Log::info('Task completed', [
                'task_id' => $taskDetail['task_id'],
                'message' => $taskDetail['message'],
                'attachments' => count($attachments),
            ]);
            
            // Process completed task...
        }
        
        if (WebhookHandler::isTaskAskingForInput($payload)) {
            // Handle task that needs user input...
        }
        
        return response()->json(['status' => 'ok']);
        
    } catch (Exception $e) {
        Log::error('Webhook error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 400);
    }
});

PHP;
    echo "\n------------------------------\n\n";

    // 3. To delete the webhook later
    // echo "3. Deleting webhook...\n";
    // $deleted = $client->deleteWebhook($webhookId);
    // echo "Webhook deleted: " . ($deleted ? 'yes' : 'no') . "\n\n";

    echo "=== Webhook example completed! ===\n";

} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}
