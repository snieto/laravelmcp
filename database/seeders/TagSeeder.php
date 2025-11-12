<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'bug', 'color' => '#dc2626', 'description' => 'Something isn\'t working'],
            ['name' => 'feature', 'color' => '#2563eb', 'description' => 'New feature or request'],
            ['name' => 'enhancement', 'color' => '#7c3aed', 'description' => 'Improvement to existing feature'],
            ['name' => 'documentation', 'color' => '#0891b2', 'description' => 'Documentation updates'],
            ['name' => 'urgent', 'color' => '#ea580c', 'description' => 'Requires immediate attention'],
            ['name' => 'frontend', 'color' => '#059669', 'description' => 'Frontend related work'],
            ['name' => 'backend', 'color' => '#0284c7', 'description' => 'Backend related work'],
            ['name' => 'database', 'color' => '#7e22ce', 'description' => 'Database changes'],
            ['name' => 'security', 'color' => '#b91c1c', 'description' => 'Security issues or improvements'],
            ['name' => 'testing', 'color' => '#65a30d', 'description' => 'Testing related tasks'],
            ['name' => 'refactoring', 'color' => '#c026d3', 'description' => 'Code refactoring'],
            ['name' => 'performance', 'color' => '#ea580c', 'description' => 'Performance optimization'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }

        $this->command->info('✓ 12 tags created successfully');
    }
}
