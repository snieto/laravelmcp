<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@taskmaster.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'bio' => 'System Administrator',
            'timezone' => 'UTC',
        ]);

        // Project managers
        User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@taskmaster.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'bio' => 'Senior Project Manager with 10+ years experience',
            'timezone' => 'America/New_York',
        ]);

        User::create([
            'name' => 'Michael Chen',
            'email' => 'michael@taskmaster.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'bio' => 'Technical Project Manager & Scrum Master',
            'timezone' => 'Asia/Tokyo',
        ]);

        // Developers
        User::create([
            'name' => 'Emma Rodriguez',
            'email' => 'emma@taskmaster.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'bio' => 'Full Stack Developer specializing in Laravel & Vue',
            'timezone' => 'Europe/Madrid',
        ]);

        User::create([
            'name' => 'David Kim',
            'email' => 'david@taskmaster.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'bio' => 'Backend Engineer | PHP | PostgreSQL',
            'timezone' => 'Asia/Seoul',
        ]);

        User::create([
            'name' => 'Lisa Anderson',
            'email' => 'lisa@taskmaster.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'bio' => 'Frontend Developer | React & Livewire',
            'timezone' => 'America/Los_Angeles',
        ]);

        // Designers
        User::create([
            'name' => 'Alex Turner',
            'email' => 'alex@taskmaster.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'bio' => 'UI/UX Designer & Product Designer',
            'timezone' => 'Europe/London',
        ]);

        // QA Testers
        User::create([
            'name' => 'Maria Garcia',
            'email' => 'maria@taskmaster.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'bio' => 'QA Engineer | Automation Testing',
            'timezone' => 'America/Mexico_City',
        ]);

        $this->command->info('✓ 8 users created successfully');
    }
}
