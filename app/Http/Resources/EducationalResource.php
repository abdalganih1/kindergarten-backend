<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'resource_id' => $this->resource_id,
            'title' => $this->title,
            'description' => $this->description,
            'resource_type' => $this->resource_type,
            'url_or_path' => $this->url_or_path,
            'target_age_min' => $this->target_age_min,
            'target_age_max' => $this->target_age_max,
            'subject' => $this->subject,
            // 'added_by_id' => $this->added_by_id, // قد لا يكون مهمًا للـ API
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null, // استخدام created_at
            // 'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // ---=== استخدام اسم العلاقة الجديد ===---
            'added_by_user' => new UserResource($this->whenLoaded('addedByUser')),
            // -------------------------------------
        ];
    }
}