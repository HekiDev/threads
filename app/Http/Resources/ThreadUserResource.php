<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadUserResource extends JsonResource
{
    /**
     * Disable data wrapper for this resource only.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => '@'.str_replace(' ', '', strtolower($this->name)),
            'avatar' => null,
            'followed' => $this->followed ?? false,
        ];
    }
}
