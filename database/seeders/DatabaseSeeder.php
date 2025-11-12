<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🌱 Seeding TaskMaster AI Database...');
        $this->command->info('');

        $this->call([
            UserSeeder::class,
            TagSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
            CommentSeeder::class,
        ]);

        $this->command->info('');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✨ Database seeding completed successfully!');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('  • 8 Users');
        $this->command->info('  • 12 Tags');
        $this->command->info('  • 5 Projects');
        $this->command->info('  • 14 Tasks with tags');
        $this->command->info('  • 14 Comments');
        $this->command->info('');
        $this->command->info('🔐 Default Credentials:');
        $this->command->info('  Email: admin@taskmaster.ai');
        $this->command->info('  Password: password');
        $this->command->info('');
    }
}
