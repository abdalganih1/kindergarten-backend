<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; // إذا كان document_path يحتاج لمعالجة URL

class HealthRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // ترجمة نوع السجل
         $recordTypeText = match ($this->record_type) {
            'Vaccination' => 'تطعيم',
            'Checkup' => 'فحص طبي',
            'Illness' => 'مرض/إصابة',
            'MedicationAdministered' => 'دواء تم إعطاؤه',
            default => $this->record_type
        };

        return [
            'record_id' => $this->record_id,
            'record_type' => $this->record_type, // القيمة الأصلية
            'record_type_text' => $recordTypeText, // النص المترجم
            'record_date' => $this->record_date ? \Carbon\Carbon::parse($this->record_date)->format('Y-m-d') : null,
            'details' => $this->details,
            'next_due_date' => $this->next_due_date ? \Carbon\Carbon::parse($this->next_due_date)->format('Y-m-d') : null,
            'document_url' => $this->document_path ? Storage::disk('public')->url($this->document_path) : null, // الرابط الكامل للمستند
            'entered_at' => $this->created_at->format('Y-m-d H:i:s'), // استخدام created_at

            // تضمين بيانات الطفل (إذا تم تحميلها) - معلومات أساسية
            'child' => new ChildResource($this->whenLoaded('child')), // سيعرض ChildResource الأساسي

            // تضمين بيانات من قام بالإدخال (إذا تم تحميلها) - معلومات أساسية
            'entered_by' => new UserResource($this->whenLoaded('enteredByUser')), // سيعرض UserResource الأساسي
        ];
    }
}