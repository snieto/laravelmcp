<?php

namespace App\Livewire;

use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use App\Domain\TaskManagement\ValueObjects\Status;
use Livewire\Component;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $projectFilter = '';
    public $priorityFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'projectFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingProjectFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->projectFilter = '';
        $this->priorityFilter = '';
        $this->resetPage();
    }

    public function render(TaskRepositoryInterface $taskRepository)
    {
        $query = $taskRepository->all();

        // Apply filters
        if ($this->search) {
            $query = $taskRepository->search($this->search);
        }

        if ($this->statusFilter) {
            $query = $taskRepository->findByStatus(Status::from($this->statusFilter));
        }

        if ($this->projectFilter) {
            $query = $taskRepository->findByProjectId((int) $this->projectFilter);
        }

        // Note: Priority filter would need additional repository method
        // For now, we'll filter in memory for simplicity
        if ($this->priorityFilter) {
            $query = $query->filter(function ($task) {
                return $task->priority->value === $this->priorityFilter;
            });
        }

        return view('livewire.task-list', [
            'tasks' => $query->take(20),
        ])->layout('layouts.app');
    }
}
