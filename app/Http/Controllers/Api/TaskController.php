<?php

namespace App\Http\Controllers\Api;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): TaskCollection
    {
        // Get filters from query parameters
        $projectId = $request->query('project_id');
        $status = $request->query('status');
        $assignedTo = $request->query('assigned_to');
        $priority = $request->query('priority');
        $search = $request->query('search');

        // Apply filters
        if ($projectId) {
            $tasks = $this->taskRepository->findByProjectId((int) $projectId);
        } elseif ($status) {
            $tasks = $this->taskRepository->findByStatus(Status::from($status));
        } elseif ($assignedTo) {
            $tasks = $this->taskRepository->findByAssignedTo((int) $assignedTo);
        } elseif ($priority) {
            $tasks = $this->taskRepository->findByPriority(Priority::from($priority));
        } elseif ($search) {
            $tasks = $this->taskRepository->search($search);
        } else {
            $tasks = $this->taskRepository->all();
        }

        return new TaskCollection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Add created_by from authenticated user
        $data['created_by'] = $request->user()->id;

        // Create the task
        $task = $this->taskRepository->create($data);

        // Attach tags if provided
        if (isset($data['tags'])) {
            $this->taskRepository->attachTags($task->id, $data['tags']);
            $task->load('tags');
        }

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): TaskResource|JsonResponse
    {
        $task = $this->taskRepository->findById((int) $id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, string $id): TaskResource|JsonResponse
    {
        $task = $this->taskRepository->findById((int) $id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $data = $request->validated();

        // Handle tags separately
        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        // Update the task
        $this->taskRepository->update((int) $id, $data);

        // Update tags if provided
        if ($tags !== null) {
            // Detach all existing tags first
            $currentTags = $task->tags->pluck('id')->toArray();
            if (!empty($currentTags)) {
                $this->taskRepository->detachTags((int) $id, $currentTags);
            }

            // Attach new tags
            if (!empty($tags)) {
                $this->taskRepository->attachTags((int) $id, $tags);
            }
        }

        // Reload the task with relationships
        $task = $this->taskRepository->findById((int) $id);

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $task = $this->taskRepository->findById((int) $id);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $this->taskRepository->delete((int) $id);

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
