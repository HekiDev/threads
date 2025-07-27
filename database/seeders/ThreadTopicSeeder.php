<?php

namespace Database\Seeders;

use App\Models\ThreadTopic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThreadTopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ThreadTopic::factory()->count(10)->create();
    }
}
