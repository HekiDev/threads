<?php

namespace Database\Factories;

use App\Models\ThreadTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Thread>
 */
class ThreadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(), // fallback to a new user if none exist
            'thread_topic_id' => ThreadTopic::inRandomOrder()->first()?->id ?? ThreadTopic::factory(), // fallback to a new topic if none exist
            'title' => $this->faker->sentence(1),
            'description' => $this->faker->paragraph(),
        ];
    }
}
