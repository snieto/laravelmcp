<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\Comment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comments = [
            // Task 1 comments
            ['task_id' => 1, 'user_id' => 2, 'content' => 'Great work on the MCP implementation! The architecture looks solid.', 'created_at' => Carbon::now()->subDays(9)],
            ['task_id' => 1, 'user_id' => 5, 'content' => 'Thanks! I followed the official Laravel MCP documentation closely.', 'created_at' => Carbon::now()->subDays(9)],

            // Task 2 comments
            ['task_id' => 2, 'user_id' => 4, 'content' => 'Working on the repository pattern implementation. Should be done by tomorrow.', 'created_at' => Carbon::now()->subDays(2)],
            ['task_id' => 2, 'user_id' => 2, 'content' => 'Sounds good! Make sure to add proper interfaces for all repositories.', 'created_at' => Carbon::now()->subDays(2)],
            ['task_id' => 2, 'user_id' => 5, 'content' => 'Let me know if you need help with the service layer integration.', 'created_at' => Carbon::now()->subDay()],

            // Task 3 comments
            ['task_id' => 3, 'user_id' => 2, 'content' => 'Please add comprehensive error handling for API failures.', 'created_at' => Carbon::now()->subDays(3)],
            ['task_id' => 3, 'user_id' => 4, 'content' => 'Done! Added retry logic and fallback responses.', 'created_at' => Carbon::now()->subDays(2)],

            // Task 7 comments
            ['task_id' => 7, 'user_id' => 7, 'content' => 'Design mockups are ready for review in Figma.', 'created_at' => Carbon::now()->subDays(6)],
            ['task_id' => 7, 'user_id' => 3, 'content' => 'Looks amazing! Approved for implementation.', 'created_at' => Carbon::now()->subDays(5)],

            // Task 8 comments
            ['task_id' => 8, 'user_id' => 4, 'content' => 'Cart is working well. Just need to add coupon code functionality.', 'created_at' => Carbon::now()->subDay()],
            ['task_id' => 8, 'user_id' => 3, 'content' => 'Great! That can be a separate task.', 'created_at' => Carbon::now()->subDay()],

            // Task 10 comments
            ['task_id' => 10, 'user_id' => 6, 'content' => 'Project structure is set up. Navigation and state management configured.', 'created_at' => Carbon::now()->subDays(14)],

            // Task 13 comments
            ['task_id' => 13, 'user_id' => 5, 'content' => 'Migration is 75% complete. Encountered some character encoding issues but resolved.', 'created_at' => Carbon::now()->subDays(1)],
            ['task_id' => 13, 'user_id' => 1, 'content' => 'Excellent progress! Keep me updated on the final 25%.', 'created_at' => Carbon::now()->subDays(1)],
        ];

        foreach ($comments as $comment) {
            Comment::create($comment);
        }

        $this->command->info('✓ 14 comments created successfully');
    }
}
