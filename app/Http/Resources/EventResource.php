<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'event_id' => $this->event_id,
            'event_name' => $this->event_name,
            'description' => $this->description,
            'event_date' => $this->event_date ? $this->event_date->format('Y-m-d H:i:s') : null,
            'location' => $this->location,
            'requires_registration' => $this->requires_registration,
            'registration_deadline' => $this->registration_deadline ? $this->registration_deadline->format('Y-m-d H:i:s') : null,
            // 'created_by_id' => $this->created_by_id, // قد لا يكون مهمًا للـ API
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // ---=== استخدام اسم العلاقة الجديد ===---
            'creator' => new UserResource($this->whenLoaded('creator')),
            // -------------------------------------

            // ---=== تضمين بيانات المسجلين إذا تم تحميلها ===---
            'registrations' => EventRegistrationResource::collection($this->whenLoaded('registrations')),
            // يمكنك إضافة عدد المسجلين إذا أردت (تحتاج لـ withCount في المتحكم)
            // 'registrations_count' => $this->when(isset($this->registrations_count), $this->registrations_count),
            // ----------------------------------------------
        ];
    }
}