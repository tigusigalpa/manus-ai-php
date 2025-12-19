<?php

namespace Tigusigalpa\ManusAI\Laravel\Commands;

use Illuminate\Console\Command;
use Tigusigalpa\ManusAI\ManusAIClient;

class ManusAITestCommand extends Command
{
    protected $signature = 'manus-ai:test {--task=}';
    protected $description = 'Test Manus AI connection and API';

    public function handle(ManusAIClient $client): int
    {
        $this->info('🚀 Testing Manus AI connection...');

        try {
            $taskPrompt = $this->option('task') ?: 'Say hello and tell me your current capabilities';

            $this->info("Creating test task: {$taskPrompt}");
            
            $result = $client->createTask($taskPrompt, [
                'agentProfile' => 'manus-1.6',
                'taskMode' => 'chat',
            ]);

            $this->info('✅ Task created successfully!');
            $this->line('Task ID: ' . ($result['task_id'] ?? 'N/A'));
            $this->line('Task Title: ' . ($result['task_title'] ?? 'N/A'));
            $this->line('Task URL: ' . ($result['task_url'] ?? 'N/A'));

            if (isset($result['share_url'])) {
                $this->line('Share URL: ' . $result['share_url']);
            }

            $this->newLine();
            $this->info('Fetching task details...');
            
            if (isset($result['task_id'])) {
                sleep(2); // Wait a bit for task to process
                $task = $client->getTask($result['task_id']);
                $this->line('Status: ' . ($task['status'] ?? 'N/A'));
                
                if (isset($task['output']) && is_array($task['output'])) {
                    $this->info('Task Output:');
                    foreach ($task['output'] as $message) {
                        if (isset($message['role']) && isset($message['content'])) {
                            $this->line("  [{$message['role']}]: " . substr($message['content'], 0, 100));
                        }
                    }
                }
            }

            $this->newLine();
            $this->info('🎉 Test completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
