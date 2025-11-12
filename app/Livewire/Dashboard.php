<?php

namespace App\Livewire;

use App\Domain\Analytics\Services\MetricsCollector;
use App\Domain\TaskManagement\Contracts\Repositories\ProjectRepositoryInterface;
use App\Domain\TaskManagement\Contracts\Repositories\TaskRepositoryInterface;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public $metrics = [];
    public $recentTasks = [];
    public $activeProjects = [];

    public function mount(
        MetricsCollector $metricsCollector,
        TaskRepositoryInterface $taskRepository,
        ProjectRepositoryInterface $projectRepository
    ) {
        // Collect metrics for the last 30 days
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        $this->metrics = $metricsCollector->collectMetrics($startDate, $endDate);

        // Get recent tasks (last 10)
        $this->recentTasks = $taskRepository->all()->take(10);

        // Get active projects
        $this->activeProjects = $projectRepository->getActive();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'metrics' => $this->metrics,
            'recentTasks' => $this->recentTasks,
            'activeProjects' => $this->activeProjects,
        ])->layout('layouts.app');
    }
}
