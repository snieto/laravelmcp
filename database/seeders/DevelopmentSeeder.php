<?php

namespace Database\Seeders;

use App\Domain\TaskManagement\ValueObjects\Priority;
use App\Domain\TaskManagement\ValueObjects\Status;
use App\Infrastructure\Persistence\Eloquent\Models\Comment;
use App\Infrastructure\Persistence\Eloquent\Models\Project;
use App\Infrastructure\Persistence\Eloquent\Models\Tag;
use App\Infrastructure\Persistence\Eloquent\Models\Task;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding development data...');

        // 1. Create Users
        $this->command->info('👤 Creating users...');

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@taskmaster.test',
            'password' => bcrypt('password'),
        ]);

        $users = collect([
            User::factory()->create([
                'name' => 'Alice Johnson',
                'email' => 'alice@taskmaster.test',
            ]),
            User::factory()->create([
                'name' => 'Bob Smith',
                'email' => 'bob@taskmaster.test',
            ]),
            User::factory()->create([
                'name' => 'Charlie Brown',
                'email' => 'charlie@taskmaster.test',
            ]),
            User::factory()->create([
                'name' => 'Diana Prince',
                'email' => 'diana@taskmaster.test',
            ]),
            User::factory()->create([
                'name' => 'Ethan Hunt',
                'email' => 'ethan@taskmaster.test',
            ]),
        ]);

        $this->command->info("✅ Created {$users->count()} users + 1 admin");

        // 2. Create Projects
        $this->command->info('📁 Creating projects...');

        $projects = collect([
            Project::factory()->create([
                'name' => 'E-commerce Platform',
                'description' => 'Building a modern e-commerce platform with Laravel and React',
                'owner_id' => $admin->id,
            ]),
            Project::factory()->create([
                'name' => 'Mobile App Redesign',
                'description' => 'Redesigning the mobile application for better UX',
                'owner_id' => $admin->id,
            ]),
            Project::factory()->create([
                'name' => 'API v2.0',
                'description' => 'Developing the next version of our public API',
                'owner_id' => $admin->id,
            ]),
        ]);

        $this->command->info("✅ Created {$projects->count()} projects");

        // 3. Create Tags
        $this->command->info('🏷️  Creating tags...');

        $tags = collect([
            Tag::factory()->create(['name' => 'frontend', 'color' => '#3B82F6']),
            Tag::factory()->create(['name' => 'backend', 'color' => '#10B981']),
            Tag::factory()->create(['name' => 'bug', 'color' => '#EF4444']),
            Tag::factory()->create(['name' => 'feature', 'color' => '#8B5CF6']),
            Tag::factory()->create(['name' => 'refactoring', 'color' => '#F59E0B']),
            Tag::factory()->create(['name' => 'documentation', 'color' => '#6366F1']),
            Tag::factory()->create(['name' => 'testing', 'color' => '#EC4899']),
            Tag::factory()->create(['name' => 'performance', 'color' => '#14B8A6']),
            Tag::factory()->create(['name' => 'security', 'color' => '#DC2626']),
            Tag::factory()->create(['name' => 'urgent', 'color' => '#F97316']),
        ]);

        $this->command->info("✅ Created {$tags->count()} tags");

        // 4. Create Tasks for Each Project
        $this->command->info('✨ Creating tasks...');

        $taskCount = 0;

        foreach ($projects as $project) {
            // Pending tasks
            for ($i = 0; $i < 8; $i++) {
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                    'assigned_to' => $users->random()->id,
                    'created_by' => $admin->id,
                    'status' => Status::PENDING,
                    'priority' => $this->randomPriority(),
                ]);

                // Attach random tags (1-3 tags per task)
                $task->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
                $taskCount++;
            }

            // In Progress tasks
            for ($i = 0; $i < 6; $i++) {
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                    'assigned_to' => $users->random()->id,
                    'created_by' => $admin->id,
                    'status' => Status::IN_PROGRESS,
                    'priority' => $this->randomPriority(),
                ]);

                $task->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
                $taskCount++;
            }

            // Completed tasks
            for ($i = 0; $i < 10; $i++) {
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                    'assigned_to' => $users->random()->id,
                    'created_by' => $admin->id,
                    'status' => Status::COMPLETED,
                    'priority' => $this->randomPriority(),
                    'completed_at' => now()->subDays(rand(1, 30)),
                ]);

                $task->tags()->attach($tags->random(rand(1, 2))->pluck('id'));
                $taskCount++;
            }

            // Review tasks
            for ($i = 0; $i < 3; $i++) {
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                    'assigned_to' => $users->random()->id,
                    'created_by' => $admin->id,
                    'status' => Status::REVIEW,
                    'priority' => Priority::HIGH,
                ]);

                $task->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
                $taskCount++;
            }

            // Blocked tasks (high priority)
            for ($i = 0; $i < 2; $i++) {
                $task = Task::factory()->create([
                    'project_id' => $project->id,
                    'assigned_to' => $users->random()->id,
                    'created_by' => $admin->id,
                    'status' => Status::BLOCKED,
                    'priority' => rand(0, 1) ? Priority::HIGH : Priority::CRITICAL,
                ]);

                $task->tags()->attach($tags->random(rand(2, 4))->pluck('id'));
                $taskCount++;
            }
        }

        $this->command->info("✅ Created {$taskCount} tasks across all projects");

        // 5. Create Comments
        $this->command->info('💬 Creating comments...');

        $commentCount = 0;
        $allTasks = Task::all();

        foreach ($allTasks as $task) {
            // Random number of comments (0-5)
            $numComments = rand(0, 5);

            for ($i = 0; $i < $numComments; $i++) {
                Comment::factory()->create([
                    'task_id' => $task->id,
                    'user_id' => $users->random()->id,
                ]);
                $commentCount++;
            }
        }

        $this->command->info("✅ Created {$commentCount} comments");

        // 6. Create some overdue tasks
        $this->command->info('⏰ Creating overdue tasks...');

        $overdueCount = 0;
        foreach ($projects->take(2) as $project) {
            for ($i = 0; $i < 3; $i++) {
                Task::factory()->create([
                    'project_id' => $project->id,
                    'assigned_to' => $users->random()->id,
                    'created_by' => $admin->id,
                    'status' => rand(0, 1) ? Status::PENDING : Status::IN_PROGRESS,
                    'priority' => Priority::HIGH,
                    'due_date' => now()->subDays(rand(1, 10)), // Overdue
                ]);
                $overdueCount++;
            }
        }

        $this->command->info("✅ Created {$overdueCount} overdue tasks");

        // Summary
        $this->command->newLine();
        $this->command->info('🎉 Development seeding completed!');
        $this->command->newLine();
        $this->command->table(
            ['Resource', 'Count'],
            [
                ['Users', User::count()],
                ['Projects', Project::count()],
                ['Tasks', Task::count()],
                ['Tags', Tag::count()],
                ['Comments', Comment::count()],
                ['Pending', Task::where('status', Status::PENDING)->count()],
                ['In Progress', Task::where('status', Status::IN_PROGRESS)->count()],
                ['Completed', Task::where('status', Status::COMPLETED)->count()],
                ['Blocked', Task::where('status', Status::BLOCKED)->count()],
                ['Overdue', Task::where('due_date', '<', now())->whereNotIn('status', [Status::COMPLETED])->count()],
            ]
        );

        // Generate a token for Claude Desktop
        $this->command->newLine();
        $this->command->info('🔑 Generating Sanctum token for Claude Desktop...');

        $claudeUser = User::firstWhere('email', 'admin@taskmaster.test');
        $token = $claudeUser->createToken('claude-desktop')->plainTextToken;

        $this->command->newLine();
        $this->command->warn('📋 SAVE THIS TOKEN FOR CLAUDE DESKTOP:');
        $this->command->line($token);
        $this->command->newLine();
        $this->command->info('Add this to your claude_desktop_config.json:');
        $this->command->line('"Authorization": "Bearer ' . $token . '"');
        $this->command->newLine();
    }

    /**
     * Get a random priority.
     */
    private function randomPriority(): Priority
    {
        $priorities = [
            Priority::LOW,
            Priority::LOW,
            Priority::MEDIUM,
            Priority::MEDIUM,
            Priority::MEDIUM,
            Priority::HIGH,
            Priority::HIGH,
            Priority::CRITICAL,
        ];

        return $priorities[array_rand($priorities)];
    }
}
