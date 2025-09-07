<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ThreadCommentResource extends JsonResource
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
            'comment' => $this->comment,
            'user' => new ThreadUserResource($this->whenLoaded('user')),
            'attachments' => ThreadAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => Carbon::parse($this->created_at)->diffForHumans([
                'parts' => 1,
                'short' => true,
            ]),
            'replies_count' => $this->replies_count,
            'replies' => ThreadCommentReplyResource::collection($this->whenLoaded('replies')),
            'reactions_count' => $this->reactions_count,
            'reacted' => $this->reacted,
        ];
    }
}
