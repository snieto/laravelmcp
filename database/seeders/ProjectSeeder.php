<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::create([
            'owner_id' => 2, // Sarah Johnson
            'name' => 'TaskMaster AI Platform',
            'description' => 'Build an intelligent task management platform with AI-powered features using Laravel and MCP',
            'status' => 'active',
            'starts_at' => Carbon::now()->subMonths(3),
            'ends_at' => Carbon::now()->addMonths(6),
        ]);

        Project::create([
            'owner_id' => 3, // Michael Chen
            'name' => 'E-Commerce Website Redesign',
            'description' => 'Complete redesign of the company e-commerce platform with modern UI/UX and improved performance',
            'status' => 'active',
            'starts_at' => Carbon::now()->subMonth(),
            'ends_at' => Carbon::now()->addMonths(4),
        ]);

        Project::create([
            'owner_id' => 2, // Sarah Johnson
            'name' => 'Mobile App Development',
            'description' => 'Native mobile applications for iOS and Android with real-time synchronization',
            'status' => 'active',
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addMonths(8),
        ]);

        Project::create([
            'owner_id' => 1, // Admin
            'name' => 'Internal Tools Migration',
            'description' => 'Migrate all internal tools to new infrastructure and technology stack',
            'status' => 'active',
            'starts_at' => Carbon::now()->subMonths(2),
            'ends_at' => Carbon::now()->addMonths(3),
        ]);

        Project::create([
            'owner_id' => 3, // Michael Chen
            'name' => 'Legacy System Upgrade',
            'description' => 'Upgrade legacy PHP 7.4 application to PHP 8.3 and Laravel 11',
            'status' => 'completed',
            'starts_at' => Carbon::now()->subMonths(6),
            'ends_at' => Carbon::now()->subMonth(),
        ]);

        $this->command->info('✓ 5 projects created successfully');
    }
}
