<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'project' => $this->when($this->relationLoaded('project'), [
                'id' => $this->project?->id,
                'name' => $this->project?->name,
                'description' => $this->project?->description,
            ]),
            'assigned_to' => $this->assigned_to,
            'assignee' => $this->when($this->relationLoaded('assignedTo'), [
                'id' => $this->assignedTo?->id,
                'name' => $this->assignedTo?->name,
                'email' => $this->assignedTo?->email,
            ]),
            'created_by' => $this->created_by,
            'creator' => $this->when($this->relationLoaded('createdBy'), [
                'id' => $this->createdBy?->id,
                'name' => $this->createdBy?->name,
                'email' => $this->createdBy?->email,
            ]),
            'tags' => $this->when($this->relationLoaded('tags'),
                $this->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'color' => $tag->color,
                ])
            ),
            'comments' => $this->when($this->relationLoaded('comments'),
                $this->comments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => $comment->user->name,
                    'created_at' => $comment->created_at->toIso8601String(),
                ])
            ),
            'due_date' => $this->due_date?->toDateString(),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'is_overdue' => $this->isOverdue(),
            'is_completed' => $this->isCompleted(),
            'is_blocked' => $this->isBlocked(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
