<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\ChatMessageStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Temporarily disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables in proper dependency order
        ChatMessageStatus::truncate();
        ChatMessage::truncate();
        ChatMember::truncate();
        Chat::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Step 1: Fetch Users
        $users = User::latest()->take(5)->get(); // at least 5 users if possible
        $creator = User::latest('id')->first(); // your main user

        // Step 2: Create Chats
        $chats = Chat::factory()
            ->count(3)
            ->create([
                'user_id' => $creator->id, // Chat creator
            ]);

        // Step 3: Assign unique members per chat
        $otherMembers = $users->where('id', '!=', $creator->id)->values();
        $memberIndex = 0;

        foreach ($chats as $chat) {
            // Get next user as member (cycle through if not enough users)
            $secondMember = $otherMembers[$memberIndex % $otherMembers->count()];
            $memberIndex++;

            // First member is the creator
            ChatMember::create([
                'user_id' => $creator->id,
                'chat_id' => $chat->id,
                'is_creator' => true,
            ]);

            // Second member is not creator
            ChatMember::create([
                'user_id' => $secondMember->id,
                'chat_id' => $chat->id,
                'is_creator' => false,
            ]);

            // Step 4: Create Messages for this chat
            $messages = collect();
            foreach (range(1, rand(3, 6)) as $i) {
                $sender = collect([$creator, $secondMember])->random();

                $message = ChatMessage::create([
                    'user_id' => $sender->id,
                    'chat_id' => $chat->id,
                    'message' => fake()->sentence(rand(3, 10)),
                    'type' => 'text',
                ]);

                $messages->push($message);
            }

            // Step 5: Add Message Statuses for each message
            foreach ($messages as $message) {
                foreach ([$creator, $secondMember] as $member) {
                    ChatMessageStatus::create([
                        'user_id' => $member->id,
                        'chat_message_id' => $message->id,
                        'received_at' => now(),
                        'read_at' => rand(0, 1) ? now() : null,
                    ]);
                }
            }
        }
    }
}
