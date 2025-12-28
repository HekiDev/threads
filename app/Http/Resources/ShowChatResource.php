<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowChatResource extends JsonResource
{
    protected $auth;

    /**
     * Disable data wrapper for this resource only.
     *
     * @var string|null
     */
    public static $wrap = null;

    public function __construct($resource, $auth)
    {
        parent::__construct($resource);
        $this->auth = $auth;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        parent::toArray($request);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'is_blocked' => (bool) $this->whenLoaded('blocker'),
            'blocker_user_id' => $this->whenLoaded('blocker', function ($blocker) {
                return $blocker->blocker_user_id;
            }),
        ];
    }
}
