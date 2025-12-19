<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tigusigalpa\ManusAI\Laravel\ManusAI;
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

// This example demonstrates Laravel integration
// Make sure to add MANUS_AI_API_KEY to your .env file

Route::get('/manus-ai/demo', function () {
    try {
        // Using Facade
        $result = ManusAI::createTask('Explain Laravel routing in 3 sentences', [
            'agentProfile' => 'manus-1.6',
            'taskMode' => 'chat',
        ]);

        return response()->json([
            'success' => true,
            'task_id' => $result['task_id'],
            'task_url' => $result['task_url'],
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Get task status
Route::get('/manus-ai/task/{taskId}', function ($taskId) {
    try {
        $task = ManusAI::getTask($taskId);

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Create task with file upload
Route::post('/manus-ai/task-with-file', function (Request $request) {
    try {
        // Validate file
        $request->validate([
            'prompt' => 'required|string',
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        // Create file record
        $fileName = $request->file('file')->getClientOriginalName();
        $fileResult = ManusAI::createFile($fileName);

        // Upload file content
        $fileContent = file_get_contents($request->file('file')->getRealPath());
        $mimeType = $request->file('file')->getMimeType();
        
        ManusAI::uploadFileContent(
            $fileResult['upload_url'],
            $fileContent,
            $mimeType
        );

        // Create task with attachment
        $attachment = TaskAttachment::fromFileId($fileResult['id']);
        
        $taskResult = ManusAI::createTask($request->input('prompt'), [
            'agentProfile' => 'manus-1.6',
            'attachments' => [$attachment],
        ]);

        return response()->json([
            'success' => true,
            'task_id' => $taskResult['task_id'],
            'task_url' => $taskResult['task_url'],
        ]);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});
