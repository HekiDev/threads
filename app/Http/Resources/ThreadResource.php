<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ThreadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'user' => new ThreadUserResource($this->whenLoaded('user')),
            'attachments' => ThreadAttachmentResource::collection($this->whenLoaded('attachments')),
            'topic' => $this->whenLoaded('topic', function () {
                return [
                    'id' => $this->topic->id,
                    'name' => $this->topic->name,
                ];
            }),
            'created_at' => Carbon::parse($this->created_at)->diffForHumans([
                'parts' => 1,
                'short' => true,
            ]),
            'comments_count' => $this->comments_count,
            'reactions_count' => $this->reactions_count,
            'reacted' => $this->reacted,
            'views_count' => $this->views_count ?? 0,
        ];
    }
}
