<?php

namespace Database\Seeders;

use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            // TaskMaster AI Platform (Project 1)
            [
                'project_id' => 1,
                'title' => 'Implement MCP Server Architecture',
                'description' => 'Design and implement the Model Context Protocol server architecture with all required tools, resources, and prompts',
                'status' => Status::COMPLETED,
                'priority' => Priority::CRITICAL,
                'assigned_to' => 5, // David Kim
                'created_by' => 2,
                'due_date' => Carbon::now()->subDays(10),
                'estimated_hours' => 40,
                'actual_hours' => 45,
            ],
            [
                'project_id' => 1,
                'title' => 'Create Task Management Domain Layer',
                'description' => 'Implement DDD architecture with value objects, repositories, and domain services for task management',
                'status' => Status::IN_PROGRESS,
                'priority' => Priority::HIGH,
                'assigned_to' => 4, // Emma Rodriguez
                'created_by' => 2,
                'due_date' => Carbon::now()->addDays(5),
                'estimated_hours' => 32,
                'actual_hours' => 20,
            ],
            [
                'project_id' => 1,
                'title' => 'Integrate OpenAI API for AI Features',
                'description' => 'Set up OpenAI integration for task description generation, productivity analysis, and recommendations',
                'status' => Status::REVIEW,
                'priority' => Priority::HIGH,
                'assigned_to' => 4,
                'created_by' => 2,
                'due_date' => Carbon::now()->addDays(3),
                'estimated_hours' => 24,
                'actual_hours' => 26,
            ],
            [
                'project_id' => 1,
                'title' => 'Build Livewire Components for Dashboard',
                'description' => 'Create reactive Livewire components for the main dashboard including metrics, charts, and task lists',
                'status' => Status::PENDING,
                'priority' => Priority::MEDIUM,
                'assigned_to' => 6, // Lisa Anderson
                'created_by' => 2,
                'due_date' => Carbon::now()->addDays(15),
                'estimated_hours' => 30,
                'actual_hours' => null,
            ],
            [
                'project_id' => 1,
                'title' => 'Write Unit Tests for Domain Layer',
                'description' => 'Comprehensive unit tests for all value objects, repositories, and domain services',
                'status' => Status::PENDING,
                'priority' => Priority::MEDIUM,
                'assigned_to' => 8, // Maria Garcia
                'created_by' => 2,
                'due_date' => Carbon::now()->addDays(20),
                'estimated_hours' => 16,
                'actual_hours' => null,
            ],
            [
                'project_id' => 1,
                'title' => 'Implement Real-time Notifications',
                'description' => 'Set up Laravel Broadcasting with Redis for real-time task updates and notifications',
                'status' => Status::BLOCKED,
                'priority' => Priority::LOW,
                'assigned_to' => 5,
                'created_by' => 2,
                'due_date' => Carbon::now()->addDays(25),
                'estimated_hours' => 20,
                'actual_hours' => null,
            ],

            // E-Commerce Website Redesign (Project 2)
            [
                'project_id' => 2,
                'title' => 'Design New Product Listing Page',
                'description' => 'Create modern, mobile-responsive product listing page design with filters and sorting',
                'status' => Status::COMPLETED,
                'priority' => Priority::HIGH,
                'assigned_to' => 7, // Alex Turner
                'created_by' => 3,
                'due_date' => Carbon::now()->subDays(5),
                'estimated_hours' => 16,
                'actual_hours' => 18,
            ],
            [
                'project_id' => 2,
                'title' => 'Implement Shopping Cart Functionality',
                'description' => 'Build shopping cart with session persistence, quantity updates, and price calculations',
                'status' => Status::IN_PROGRESS,
                'priority' => Priority::CRITICAL,
                'assigned_to' => 4,
                'created_by' => 3,
                'due_date' => Carbon::now()->addDays(7),
                'estimated_hours' => 24,
                'actual_hours' => 15,
            ],
            [
                'project_id' => 2,
                'title' => 'Optimize Database Queries for Performance',
                'description' => 'Identify and optimize slow database queries, add proper indexes, implement caching strategy',
                'status' => Status::PENDING,
                'priority' => Priority::HIGH,
                'assigned_to' => 5,
                'created_by' => 3,
                'due_date' => Carbon::now()->addDays(10),
                'estimated_hours' => 12,
                'actual_hours' => null,
            ],

            // Mobile App Development (Project 3)
            [
                'project_id' => 3,
                'title' => 'Set Up React Native Project Structure',
                'description' => 'Initialize React Native project with TypeScript, navigation, and state management',
                'status' => Status::COMPLETED,
                'priority' => Priority::CRITICAL,
                'assigned_to' => 6,
                'created_by' => 2,
                'due_date' => Carbon::now()->subDays(15),
                'estimated_hours' => 16,
                'actual_hours' => 14,
            ],
            [
                'project_id' => 3,
                'title' => 'Implement Authentication Flow',
                'description' => 'Build login, registration, and password reset flows for mobile app',
                'status' => Status::IN_PROGRESS,
                'priority' => Priority::HIGH,
                'assigned_to' => 4,
                'created_by' => 2,
                'due_date' => Carbon::now()->addDays(8),
                'estimated_hours' => 20,
                'actual_hours' => 12,
            ],
            [
                'project_id' => 3,
                'title' => 'Create API Endpoints for Mobile',
                'description' => 'Design and implement RESTful API endpoints optimized for mobile consumption',
                'status' => Status::PENDING,
                'priority' => Priority::HIGH,
                'assigned_to' => 5,
                'created_by' => 2,
                'due_date' => Carbon::now()->addDays(12),
                'estimated_hours' => 28,
                'actual_hours' => null,
            ],

            // Internal Tools Migration (Project 4)
            [
                'project_id' => 4,
                'title' => 'Migrate Legacy Database to PostgreSQL',
                'description' => 'Export data from old MySQL database and import into new PostgreSQL instance',
                'status' => Status::IN_PROGRESS,
                'priority' => Priority::CRITICAL,
                'assigned_to' => 5,
                'created_by' => 1,
                'due_date' => Carbon::now()->addDays(5),
                'estimated_hours' => 40,
                'actual_hours' => 30,
            ],
            [
                'project_id' => 4,
                'title' => 'Update CI/CD Pipeline',
                'description' => 'Modernize deployment pipeline with GitHub Actions and Docker',
                'status' => Status::PENDING,
                'priority' => Priority::MEDIUM,
                'assigned_to' => 5,
                'created_by' => 1,
                'due_date' => Carbon::now()->addDays(18),
                'estimated_hours' => 16,
                'actual_hours' => null,
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = Task::create($taskData);

            // Attach random tags (1-3 tags per task)
            $tagIds = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12])
                ->random(rand(1, 3))
                ->toArray();
            $task->tags()->attach($tagIds);
        }

        $this->command->info('✓ 14 tasks created successfully with tags');
    }
}
