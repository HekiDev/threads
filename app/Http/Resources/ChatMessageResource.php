<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->is_readed ? 'read' : 'sent',
            'is_mine' => boolval($this->is_mine),
            'created_at' => $this->created_at,
            'message' => $this->message,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'avatar' => $this->user->avatar_url,
                ];
            }),
            'datetime' => $this->formattedDate($this->created_at),
        ];
    }

    private function formattedDate($timestamp): string
    {
        $time = Carbon::parse($timestamp);
        if ($time->isToday()) {
            return $time->format('g:i A');
        }

        if ($time->isYesterday()) {
            return 'Yesterday';
        }

        if ($time->isCurrentWeek()) {
            return $time->format('D');
        }

        if ($time->isCurrentYear()) {
            return $time->format('M j');
        }

        return $time->format('M j, Y');
    }
}
