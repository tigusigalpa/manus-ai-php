<?php

namespace Tigusigalpa\ManusAI\Laravel\Commands;

use Illuminate\Console\Command;
use Tigusigalpa\ManusAI\ManusAIClient;

class ManusAITaskCommand extends Command
{
    protected $signature = 'manus-ai:task
                            {action : Action to perform: create, list, get, update, delete}
                            {--id= : Task ID for get, update, or delete actions}
                            {--prompt= : Task prompt for create action}
                            {--profile=manus-1.6 : Agent profile (manus-1.6, manus-1.6-lite, manus-1.6-max)}
                            {--mode=agent : Task mode (chat, adaptive, agent)}
                            {--title= : New title for update action}
                            {--limit=10 : Number of tasks to retrieve in list action}
                            {--status=* : Filter by status (pending, running, completed, failed)}';

    protected $description = 'Manage Manus AI tasks via CLI';

    public function handle(ManusAIClient $client): int
    {
        $action = $this->argument('action');

        try {
            return match ($action) {
                'create' => $this->createTask($client),
                'list' => $this->listTasks($client),
                'get' => $this->getTask($client),
                'update' => $this->updateTask($client),
                'delete' => $this->deleteTask($client),
                default => $this->error("Unknown action: {$action}") ?? Command::FAILURE,
            };
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function createTask(ManusAIClient $client): int
    {
        $prompt = $this->option('prompt');
        
        if (!$prompt) {
            $this->error('Prompt is required for create action. Use --prompt option.');
            return Command::FAILURE;
        }

        $this->info('Creating task...');
        
        $result = $client->createTask($prompt, [
            'agentProfile' => $this->option('profile'),
            'taskMode' => $this->option('mode'),
        ]);

        $this->info('✅ Task created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Task ID', $result['task_id'] ?? 'N/A'],
                ['Title', $result['task_title'] ?? 'N/A'],
                ['URL', $result['task_url'] ?? 'N/A'],
            ]
        );

        return Command::SUCCESS;
    }

    private function listTasks(ManusAIClient $client): int
    {
        $this->info('Fetching tasks...');

        $filters = [
            'limit' => (int) $this->option('limit'),
        ];

        $statuses = $this->option('status');
        if (!empty($statuses)) {
            $filters['status'] = $statuses;
        }

        $result = $client->getTasks($filters);

        if (empty($result['data'])) {
            $this->warn('No tasks found.');
            return Command::SUCCESS;
        }

        $tasks = array_map(function ($task) {
            return [
                'ID' => $task['id'] ?? 'N/A',
                'Status' => $task['status'] ?? 'N/A',
                'Created' => isset($task['created_at']) ? date('Y-m-d H:i:s', $task['created_at']) : 'N/A',
            ];
        }, $result['data']);

        $this->table(['ID', 'Status', 'Created'], $tasks);
        
        if ($result['has_more'] ?? false) {
            $this->info('More tasks available. Last ID: ' . ($result['last_id'] ?? 'N/A'));
        }

        return Command::SUCCESS;
    }

    private function getTask(ManusAIClient $client): int
    {
        $taskId = $this->option('id');
        
        if (!$taskId) {
            $this->error('Task ID is required. Use --id option.');
            return Command::FAILURE;
        }

        $this->info("Fetching task: {$taskId}");
        
        $task = $client->getTask($taskId);

        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $task['id'] ?? 'N/A'],
                ['Status', $task['status'] ?? 'N/A'],
                ['Model', $task['model'] ?? 'N/A'],
                ['Created', isset($task['created_at']) ? date('Y-m-d H:i:s', $task['created_at']) : 'N/A'],
                ['Updated', isset($task['updated_at']) ? date('Y-m-d H:i:s', $task['updated_at']) : 'N/A'],
                ['Credits Used', $task['credit_usage'] ?? 'N/A'],
            ]
        );

        if (isset($task['output']) && is_array($task['output'])) {
            $this->info('Task Output:');
            foreach ($task['output'] as $index => $message) {
                $role = $message['role'] ?? 'unknown';
                $content = $message['content'] ?? '';
                $this->line("  [{$index}] {$role}: " . substr($content, 0, 200));
            }
        }

        return Command::SUCCESS;
    }

    private function updateTask(ManusAIClient $client): int
    {
        $taskId = $this->option('id');
        $title = $this->option('title');
        
        if (!$taskId) {
            $this->error('Task ID is required. Use --id option.');
            return Command::FAILURE;
        }

        if (!$title) {
            $this->error('At least one update field is required (e.g., --title).');
            return Command::FAILURE;
        }

        $updates = [];
        if ($title) {
            $updates['title'] = $title;
        }

        $this->info("Updating task: {$taskId}");
        
        $result = $client->updateTask($taskId, $updates);

        $this->info('✅ Task updated successfully!');
        $this->line('New title: ' . ($result['task_title'] ?? 'N/A'));

        return Command::SUCCESS;
    }

    private function deleteTask(ManusAIClient $client): int
    {
        $taskId = $this->option('id');
        
        if (!$taskId) {
            $this->error('Task ID is required. Use --id option.');
            return Command::FAILURE;
        }

        if (!$this->confirm("Are you sure you want to delete task {$taskId}?")) {
            $this->info('Deletion cancelled.');
            return Command::SUCCESS;
        }

        $this->info("Deleting task: {$taskId}");
        
        $client->deleteTask($taskId);

        $this->info('✅ Task deleted successfully!');

        return Command::SUCCESS;
    }
}
