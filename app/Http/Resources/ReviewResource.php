<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'user_name' => $this->whenLoaded('user', fn () => $this->user->name),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'reviewed_date' => $this->created_at->toIso8601String(),
        ];
    }
}
