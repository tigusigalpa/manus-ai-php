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
                            {--profile=standard : Agent profile (standard, lite, max)}
                            {--title= : New title for update action}
                            {--limit=10 : Number of tasks to retrieve in list action}';

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
            'agent_profile' => $this->option('profile'),
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

        $result = $client->getTasks($filters);

        if (empty($result['data'])) {
            $this->warn('No tasks found.');
            return Command::SUCCESS;
        }

        $tasks = array_map(function ($task) {
            return [
                'ID' => $task['id'] ?? 'N/A',
                'Status' => $task['status'] ?? 'N/A',
                'Created' => $this->formatTimestamp($task['created_at'] ?? null),
            ];
        }, $result['data']);

        $this->table(['ID', 'Status', 'Created'], $tasks);
        
        if ($result['has_more'] ?? false) {
            $this->info('More tasks available. Next cursor: ' . ($result['next_cursor'] ?? 'N/A'));
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
                ['Profile', $task['agent_profile'] ?? 'N/A'],
                ['Created', $this->formatTimestamp($task['created_at'] ?? null)],
                ['Updated', $this->formatTimestamp($task['updated_at'] ?? null)],
                ['Credits Used', $task['credit_usage'] ?? 'N/A'],
            ]
        );

        $this->line('Use task.listMessages to read the task event history and output.');

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

    private function formatTimestamp(mixed $timestamp): string
    {
        if (!is_numeric($timestamp)) {
            return 'N/A';
        }

        $seconds = (int) $timestamp;
        if ($seconds > 9_999_999_999) {
            $seconds = (int) floor($seconds / 1000);
        }

        return date('Y-m-d H:i:s', $seconds);
    }
}
